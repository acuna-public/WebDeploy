<?php
  
  // Class to perform all file changes
  
  namespace WebDeploy;
  
  class Deployment {
    
    public $result;
    
    protected $files = ['modified' => [], 'removed' => []], $errors = 0;
    
    function __construct (\WebDeploy $hook, ConfigRule $rule) {
      
      $this->hook = $hook;
      $this->rule = $rule;
      
    }
    
    protected function setup () {
      
      $this->hook->logger->setLogLevel ($this->rule->get ('log-level'));
      
      $commitId = substr ($this->hook->get ('commit-id'), 0, 6);
      
      $this->hook->logger->message ('Deploying '.$commitId.' ('.$this->hook->get ('branch').') from '.$this->hook->get ('repository').\WebDeploy::NL.'Destination: '.$this->rule->get ('destination'));
      
      try {
        
        $this->hook->storage->makeDir ();
        return true;
        
      } catch (\StorageException $e) {
        $this->hook->logger->error ('Error creating destination directory', 500);
      }
      
      return false;
      
    }
    
    function deploy (): bool {
      
      if ($this->setup ()) {
        
        if (!$this->deployFiles ())
          $this->result = 'failure';
        
        if (!$this->errors) {
          
          $this->result = 'success';
          $this->hook->logger->message ('Repository deployed successfully in mode '.$this->rule->get ('mode'));
          
          return true;
          
        } else {
          
          $this->result = $this->errors.' error'.($this->errors != 1 ? 's' : '');
          
          $this->hook->logger->message ('Repository deployed in mode '.$this->rule->get ('mode').' with '.$this->result);
          
        }
        
      }
      
      return false;
      
    }
    
    // Determine the actual deployment mode to use
    protected function getMode () {
      
      if ($this->hook->get ('forced')) {
        
        $mode = 'replace';
        $this->hook->logger->message ('Forced update - deploying all files');
        
      } elseif (in_array ($this->rule->get ('mode'), ['deploy', 'dry-run'])) {
        
        if (!$this->countFiles ($this->rule->get ('destination'), false)) {
          
          $mode = 'replace';
          $this->hook->logger->message ('Destination is empty - deploying all files');
          
        } else $mode = 'update';
        
      } else $mode = $this->rule->get ('mode');
      
      return $mode;
      
    }
    
    // Extract files according to WebDeploy commit data
    protected function deployFiles () {
      
      $dryRun = ($this->rule->get ('mode') == 'dry-run');
      
      //$this->hook->logger->message ('Modified files: '.implode (', ', $this->files['modified']), Logger::LOG_DEBUG);
      //$this->hook->logger->message ('Removed files: '.implode (', ', $this->files['removed']), Logger::LOG_DEBUG);
      //$this->hook->logger->message ('Repository files: '.implode (', ', $archive->listFiles ()), Logger::LOG_DEBUG);
      
      foreach ($this->hook->get ('files') as $file) {
        
        if ($this->isIgnored ($file->get ('name'))) {
          
          $this->hook->logger->message ('Skipping ignored file '.$file->get ('name'), Logger::LOG_VERBOSE);
          continue;
          
        }
        
        if (
          ($file->get ('status') == 'modified' and $this->getMode () != 'replace') or // Изменен локально
          !$this->hook->storage->exists ($file->get ('name')) // Не существует на сервере
        ) {
          
          $this->hook->logger->message ('Writing file '.$file->get ('name'), Logger::LOG_VERBOSE);
          
          if (!$dryRun) {
            
            try {
              $this->writeFile ($file->get ('name'), $this->hook->readFile ($file->get ('name')));
            } catch (\StorageException $e) {
              
              $this->hook->logger->message ('Error writing to file '.$e->getFile (), Logger::LOG_BASIC);
              $this->errors++;
              
            }
            
          }
          
        } elseif ($file->get ('status') == 'removed' and $this->hook->storage->exists ($file->get ('name'))) {
          
          $this->hook->logger->message ('Removing file '.$file->get ('name'), Logger::LOG_VERBOSE);
          
          if (!$dryRun) {
            
            try {
              
              $this->removeFile ($file->get ('name'));
              //$this->cleanDirs (dirname ($this->hook->storagename));
              
            } catch (\StorageException $e) {
              
              $this->hook->logger->message ('Error while removing file '.$e->getFile (), Logger::LOG_BASIC);
              $this->errors++;
              
            }
            
          }
          
        }
        
      }
      
      return true;
      
    }
    
    protected function writeFile ($file, $data) {
      
      $this->hook->storage->makeDir ($this->hook->storage->getDir ($file));
      return $this->hook->storage->write ($file, $data);
      
    }
    
    protected function removeFile ($file) {
      return ($this->hook->storage->isFile ($file) && $this->hook->storage->delete ($file));
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
      foreach ($this->rule->get ('ignore') as $pattern) {
        if (fnmatch ($pattern, $filename)) // TODO
          return true;
      }
      
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
    protected function countFiles ($path, $all=true) {
      $count = 0;
      $files = array_diff (scandir ($path), ['.', '..']);
      foreach ($files as $file) {
        if ($all || !$this->isIgnored ($file))
          $count ++;
      }
      return $count;
    }
    
  }