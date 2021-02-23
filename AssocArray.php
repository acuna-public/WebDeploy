<?php
  
  abstract class AssocArray {
    
    public $pairs = [];
    
    protected $required, $values;
    
    function __construct (array $data = []) {
      
      $this->required = $this->getRequiredPairs ();
      $this->values = $this->getRequiredValues ();
      
      foreach ($this->getPairs () as $key => $value)
        $this->set ($key, $value);
      
      foreach ($data as $key => $value)
        $this->set ($key, $value);
    
    }
    
    protected function getPairs (): array {
      return [];
    }
    
    protected function getRequiredPairs (): array {
      return [];
    }
    
    protected function getRequiredValues (): array {
      return [];
    }
    
    function set ($key, $value) {
      
      if (in_array ($key, array_keys ($this->values)) and !in_array ($value, $this->values[$key]))
        throw new \Exception ($key.' value must be '.implode (', ', $this->values[$key]));
      else
        $this->pairs[$key] = $value;
      
      return $this;
      
    }
    
    function get ($key) {
      
      if (isset ($this->pairs[$key]))
        return $this->pairs[$key];
      else
        return null;
      
    }
    
    function validate (): AssocArray {
      
      if ($missed = $this->diff (array_keys ($this->pairs), $this->required))
        throw new \Exception ('Required keys missed: '.implode (', ', $missed));
      
      return $this;
      
    }
    
    protected function diff (...$arrays) {
      
      $diff = [];
      
      $arr = $arrays[0];
      
      foreach ($arrays as $key => $value) {
        
        foreach ($value as $value)
          if (!in_array ($value, $arr))
            $diff[] = $value;
        
      }
      
      return $diff;
      
    }
    
  }