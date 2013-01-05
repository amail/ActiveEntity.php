<?php
	
    /*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	// Represents a Converter which can convert values based on their type
	// I.e. from persisted to local-representation, or the other way around
	interface ITypeConverter {
		public function canHandleType($type);
		public function toLocalValue($type, $persisted_value);
		public function toPersistedValue($type, $local_value);
	}

?>