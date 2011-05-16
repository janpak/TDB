<?php


	class Test_Database_FunctionStack{

		protected $_s = null;

		public function __construct(){
			$this->_s = new SplStack();


		}
		

		public function push($func,$args){
			$this->_s->push(array($func,$args));

			return $this;


		}


		public function pop(){
			return $this->_s->pop();

		}

		public function execute(){

			while(!$this->_s->isEmpty()){
				list($func,$args) = $this->pop();
				$func = 'Test_Database_Function_'.$func;
				//TODO::put reflection method invokeArgs here	

			}



		}

	}
