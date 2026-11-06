<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
class InspectorReceiver extends Command {
 protected $signature='inspector:receiver';
 protected $description='Menjalankan penerima pengujian webhook lokal yang terverifikasi';
 public function handle():int {
  $port=(int)config('inspector.receiver_port');$this->info('Penerima khusus: http://127.0.0.1:'.$port.'/receive');
  $process=new Process([PHP_BINARY,'-S','127.0.0.1:'.$port,base_path('receiver/server.php')],base_path(),['INSPECTOR_RECEIVER_SECRET'=>config('app.key')]);
  $process->setTimeout(null);return $process->run(fn($type,$buffer)=>$this->output->write($buffer));
 }
}
