<?php
	
	require 'StorageException.php';
	
	abstract class Storage {
		
		abstract function setFile ($file);
		
		abstract function read (): string;
		abstract function write ($content, $append = false);
		abstract function makeDir ($chmod = 0777);
		abstract function isDir (): bool;
		abstract function isFile (): bool;
		abstract function delete (): bool;
		abstract function exists (): bool;
		abstract function size ();
		abstract function modified ();
		abstract function getDir (): string;
		
		function chmod ($chmod): bool {
			return false;
		}
		
	}