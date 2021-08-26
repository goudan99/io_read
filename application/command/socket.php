<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use PHPSocketIO\SocketIO;
use Workerman\Worker;
use \Workerman\Lib\Timer;
use app\repositories\machine;

class socket extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('socket');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
		$io = new SocketIO(2020);
		$io->on('workerStart', function () use ($io) {
		  $time_interval = 2.5;
		  Timer::add($time_interval, function() use ($io)
		  {
	        $mac=new machine();
			$io->emit('new_msg', json_encode($mac->getAllMachine(1,10)));//这里写了固定数据，请根据自己项目需求去做调整，不懂这里的可以看看官方文档，很清楚
		  });
		  
		});
		Worker::runAll();
    }
}
