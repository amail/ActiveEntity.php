<?php

	class Todo extends Entity {
		public function __construct(IKeyBasedDataStore $data_store, $values = array(), $collection = null, $autoload = false){
			parent::__construct(
				$data_store,
				$values,
				array(
					"increment_id" => "examples:todos:current_id",
					"property" => "examples:todos:{id}:{property_name}"
				),
				array(
					"text" => "string",
					"completed" => "boolean",
					"created" => "integer"
				),
				$collection,
				$autoload
			);
		}
	}

?>