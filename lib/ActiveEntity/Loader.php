<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	$neccessary_includes = array(
		"Logging" => array(
			"ILogger.php",
			"MemoryLogger.php"
		),
		"DataStore" => array(
			"ITypeConverter.php",
			"IKeyBasedDataStore.php",
			"DataStoreBase.php",
			"EntityDataStore.php",
			"CollectionDataStore.php",
			"LoggedDataStore.php",
			"Redis" => array(
				"RedisTypeConverter.php",
				"RedisDataStore.php"
			)
		),
		"Entity" => array(
			"EntityBase.php",
			"EntityAccessException.php",
			"EntityCollection.php",
			"Entity.php"
		)
	);

	function includeFiles($files, $base = ''){
		foreach($files as $key => $value){
			if(is_array($value)){
				includeFiles($value, $base . "/" . $key);
			}else{
				include_once($base == '' ? $value : sprintf("%s/%s", $base, $value));
			}
		}
	}

	includeFiles($neccessary_includes);

?>