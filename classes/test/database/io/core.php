<?php

	abstract	class Test_Database_IO_Core implements Test_Database_Interface_IO{
		private  $_database_path = null;

		public function clean_up(){

			$dir = new DirectoryIterator($this->_database_path);

			foreach($dir as $file){
				$dir->isFile()?unlink($this->_database_path.$file):null;

			}

			rmdir($this->_database_path);


		}




		public function database_path($path = null){

			if($path === null){
				return $this->_database_path;

			}

			$this->_database_path = preg_match('/^\/{0,1}(\((.*)\)\/)+$/',$path)?$path:$path.'/';

			if(!is_dir($this->_database_path)){
					mkdir($this->_database_path,0777,TRUE);	

			}

			return $this;

		}




	}


?>
