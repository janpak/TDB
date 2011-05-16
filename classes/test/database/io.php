<?php

	class Test_Database_IO implements Test_Database_Interface_IO{
		protected static $_data = array();
		protected static $_schemas = array();
		protected static $_database_path = null;
		protected static $_current = null;

		public static function instance(){

			if(!self::$_current){
				self::$_database_path = Kohana::$cache_dir.'/database';
				self::$_current = new Test_Database_IO;

			}

			return self::$_current;

		}

		public static function fetch_schema($table){
			$path = self::$_database_path.'/'.$table.'.schema';
			if(file_exists($path)){
				if(!isset(self::$_schemas[$table])){
					self::$_schemas[$table] = json_decode(file_get_contents($path),true);

				}

				return self::$_schemas[$table];
			}

			throw new Kohana_Exception(
				'The Schema file :file.schema can not be found, consider running the cache command',
				array(':file' => $table)
			);

		}

	 public static function fetch_table($field){
			$tables = Test_Database::config_tables();
			foreach($tables as $table){
				$schema = Test_Database_IO::fetch_schema($table);
				if(isset($schema[$field])){
					return $table; 

				}
			}

			throw new Kohana_Exception(
				'A table can not be found with the field :field', 
				array(':field' =>$field));

	 }


		public static function fetch_data($field, $table = null){
			$regex = '/^[a-z0-9]+\./';
			$field = preg_replace($regex,'',$field);
			$table = $table !== null?$table:self::fetch_table($field);
			$database_path = Kohana::$cache_dir.'/database';
			$data_path = $database_path.'/'.$table.'.data';
			if(!isset(self::$_data[$table]) && file_exists($data_path)){
				self::$_data[$table] = json_decode(file_get_contents($data_path),true);

			}

			return self::$_data[$table][rand(0,count(self::$_data[$table])-1)][$field];

			//throw new Kohana_Exception('The data file for :table does not exist',array(':table' => $table));

		}

		public static function cache_schemas(){
			$tables = Test_Database::config_tables();
			$database_path = Kohana::$cache_dir.'/database';
			if(!is_dir($database_path)){
					mkdir($database_path,0777,TRUE);	

			}
			foreach($tables as $table){
				$query = new Kohana_Database_Query(Database::SELECT,'DESCRIBE '.$table);
				$schema = $query->execute()->as_array()->data();
				$schema = json_encode(TArray::get_all_recursive(JP_Format::format_by($schema,'Field','distinct'),'Type'));
				$resource = fopen($database_path.'/'.$table.'.schema','w+');
				fwrite($resource,$schema);
				fclose($resource);
			}	
			
			return $this;
		}

		public static function cache_data(){
			$tables = Test_Database::config_tables();
			$database_path = Kohana::$cache_dir.'/database';
			if(!is_dir($database_path)){
					mkdir($database_path,0777,TRUE);	

			}

			foreach($tables as $table){
				$schema = Test_Database_IO::fetch_schema($table);
				if(isset($schema['ship_dt'])){
					$query = new Kohana_Database_Query(Database::SELECT,'SELECT * FROM '.$table.' ORDER BY ship_dt DESC LIMIT 1000');

				}
				else{
					$query = new Kohana_Database_Query(Database::SELECT,'SELECT * FROM '.$table.' LIMIT 1000');
					
				}
				$data = json_encode($query->execute()->as_array()->data());
				$resource = fopen($database_path.'/'.$table.'.data','w+');
				fwrite($resource,$data);
				fclose($resource);
			}	
			
			return $this;

		}

	}
