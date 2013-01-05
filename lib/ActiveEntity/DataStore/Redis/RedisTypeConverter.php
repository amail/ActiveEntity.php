<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	// Converter specialized at converting types to and from Redis storage
	class RedisTypeConverter implements ITypeConverter {
		protected $_supported_types = array("str", "string", "int", "integer", "flt", "float", "double", "bool", "boolean", "obj", "object");

		public function canHandleType($type){
			return in_array($type, $this->_supported_types);
		}

		public function toLocalValue($type, $persisted_value){
			$result = null;

			switch($type){
				case 'str':
				case 'string':
					$result = $persisted_value;
					break;
				case 'int':
				case 'integer':
					$result = (int)$persisted_value;
					break;
				case 'flt':
				case 'float':
				case 'double':
					$result = (float)$persisted_value;
					break;
				case 'bool':
				case 'boolean':
					$result = (bool)$persisted_value;
					break;
				case 'obj':
				case 'object':
					$result = json_decode($persisted_value);
					break;
			}

			return $result;

		}

		public function toPersistedValue($type, $local_value){
			$result = null;

			switch($type){
				case 'str':
				case 'string':
					$result = (string)$local_value;
					break;
				case 'int':
				case 'integer':
					$result = (int)$local_value;
					break;
				case 'flt':
				case 'float':
				case 'double':
					$result = (float)$local_value;
					break;
				case 'bool':
				case 'boolean':
					$result = (int)((bool)$local_value);
					break;
				case 'obj':
				case 'object':
					$result = json_encode($local_value);
					break;
			}

			return $result;
		}
	}

?>