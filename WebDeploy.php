<?php
  /**
   * WebDeploy
   * https://github.com/
   * @author Acuna
   * @license GPLv3
   * @version 1.0.1
   */
  
  require_once 'AssocArray.php';
  require_once 'Logger.php';
  require_once 'WebDeploy.php';
  require_once 'ConfigRule.php';
  require_once 'Deployment.php';
  
  abstract class WebDeploy extends AssocArray {
    
    const VERSION_INFO = 'WebDeploy v1.0.1', NL = '<br/>';
    
    protected $config, $matched = [], $filters = [], $git;
    public $debug = 0, $storage, $logger;
    
    function __construct (array $config, Storage\Adapter $storage, string $log) {
      
      parent::__construct ();
      
      $this->config = $config;
      $this->storage = $storage;
      $this->logger = new WebDeploy\Logger ($log);
      
    }
    
    protected function getRequiredPairs (): array {
      return ['repository', 'files'];
    }
    
    protected function addRule (string $name) {
      
      try {
        
        if (isset ($this->config[$this->get ('repository')])) {
          
          $rule = new WebDeploy\ConfigRule ($this);
          
          if ($rule->compare ()) {
            
            $rule->set ('name', $name);
            
            $this->storage->config['path'] = $rule->get ('destination');
            
            $deploy = new \WebDeploy\Deployment ($this, $rule);
            
            if ($deploy->deploy ())
              $this->results[] = $deploy->result;
            
            $this->logger->setLogLevel ();
            
            foreach ($rule->get ('repositories') as $repo)
              $this->addRule ($repo);
            
          }
          
        } else $this->logger->error ('Rules for repository "'.$this->get ('repository').'" not found in the deployment config', 500);
        
      } catch (\Exception $e) {
        $this->logger->error ($e->getMessage (), $e->getCode ());
      }
      
    }
    
    final function getConfig (): array {
      return $this->config[$this->get ('repository')];
    }
    
    protected function logStatus ($success = true) {
      
      if ($success)
        $this->logger->setStatus ($message);
      else
        $this->logger->setStatus ($message, 500);
        
    }
    
    abstract protected function onParse ();
    abstract protected function isDeploy (): bool;
    
    function readFile ($file): string {
      return $this->git->readFile ($this->get ('repository'), $file);
    }
    
    final function deploy () {
      
      if ($this->isDeploy ()) {
        
        if ($this->config) {
          
          $this->onParse ();
          $this->addRule ($this->get ('repository'));
          
        } else $this->logger->error ('Config is empty', 500);
        
      }
      
      $this->logger->sendStatus ();
      
    }
    
  }