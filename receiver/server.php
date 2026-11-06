<?php
$secret=getenv('INSPECTOR_RECEIVER_SECRET');
if(!$secret){http_response_code(503);exit;}
header('Content-Type: application/json');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if($path==='/health') {echo json_encode(['proof'=>hash_hmac('sha256',(string)($_GET['challenge']??''),$secret)]);exit;}
if($path!=='/receive'){http_response_code(404);exit;}
if((int)($_SERVER['CONTENT_LENGTH']??0)>65536){http_response_code(413);exit;}
$body=file_get_contents('php://input',false,null,0,65537);
if(strlen($body)>65536||!hash_equals(hash_hmac('sha256',$body,$secret),$_SERVER['HTTP_X_INSPECTOR_PROOF']??'')){http_response_code(401);exit;}
http_response_code(202);echo json_encode(['received'=>true,'bytes'=>strlen($body),'method'=>$_SERVER['REQUEST_METHOD'],'sha256'=>hash('sha256',$body)]);
