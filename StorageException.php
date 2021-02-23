<?php
  
  class StorageException extends Exception {
    
    protected $adapter;
    
    function __construct (\Storage\Adapter $adapter, $mess, $file = '') {
      
      parent::__construct ($mess);
      
      $this->adapter = $adapter;
      $this->file = $file;
      
    }
    
  }