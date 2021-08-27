<?php

namespace app\repositories;
use Illuminate\Support\Collection;

class machine
{
	private $client=null;
		
    public function __construct()
    {
      $this->client = new \Predis\Client([
        'scheme' => 'tcp',
        'host'   => '106.53.8.100',
        'port'   => 6379,
        'password'   => 'blue',
      ]);
    }
	
	
    public function getAllMachine($page=0,$limit=0)
    {
	  
	  $j=0;
	  
	  $result = [];
	  
	  
	  $keys=$this->getKeys();
	  
	  $limit&&$page?$keys=$keys->forpage($page,$limit):'';
	  
	  $keys=$keys->toArray();
	  
	  foreach($keys as $item)
	  {
		$j++;
		
		$item["isMaterial"]=$this->client->get($item["cnc1"]."/materials_status");
		
		$item["isAlarm"]=$this->client->get($item["cnc1"]."/redFlash");
		
		$item["isYellow"]=$this->client->get($item["cnc1"]."/yellowFlash");
		
		$item["isRed"]=$this->client->get($item["cnc1"]."/redFlash");
		
		$item["finished_time"]=$this->client->get($item["cnc1"]."/finished_time");
		
		$item["ct_time"]=$this->client->get($item["cnc1"]."/ct_time");
		
		$item["countdown"]=$this->client->get($item["cnc1"]."/countdown");
		
		$bits = unpack("C4",$this->client->get($item["cnc1"]."/io_read"));

		$item["io_read"]=$this->fromate32($bits);
		
		$item["isOneline"]=$item["io_read"][0][1];
		
		$item["isMachining"]=$item["io_read"][0][12];
		
		$item["isGreen"]=$item["io_read"][1][12];

		$result[]=$item;
	  }
	  
	  return $result; 
    }
	
    private function fromate($num)
	{
		$str=base_convert($num,10,2);
		
		for($i=strlen($str);$i<8;$i++){
			$str='0'.$str;
		}
		return $str; 
		//print_r($num);
	}
	
    private function fromate32($num)
	{
        $str  ="";
		foreach($num as $key=>$item){
			
			$str = $str.$this->fromate($item);
			
			if($key==2){$str=$str.",";}
			
		}
		
		return explode(",",$str);
	}
	
    private function getKeys(){
		
	  $data = [];
	  
      $keys=$this->client->keys('*');
	  
	  foreach($keys as $item)
	  {

		$arr= explode("/",$item);
		
        if(count($arr)<4){continue;}
		
		if($arr[1]==""){continue;}
		
		if($arr[3]!="io_read"){continue;}
		
		$data[] = [
		  "workshop"=>$arr[1],
		  
		  "productionLine"=>substr($arr[2], 1,1), 
		  
		  "cnc"=>substr($arr[2], 2), 

		  "cnc1"=>implode("/",[$arr[0],$arr[1],$arr[2]]),
		  
		  "isOneline"=>0,
		  
          "isAlarm"=>0,
		  
          "isMaterial"=>0, 	
		  
          "isMachining"=>0, 	
		  
		  "io_read"=>[]
		];
	
      }

	  $collect = new Collection($data);

	  return $collect->sortBy(function($item) {
		return $item['workshop'].$item['productionLine'].($item['cnc']<10?'0'.$item['cnc']:$item['cnc']);
      });
	}
	
}
