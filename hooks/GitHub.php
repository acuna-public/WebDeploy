<?php
	
	namespace WebDeploy;
	
	class GitHub extends \WebDeploy {
		
		protected $data = [];
		
		protected function onParse () {
			
			$this->git = new \Git\GitHub (['login' => $this->config['login'], 'token' => $this->token]);
			
			$commit = $this->git->getCommit ($this->data);
			
			$this->set ('files', $commit->get ('files'));
			
		}
		
		protected function isDeploy (): bool {
			
			if (isset ($_SERVER['HTTP_X_GITHUB_EVENT']) or $this->debug) {
				
				if ($this->debug)
					$payload = file_get_contents (__DIR__.'/github.json');
				elseif ($_POST)
					$payload = $_POST['payload'];
				else
					$payload = file_get_contents ('php://input');
				
				$this->data = json_decode ($payload, true);
				
				$this->setRepositoryName ($this->data['repository']['name']);
				
				if (!$this->debug and $_SERVER['HTTP_X_GITHUB_EVENT'] == 'ping')
					$this->logger->message ('Ping received');
				else
					return true;
				
			}
			
			return false;
			
		}
		
	}