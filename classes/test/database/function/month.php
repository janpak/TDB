<?php

	class Test_Database_Function_MONTH extends Test_Database_Function{
		public function execute(){
				$months = TDate::fill_months();

				return $months[rand(0,11)];

				

			}


	}

?>
