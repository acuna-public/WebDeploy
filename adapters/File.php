<?php
	
	namespace Storage;
	
	class File extends \Storage {
		
		public \File $file;
		
		function setFile ($file) {
			$this->file = new \File ($file);
		}
		
		function read (): string {
			return $this->file->read ();
		}
		
		function write ($content, $append = false, $chmod = 0644) {
			
			if ($this->file->write ($content, $append, $chmod) === false)
				throw new \StorageException ($this, 'Can\'t write to file');
			
		}
		
		function chmod ($chmod): bool {
			return $this->file->chmod ($chmod);
		}
		
		function makeDir ($chmod = 0777) {
			
			if (!$this->file->makeDir ($chmod))
				throw new \StorageException ($this, 'Can\'t create dir');
			
		}
		
		function isDir (): bool {
			return $this->file->isDir ();
		}
		
		function isFile (): bool {
			return $this->file->isFile ();
		}
		
		function getDir (): string {
			return realpath ($this->file);
		}
		
		function delete (): bool {
			
			if (!$this->file->delete ())
				throw new \StorageException ($this, 'Can\'t delete file');
			
			return true;
			
		}
		
		function exists (): bool {
			return $this->file->exists ();
		}
		
		function size () {
			return $this->file->size ();
		}
		
		function modified () {
			return $this->file->modified ();
		}
		
	}