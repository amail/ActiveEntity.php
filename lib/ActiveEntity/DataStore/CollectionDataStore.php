<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	class CollectionDataStore extends DataStoreBase {
		protected $_values;

		public function __construct(IKeyBasedDataStore $data_store, $values, $configuration){
			$this->_values = $values;
			parent::__construct($data_store, $configuration);
		}

		public function getItems(){
			return $this->_data_store->listGet($this->getCollectionEntityKey());
		}

		public function addItem($item){
			$this->_data_store->listPush($this->getCollectionEntityKey(), $item);
		}

		public function removeItem($item){
			return $this->_data_store->listRemoveValue($this->getCollectionEntityKey(), $item);
		}

		public function getTotalItems(){
			return $this->_data_store->listLength($this->getCollectionEntityKey());
		}

		private function getCollectionEntityKey(){
			return $this->getEntityKey("collection", $this->_values);
		}
	}

?>