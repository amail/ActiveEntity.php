<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	abstract class SortOrder {
		const Ascending = 0;
		const Descending = 1;
	}

	abstract class EntityCollection extends EntityBase implements \Iterator {
		protected $_model_type;

		protected $_items = array();
		protected $_items_eof = true;
		protected $_items_loaded = false;
		protected $_items_removed = array();
		protected $_items_sort_order = SortOrder::Ascending;

		private $_key_based_data_store;

		public function __construct(IKeyBasedDataStore $data_store, $values, $configuration, $type = null){
			$this->_values = $values;
			$this->_key_based_data_store = $data_store;
			$this->_model_type = $type == null ? $this->getClassModelName() : $type;
			$this->_data_store = new CollectionDataStore($data_store, $values, $configuration);
			$this->setSortOrder(SortOrder::Ascending);
		}

		// Magic function which intercepts all method calls and directs them,
		// either to an existing function, or to property read/write.
	    public function __call($method, $arguments){
	    	$segments = $this->splitIntoWords($method);

	    	// If method exists (overload), call that instead
	    	if(method_exists($this, $method)){
	    		return call_user_func_array(array($this, $method), $arguments);
	    	}

	    	if($segments[0] == 'find' && $segments[1] == 'by'){
	    		// Remove the 2 segments ['find', 'by']
	    		array_splice($segments, 0, 2);

	    		$property_name = implode("", array_map('ucfirst', $segments));

	    		return $this->scanItemsForValue($property_name, $arguments[0], count($arguments) == 2 ? $arguments[1] : null);
	    	}else{
	    		throw new EntityInvalidAccessException(sprintf("Invalid method call '%s'", $segments[0]));
	    	}
	    }

	    // Loads the collection
		public function load($force = false){
			if($force || !$this->_items_loaded){
				$result = array();
				$this->_items_loaded = true;

				foreach($this->_data_store->getItems() as $item_id){
					if(array_key_exists($item_id, $this->_items)){
						$result[$item_id] = $this->_items[$item_id];
					}else{
						$values = $this->_values;
						$values["id"] = $item_id;
						$result[$item_id] = new $this->_model_type($this->_key_based_data_store, $values, $this);
					}
				}

				$this->_items = $result;

				$this->sortItems();
				$this->rewind();
			}

			return $this;
		}

		// Loads the collection and all underlying items
		public function loadAll($force = false){
			$this->load($force);

			foreach($this->_items as $item){
				$item->load($force);
			}

			return $this;
		}

		// Should be able to find it instantaneously without scanning
		public function getById($entity_id){
			$result = $this->scanItemsForValue("Id", $entity_id, 1);
			return count($result) == 0 ? null : $result[0];
		}

		protected function scanItemsForValue($property_name, $value, $max_count = null){
			$result = array();
			$this->load();

			foreach($this->_items as $item){
				$property_value = call_user_func_array(array($item, 'get' . $property_name), array());
				if($property_value == $value){
					$result[] = $item;
					if($max_count != null && count($result) == $max_count){
						break;
					}
				}
			}

			return $result;
		}

		public function current(){
			// Returns the current model
			return current($this->_items);
		}

		public function key(){
			// Returns the current key
			return key($this->_items);
		}

		public function next(){
			// Move forward.. (no return)
			$this->_items_eof = next($this->_items) === false;
		}

		public function rewind(){
			// Removed items marked for removal
			if(count($this->_items_removed) > 0){
				foreach($this->_items_removed as $item_id => $value){
					unset($this->_items[$item_id]);
				}

				$this->_items_removed = array();
			}

			// Reset (no return)
			$this->_items_eof = false;
			reset($this->_items);
		}

		public function valid(){
			$this->load();
			return count($this->_items) > 0 && !$this->_items_eof;
		}

		public function add(Entity $entity){
			$entity_id = $entity->getId();
			$result = $this->_data_store->addItem($entity_id);
			
			$this->_items[$entity_id] = $entity;
			$this->sortItems();

			return $result;
		}

		public function remove(Entity $entity){
			$entity_id = $entity->getId();
			$this->_items_removed[$entity_id] = null;
			return $this->_data_store->removeItem($entity_id);
		}

		public function removeAll(){
			$this->load();

			foreach($this->_items as $item){
				$item->remove();
			}

			return $this;
		}

		public function count(){
			if($this->_items_loaded){
				return count($this->_items);
			}

			return $this->_data_store->getTotalItems();
		}

		public function getData(){
			$result = array();
			$this->load();

			foreach($this->_items as $item){
				$result[] = $item->getData();
			}

			return $result;
		}

		public function setSortOrder($sort_order){
			$this->_items_sort_order = $sort_order;
			return $this;
		}

		protected function sortItems(){
			if($this->_items_sort_order == SortOrder::Ascending){
				ksort($this->_items, SORT_NUMERIC);
			}else{
				krsort($this->_items, SORT_NUMERIC);
			}
		}

		private function getClassModelName(){
			$segments = $this->splitIntoWords(get_class($this), false);

			if(count($segments) > 0 && $segments[count($segments)-1] == 'Collection'){
				array_pop($segments);
			}

			return implode("", $segments);
		}
	}
	
?>