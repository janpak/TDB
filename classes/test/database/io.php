<?php

	class Test_Database_IO implements Test_Database_Interface_IO{
		protected static $_data = array();
		protected static $_schemas = array();
		protected static $_database_path = null;
		protected static $_current = null;

		/**
		 * instance, returns the Test_Database_IO instance, sets up the $_database_path
		 *
		 * @return Test_Database_IO
		 */
		public static function instance(){

			if(!self::$_current){
				self::$_database_path = Kohana::$cache_dir.'/database/';
				if(!is_dir(self::$_database_path)){
					mkdir($database_path,0777,TRUE);	

				}
				self::$_current = new Test_Database_IO;

			}

			return self::$_current;

		}

		/**
		 * schema, get or set the schema
		 *
		 * @param array
		 * @return Test_Database_IO|array
		 */
		public function schema($schema = null){
			if($schema === null){
				return $schema;

			}

			self::$_schemas = $schema;

			return $this;

		}

		/**
		 * data, get or set the data
		 *
		 * @param array
		 * @return Test_Database_IO|array
		 */
		public function data($data = null){
			if($data === null){
				return self::$_data;

			}
	
			self::$_data = $data;

			return $this;

		}

		/**
		 * fetch_schema, fetches a schema from the cache or file
		 * returns a key => value store of fields => types
		 *
		 * @param string
		 * @return array
		 */
		public function fetch_schema($table){
			$path = self::$_database_path.$table.'.schema';

			if(isset(self::$_schemas[$table])){
				return self::$_schemas[$table];

			}
			else if(file_exists($path)){
					self::$_schemas[$table] = json_decode(file_get_contents($path),true);

				return self::$_schemas[$table];
	
			}

			throw new Kohana_Exception(
				'The Schema file :file can not be found, consider running the cache command',
				array(':file' => $path)
			);

		}

		/**
		 * fetch_table, fetches a table given a field
		 * finds the first table that has the given field
		 *
		 *
		 * @param string
		 * @return string
		 */
	 public function fetch_table($field){
			$tables =empty(self::$_schemas)? Test_Database::config_tables():array_keys(self::$_schemas);
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

		private function _fetch_value($table,$row,$field){
			if(isset(self::$_data[$table][$row][$field])){
				return self::$_data[$table][$row][$field];	
				
			};	

			throw new Kohana_Exception('The field: :field does not exist in table: :table and row: :row',array(':field' => $field,':table' => $table, ':row' => $row));

		}

		protected function _fill_range($table,$row_index = null,$field,$range = 1){
				$total_rows = count(self::$_data[$table])-1;
				$row_index = rand(0,$total_rows);
				if($range > 1){
					$values = array();
					for($i = 0; $i < $range;$i++){
						$values[] = $this->_fetch_value($table,$row_index,$field); 

						$row_index = rand(0,$total_rows);
					}

					return $values;
				}

				return $this->_fetch_value($table,$row_index,$field);


		}

		/**
		 * fetch_data, fetches a random data row given a field and optional table, if no table is given the method will do a fetch table to find a table
		 *
		 * @param string
		 * @param string
		 * @param int
		 * @return mixed 
		 */
		public function fetch_data($field, $table = null,$range = 1){
			$field = preg_replace('/^[a-z0-9]+\./','',$field);

			//get the table if none is given
			$table = $table !== null?$table:self::fetch_table($field);

			$data_path = self::$_database_path.$table.'.data';

			//this if is structured like this to avoid a file io if possible
			if(isset(self::$_data[$table])){
				return $this->_fill_range($table,null,$field,$range);	
			}
			elseif(file_exists($data_path)){
				//get the data file off the file system
				self::$_data[$table] = json_decode(file_get_contents($data_path),true);

				return $this->_fill_range($table,null,$field,$range);	
			}

			throw new Kohana_Exception(
				'The data file can not be found :path', 
				array(':path' =>$data_path));


		}

		/**
		 * cache_schemas, for all the tables given in the config file, runs a DESCRIBE on the database and dumps the schema and writes the result to a .schema file
		 *
		 * @return void
		 */
		public function cache_schemas(){
			$tables = Test_Database::config_tables();
			
			foreach($tables as $table){
				$query = new Kohana_Database_Query(Database::SELECT,'DESCRIBE '.$table);
				$schema = $query->execute()->as_array()->data();
				$schema = json_encode(TArray::get_all_recursive(JP_Format::format_by($schema,'Field','distinct'),'Type'));
				$filename = self::$_database_path.$table.'.schema';
				$resource = fopen($filename,'w+');
				fwrite($resource,$schema);
				fclose($resource);
				chmod($filename,0777);
			}	
			
		}

		/**
		 * cache_data, for each of the tables in the config file, runs a SELECT * FROM $table LIMIT 1000 and write the result to a .data file
		 *
		 * @return void
		 */
		public function cache_data(){
			$tables = Test_Database::config_tables();

			foreach($tables as $table){
				$schema = Test_Database_IO::fetch_schema($table);
				if(isset($schema['ship_dt'])){
					$query = new Kohana_Database_Query(Database::SELECT,'SELECT * FROM '.$table.' ORDER BY ship_dt DESC LIMIT 1000');

				}
				else{
					$query = new Kohana_Database_Query(Database::SELECT,'SELECT * FROM '.$table.' LIMIT 1000');
					
				}

				$data = json_encode($query->execute()->as_array()->data());
				$filename = self::$_database_path.$table.'.data';
				$resource = fopen($filename,'w+');
				fwrite($resource,$data);
				fclose($resource);
				chmod($filename,0777);
			}	

		}

	}
