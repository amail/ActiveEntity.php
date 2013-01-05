<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	// Redis specific data store
	class RedisDataStore implements IKeyBasedDataStore
	{
		protected $_connection = null;
		protected $_type_converter = null;

		public function __construct($ip_address = "127.0.0.1", $port = 6379){
			include_once("Predis.php");
            $this->_connection = new Predis\Client(sprintf("tcp://%s:%s", $ip_address, $port));
            $this->_type_converter = new RedisTypeConverter();
		}

		// Other...

		public function getTypeConverter(){
			return $this->_type_converter;
		}

		// List operations

		public function listGet($key){
            $command = $this->_connection->createCommand("lrange");
            $command->setArgumentsArray(array($key, 0, -1));
            return (array)$this->_connection->executeCommand($command);
		}

		public function listPush($key, $value){
            $command = $this->_connection->createCommand("lpush");
            $command->setArgumentsArray(array($key, $value));
            return (int)$this->_connection->executeCommand($command);
		}

		public function listRemoveValue($key, $value){
            $command = $this->_connection->createCommand("lrem");
            $command->setArgumentsArray(array($key, -1, $value));
            return (int)$this->_connection->executeCommand($command);
		}

		public function listLength($key){
            return (int)$this->_connection->llen($key);
		}

		// Key operations

		public function keyIncrement($key){
            return (int)$this->_connection->incr($key);
		}

		public function keySet($key, $value){
            return (bool)$this->_connection->set($key, $value);
		}

		public function keyGet($key){
            return $this->_connection->get($key);
		}

		public function keyRemove($key){
			return (int)$this->_connection->del($key);
		}
	}
	
?>