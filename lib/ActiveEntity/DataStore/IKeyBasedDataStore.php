<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	// Represents a key-based storage such as InMemory, Redis, MongoDb, etc.
	interface IKeyBasedDataStore {
		// Converter which converts values back and to the storage medium
		public function getTypeConverter();

		// Operations concerning lists
		public function listGet($key);
		public function listPush($key, $value);
		public function listRemoveValue($key, $value);
		public function listLength($key);

		// Operations concerning keys
		public function keyIncrement($key);
		public function keySet($key, $value);
		public function keyGet($key);
		public function keyRemove($key);
	}

?>