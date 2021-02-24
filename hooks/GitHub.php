<?php
  
  namespace WebDeploy;
  
  class GitHub extends \WebDeploy {
    
    protected $commit;
    
    protected function onParse () {
      
      if ($this->debug)
        $_POST['payload'] = file_get_contents (__DIR__.'/github.json');
      
      $data = json_decode ($_POST['payload'], true);
      
      $this->set ('repository', $data['repository']['name']);
      
      $this->git = new \Git\Adapter\GitHub ($this->getConfig ());
      
      $this->commit = $this->git->getCommit ($this->get ('repository'), $data['head_commit']['id']);
      
      $this->set ('files', $this->commit->get ('files'));
      
    }
    
    protected function isDeploy (): bool {
      
      if (isset ($_SERVER['HTTP_X_GITHUB_EVENT']) or $this->debug) {
        
        if (!$this->debug and $_SERVER['HTTP_X_GITHUB_EVENT'] == 'ping')
          $this->logger->success ('Ping received');
        else
          return true;
        
      }
      
      return false;
      
    }
    
  }