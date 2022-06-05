<?php
	
	class File {
		
		private $fp;
		public $file, $size;
		
		private const READ = 'rb', WRITE = 'ab', REWRITE = 'w+b';
		
		function __construct ($file) {
			
			$this->file = $file;
			
			if ($this->exists ())
				$this->size = $this->size ();
			
		}
		
		private function open ($mode) {
			
			if (!$this->fp)
				$this->fp = @fopen ($this->file, $mode);
			
		}
		
		function read (): string {
			
			$this->open (self::READ);
			return @fread ($this->fp, $this->size);
			
		}
		
		function write ($content, $append = false) {
			
			$this->open ($append ? self::WRITE : self::REWRITE);
			return @fwrite ($this->fp, $content);
			
		}
		
		function chmod ($chmod): bool {
			return chmod ($this->file, $chmod);
		}
		
		function char ($offset): string {
			
			$this->open (self::READ);
			
			fseek ($this->fp, $offset);
			return fgetc ($this->fp);
			
		}
		
		function makeDir ($chmod = 0777) {
			
			$dir = '';
			
			foreach (explode ('/', str_replace ('\\', '/', $this->file)) as $i => $part) {
				
				if ($part == '') continue;
				if ($i > 0) $dir .= '/';
				
				$dir .= $part;
				if (!@is_dir ($dir)) @mkdir ($dir, $chmod);
				
			}
			
		}
		
		function isDir (): bool {
			return is_dir ($this->file);
		}
		
		function getDir (): string {
			return dirname ($this->file);
		}
		
		function isFile (): bool {
			return is_file ($this->file);
		}
		
		function delete (): bool {
			return unlink ($this->file);
		}
		
		function exists (): bool {
			return file_exists ($this->file);
		}
		
		function size () {
			return filesize ($this->file);
		}
		
		function modified () {
			return filemtime ($this->file);
		}
		
		function __destruct () {
			if ($this->fp) fclose ($this->fp);
		}
		
		function __toString () {
			return $this->file;
		}
		
	}