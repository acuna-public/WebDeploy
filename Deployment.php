<?php
	
	namespace WebDeploy;
	
	class Deployment {
		
		protected $files = ['modified' => [], 'removed' => []];
		
		function __construct (\WebDeploy $deploy, ConfigRule $rule) {
			
			$this->deploy = $deploy;
			$this->rule = $rule;
			
		}
		
		protected function setup () {
			
			$this->deploy->logger->setLogLevel ($this->rule->get ('log-level'));
			
			if ($commit = substr ($this->deploy->get ('commit-id'), 0, 6))
				$this->deploy->logger->message ('Deploying '.$commit.' ('.$this->deploy->get ('branch').') from '.$this->deploy->get ('repository'));
			
			try {
				$this->deploy->storage->makeDir ();
			} catch (\StorageException $e) {
				$this->deploy->logger->error ('Error creating destination directory '.$e->getFile (), 500);
			}
			
		}
		
		function process () {
			
			$this->deploy->logger->message ('Starting to deploy \''.$this->deploy->get ('repository').'\' repository...');
			
			$this->setup ();
			$this->deployFiles ();
			
			if (!$this->deploy->logger->message['error'])
				$this->deploy->logger->message ('Repository \''.$this->deploy->get ('repository').'\' deployed in \''.$this->rule->get ('mode').'\' mode');
			
		}
		
		// Determine the actual deployment mode to use
		
		protected function getMode () {
			
			if ($this->deploy->get ('forced')) {
				
				$mode = 'replace';
				$this->deploy->logger->message ('Forced update - deploying all files', \Logger::LOG_VERBOSE);
				
			} elseif (in_array ($this->rule->get ('mode'), ['deploy', 'dry-run'])) {
				
				if (!$this->countFiles ($this->rule->get ('destination'), false)) {
					
					$mode = 'replace';
					$this->deploy->logger->message ('Destination is empty - deploying all files', \Logger::LOG_VERBOSE);
					
				} else $mode = 'update';
				
			} else $mode = $this->rule->get ('mode');
			
			return $mode;
			
		}
		
		// Extract files according to WebDeploy commit data
		
		protected function deployFiles () {
			
			$dryRun = ($this->rule->get ('mode') == 'dry-run');
			
			$this->deploy->logger->message ('Modified files: '.implode (', ', $this->files['modified']), \Logger::LOG_DEBUG);
			$this->deploy->logger->message ('Removed files: '.implode (', ', $this->files['removed']), \Logger::LOG_DEBUG);
			//$this->deploy->logger->message ('Repository files: '.implode (', ', $archive->listFiles ()), \Logger::LOG_DEBUG);
			
			foreach ($this->deploy->get ('files') as $file) {
				
				if ($this->isIgnored ($file->get ('name'))) {
					
					$this->deploy->logger->message ('Skipping ignored file '.$file->get ('name'), \Logger::LOG_VERBOSE);
					continue;
					
				}
				
				if (
					$file->get ('status') == 'modified' or $this->getMode () == 'replace' // Изменен локально
					or $file->get ('status') == 'added' // Создан локально
				) {
					
					$this->deploy->logger->message ('Writing file '.$file->get ('name'));
					
					if (!$dryRun) {
						
						try {
							$this->writeFile ($file->get ('name'), $this->deploy->git->readFile ($this->deploy->get ('repository'), $file->get ('name')));
						} catch (\StorageException $e) {
							$this->deploy->logger->error ('Error writing to file '.$e->getFile ());
						}
						
					}
					
				} elseif ($file->get ('status') == 'removed' and $this->deploy->storage->exists ($file->get ('name'))) {
					
					$this->deploy->logger->message ('Removing file '.$file->get ('name'));
					
					if (!$dryRun) {
						
						try {
							
							$this->removeFile ($file->get ('name'));
							//$this->cleanDirs (dirname ($this->deploy->storagename));
							
						} catch (\StorageException $e) {
							$this->deploy->logger->error ('Error while removing file '.$e->getFile ());
						}
						
					}
					
				}
				
			}
			
		}
		
		protected function writeFile ($file, $data) {
			
			$this->deploy->storage->makeDir ($this->deploy->storage->getDir ($file));
			
			$this->deploy->storage->write ($file, $data);
			$this->deploy->storage->chmod ($file, 0777);
			
		}
		
		protected function removeFile ($file) {
			return $this->deploy->storage->delete ($file);
		}
		
		protected function cleanDirs ($path) {
			
			while ($path != $this->rule->get ('destination') and !$this->countFiles ($path)) {
				
				rmdir ($path);
				$path = dirname ($path);
				
			}
			
		}
		
		// Check to see if a file should be ignored
		
		protected function isIgnored ($filename) {
			
			// Match by glob pattern
			foreach ($this->rule->get ('ignore') as $pattern)
				if (fnmatch ($pattern, $filename)) // TODO
					return true;
			
			// Match by path fragments
			
			$fragments = explode ('/', $filename);
			
			foreach ($fragments as $index => $fragment) {
				
				$path = implode ('/', array_slice ($fragments, 0, $index + 1));
				
				if (in_array ($path, $this->rule->get ('ignore')))
					return true;
				 
			}
			
			return false;
			
		}

		// Count the number of files in a directory, including ignored if required
		
		protected function countFiles ($path, $all = true) {
			
			$count = 0;
			
			$files = array_diff (scandir ($path), ['.', '..']);
			
			foreach ($files as $file)
				if ($all or !$this->isIgnored ($file))
					$count++;
			
			return $count;
			
		}
		
	}