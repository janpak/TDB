<?php

class Test_Database_Function implements Test_Database_Interface_Function{
	protected $_name = null;
	protected $_args = null;

	public function __construct($name){
		$this->_name = $name;


	}

	public function get_name(){
		return $this->_name;

	}

	public static function factory($name,$args =null){
		$class = 'Test_Database_Function_'.$name;	
		if(class_exists($class)){
			return new $class($name);

		}

		throw new Kohana_Exception('Tried to construct the Test Database Function :name',array(':name' => $name));

	}

	public static function run($field){
		$cfunction =  Test_Database_Function::get_function($field);
		$args = $cfunction->parse_args($field);

		foreach($args as $index => $arg){
			if(Test_Database_Function::is_function($arg)){
				$args[$index] = self::run($arg,false);			

			}
			else{
				$args[$index] = $arg;

			}

		}

		$rfunction = new ReflectionClass($cfunction);

		if($rfunction->hasMethod('execute')){
			return $rfunction->getMethod('execute')->invokeArgs($cfunction,$args);	

		}

		throw new Kohana_Exception('No execute method given for :class',array(':class' => $function->get_name()));


	}

	public static function get_function($field){
		$pos = strpos($field,'(');
		$name = substr($field,0,$pos);
		$class = 'Test_Database_Function_'.$name;
		return new $class($name); 

	}

	public static function is_function($field){
		$result = preg_match('/\(.*\)$/',$field);

		return $result;

	}

	public function is_field($arg){
		return strpos('`',$arg);


	}

	public function clean_arg($arg){
		return preg_replace('/\'|\`/','',$arg);

	}

	public function clean_args(array $args = null){
		$args = $args === null? $this->_args:$args; 
		foreach($args as $index => $arg){
		 $args[$index] = $this->clean_arg($arg);

		}

		return $args;

	}

	public function parse_args($field){
		$this->_args = substr($field,strlen($this->_name)+1,strlen($field) - strlen($this->_name) -2);
		$this->_args = explode(',',$this->_args);

		return $this->_args;

	}

}
