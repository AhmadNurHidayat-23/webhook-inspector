<?php
namespace App\Services;
class PayloadRedactor {
 public function redact(mixed $value,int $depth=0):mixed {
  if($depth>24)return '[REDACTED: kedalaman dibatasi]';
  if(is_array($value)) { $out=[];foreach($value as $key=>$item){$out[$key]=is_string($key)&&preg_match('/password|passwd|secret|token|auth|cookie|api.?key|signature|session|email|phone|address|ssn|nik|card.?number|cvv/i',$key)?'[REDACTED]':$this->redact($item,$depth+1);}return $out; }
  if(is_string($value))return preg_replace(['/Bearer\s+[A-Za-z0-9._~+\/-]+/i','/eyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+/'],['Bearer [REDACTED]','[REDACTED JWT]'],$value);
  return $value;
 }
 public function body(string $raw,string $type):array {
  if($raw==='')return ['empty',''];
  try {
   if(str_contains(strtolower($type),'json')){$data=json_decode($raw,true,32,JSON_THROW_ON_ERROR);return ['json',json_encode($this->redact($data),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE)];}
   if(str_contains(strtolower($type),'application/x-www-form-urlencoded')){parse_str($raw,$data);return ['form',json_encode($this->redact($data),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE)];}
  }catch(\JsonException){}
  return ['hidden','[Payload disembunyikan: gunakan JSON valid atau form URL-encoded. Data mentah tidak disimpan.]'];
 }
}
