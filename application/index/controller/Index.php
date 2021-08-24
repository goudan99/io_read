<?php
namespace app\index\controller;
use Illuminate\Support\Collection;

class Index
{
	 
	private $client=null;

    public function __construct($parameters = null, $options = null)
    {
      $this->client = new \Predis\Client([
        'scheme' => 'tcp',
        'host'   => '106.53.8.100',
        'port'   => 6379,
        'password'   => 'blue',
      ]);
	  
    }
	
    public function index()
    {
		
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",0,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",1,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",2,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",3,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",4,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",5,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",6,0b0);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",7,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",8,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",9,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",10,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",11,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",12,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",30,0b1);
	  $rest=$this->client->SETBIT("/workshop1/1A1/io_read",31,0b1);
	  
	  $bits = $this->client->get("/workshop1/1A1/io_read");
	  
	  $ch = unpack("C4", $bits);
	  
	  $str= $this->fromate32($ch);
	  
	  //print_r($str);
	  //print_r($str[7]);
	  
	  return json_encode($this->getPageData(1,10));
    }

	/*** 获取redis中的所有数据
	** @params page 为当前页数
	** @params limit 为一页大小
	** @return array 所有数据
	***/
    public function getPageData($page,$limit)
    {
	  
	  $j=0;
	  
	  $result = [];
	  
	  $a=$this->getKeys()->forpage($page,$limit)->toArray();
	  
 
	  foreach($a as $item)
	  {
		$j++;
		
		$item["isMaterial"]=$this->client->get($item["cnc1"]."/materials_status");
		
		$item["isAlarm"]=$this->client->get($item["cnc1"]."/redFlash");
		
		$bits = unpack("C4",$this->client->get($item["cnc1"]."/io_read"));

		$item["io_read"]=$this->fromate32($bits);
		
		//$item["isOneline"]=$this->client->GETBIT($item["cnc1"]."/io_read",1);
		$item["isOneline"]=$item["io_read"][0][1];

		//$item["isMachining"]=$this->client->GETBIT($item["cnc1"]."/io_read",12);
		$item["isMachining"]=$item["io_read"][0][12];
		
		$result[]=$item;
	  }
	  
	  return $result; 
    }
    public function fromate($num)
	{
		$str=base_convert($num,10,2);
		
		for($i=strlen($str);$i<8;$i++){
			$str='0'.$str;
		}
		return $str; 
		//print_r($num);
	}
	
    public function fromate32($num)
	{
        $str  ="";
		foreach($num as $key=>$item){
			
			$str = $str.$this->fromate($item);
			
			if($key==2){$str=$str.",";}
			
		}
		
		return explode(",",$str);
	}
	
    public function getKeys(){
		
	  $data = [];
	  
      $keys=$this->client->keys('*');
	  
	  foreach($keys as $item)
	  {

		$arr= explode("/",$item);
		
        if(count($arr)<4){continue;}
		
		if($arr[1]==""){continue;}
		
		if($arr[3]!="io_read"){continue;}
		
		$arr1 = [
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
		
		$data[] = $arr1;
      }

	  $collect = new Collection($data);

	  return $collect->sortBy(function($item) {
                return $item['workshop'].$item['productionLine'].($item['cnc']<10?'0'.$item['cnc']:$item['cnc']);
            });
	}
	
    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
	
}
