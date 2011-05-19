<?php
	interface Test_Database_Interface_IO{
			function cache_schema(array $tables = null);
			function cache_data(array $tables = null);
			function fetch_data($field,$table = null);
			function fetch_schema($table);
			function fetch_table($table);


	}
