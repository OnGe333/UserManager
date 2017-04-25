<?php 
namespace Onge\UserManager\Protection;

class ProtectionProvider implements ProtectionProviderInterface {
	protected $attemptStorage;

	protected $lockdownStorage;

	protected $monitoredInterval;

	protected $recordedInterval;

	protected $minSlowDown;

	protected $maxSlowDown;
	
	protected $slowDownPerAttemptGeneral;

	protected $slowDownPerAttemptLoginSpecific;

	protected $autocleanup;

	public function __construct(Storage\Attempt\StorageInterface $attemptStorage, Storage\Lockdown\StorageInterface $lockdownStorage, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider, array $config = array()) {
		$this->attemptStorage = $attemptStorage;
		$this->lockdownStorage = $lockdownStorage;

		$this->monitoredInterval = isset($config['monitoredInterval']) ? $config['monitoredInterval'] : 30;
		$this->recordedInterval = isset($config['recordedInterval']) ? $config['recordedInterval'] : 43200; // 30 days default
		
		$this->challengeLimit = isset($config['challengeLimit']) ? $config['challengeLimit'] : 3;
		$this->warningLimit = isset($config['warningLimit']) ? $config['warningLimit'] : 10;
		$this->lockdownLimit = isset($config['lockdownLimit']) ? $config['lockdownLimit'] : 20;

		$this->lockdownInterval = isset($config['lockdownInterval']) ? $config['lockdownInterval'] : 30; // should not be less than monitoredInterval - otherwise there is risk user will fail another attempt and will be locked out again at first try.

		$this->minSlowDown = isset($config['minSlowDown']) ? $config['minSlowDown'] : 5000;
		$this->maxSlowDown = isset($config['maxSlowDown']) ? $config['maxSlowDown'] : 1000000;
		$this->slowDownPerAttemptGeneral = isset($config['slowDownPerAttemptGeneral']) ? $config['slowDownPerAttemptGeneral'] : 100;
		$this->slowDownPerAttemptLoginSpecific = isset($config['slowDownPerAttemptLoginSpecific']) ? $config['slowDownPerAttemptLoginSpecific'] : 10000;

		$this->autocleanup = isset($config['autocleanup']) ? $config['autocleanup'] : true;

	}

	public function attempt($login = null, $ip = null) {
		// autocleanup
		if ($this->autocleanup) {
			$this->attemptStorage()->cleanup($this->longestInterval());
		}

		if (is_null($ip)) {
			$ip = $this->getIp();
		}

		$data['login'] = $login;
		$data['ip'] = $ip;
		$data['url'] = $_SERVER['REQUEST_URI'];

		$this->attemptStorage()->addAttempt($data);
	}

	public function lockdown($login = null, $ip = null) {
		// autocleanup
		if ($this->autocleanup) {
			$this->lockdownStorage()->cleanup($this->longestInterval());
		}

		if (is_null($ip)) {
			$ip = $this->getIp();
		}

		if (!empty($login)) {
			if ($expire = $this->lockdownStorage()->isLockedLogin($login)) {
				return $expire;
			} else {
				echo $attempts = $this->attemptStorage()->totalAttemptsLogin($this->monitoredInterval(), $login);
				if ($attempts > $this->lockdownLimit()) {
					$this->lockdownStorage()->lock($this->lockdownInterval(), $ip, $login);
					return $this->lockdownInterval();
				}				
			}
		}

		if (empty($ip)) {
			// unable to determine IP
			// maybe access from localhost and IP not set
			// or something is really wrong
		} else {
			if ($expire = $this->lockdownStorage()->isLockedIp($ip)) {
				return $expire;
			} else {
				$attempts = $this->attemptStorage()->totalAttemptsIp($this->monitoredInterval(), $ip);
				if ($attempts > $this->lockdownLimit()) {
					$this->lockdownStorage()->lock($ip, $login, $this->lockdownInterval());
					return date('Y-m-d H:i:s', time() + ($this->lockdownInterval() * 60));
				}				
			}
		}

		return false;
	}

	public function slowDown($login) {
		$slowDown = $this->attemptStorage()->totalAttempts($this->monitoredInterval()) * $this->slowDownPerAttemptGeneral;
		
		if (!is_null($login)) {
			$slowDown += $this->attemptStorage()->totalAttemptsLogin($this->monitoredInterval(), $login) * $this->slowDownPerAttemptLoginSpecific;
		}

		$slowDown = min($this->maxSlowDown, max($this->minSlowDown, $slowDown));

		usleep($slowDown);
	}

	public function longestInterval() {
		return max($this->monitoredInterval(), $this->recordedInterval());
	}

	public function monitoredInterval() {
		return $this->monitoredInterval;
	}

	public function recordedInterval() {
		return $this->recordedInterval;
	}

	public function lockdownInterval() {
		return $this->lockdownInterval;
	}

	protected function lockdownStorage() {
		return $this->lockdownStorage;
	}
	
	protected function attemptStorage() {
		return $this->attemptStorage;
	}

	public function lockdownLimit() {
		return $this->lockdownLimit;
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