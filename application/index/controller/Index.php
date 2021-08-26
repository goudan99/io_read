<?php
namespace app\index\controller;
use Illuminate\Support\Collection;
use app\repositories\machine;

class Index
{
	 
    public function __construct($parameters = null, $options = null)
    {
    }
	
    public function index()
    {
	   $mac=new machine();
		
	  return json_encode($mac->getAllMachine(1,10));
    }
	
    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
	
}
