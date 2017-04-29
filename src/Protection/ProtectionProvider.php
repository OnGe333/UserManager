<?php 
namespace Onge\UserManager\Protection;

class ProtectionProvider implements ProtectionProviderInterface {
	protected $attemptStorage;

	protected $lockdownStorage;

	protected $monitoredInterval;

	protected $recordedInterval;

	protected $autocleanup;

	/**
	 * Set up dependencies and configurate variables. 
	 * Any config variable may be ommited, then default value is set
	 * 
	 * @param Storage\Attempt\StorageInterface                   $attemptStorage  storage provider instance
	 * @param Storage\Lockdown\StorageInterface                  $lockdownStorage storage provider instance
	 * @param \Onge\UserManager\Session\SessionProviderInterface $sessionProvider session provider instance
	 * @param array                                              $config          configuration associative array
	 */
	public function __construct(Storage\Attempt\StorageInterface $attemptStorage, Storage\Lockdown\StorageInterface $lockdownStorage, Storage\Warning\StorageInterface $warningStorage, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider, array $config = array()) {
		$this->attemptStorage = $attemptStorage;
		$this->warningStorage = $warningStorage;
		$this->lockdownStorage = $lockdownStorage;

		$this->monitoredInterval = isset($config['monitoredInterval']) ? $config['monitoredInterval'] : 30;
		$this->recordedInterval = isset($config['recordedInterval']) ? $config['recordedInterval'] : 43200; // 30 days default
		
		$this->challengeLimit = isset($config['challengeLimit']) ? $config['challengeLimit'] : 3;
		$this->warningLimit = isset($config['warningLimit']) ? $config['warningLimit'] : 10;
		$this->lockdownLimit = isset($config['lockdownLimit']) ? $config['lockdownLimit'] : 20;

		$this->warningInterval = isset($config['warningInterval']) ? $config['warningInterval'] : 1440; // 24 hours
		$this->lockdownInterval = isset($config['lockdownInterval']) ? $config['lockdownInterval'] : 30; // should not be less than monitoredInterval - otherwise there is risk user will fail another attempt and will be locked out again at first try.

		$this->autocleanup = isset($config['autocleanup']) ? $config['autocleanup'] : true;

	}

	/**
	 * create record attempt - failed or otherwise suspictios
	 * 
	 * @param  string $login login name of attempting user
	 * @param  string $ip    attempt IP address. If null, provider try to guess
	 * @return void
	 */
	public function attempt($login = null, $ip = null) {
		// autocleanup
		if ($this->autocleanup) {
			$this->attemptStorage()->cleanup($this->longestInterval());
		}

		if (is_null($ip)) {
			$ip = $this->getIp();
		}

		$this->attemptStorage()->addAttempt($ip, $login);
	}

	/**
	 * Challenge shall be issued?
	 * Checks number of failed attempts in monitored interval. 
	 * If there is more attempts than challenge limit, challenge like CAPTCHA should be added to form
	 * It is up to you to implement challenge.
	 * 
	 * @param  string $login 	attempt login name
	 * @param  string $ip    	attempt IP address. If null, provider try to guess
	 * @return bool  		 	true if challenge is required, otherwise false
	 */
	public function challenge($login = null, $ip = null) {
		if (is_null($ip)) {
			$ip = $this->getIp();
		}

		if (!empty($login)) {
			$attempts = $this->attemptStorage()->totalAttemptsLogin($this->monitoredInterval(), $login);
			if ($attempts > $this->challengeLimit()) {
				return true;
			}
		}

		if (empty($ip)) {
			// unable to determine IP
			// maybe access from localhost and IP not set
			// or something is really wrong
		} else {
			$attempts = $this->attemptStorage()->totalAttemptsIp($this->monitoredInterval(), $ip);
			if ($attempts > $this->challengeLimit()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Warning shall be issued?
	 * Checks number of failed attempts in monitored interval. 
	 * If there is more attempts than warning limit, warning should be issued
	 * It is up to you to implement warning. It may be log record, email to admin, user, force user to change password or whatever else.
	 * 
	 * @param  string $login 	attempt login name
	 * @return bool  		 	true if challenge is required, otherwise false
	 */
	public function warning($login) {
		// autocleanup
		if ($this->autocleanup) {
			$this->warningStorage()->cleanup($this->longestInterval());
		}

		if ($this->warningStorage()->warningExist($this->warningInterval(), $login)) {
			return true;
		} else {
			$attempts = $this->attemptStorage()->totalAttemptsLogin($this->monitoredInterval(), $login);
			if ($attempts > $this->warningLimit()) {
				$this->warningStorage()->warning($this->warningInterval(), $login);
				return true;
			}				
		}

		return false;
	}

	/**
	 * shall action be locked down? 
	 * Checks number of failed attempts in monitored interval. 
	 * If there is more attempts than lockdown limit, lockdown begins
	 * It is up to you to lock users action since there is lockdown
	 * 
	 * @param  string $login attempt login name
	 * @param  string $ip    attempt IP address. If null, provider try to guess
	 * @return string/false  false if there is no lockdown, otherwise return time when lockdown ends
	 */
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
				$attempts = $this->attemptStorage()->totalAttemptsLogin($this->monitoredInterval(), $login);
				if ($attempts > $this->lockdownLimit()) {
					$this->lockdownStorage()->lock($this->lockdownInterval(), null, $login);
					return date('Y-m-d H:i:s', time() + ($this->lockdownInterval() * 60));
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
					$this->lockdownStorage()->lock($this->lockdownInterval(), $ip, null);
					return date('Y-m-d H:i:s', time() + ($this->lockdownInterval() * 60));
				}				
			}
		}

		return false;
	}

	/**
	 * Longest interval from settings in minutes. Useful for autocleanup
	 * 
	 * @return int	minutes of interval
	 */
	public function longestInterval() {
		return max($this->monitoredInterval(), $this->recordedInterval());
	}

	/**
	 * get monitored interval
	 * 
	 * @return int 	minutes of interval
	 */
	public function monitoredInterval() {
		return $this->monitoredInterval;
	}

	/**
	 * get recorded interval
	 * 
	 * @return int 	minutes of interval
	 */
	public function recordedInterval() {
		return $this->recordedInterval;
	}

	/**
	 * get warning interval
	 * 
	 * @return int 	minutes of interval
	 */
	public function warningInterval() {
		return $this->warningInterval;
	}

	/**
	 * get lockdown interval
	 * 
	 * @return int 	minutes of interval
	 */
	public function lockdownInterval() {
		return $this->lockdownInterval;
	}

	/**
	 * storage for user warning
	 * 
	 * @return Storage\Warning\StorageInterface
	 */
	protected function warningStorage() {
		return $this->warningStorage;
	}

	/**
	 * storage for user lockdown
	 * 
	 * @return Storage\Lockdown\StorageInterface
	 */
	protected function lockdownStorage() {
		return $this->lockdownStorage;
	}
	
	/**
	 * storage for user lockdown
	 * 
	 * @return Storage\Attempt\StorageInterface
	 */
	protected function attemptStorage() {
		return $this->attemptStorage;
	}

	/**
	 * how many attempts before warning
	 * 
	 * @return int number of attempts
	 */
	public function warningLimit() {
		return $this->warningLimit;
	}

	/**
	 * how many attempts before lockdown
	 * 
	 * @return int number of attempts
	 */
	public function lockdownLimit() {
		return $this->lockdownLimit;
	}

	/**
	 * try to guess IP from _SERVER variables, ignores local IP
	 * 
	 * @return string IP address
	 */
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