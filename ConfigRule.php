<?php
	
	/**
		Class to hold, validate and match a single config rule
	*/
	
	namespace WebDeploy;
	
	class ConfigRule extends \AssocArray {
		
		protected $hook;
		
		function __construct (\WebDeploy $hook) {
			
			$this->hook = $hook;
			
			parent::__construct ($this->hook->config);
			
		}
		
		function getPairs (): array {
			
			return [
				
				'branches' => [],
				'events' => [],
				'pre-releases' => false,
				'ignore' => [],
				'log-level' => 'basic',
				'mode' => 'update',
				
			];
			
		}
		
		function getRequiredPairs (): array {
			return ['destination', 'mode'];
		}
		
		function getRequiredValues (): array {
			return ['mode' => ['update', 'replace', 'deploy', 'dry-run']];
		}
		
		/**
			Attempt to match rule against WebDeploy
		*/
		function compare (): bool {
			
			$this->validate ();
			
			if ($this->get ('events') and !in_array ($this->get ('event'), $this->get ('events')))
				$this->hook->logger->error ('Wrong event \''.$this->get ('event').'\', events must be '.implode (', ', $this->get ('events')), 400);
			elseif ($this->get ('event') == 'release' and $this->get ('pre-release') and !$this->get ('pre-releases'))
				$this->hook->logger->error ('Event \''.$this->get ('event').'\' must have \'pre-release\' option', 400);
			elseif ($this->get ('branches') and !$this->branchMatch ())
				$this->hook->logger->error ('Wrong branch \''.$this->get ('branch').'\'', 400);
			else
				return true;
			
			return false;
			
		}
		
		protected function branchMatch () {
			
			foreach ($this->get ('branches') as $branch)
				if (strpos ($this->get ('branch'), $branch) === 0)
					return true;
			
			return false;
			
		}
		
	}