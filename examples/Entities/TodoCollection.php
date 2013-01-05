<?php

	class TodoCollection extends EntityCollection
	{
		public function __construct(IKeyBasedDataStore $data_store, $values = array()){
			parent::__construct(
				$data_store,
				$values,
				array(
					"collection" => "examples:todos"
				)
			);
		}
	}

?>