<?php
namespace Tests\Feature;
use App\Models\{User,WebhookEndpoint};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
class WebhooksTest extends TestCase {
 use RefreshDatabase;
 private function endpoint():WebhookEndpoint{return WebhookEndpoint::create(['user_id'=>User::factory()->create()->id,'uuid'=>(string)\Illuminate\Support\Str::uuid(),'name'=>'Endpoint uji','secret'=>str_repeat('a',64),'token_hash'=>hash('sha256',str_repeat('a',64)),'response_status'=>202]);}
 private function send(WebhookEndpoint $e,array $body=[]){return $this->withHeaders(['X-Webhook-Token'=>str_repeat('a',64),'Authorization'=>'Bearer confidential'])->postJson('/hook/'.$e->uuid,$body);}
 public function test_receives_real_request_fields_and_redacts_sensitive_data():void{$e=$this->endpoint();$this->send($e,['event'=>'created','password'=>'never-store','nested'=>['api_key'=>'private-key']])->assertStatus(202);$r=$e->requests()->firstOrFail();$this->assertSame('POST',$r->method);$this->assertStringContainsString('created',$r->body);$this->assertStringNotContainsString('never-store',$r->body);$this->assertStringNotContainsString('private-key',$r->body);$this->assertStringNotContainsString('confidential',json_encode($r->headers));$this->assertSame(202,$r->response_status);}
 public function test_token_is_required_and_not_stored_in_plaintext():void{$e=$this->endpoint();$this->postJson('/hook/'.$e->uuid,['x'=>1])->assertUnauthorized();$this->assertDatabaseCount('webhook_requests',0);$this->assertNotSame(str_repeat('a',64),$e->getRawOriginal('secret'));}
 public function test_payload_size_and_request_frequency_are_limited():void{$e=$this->endpoint();config(['inspector.request_limit'=>2]);$this->send($e,['x'=>str_repeat('x',65537)])->assertStatus(413);$this->send($e,['x'=>1])->assertStatus(202);$this->send($e,['x'=>2])->assertStatus(429);$this->assertDatabaseCount('webhook_requests',1);}
 public function test_other_user_cannot_inspect_or_clear_history():void{$e=$this->endpoint();$this->actingAs(User::factory()->create())->get('/endpoints/'.$e->id)->assertForbidden();$this->post('/endpoints/'.$e->id.'/clear',['confirmation'=>'HAPUS'])->assertForbidden();}
 public function test_deletion_requires_explicit_confirmation():void{$e=$this->endpoint();$this->send($e);$this->actingAs(User::find($e->user_id))->post('/endpoints/'.$e->id.'/clear')->assertSessionHasErrors('confirmation');$this->assertDatabaseCount('webhook_requests',1);$this->post('/endpoints/'.$e->id.'/clear',['confirmation'=>'HAPUS'])->assertRedirect();$this->assertDatabaseCount('webhook_requests',0);}
 public function test_replay_rejects_arbitrary_urls_and_unverified_receiver():void{$e=$this->endpoint();$this->send($e);$id=$e->requests()->first()->id;Http::fake(['*'=>Http::response(['proof'=>'invalid'])]);$this->actingAs(User::find($e->user_id))->post('/endpoints/'.$e->id.'/requests/'.$id.'/replay',['target'=>'local','url'=>'http://169.254.169.254'])->assertSessionHasErrors('url');Http::assertNothingSent();$this->post('/endpoints/'.$e->id.'/requests/'.$id.'/replay',['target'=>'local'])->assertSessionHasErrors('target');}
 public function test_replay_uses_verified_fixed_target_and_records_response():void{
  $e=$this->endpoint();$this->send($e,['event'=>'test','token'=>'private']);$id=$e->requests()->first()->id;
  Http::fake(function($request){if(str_contains($request->url(),'/health')){parse_str(parse_url($request->url(),PHP_URL_QUERY),$q);return Http::response(['proof'=>hash_hmac('sha256',$q['challenge'],config('app.key'))]);}return Http::response(['received'=>true],202);});
  $this->actingAs(User::find($e->user_id))->post('/endpoints/'.$e->id.'/requests/'.$id.'/replay',['target'=>'local'])->assertRedirect();$this->assertDatabaseHas('webhook_requests',['id'=>$id,'replay_status'=>202]);Http::assertSent(fn($request)=>$request->url()==='http://127.0.0.1:48112/receive'&&!str_contains($request->body(),'private'));
 }
 public function test_non_structured_body_is_not_persisted_raw():void{$e=$this->endpoint();$this->call('POST','/hook/'.$e->uuid,[],[],[],['HTTP_X_WEBHOOK_TOKEN'=>str_repeat('a',64),'CONTENT_TYPE'=>'text/plain'],'raw-private-value')->assertStatus(202);$this->assertStringNotContainsString('raw-private-value',$e->requests()->first()->body);}
}
