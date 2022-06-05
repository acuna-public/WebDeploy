<?php
	
	namespace Storage;
	
	class File extends \Storage {
		
		protected function getFile ($file): \File {
			return new \File ($this->config['path'].'/'.$file);
		}
		
		function read ($file): string {
			return $this->getFile ($file)->read ();
		}
		
		function write ($file, $content, $append = false, $chmod = 0644) {
			
			if ($this->getFile ($file)->write ($content, $append, $chmod) === false)
				throw new \StorageException ($this, 'Can\'t write to file', $file);
			
		}
		
		function chmod ($file, $chmod): bool {
			return $this->getFile ($file)->chmod ($chmod);
		}
		
		function makeDir ($dir = '', $chmod = 0777) {
			$this->getFile ($dir)->makeDir ($chmod);
		}
		
		function isDir ($dir = ''): bool {
			return $this->getFile ($dir)->isDir ();
		}
		
		function isFile ($file): bool {
			return $this->getFile ($file)->isFile ();
		}
		
		function getDir ($dir): string {
			return dirname ($dir);
		}
		
		function delete ($file): bool {
			
			if (!$this->getFile ($file)->delete ())
				throw new \StorageException ($this, 'Can\'t delete file', $file);
			
		}
		
		function exists ($file): bool {
			return $this->getFile ($file)->exists ();
		}
		
		function size ($file) {
			return $this->getFile ($file)->size ();
		}
		
		function modified ($file) {
			return $this->getFile ($file)->modified ();
		}
		
	}