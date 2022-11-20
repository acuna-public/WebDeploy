<?php
	
	namespace WebDeploy;
	
	class GitHub extends \WebDeploy {
		
		protected $commit;
		
		protected function onParse () {
			
			if ($this->debug)
				$payload = file_get_contents (__DIR__.'/github.json');
			elseif ($_POST)
				$payload = $_POST['payload'];
			else
				$payload = file_get_contents ('php://input');
			
			$data = json_decode ($payload, true);
			
			$this->set ('repository', $data['repository']['name']);
			
			$config = $this->getConfig ();
			
			$this->git = new \Git\GitHub (['login' => $config['login'], 'token' => $this->token]);
			
			$this->commit = $this->git->getCommit ($data);
			
			$this->set ('files', $this->commit->get ('files'));
			
		}
		
		protected function isDeploy (): bool {
			
			if (isset ($_SERVER['HTTP_X_GITHUB_EVENT']) or $this->debug) {
				
				if (!$this->debug and $_SERVER['HTTP_X_GITHUB_EVENT'] == 'ping')
					$this->logger->message ('Ping received');
				else
					return true;
				
			}
			
			return false;
			
		}
		
	}