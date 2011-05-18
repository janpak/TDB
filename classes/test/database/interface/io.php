<?php
	interface Test_Database_Interface_IO{
			function cache_schemas();
			function cache_data();
			function fetch_data($field,$table = null);
			function fetch_schema($table);
			function fetch_table($table);


	}
