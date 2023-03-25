<?php
	
	namespace WebDeploy;
	
	class Deployment {
		
		protected $files = ['modified' => [], 'removed' => []];
		
		function __construct (\WebDeploy $deploy, ConfigRule $rule) {
			
			$this->deploy = $deploy;
			$this->rule = $rule;
			
		}
		
		function process () {
			
			$this->deploy->logger->message ('Starting to deploy \''.$this->deploy->get ('repository').'\' repository...');
			
			$this->deployFiles ();
			
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
			
			$this->deploy->logger->setLogLevel ($this->rule->get ('log-level'));
			
			if ($commit = substr ($this->deploy->get ('commit-id'), 0, 6))
				$this->deploy->logger->message ('Deploying '.$commit.' ('.$this->deploy->get ('branch').') from '.$this->deploy->get ('repository'));
			
			$dryRun = ($this->rule->get ('mode') == 'dry-run');
			
			$this->deploy->logger->message ('Modified files: '.implode (', ', $this->files['modified']), \Logger::LOG_DEBUG);
			$this->deploy->logger->message ('Removed files: '.implode (', ', $this->files['removed']), \Logger::LOG_DEBUG);
			//$this->deploy->logger->message ('Repository files: '.implode (', ', $archive->listFiles ()), \Logger::LOG_DEBUG);
			
			foreach ($this->deploy->get ('files') as $file) {
				
				try {
					
					$this->deploy->storage->setFile ($this->rule->get ('destination').'/'.$file->get ('name'));
					
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
							
							$this->deploy->storage->makeDir ();
							
							$this->deploy->storage->write ($this->deploy->git->readFile ($this->deploy->get ('repository'), $file->get ('name')));
							
							$this->deploy->logger->message ('File '.$file->get ('name').' written succesfully');
							
						}
						
					} elseif ($file->get ('status') == 'removed' and $this->deploy->storage->exists ()) {
						
						$this->deploy->logger->message ('Removing file '.$file->get ('name'));
						
						if (!$dryRun) {
							
							$this->deploy->storage->delete ();
							//$this->cleanDirs (dirname ($this->deploy->storagename));
							$this->deploy->logger->message ('File '.$file->get ('name').' deleted succesfully');
							
						}
						
					}
					
				} catch (\StorageException $e) {
					$this->deploy->logger->error ($e->toString ());
				}
				
			}
			
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