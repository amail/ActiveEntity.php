<?php
	
	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	abstract class DataStoreBase {
		protected $_data_store;
		protected $_configuration;

		public function DataStoreBase(IKeyBasedDataStore $data_store, $configuration){
			$this->_data_store = $data_store;
			$this->_configuration = $configuration;
		}

		private function tokenizeEntityKey($key){
			$buffer = "";
			$tokens = array();

			for($i=0;$i<strlen($key);++$i){
				$character = $key[$i];

				switch($character){
					case '{':
						// Push text
						if($buffer != ""){
							$tokens[] = array("type" => "text", "value" => $buffer);
							$buffer = "";
						}
						break;
					case '}':
						// Push variable
						if($buffer != ""){
							$tokens[] = array("type" => "variable", "value" => $buffer);
							$buffer = "";
						}
						break;
					default:
						$buffer .= $character;
						break;
				}
			}

			if($buffer != ""){
				$tokens[] = array("type" => "text", "value" => $buffer);
			}

			return $tokens;
		}

		protected function getEntityKey($key, $arguments = array()){
			$result = "";

			$entity_key = $this->_configuration[$key];
			$tokens = $this->tokenizeEntityKey($entity_key);

			foreach($tokens as $token){
				$value = $token['value'];
				switch($token['type']){
					case 'text':
						$result .= $value;
						break;
					case 'variable':
						if(array_key_exists($value, $arguments)){
							$result .= $arguments[$value];
						}else{
							$property_name = "get" . implode("", array_map("ucfirst", explode("_", $value)));
							$property_value = call_user_func_array(array($this->_entity, $property_name), array());

							if($property_value === null){
								throw new Exception(sprintf("Property value for entity key '%s' cannot be null", $property_name));
							}

							$result .= $property_value;
						}
						break;
				}
			}

			return $result;
		}
	}

?>