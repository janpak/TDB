<?php
	interface Test_Database_Interface_IO{
			static function cache_schemas();
			static function cache_data();
			static function fetch_data($field,$table = null);
			static function fetch_schema($table);
			static function fetch_table($table);


	}
