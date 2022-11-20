<?php
	/**
	 * WebDeploy
	 * https://github.com/acuna-public/WebDeploy
	 * @author Acuna
	 * @license GPLv3
	 * @version 1.2
	 */
	
	require_once 'AssocArray.php';
	require_once 'WebDeploy.php';
	require_once 'ConfigRule.php';
	require_once 'Deployment.php';
	
	abstract class WebDeploy extends AssocArray {
		
		const VERSION = '1.2';
		
		protected $matched = [], $filters = [];
		public $debug = 0, $git;
		
		public $token, $config, $storage, $logger;
		
		function __construct (string $token, array $config, Storage $storage, Logger $logger) {
			
			parent::__construct ();
			
			$this->token = $token;
			$this->config = $config;
			$this->storage = $storage;
			$this->logger = $logger;
			
			$this->logger->setName ('WebDeploy', self::VERSION);
			
		}
		
		function getRequiredPairs (): array {
			return ['repository', 'files'];
		}
		
		protected function addRule () {
			
			try {
				
				if (isset ($this->config[$this->get ('repository')])) {
					
					$rule = new WebDeploy\ConfigRule ($this);
					
					if ($rule->compare ()) {
						
						$this->storage->config['path'] = $rule->get ('destination');
						
						$deploy = new WebDeploy\Deployment ($this, $rule);
						
						$deploy->process ();
						
						$this->logger->setLogLevel ();
						
					}
					
				} else $this->logger->error ('Rules for repository \''.$this->get ('repository').'\' not found in deployment config', 404);
				
			} catch (\Exception $e) {
				$this->logger->error ($e->getMessage (), $e->getCode ());
			}
			
		}
		
		final function getConfig (): array {
			return $this->config[$this->get ('repository')];
		}
		
		abstract protected function onParse ();
		abstract protected function isDeploy (): bool;
		
		final function deploy () {
			
			try {
				
				if ($this->isDeploy ()) {
					
					if ($this->config) {
						
						$this->onParse ();
						$this->addRule ();
						
					} else $this->logger->error ('Config is empty', 204);
					
				}
				
				$this->logger->statusCode = ($this->logger->message['error'] ? 403 : 200);
				
				$this->logger->sendStatus ();
				
			} catch (\GitException $e) {
				$this->logger->error ($e->getMessage (), $e->getCode ());
			}
			
		}
		
	}