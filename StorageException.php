<?php
	
	class StorageException extends \Exception {
		
		public \Storage $storage;
		
		function __construct (\Storage $storage, $mess) {
			
			parent::__construct ($mess);
			
			$this->storage = $storage;
			
		}
		
		function toString () {
			return $this->getMessage ().' \''.$this->storage->file.'\'';
		}
		
	}