<?php

	class Test_Database_Function_Max extends Test_Database_Function{

		public function execute($field){
				return Test_Database_IO::fetch_data($field);				


			}



	}


?>
