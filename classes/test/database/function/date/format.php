<?php

class Test_Database_Function_DATE_FORMAT extends Test_Database_Function{

	public function execute($field,$format){
				$format = $this->clean_arg($format);
				switch($format){
					case '%M':
						return Test_Database_Function::factory('MONTH')->execute();

					case '%Y':
						return Test_Database_Function::factory('YEAR')->execute();

					default:
						throw new Kohana_Exception(
							'The format :format has not been implmented in :class::DATE_FORMAT()',
							array(':format' => $format, ':class' => __CLASS__)
						);

				}



			}


}

?>
