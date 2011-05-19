<?php

class TestDatabaseIOTest extends PHPUnit_Framework_TestCase{

	public function setUp(){
		
		Test_Database_IO::instance()->schema(array(
			'test_table' => array( 'foobar' => 'int(10) unsigned', 'foobaz' => 'varchar(20)'),
			'test_table2' => array( 'foobaz' => 'varchar(20)', 'gah' => 'int(11)')	
		));

	}

	public function mock_test_io(){
		return	$this->getMock('Test_Database_IO',array('_database_data','_describe','_database_rows','_database_connect','_database_close'));

	}

	public function provider_clean_up(){
		return array(
			array('testdir1'),
			array('testdir2')

			);

	}

	/**
	 * test_clean_up, tests Test_Database_IO_Core::clean_up()
	 *
	 * @dataProvider provider_clean_up
	 *
	 * @param string
	 * @return void
	 */
	public function test_clean_up($path){
			$io = new Test_Database_IO;
			$io->database_path($path);

			$io->clean_up();

			$this->assertFalse(is_dir($path));


	}

	public function provider_database_path(){
		return array(
			array('testdir1'),
			array('testdir2')

			);

	}

	/**
	 * test_database_path, tests Test_Database_IO_Core::database_path()
	 * also tests clean_up, but created a seperate test for clean up also above
	 *
	 * @dataProvider provider_database_path
	 *
	 * @param string
	 * @return void
	 */
	public function test_database_path($path){
			$io = new Test_Database_IO;
			$io->database_path($path);

			$this->assertTrue(is_dir($path));

			$io->clean_up();

			$this->assertFalse(is_dir($path));
		}


	public function provider_cache_schema(){

		return array(
			array(
				array('test'),
				array('test' => array(
					array('Field'=> 'some','Type' =>  'int(10)'),
					array('Field' => 'wierd', 'Type' => 'varchar(20)'),
					array('Field' => 'schema','Type' => 'date'))
				),
				array('test' => array('some' => 'int(10)', 'wierd' => 'varchar(20)','schema' => 'date'))
			)

		);


	}
	

	/**
	 * test_cache_schema, tests Test_Database_IO::cache_schema()
	 *
	 * @dataProvider provider_cache_schema
	 *
	 * @param string
	 * @param array
	 * @return void
	 */
	public function test_cache_schema(array $tables, array $schemas,array $expected){
		$io = $this->mock_test_io(); 

		foreach($tables as $table){
			$io->expects($this->any())
				->method('_describe')
				->will($this->returnValue($schemas[$table]));

			$io->database_path('test-database')->cache_schema(array($table));

			$filepath = $io->database_path().$table.'.schema';

			$this->assertFileExists($filepath);

			$this->assertSame($expected[$table],json_decode(file_get_contents($filepath),true));


		}

		$io->clean_up();

	}


	public function provider_cache_data(){

		return array(
			array(
				array('test'),
				array('test' => array(
					array('test' => 'foo', 'test2' => 'bar'),
					array('test3' => 'foo', 'test4' => 'bar')
				))
			)
		);


	}

	/**
	 * test_cache_data, tests Test_Database_IO::cache_data()
	 *
	 * @dataProvider provider_cache_data
	 *
	 * @param array
	 * @return void
	 */
	public function test_cache_data($tables,$expected){
		$io = $this->mock_test_io();

		foreach($tables as $table){
			$io->expects($this->any())
				->method('_database_data')
				->will($this->returnValue($expected[$table]));

			$io->database_path('test-database')->cache_data(array($table));

			$filename = $io->database_path().$table.'.data';
			$this->assertFileExists($filename);

			$this->assertSame($expected[$table],json_decode(file_get_contents($filename),true));


		}

		$io->clean_up();

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
