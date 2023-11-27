<?php
	/**
	 * WebDeploy
	 * https://github.com/acuna-public/WebDeploy
	 * @author Acuna
	 * @license GPLv3
	 * @version 1.4
	 */
	
	require 'WebDeploy.php';
	require_once 'ConfigRule.php';
	require_once 'Deployment.php';
	
	abstract class WebDeploy extends \AssocArray {
		
		const VERSION = '1.4';
		
		protected $matched = [], $filters = [];
		public $debug = 0, $git;
		
		public $token, $configs, $config = [];
		
		public \Storage $storage;
		public \Logger $logger;
		
		function __construct (string $token, array $configs, \Storage $storage, \Logger $logger) {
			
			parent::__construct ();
			
			$this->token = $token;
			$this->configs = $configs;
			$this->storage = $storage;
			$this->logger = $logger;
			
			$this->logger->setName ('WebDeploy', self::VERSION);
			
		}
		
		function getRequiredPairs (): array {
			return ['repository', 'files'];
		}
		
		protected function addRules () {
			
			$rule = new \WebDeploy\ConfigRule ($this);
			
			if ($rule->compare ()) {
				
				$deploy = new \WebDeploy\Deployment ($this, $rule);
				
				$deploy->process ();
				
				$this->logger->setLogLevel ();
				
			}
			
		}
		
		protected function setRepositoryName ($name) {
			$this->set ('repository', $name);
		}
		
		abstract protected function onParse ();
		abstract protected function isDeploy (): bool;
		
		final function deploy () {
			
			if ($this->isDeploy ()) {
				
				if (isset ($this->configs[$this->get ('repository')])) {
					
					if ($this->config = $this->configs[$this->get ('repository')]) {
						
						$this->onParse ();
						$this->addRules ();
						
					} else $this->logger->error ('Config is empty', 406);
					
				} else $this->logger->error ('Repository \''.$this->get ('repository').'\' not found in deployment config', 404);
				
			}
			
			echo json_encode ($this->logger->message, JSON_PRETTY_PRINT);
			
		}
		
	}