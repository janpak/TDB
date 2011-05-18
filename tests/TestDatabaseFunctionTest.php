<?php

class TestDatabaseFunctionTest extends PHPUnit_Framework_TestCase{
	public function setUp(){
		Test_Database_IO::instance()->schema(array('test_table' => array(
			'foobar' => 'varchar(20)',
			'foobar_date' => 'date',
			'foobar_int' => 'int'
		
		)));

		Test_Database_IO::instance()->data(
			array('test_table' => array(
				'foobar' => 'bazbam',
				'foobar_date' => 'date',
				'foobar_int' => 'int'
			
			)));


	}


	public function provider_run(){
		return array(
			array('SUM(`foobar_int`)','SUM'),
			array('MONTH(`foobar_date`)','MONTH'),
			array('YEAR(`foobar_date`)','YEAR'),
			array('GROUP_CONCAT(`foobar`)','GROUP_CONCAT'),
			array('DATE_FORMAT(`foobar_date`,\'%M\')','DATE_FORMAT',array('%M')),
			array('IF(`foobar` = \'MISC\',\'MISC\',\'foobar\'','IF'),
			array('CAST(GROUP_CONCAT(`foobar`))','CAST')

		);

	}

	/**
	 * test_run, tests Test_Database_Function::run()
	 *
	 * @dataProvider provider_run
	 *
	 * @param string
	 * @return void
	 */
	public function test_run($field,$func,array $args = array()){
		$result =Test_Database_Function::run($field);
		switch($func){
			case 'MONTH':
				$this->assertContains($result,TDate::fill_months());
				break;
			case 'YEAR':
				$this->assertContains($result,Tdate::fill_years(3));	
				break;
			case 'SUM':
				$this->assertInternalType('float',$result);
				$this->assertGreaterThan(0,$result);
				break;
			case 'GROUP_CONCAT':
				$this->assertInternalType('string',$result);
				$this->assertEquals('foobar',$result);
				break;
			case 'DATE_FORMAT':	
				$this->assertInternalType('string',$result);
				switch($args[0]){
				case '%M':
					$this->assertContains($result,TDate::fill_months());
					break;

				case '%Y':
					$this->assertContains($result,Tdate::fill_years(3));	
					break;

				}
				break;
			case 'IF':
				$this->assertInternalType('string',$result);
				$this->assertEquals('foobar',$result);
				break;
			case 'CAST':
				$this->assertEquals('foobar',$result);
				break;

		}

	}




}

?>
