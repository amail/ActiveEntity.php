<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/
	
	class EntityDataStore extends DataStoreBase {
		protected $_entity;

		public function __construct(IKeyBasedDataStore $data_store, $configuration, $entity){
			parent::__construct($data_store, $configuration);
			$this->_entity = $entity;
		}

		public function incrementId(){
			$new_id = $this->_data_store->keyIncrement($this->getEntityKey("increment_id"));
			return $new_id;
		}

		public function setProperty($name, $value){
			$key_path = $this->getEntityKey("property", array("property_name" => $name));
			$value = $this->getPersistentValue($name, $value);
			return $this->_data_store->keySet($key_path, $value);
		}

		public function getProperty($name){
			$key_path = $this->getEntityKey("property", array("property_name" => $name));
			$value = $this->_data_store->keyGet($key_path);
			return $this->getLocalValue($name, $value);
		}

		public function removeProperty($name){
			$key_path = $this->getEntityKey("property", array("property_name" => $name));
			return $this->_data_store->keyRemove($key_path);
		}

		// Convert a local value to a persisted value
		private function getPersistentValue($name, $value){
			$type = $this->_entity->getPropertyType($name);
			return $this->_data_store->getTypeConverter()->toPersistedValue($type, $value);
		}

		// Convert a persisted value to a local value
		private function getLocalValue($name, $value){
			$type = $this->_entity->getPropertyType($name);
			return $this->_data_store->getTypeConverter()->toLocalValue($type, $value);
		}
	}

?>