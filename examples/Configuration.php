<?php

	// Change address/port so that it points to your Redis dev server
	$data_store = new LoggedDataStore(new RedisDataStore("192.168.15.137", 6379));

?>