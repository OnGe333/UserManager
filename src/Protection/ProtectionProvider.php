<?php 
namespace Onge\UserManager\Protection;

class ProtectionProvider implements ProtectionProviderInterface {
	protected $attemptStorage;

	protected $monitoredInterval;

	protected $recordedInterval;

	protected $minSlowDown;

	protected $maxSlowDown;
	
	protected $slowDownPerAttemptGeneral;

	protected $slowDownPerAttemptLoginSpecific;

	protected $autoclean;

	public function __construct(Storage\Attempt\StorageInterface $attemptStorage, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider, array $config = array()) {
		$this->attemptStorage = $attemptStorage;

		$this->monitoredInterval = isset($config['monitoredInterval']) ? $config['monitoredInterval'] : 10;
		$this->recordedInterval = isset($config['monitoredInterval']) ? $config['monitoredInterval'] : 43200; // 30 days default
		
		$this->challengeLimit = isset($config['challengeLimit']) ? $config['challengeLimit'] : 3;
		$this->warningLimit = isset($config['warningLimit']) ? $config['warningLimit'] : 10;
		$this->lockdownLimit = isset($config['lockdownLimit']) ? $config['lockdownLimit'] : 20;
		$this->totalLockdownLimit = isset($config['totalLockdownLimit']) ? $config['totalLockdownLimit'] : 0;

		$this->lockdownInterval = isset($config['lockdownInterval']) ? $config['lockdownInterval'] : 10;

		$this->minSlowDown = isset($config['minSlowDown']) ? $config['minSlowDown'] : 5000;
		$this->maxSlowDown = isset($config['maxSlowDown']) ? $config['maxSlowDown'] : 1000000;
		$this->slowDownPerAttemptGeneral = isset($config['slowDownPerAttemptGeneral']) ? $config['slowDownPerAttemptGeneral'] : 100;
		$this->slowDownPerAttemptLoginSpecific = isset($config['slowDownPerAttemptLoginSpecific']) ? $config['slowDownPerAttemptLoginSpecific'] : 10000;

		$this->autoclean = isset($config['autoclean']) ? $config['autoclean'] : true;

	}

	public function attempt($login = null, $ip = null) {
		if (is_null($ip)) {
			$ip = $this->getIp();
		}

		$data['login'] = $login;
		$data['ip'] = $ip;
		$data['url'] = $_SERVER['REQUEST_URI'];

		$this->attemptStorage->addAttempt($data);
	}

	public function protect($login = null, $ip = null) {
		// autocleanup
		if ($this->autocleanup) {
			$this->attemptStorage->
		}

		if (is_null($ip)) {
			$ip = $this->getIp();
		}

		return true;
	}

	public function slowDown($login) {
		$slowDown = $this->attemptStorage->totalAttempts($this->monitoredInterval) * $this->slowDownPerAttemptGeneral;
		
		if (!is_null($login)) {
			$slowDown += $this->attemptStorage->totalAttemptsLoginSpecific($this->monitoredInterval, $login)* $this->slowDownPerAttemptLoginSpecific;
		}

		$slowDown = min($this->maxSlowDown, max($this->minSlowDown, $slowDown));

		usleep($slowDown);
	}

	public function getIp()	{
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ipAddress) {
					$ipAddress = trim($ipAddress);

					if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
						return $ipAddress;
					}
				}
			}
		}
	}
}