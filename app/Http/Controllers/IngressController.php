<?php
namespace App\Http\Controllers;
use App\Models\WebhookEndpoint;
use App\Services\PayloadRedactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
class IngressController extends Controller {
 public function __invoke(Request $request,string $uuid,PayloadRedactor $redactor) {
  $endpoint=WebhookEndpoint::where('uuid',$uuid)->firstOrFail();$key='hook:'.$uuid.':'.$request->ip();
  abort_if(RateLimiter::tooManyAttempts($key,config('inspector.request_limit')),429,'Batas frekuensi request tercapai.');RateLimiter::hit($key,60);
  abort_unless(hash_equals($endpoint->token_hash,hash('sha256',(string)$request->header('X-Webhook-Token'))),401,'Token webhook tidak valid.');
  abort_if((int)$request->header('Content-Length',0)>config('inspector.max_payload'),413);
  $raw=(string)$request->attributes->get('inspector_raw','');abort_if(strlen($raw)>config('inspector.max_payload'),413);
  abort_if(strlen(json_encode($request->headers->all()))>16384,431);
  abort_if($endpoint->requests()->count()>=config('inspector.max_history'),429,'Riwayat penuh. Pemilik harus menghapus riwayat terlebih dahulu.');
  [$format,$body]=$redactor->body($raw,$request->header('Content-Type',''));
  $endpoint->requests()->create(['method'=>$request->method(),'headers'=>$redactor->redact($request->headers->all()),'query'=>$redactor->redact($request->query()),'body'=>$body,'format'=>$format,'bytes'=>strlen($raw),'response_status'=>$endpoint->response_status]);
  return $endpoint->response_status===204?response('',204):response()->json(['received'=>true],$endpoint->response_status);
 }
}
