<?php


	class Test_Database_Function_YEAR extends Test_Database_Function{
		public function execute(){
				$years = TDate::fill_years(3);

				return $years[rand(0,2)];

			}



	}

?>
