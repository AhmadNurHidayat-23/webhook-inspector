<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebhookEndpoint extends Model {
 protected $guarded=[];protected $hidden=['secret','token_hash'];
 protected function casts():array{return ['secret'=>'encrypted'];}
 public function requests(){return $this->hasMany(WebhookRequest::class);}
}
