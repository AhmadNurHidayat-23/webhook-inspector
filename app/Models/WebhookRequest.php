<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebhookRequest extends Model {
 protected $guarded=[];
 protected function casts():array{return ['headers'=>'array','query'=>'array','replay_at'=>'datetime'];}
}
