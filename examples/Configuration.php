<?php

	// Change address/port so that it points to your Redis dev server
	$data_store = new LoggedDataStore(new RedisDataStore("localhost", 6379));

?>