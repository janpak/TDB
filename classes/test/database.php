<?php
class Test_Database_Query extends Kohana_Database_Query{
		public function execute($db = null){
			$db  = Test_Database::instance()->execute($this); 

			return parent::execute($db);
		}

	}
	class Database_Query extends Test_Database_Query{}

	class Test_Database_Query_Builder_Select extends JP_Database_Query_Builder_Select{

		public function compile(Database $db){
			$db = Test_Database::instance()->getMockDatabase();

			return parent::compile($db);

		}

	}
	class Database_Query_Builder_Select extends Test_Database_Query_Builder_Select{}
	
	require_once Kohana::find_file('tests','kohana/DatabaseTest');

	class Test_Database extends Test_Database_Core{
		protected static $_phpunit = null;

		public static function instance(){
			
			if(parent::$current){
				self::$_phpunit = new Kohana_DatabaseTest;

			}

			return parent::instance();


		}

		public function getMockDatabase(){
				return self::$_phpunit->getMockDatabase();

		}

		private function _recurse_fields($query,&$fields){
			$from_prop = new ReflectionProperty($query,'_from');
			$from_prop->setAccessible( true ); 
			$froms = $from_prop->getValue($query);

			$rquery = new ReflectionClass($query);		
			$select_prop = new ReflectionProperty($query,'_select');
			$select_prop->setAccessible(true); 

			$fields = $select_prop->getValue($query);	

			foreach($froms as $from){
				if(is_array($from) && $from[0] instanceof Database_Query_Builder_Select){
					$this->_recurse_fields($from[0],$fields);
					return;
				}
				elseif(empty($fields)){
					$fields = array_keys(Test_Database_IO::fetch_schema(is_array($from)?$from[0]:$from));	


				}

			}

			$fields = $this->_prep_fields($fields);
		}

		private function _find_fields(Database_Query $query){
			$fields = array();
			$this->_recurse_fields($query,$fields);

			return $fields;

		}

		
		public function execute($query = array()){
				//if we are given a Database_Query_Builder Select we can determine the fields automatically using reflection
				if($query instanceof Database_Query_Builder_Select){
					$fields = $this->_find_fields($query);
					$this->_result = $this->_generate_rows($fields);

				}

				//if no result is set then throw an exception
				if($this->_result === null) throw new Kohana_Exception('Attempted to run a Test_Database without setting a result');

				//Mock a result specifically mocking as_array and first methods
				$result =  self::$_phpunit->getMockBuilder('Database_MySQL_Result',array('as_array','first'))
					->disableOriginalConstructor()
					->getMock();

				//when as_array() is called on $result return the $this->_result, this method maybe called any number of times
				$result->expects(self::$_phpunit->any())
					->method('as_array')
					->will(self::$_phpunit->returnValue(Result::factory($this->result())));

				//when first() return the first row in $this->_result, this method maybe called any number of times
				$result->expects(self::$_phpunit->any())
					->method('first')
					->will(self::$_phpunit->returnValue(Result::factory(current($this->result()))));

				//get the DummyDatabase Instance from Kohana_DatabaseTest
				$db = self::$_phpunit->getMockDatabase();

				//mock the escape method to return '' when called
				$db->expects(self::$_phpunit->any())
					->method('escape')
					->will(self::$_phpunit->returnValue(''));

				//mock the connect method to always return true, we are not actually connecting to anything so this is a safe assumption
				$db->expects(self::$_phpunit->any())
					->method('connect')
					->will(self::$_phpunit->returnValue(true));

				//mock the query method to return the mocked Database_MySQL_Result object, this object was mocked above to always return $this->_result
				//when as_array() is invoked on it
				$db->expects(self::$_phpunit->any())
					->method('query')
					->will(self::$_phpunit->returnValue($result));

				return $db;

			}



	}
