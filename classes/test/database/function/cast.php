<?php

	class Test_Database_Function_CAST extends Test_Database_Function{

		public function parse_args($field){
			$this->_args = explode(',',preg_replace('/^CAST\((.*) AS (.*)\)$/','$1,$2',$field));			

			return $this->_args;
		}


		public function execute($field,$as){
				
				return 'foobar';
		}

	}
