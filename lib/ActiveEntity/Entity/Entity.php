<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	abstract class Entity extends EntityBase {
		protected $_data_store;
		protected $_key_based_data_store;

		protected $_id;
		protected $_properties;
		protected $_property_changes;

		protected $_values;
		protected $_values_persisted;
		protected $_values_initial;

		protected $_loaded;
		protected $_collection;

		protected $_collection_references;
		protected $_entity_references;

		public function __construct(IKeyBasedDataStore $data_store, $values = array(), $configuration = array(), $properties = array(), $collection = null, $autoload = false){
			$has_identity = $values != null && array_key_exists("id", $values) && $values["id"] != null;

			// Set entity properties
			$this->_properties = (array)$properties;
			$this->_property_changes = array();
			$this->_values = array();
			$this->_values_persisted = array();
			$this->_collection = $collection;
			$this->_values_initial = $values;
			$this->_loaded = false;

			$this->_collection_references = array();
			$this->_entity_references = array();

			// Setup data store
			$this->_key_based_data_store = $data_store;
			$this->_data_store = new EntityDataStore($data_store, $configuration, $this);

			// Remove values not mapped to any properties
			foreach($values as $key => $value){
				if($key != 'id'){
					if(!array_key_exists($key, $properties)){
						unset($values[$key]);
					}
				}
			}

			// Set values
			if($values != null){
				if($has_identity){
					$this->_id = $values["id"];
				}
				foreach($values as $key => $value){
					if($key != 'id'){
						// First try and retrieve the property, then set it.
						// This way, changes to the property will be detected and only written if neccessary.
						if($has_identity){
							if($this->getProperty($key) != $value){
								throw new Exception(sprintf("Required property '%s' did not match required value '%s' (Id = %s)", $key, $value, $this->_id));
							}
						}

						$this->setProperty($key, $value);
					}
				}
			}

			if($autoload && $this->_id != null){
				$this->load();
			}
		}

		// Get the Identity
		public function getId(){
			return $this->_id;
		}

		// Magic function which intercepts all method calls and directs them,
		// either to an existing function, or to property read/write.
	    public function __call($method, $arguments){
	    	$operation_type = null;
	    	$segments = $this->splitIntoWords($method);

	    	// If method exists (overload), call that instead
	    	if(method_exists($this, $method)){
	    		return call_user_func_array(array($this, $method), $arguments);
	    	}

	    	if($segments[0] == 'get' || $segments[0] == 'set'){
	    		$operation_type = $segments[0];
	    		array_shift($segments);
	    	}else{
	    		throw new EntityAccessException(sprintf("Invalid method call '%s'", $segments[0]));
	    	}

	    	$property_name = $this->formatPropertyName($segments);

	    	if($operation_type == 'set')
	    	{
	    		if(count($arguments) != 1)
	    		{
	    			throw new EntityAccessException(sprintf("Set method for property '%s' must have exactly 1 argument", $segments[0]));
	    		}

	    		return $this->setProperty($property_name, $arguments[0]);
	    	}
	    	else
	    	{
	    		return $this->getProperty($property_name);
	    	}
	    }

	    // Load all properties
	    public function load($force = false){
	    	if($this->getId() != null && ($force || !$this->_loaded)){
	    		$this->_loaded = true;
		    	foreach($this->_properties as $property => $type){
		    		$this->getProperty($property);
		    	}
	    	}

	    	return $this;
	    }

	    // Create from scratch or if existing, persist all changes
	    public function save(){
	    	if($this->_id == null){
	    		$this->_id = $this->_data_store->incrementId();

	    		// Add the identity to the collection
	    		$collection = $this->getCollection();
	    		if($collection != null){
					$collection->add($this);
				}
	    	}

	    	if($this->_id == -1){
	    		throw new Exception("Cannot save. Entity already removed!");
	    	}

	    	foreach(array_keys($this->_property_changes) as $property_name){
	    		if($this->hasPropertyChanged($property_name)){
	    			$property_value = $this->_values[$property_name];
	    			if($this->_data_store->setProperty($property_name, $property_value)){
	    				$this->_values_persisted[$property_name] = $property_value;
	    			}
    			}
	    	}

	    	return $this;
	    }

	    // Remove whole entity
	    public function remove(){
	    	if($this->_id == -1){
	    		throw new Exception("Entity already removed!");
	    	}

	    	foreach(array_keys($this->_properties) as $property_name){
	    		$this->_data_store->removeProperty($property_name);
	    	}

	    	$collection = $this->getCollection();
	    	if($collection != null){
	    		$collection->remove($this);
	    	}

    		$this->_id = -1;
	    }

	   	// Retrieve all data loaded into this entity
	    public function getData(){
	    	$result = array();

	    	if($this->_id != null){
	    		$result["id"] = $this->_id;
	    	}

	    	foreach($this->_properties as $property_name => $type){
	    		if(array_key_exists($property_name, $this->_values)){
	    			$result[$property_name] = $this->_values[$property_name];
	    		}
	    	}

	    	return $result;
	    }

	    public function getCollection(){
	    	// Check whether or not a collection is attached.
	    	if($this->_collection == null){
	    		// Try and resolve the collection using the naming convention "[Entity Name]Collection"
	    		$collection_class_name = get_class($this) . "Collection";
	    		if(class_exists($collection_class_name)){
	    			$this->_collection = new $collection_class_name($this->_key_based_data_store, $this->_values_initial);
	    		}
	    	}

	    	return $this->_collection;
	    }

	    // Retrieve the type for a specific property
	    public function getPropertyType($name){
	    	if(!array_key_exists($name, $this->_properties)){
	    		throw new Exception("Property does not exist!");
	    	}

	    	return $this->_properties[$name];
	    }

	    // Check whether or not a property has been modified
	    protected function hasPropertyChanged($name){
    		$property_value = $this->_values[$name];
    		$has_persisted_value = array_key_exists($name, $this->_values_persisted);

    		// Only store the value if it has been changed
    		// The property is determined as changed if the latest persisted value (if set) is different from the current value
    		return ($has_persisted_value && $property_value != $this->_values_persisted[$name]) || !$has_persisted_value;
	    }

	    protected function getProperty($name){
	    	$result = null;

	    	if($name == null || strlen($name) == 0){
	    		throw new Exception("Argument 'name' cannot be null or empty");
	    	}
	    	
	    	if(array_key_exists($name, $this->_properties)){
	    		$property_type = $this->_properties[$name];
	    		if($this->_key_based_data_store->getTypeConverter()->canHandleType($property_type)){
		    		if(array_key_exists($name, $this->_values)){
		    			$result = $this->_values[$name];
		    		}else{
		    			if($this->getId() != null){
			    			$result = $this->_data_store->getProperty($name);
			    			$this->_values[$name] = $result;
			    			$this->_values_persisted[$name] = $result;
		    			}
		    		}
	    		}else if($property_type == 'collection'){
	    			$collection_type_name = $this->convertPropertyNameToTypeName($name, true) . "Collection";
	    			if(class_exists($collection_type_name)){
	    				if(array_key_exists($name, $this->_collection_references)){
	    					$result = $this->_collection_references[$name];
	    				}else{
	    					$values = $this->_values_initial;
	    					
	    					$segments = array_merge($this->splitIntoWords(get_class($this)), array("id"));
	    					$reference_id_name = $this->formatPropertyName($segments);

	    					$values[$reference_id_name] = $this->getId();
	    					unset($values["id"]);

	    					$result = new $collection_type_name($this->_key_based_data_store, $values);
	    					$this->_collection_references[$name] = $result;
	    				}
	    			}
	    		}else{
	    			throw new Exception(sprintf("Unable to get property '%s', cannot handle type '%s'", $name, $property_type));
	    		}
	    	}else{
	    		$identity_name = $name . '_id';
	    		if(array_key_exists($identity_name, $this->_properties)){
	    			$property_type = $this->convertPropertyNameToTypeName($name);
	    			if(class_exists($property_type)){
	    				if(array_key_exists($name, $this->_entity_references)){
	    					$result = $this->_entity_references[$name]; 
	    				}else{
	    					$values = $this->_values_initial;
	    					$values["id"] = $this->getProperty($identity_name);
	    					
	    					$result = new $property_type($this->_key_based_data_store, $values);
	    					$this->_entity_references[$name] = $result;
	    				}
	    			}else{
	    				throw new EntityAccessException(sprintf("Entity '%s' for property '%s' (reference '%s') does not exist",
	    					$property_type, $name, $identity_name));
	    			}
	    		}else{
	    			throw new EntityAccessException(sprintf("Property '%s' does not exist", $name));
	    		}
	    	}

	    	return $result;
	    }

	    protected function setProperty($name, $value){
	    	if($name == null || strlen($name) == 0){
	    		throw new Exception("Argument 'name' cannot be null or empty");
	    	}

	    	if(array_key_exists($name, $this->_properties)){
    			$this->_values[$name] = $value;
    			$this->_property_changes[$name] = true;

    			// Notify the collection that a property has been changed.
    			// This is important in case the collection needs to do a resort, aso..
    			$collection = $this->getCollection();
    			if($collection != null && $this->hasPropertyChanged($name)){
    				$collection->notifyPropertyChange($name);
    			}
	    	}else{
	    		throw new EntityAccessException(sprintf("Property '%s' does not exist", $name));
	    	}

	    	return $this;
	    }
	}

?>