<?php 
namespace Onge\UserManager;

class UserManager {
	/**
	 * Instance of user manager object
	 * 
	 * @var Onge\UserManager\UserManager
	 */
	protected static $instance;

	/**
	 * Instance of currently logged in user
	 * 
	 * @var Onge\UserManager\User\UserInterface
	 */
	protected $currentUser;

	protected $userProvider;

	protected $sessionProvider;

	protected $cookieProvider;

	protected $protectionProvider;

	protected function __construct(User\UserProviderInterface $userProvider, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider, \Onge\UserManager\Cookie\CookieProviderInterface $cookieProvider, Protection\ProtectionProviderInterface $protectionProvider) {
		
		$this->userProvider = $userProvider;
		
		$this->sessionProvider = $sessionProvider;
		
		$this->cookieProvider = $cookieProvider;
		
		$this->protectionProvider = $protectionProvider;
	}

	/**
	 * Set up dependencies 
	 * 
	 * @param  array  $dependencies 	see __constructor params
	 * @return void
	 */
	public static function prepareInstance(array $dependencies) {
		if (isset($dependencies['userProvider'])) {
			$userProvider = isset($dependencies['userProvider']) ? $dependencies['userProvider'] : new User\UserProvider(new User\Storage\Dibi(), new Session\SessionProvider());
			$sessionProvider = isset($dependencies['sessionProvider']) ? $dependencies['sessionProvider'] : new Session\SessionProvider();
			$cookieProvider = isset($dependencies['cookieProvider']) ? $dependencies['cookieProvider'] : new Cookie\CookieProvider();
			$protectionProvider = isset($dependencies['protectionProvider']) ? $dependencies['protectionProvider'] : new Protection\ProtectionProvider();
		} else {
			throw new Exception('User Provider not defined');
		}
		
		static::$instance = new static($userProvider, $sessionProvider, $cookieProvider, $protectionProvider);
	}

	protected static function getInstance() {
		if (is_null(static::$instance)) {
			static::prepareInstance();
		}

		return static::$instance;
	}

	public static function getUserProvider() {
		return static::getInstance()->userProvider();
	}

	public function protectionProvider() {
		return $this->protectionProvider;
	}

	public function userProvider() {
		return $this->userProvider;
	}

	public function sessionProvider() {
		return $this->sessionProvider;
	}

	/**
	 * Get instance of user by id
	 * 
	 * @param  int 		$id 		id of user to find
	 * @return Onge\UserManager\User\UserInterface
	 */
	public static function findById($id) {
		return static::getInstance()->userProvider()->findById($id);
	}

	/**
	 * Get instance of user by login parameter
	 * 
	 * @param  string	$login 		login name of user to find
	 * @return Onge\UserManager\User\UserInterface
	 */
	public static function findByLogin($login) {
		return static::getInstance()->userProvider()->findByLogin($login);
	}

	/**
	 * Is user logged in? Return true if is, otherwise false
	 * 
	 * @return bool 	
	 */
	public static function check() {
		return static::getInstance()->sessionProvider()->check();
	}

	/**
	 * Get instance of currently logged in user
	 * 
	 * @return Onge\UserManager\User\UserInterface
	 */
	public static function currentUser() {

	}

	/**
	 * Create new user and return it. Requirements are set in UserProvider
	 * 
	 * @param  array 	$data 	associative array of user data.
	 * @return Onge\UserManager\User\UserInterface or false on error
	 */
	public static function register($data) {
		if (static::getInstance()->userProvider()->validateRegistration($data)) {
			$user = static::getInstance()->userProvider()->newUser($data);

			$user->setPassword($data['password']);
			$user->setActive(false);
			$user->setActivationCode();

			if (static::getInstance()->userProvider()->save($user)) {
				return $user;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function authenticate(string $login, string $password, bool $permanent = false) {
		if ($userId = static::getInstance()->userProvider()->authenticate($login, $password)) {
			$this->sessionProvider()->login($userId);

			if ($permanent) {
				
			}
		}

		return $userId;
	}

	public static function attempt() {
		return static::getInstance()->protectionProvider()->attempt();
	}

	public static function protect() {
		return static::getInstance()->protectionProvider()->protect();
	}

	public static function activateByCode($code) {
		return static::getInstance()->userProvider()->activateByCode($code);
	}

	public static function refreshActivationCode($email) {
		return static::getInstance()->userProvider()->refreshActivationCode($email);
	}
}