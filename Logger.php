<?php
  
  namespace WebDeploy;
  
  class Logger {
    
    const
      LOG_NONE = 0,
      LOG_BASIC = 1,
      LOG_VERBOSE = 2,
      LOG_DEBUG = 3;
    
    protected
      $logLevel = self::LOG_BASIC,
      $statusMessage = \WebDeploy::VERSION_INFO,
      $statusCode = 0;
    
    function __construct (string $filename) {
      $this->filename = $filename;
    }
    
    // Set logger instance log level
    
    function setLogLevel ($level = self::LOG_BASIC) {
      
      if (!is_int ($level)) {
        
        $levels = [
          
          'none' => self::LOG_NONE,
          'basic' => self::LOG_BASIC,
          'verbose' => self::LOG_VERBOSE,
          'debug' => self::LOG_DEBUG,
          
        ];
        
        if (in_array ($level, $levels))
          $level = $levels[$level];
        else
          $level = self::LOG_BASIC;
          
      }
      
      $this->logLevel = $level;
      
    }
    
    // Log to file
    function message ($message, $level = self::LOG_BASIC) {
      
      if ($level <= $this->logLevel and $this->logLevel > self::LOG_NONE) {
        
        $prefix = date ('c').'  ';
        
        $message = str_replace ('\n', str_pad ('\n', strlen ($prefix) + 1), $message);
        
        file_put_contents ($this->filename, $prefix.$message."\n", FILE_APPEND);
        
      }
      
    }
    
    // Set error code and message
    
    function error ($message, $code) {
      
      $this->message ('Error: '.$message);
      $this->setStatus ($message, $code);
      
    }
    
    // Store status code and message, to be output in HTML
    
    function setStatus ($message, $code = 200) {
      
      if ($code > $this->statusCode)
        $this->statusCode = $code;
      
      $this->statusMessage .= \WebDeploy::NL.$message;
      
    }
    
    // Output status code and message
    
    function sendStatus () {
      
      http_response_code ($this->statusCode);
      echo $this->statusMessage;
      
    }
    
  }