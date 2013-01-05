<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	// Data store that acts as a proxy intercepting all commands to the data store and logging them to the provided logger.
	class LoggedDataStore implements IKeyBasedDataStore
	{
		protected $_data_store = null;
		protected $_logger = null;

		public function __construct(IKeyBasedDataStore $data_store, ILogger $logger = null){
			if($logger == null){
            	$logger = new MemoryLogger();	
            }

            $this->_data_store = $data_store;
			$this->_logger = $logger;
		}

		public function getLogger(){
			return $this->_logger;
		}

		// Other...

		public function getTypeConverter(){
			return $this->_data_store->getTypeConverter();
		}

		// Log list operations

		public function listGet($key){
			$result = $this->_data_store->listGet($key);
			$this->log("Getting list '%s', result = '%s'", $key, json_encode($result));
			return $result;
		}

		public function listPush($key, $value){
			$result = $this->_data_store->listPush($key, $value);
			$this->log("Pushing value '%s' to list '%s', result = '%s'", $value, $key, $result);
			return $result;
		}

		public function listRemoveValue($key, $value){
			$result = $this->_data_store->listRemoveValue($key, $value);
			$this->log("Removing value '%s' from list '%s', result = '%s'", $value, $key, $result);
			return $result;
		}

		public function listLength($key){
			$result = $this->_data_store->listLength($key);
			$this->log("Getting length of list '%s', result = '%s'", $key, $result);
			return $result;
		}

		// Log key operations

		public function keyIncrement($key){
			$result = $this->_data_store->keyIncrement($key);
			$this->log("Incrementing key '%s', result = '%s'", $key, $result);
			return $result;
		}

		public function keySet($key, $value){
			$result = $this->_data_store->keySet($key, $value);
			$this->log("Setting key '%s' with value '%s', result '%s'", $key, $value, $result);
			return $result;
		}

		public function keyGet($key){
            $result = $this->_data_store->keyGet($key);
			$this->log("Getting key '%s', result '%s'", $key, $result);
			return $result;
		}

		public function keyRemove($key){
			$result = $this->_data_store->keyRemove($key);
			$this->log("Removing key '%s', result = '%s'", $key, $result);
			return $result;
		}

		private function log($message, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null){
			if($arg1 == null){
				$arg1 = "(null)";
			}
			if($arg2 == null){
				$arg2 = "(null)";
			}
			if($arg3 == null){
				$arg3 = "(null)";
			}
			if($arg4 == null){
				$arg4 = "(null)";
			}

			if($this->_logger != null){
				$this->_logger->log(sprintf($message, $arg1, $arg2, $arg3, $arg4));
			}
		}
	}

?>