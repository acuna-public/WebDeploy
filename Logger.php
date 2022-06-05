<?php
	
	class Logger {
		
		const
			LOG_NONE = 0,
			LOG_BASIC = 1,
			LOG_VERBOSE = 2,
			LOG_DEBUG = 3;
		
		//const NL = '<br/>';
		
		protected
			$message = [];
		
		protected
			$file,
			$level = self::LOG_BASIC,
			$statusCode = 0;
		
		function __construct (string $file) {
			$this->file = $file;
		}
		
		function setLogLevel ($level = self::LOG_BASIC) {
			
			if (!is_int ($level)) {
				
				$levels = [
					
					'none' => self::LOG_NONE,
					'basic' => self::LOG_BASIC,
					'verbose' => self::LOG_VERBOSE,
					'debug' => self::LOG_DEBUG,
					
				];
				
				if (isset ($levels[$level]))
					$level = $levels[$level];
				else
					$level = self::LOG_BASIC;
				
			}
			
			$this->logLevel = $level;
			
		}
		
		function setName ($name, $version) {
			
			$this->message['name'] = $name;
			$this->message['version'] = $version;
			
		}
		
		function message ($message, $level = self::LOG_BASIC) {
			
			if ($this->level > self::LOG_NONE and $level <= $this->level) {
				
				$prefix = date ('c').'	';
				
				$message = str_replace ('\n', str_pad ('\n', strlen ($prefix) + 1), $message);
				
				$dir = dirname ($this->file);
				if (!is_dir ($dir)) mkdir ($dir);
				
				$this->message['mess'][] = $message;
				
				file_put_contents ($this->file, $prefix.$message."\n", FILE_APPEND);
				
			}
			
		}
		
		function error ($message, $code) {
			
			$this->message['error'][] = ['text' => $message, 'code' => $code];
			$this->setStatus ($message, $code);
			
		}
		
		function setStatus ($message, $code = 200) {
			
			if ($code > $this->statusCode)
				$this->statusCode = $code;
			
		}
		
		function sendStatus () {
			
			http_response_code ($this->statusCode);
			
			//if ($this->level > self::LOG_NONE)
				echo json_encode ($this->message, true);
			
		}
		
	}