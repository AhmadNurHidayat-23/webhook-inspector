<?php
namespace App\Services;
use App\Models\WebhookRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
class WebhookReplay {
 public function replay(WebhookRequest $record):int {
  if($record->format==='hidden')throw ValidationException::withMessages(['target'=>'Payload non-terstruktur tidak dapat dikirim ulang.']);
  $base='http://127.0.0.1:'.(int)config('inspector.receiver_port');$challenge=bin2hex(random_bytes(16));
  try {
   $health=Http::withoutRedirecting()->timeout(3)->get($base.'/health',['challenge'=>$challenge]);
   $expected=hash_hmac('sha256',$challenge,config('app.key'));
   if(!$health->successful()||!hash_equals($expected,(string)$health->json('proof')))throw new \RuntimeException('Receiver tidak terverifikasi.');
   $response=Http::withoutRedirecting()->timeout(5)->withHeaders(['X-Inspector-Proof'=>hash_hmac('sha256',$record->body,config('app.key'))])->withBody($record->body,'application/json')->send($record->method,$base.'/receive');
   $record->update(['replay_status'=>$response->status(),'replay_at'=>now()]);return $response->status();
  }catch(\Throwable $e){throw ValidationException::withMessages(['target'=>'Penerima lokal belum tersedia atau tidak terverifikasi. Jalankan php artisan inspector:receiver.']);}
 }
}
