<?php
	
	class StorageException extends Exception {
		
		protected $storage;
		
		function __construct (Storage $storage, $mess, $file = '') {
			
			parent::__construct ($mess);
			
			$this->storage = $storage;
			$this->file = $file;
			
		}
		
	}