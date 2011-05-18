<?php

class TestDatabaseIOTest extends PHPUnit_Framework_TestCase{

	public function setUp(){
		
		Test_Database_IO::instance()->schema(array(
			'test_table' => array( 'foobar' => 'int(10) unsigned', 'foobaz' => 'varchar(20)'),
			'test_table2' => array( 'foobaz' => 'varchar(20)', 'gah' => 'int(11)')	
		));

	}



	public function provider_fetch_schema(){
		return array(
			array('test_table','foobar','int(10) unsigned'),
			array('test_table2','gah','int(11)'),
			array('test','bam','Kohana_Exception')


		);


	}

	/**
	 * test_fetch_schema, tests Test_Database_IO::fetch_schema()
	 *
	 * @dataProvider provider_fetch_schema
	 *
	 * @param string
	 * @param string
	 * @return void
	 */
	public function test_fetch_schema($table,$field,$expected){
		try{
			$schema =	Test_Database_IO::instance()->fetch_schema($table);

			$this->assertArrayHasKey($field,$schema);

			$this->assertSame($expected,$schema[$field]);
		}
		catch(Exception $e){
			$this->assertInstanceOf($expected,$e);


		}


	}


	public function provider_fetch_random_data(){
		Test_Database_IO::instance()->data(array(
			'test_table' => array(
				array('foobar' => 25, 'foobaz' => 'something'),
				array('foobar' => 72, 'foobaz' => 'abczxc')	
			),
			'test_table2' => array(
				array('foobaz' => 'something else','gah' => 12345)
			)
		));
		return array(
			array('foobar',null,1,array(25,72)),
			array('foobaz',null,1,array('something','abczxc')),
			array('gah',null,1,array(12345)),
			array('gaz',null,1,'Kohana_Exception'),
			array('gah','test_table',1,'Kohana_Exception'),
			array('foobar','test_table2',1,'Kohana_Exception'),
			array('foobar',null,2,array(72,25))

		);

	}

	/**
	 * test_fetch_data, tests Test_Database_IO::fetch_data()
	 *
	 * @dataProvider provider_fetch_random_data
	 *
	 * @param string
	 * @param string
	 * @param range
	 * @param mixed
	 * @return void
	 */
	public function test_fetch_random_data($field, $table,$range ,$expected){
		try{
			$value = Test_Database_IO::instance()->fetch_data($field,$table,$range);
			if(is_array($value)){
				foreach($value as $v){
					$this->assertContains($v,$expected);
				}
				$this->assertEquals($range,count($value));
			}
			else{
				$this->assertContains($value,$expected);
				$this->assertEquals($range,1);

			}

		}
		catch(Kohana_Exception $e){
			$this->assertInstanceOf($expected, $e);


		}


	}


}

?>
