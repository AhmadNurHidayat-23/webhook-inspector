<?php
namespace App\Http\Controllers;
use App\Models\{WebhookEndpoint,WebhookRequest};
use App\Services\WebhookReplay;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
class WebhookController extends Controller {
 private function authorizeEndpoint(Request $request,WebhookEndpoint $endpoint):void{abort_unless($endpoint->user_id===$request->user()->id,403);}
 public function index(Request $request){return Inertia::render('App',['endpoints'=>WebhookEndpoint::where('user_id',$request->user()->id)->withCount('requests')->latest()->get()]);}
 public function store(Request $request){$data=$request->validate(['name'=>'required|string|max:100','response_status'=>['required','integer',Rule::in([200,201,202,204,400,422,500])]]);abort_if(WebhookEndpoint::where('user_id',$request->user()->id)->count()>=30,422,'Maksimal 30 endpoint per pengguna.');$secret=bin2hex(random_bytes(32));$endpoint=WebhookEndpoint::create([...$data,'user_id'=>$request->user()->id,'uuid'=>(string)Str::uuid(),'secret'=>$secret,'token_hash'=>hash('sha256',$secret)]);return redirect('/endpoints/'.$endpoint->id)->with('status','Endpoint dan token acak dibuat.');}
 public function show(Request $request,WebhookEndpoint $endpoint){
  $this->authorizeEndpoint($request,$endpoint);$data=$request->validate(['request'=>'nullable|integer']);
  $selected=isset($data['request'])?$endpoint->requests()->findOrFail($data['request']):$endpoint->requests()->latest('id')->first();
  return Inertia::render('App',['endpoint'=>$endpoint,'secret'=>$endpoint->secret,'records'=>$endpoint->requests()->latest('id')->paginate(20,['id','method','bytes','response_status','format','created_at'])->withQueryString(),'selected'=>$selected]);
 }
 public function replay(Request $request,WebhookEndpoint $endpoint,WebhookRequest $record,WebhookReplay $replay){$this->authorizeEndpoint($request,$endpoint);abort_unless($record->webhook_endpoint_id===$endpoint->id,404);$request->validate(['target'=>'required|in:local','url'=>'prohibited']);$code=$replay->replay($record);return back()->with('status','Payload yang telah diredaksi dikirim ulang. HTTP '.$code.'.');}
 public function clear(Request $request,WebhookEndpoint $endpoint){$this->authorizeEndpoint($request,$endpoint);$request->validate(['confirmation'=>'required|in:HAPUS']);$endpoint->requests()->delete();return redirect('/endpoints/'.$endpoint->id)->with('status','Riwayat request endpoint dihapus.');}
}
