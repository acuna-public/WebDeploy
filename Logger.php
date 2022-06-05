<?php
	
	class Logger {
		
		const
			LOG_NONE = 0,
			LOG_BASIC = 1,
			LOG_VERBOSE = 2,
			LOG_DEBUG = 3;
		
		public
			$message = ['name' => '', 'version' => '', 'mess' => [], 'error' => []],
			$statusCode = 0;
		
		protected
			$file,
			$level = self::LOG_BASIC;
		
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
		
		protected function write ($message, $level = self::LOG_BASIC) {
			
			if ($this->level > self::LOG_NONE and $level <= $this->level) {
				
				$prefix = date ('c').'	';
				
				$message = str_replace ('\n', str_pad ('\n', strlen ($prefix) + 1), $message);
				
				$dir = dirname ($this->file);
				if (!is_dir ($dir)) mkdir ($dir);
				
				file_put_contents ($this->file, $prefix.$message."\n", FILE_APPEND);
				
			}
			
		}
		
		function message ($message, $level = self::LOG_BASIC) {
			
			if ($this->level > self::LOG_NONE and $level <= $this->level) {
				
				$this->write ($message, $level);
				$this->message['mess'][] = $message;
				
			}
			
		}
		
		function error ($message, $code = 403) {
			
			$this->message['error'][] = ['text' => $message, 'code' => $code];
			
			$this->write ($message);
			
		}
		
		function sendStatus () {
			
			http_response_code ($this->statusCode);
			
			//if ($this->level > self::LOG_NONE)
				echo json_encode ($this->message, true);
			
		}
		
	}