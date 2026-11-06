<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{WebhookController,IngressController};
require __DIR__.'/auth.php';
Route::get('/',fn()=>redirect(auth()->check()?'/endpoints':'/login'));
Route::any('/hook/{uuid}',IngressController::class)->whereUuid('uuid');
Route::middleware('auth')->group(function(){Route::get('/endpoints',[WebhookController::class,'index']);Route::post('/endpoints',[WebhookController::class,'store']);Route::get('/endpoints/{endpoint}',[WebhookController::class,'show']);Route::post('/endpoints/{endpoint}/requests/{record}/replay',[WebhookController::class,'replay']);Route::post('/endpoints/{endpoint}/clear',[WebhookController::class,'clear']);});
