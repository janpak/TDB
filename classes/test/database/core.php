<?php
/**
 * Test_Database, an in-memory database that mimics the behavior of a real database
 * 
 * The goal is to serve data that allows the developer to test algorithmic behavior, not algorithmic correctness
 *
 * 
 * @author jshaw
 * @copyright 2011
 */
class Test_Database_Core implements Test_Database_Interface_IO{
	protected $_result = array();
	protected  $_config = array();
	public static $current =null;
	protected $_env = 'test';


	public function env($env = null){
		if($env === null){
			return $this->_env;

		}

		$this->_env =$env;

		return $this;


	}

	/**
	 * cache_schemas
	 *
	 * @see Test_Database_IO::cache_schemas
	 */
	public static function cache_schemas(){
		return Test_Database_IO::cache_schemas();

	}

	/**
	 * cache_data
	 *
	 * @see Test_Database_IO::cache_data
	 */
	public static function cache_data(){
		return Test_Database_IO::cache_data();

	}

	/**
	 * fetch_data
	 *
	 * @see Test_Database_IO::fetch_data
	 */
	public static function fetch_data($field,$table = null){
		return Test_Database_IO::fetch_data($field,$table);

	}

	/**
	 * fetch_schema
	 *
	 * @see Test_Database_IO::fetch_schema
	 */
	public static function fetch_schema($table){
		return Test_Database_IO::fetch_schema($table);

	}

	/**
	 * fetch_table
	 *
	 * @see Test_Database_IO::fetch_table
	 */
	public static function fetch_table($table){
		return Test_Database_IO::fetch_table($table);

	}

	public static function config_database($name = 'production'){
		return Kohana::config('database')->$name;

	}


	public static function config_tables(){
		return Kohana::config('database_test')->tables;

	}

	/**
	 * _prep_fields, puts the given field set into the format of array($alias => $field)
	 * also removes table alias prefixes
	 *
	 * Example:
	 * 	Given: array(array('SUM(`foo`)','bar'),'gah')
	 * 	Returns: array('bar' => 'SUM(`foo`)','gah' => 'gah')
	 *
	 * @param array
	 * @return array
	 */
	protected function _prep_fields($fields){
		$formatted_fields= array();
		$regex = '/^[a-z0-9]+\./';
		foreach($fields as $field){
			$formatted_fields[preg_replace($regex,'',is_array($field)?$field[1]:$field)] = 
				preg_replace($regex,'',is_array($field)?$field[0]:$field);

		}

		return $formatted_fields;

	}




	public function type($table,$field){
		if(Test_Database_Function::is_function($field)){
			return 'function';

		}
		else{
			$schema = Test_Database_IO::fetch_schema($table);
			$type = $schema[$field];
			$type = self::_remove_junk($type);	
			return $type;
		}

	}

	private static function _remove_junk($type){
		$regex = '/(\([0-9]+\)){1}[a-z\s]*$/';
		if(preg_match($regex,$type)){
			$type =preg_replace($regex,'',$type);
		}

		return $type;

	}


	public static function instance($env = null){
		if(!self::$current){
			self::$current = new Test_Database();
			Test_Database_IO::instance();

		}

		if($env !== null){
			self::$current->env($env);
		}

		return self::$current;

	}


	/**
	 * _generate_row, generates a value for each field in $fields, if the field is a function it runs a pseudo-function and returns a pseudo value
	 * that the calling environment should respect as valid
	 *
	 * Note: fields must be in array(alias => field) format
	 *
	 * Example:
	 * 	Given: array('total' => 'SUM(`price`)','month' => 'MONTH(`ship_dt`)','cus_name' => 'cus_name')
	 * 	Returns: array( 'total' => 100,'month' => 'January', 'cus_name' => 'foobar')
	 *
	 * @param array
	 * @return array
	 */
	protected function _generate_row($fields){
		$row = array();
		foreach($fields as $alias =>$field){
			$row[$alias] = Test_Database_Function::is_function($field)? Test_Database_Function::run($field):Test_Database_IO::fetch_data($field);

		}
		return $row;

	}


	/**
	 * _generate_rows, given a set of fields returns a random number of rows with the field values filled in
	 *
	 * Example:
	 * 	Given: array('total' => 'SUM(`price`)','month' => 'MONTH(`ship_dt`)','cus_name' => 'cus_name')
	 * 	Returns: array(
	 * 						array( 'total' => 100,'month' => 'January', 'cus_name' => 'foobar')
	 *						...
	 *
	 * 					)
	 * 
	 * @param array
	 * @return array
	 */
	public function _generate_rows($fields){
		$total_rows = rand(0,500);
		//todo: do a field check here
		$rows = array();
		for($i = 0;$i< $total_rows;$i++){
			$rows[] = $this->_generate_row($fields);

		}

		return $rows;
	}



	/**
	 * execute(), pseudo executes the given query, meaning it determines the indexes of the given query, and generates a appropriate result 
	 * additionally sets up mock classes to work with the above shunts
	 * 
	 * @param Database_Query
	 * @return Test_Database
	 */
	public function execute($fields = array()){
		$this->_result = $this->_generate_rows($fields);

		return $this->_result;

	}


	/**
	 * result(), gets or sets the result
	 *
	 * @param mixed
	 * @return array
	 */
	public function result($result = null){
		if($result){
			$this->_result = $result;
			return $this;
		}

		return $this->_result;

	}

}

?>
