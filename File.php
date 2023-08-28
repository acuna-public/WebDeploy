<?php
	
	class File {
		
		protected $fp;
		public $file, $size;
		
		protected const READ = 'rb', WRITE = 'ab', REWRITE = 'w+b';
		
		function __construct ($file) {
			
			$this->file = $file;
			
			if ($this->exists ())
				$this->size = $this->size ();
			
		}
		
		protected function open ($mode) {
			
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
			return @chmod ($this->file, $chmod);
		}
		
		function char ($offset): string {
			
			$this->open (self::READ);
			
			fseek ($this->fp, $offset);
			return fgetc ($this->fp);
			
		}
		
		function makeDir ($chmod = 0777) {
			return is_dir ($this->getDir ()) || @mkdir ($this->getDir (), $chmod, true);
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
			
			unlink ($this->file);
			
			return true;
			
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