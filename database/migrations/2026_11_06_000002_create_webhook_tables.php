<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('webhook_endpoints',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained();$t->uuid('uuid')->unique();$t->string('name',100);$t->text('secret');$t->string('token_hash',64);$t->unsignedSmallInteger('response_status')->default(202);$t->timestamps();});
  Schema::create('webhook_requests',function(Blueprint $t){$t->id();$t->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();$t->string('method',12);$t->json('headers');$t->json('query');$t->text('body');$t->string('format',30);$t->unsignedInteger('bytes');$t->unsignedSmallInteger('response_status');$t->unsignedSmallInteger('replay_status')->nullable();$t->timestamp('replay_at')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('webhook_requests');Schema::dropIfExists('webhook_endpoints');}
};
