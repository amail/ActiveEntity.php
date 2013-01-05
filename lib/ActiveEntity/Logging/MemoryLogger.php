<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/
	
	class MemoryLogger implements ILogger
	{
		protected $_logs = array();

		public function logType($type, $message){
			$this->_logs[] = array("type" => $type, "message" => $message, "created" => time());
		}

		public function log($message){
			$this->logType("notice", $message);
		}

		public function getLogs(){
			return $this->_logs;
		}
	}

?>