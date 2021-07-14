<?php
  
  require 'StorageException.php';
  
  abstract class Storage {
    
    public $config = [];
    
    function __construct (array $config = []) {
      $this->config = $config;
    }
    
    abstract function read ($file): string;
    abstract function write ($file, $content, $append = false);
    abstract function makeDir ($dir = '', $chmod = 0777);
    abstract function isDir ($dir = ''): bool;
    abstract function isFile ($file): bool;
    abstract function delete ($file): bool;
    abstract function exists ($file): bool;
    abstract function size ($file);
    abstract function modified ($file);
    
    function getDir ($dir): string {
      return $dir;
    }
    
    function chmod ($file, $chmod): bool {
      return false;
    }
    
  }