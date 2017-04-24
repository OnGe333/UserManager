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

	protected $permaloginHttpOnly = true;

	protected $permaloginSecure = true;

	protected function __construct(User\UserProviderInterface $userProvider, Session\SessionProviderInterface $sessionProvider, Cookie\CookieProviderInterface $cookieProvider, Protection\ProtectionProviderInterface $protectionProvider) {
		
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

	public function userProvider() {
		return $this->userProvider;
	}

	public static function getProtectionProvider() {
		return static::getInstance()->protectionProvider();
	}

	public function protectionProvider() {
		return $this->protectionProvider;
	}

	public static function getSessionProvider() {
		return static::getInstance()->sessionProvider();
	}

	public function sessionProvider() {
		return $this->sessionProvider;
	}

	public static function getCookieProvider() {
		return static::getInstance()->cookieProvider();
	}

	public function cookieProvider() {
		return $this->cookieProvider;
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

	public static function validatePasswordResetCode($code) {
		return static::getInstance()->userProvider()->validatePasswordResetCode($code);
	}

	public static function resetPassword($code, $password) {
		if (static::getInstance()->userProvider()->validatePassword($password)) {
			return static::getInstance()->userProvider()->resetPassword($code, $password);
		} else {
			return false;
		}
	}
	/**
	 * Is user logged in? Return true if is, otherwise false
	 * 
	 * @return bool 	
	 */
	public static function check() {
		if (static::currentUser() instanceof UserInterface) {
			return true;
		} elseif (static::getInstance()->sessionProvider()->check()) {
			$user = static::getInstance()->userProvider()->findById(static::getInstance()->sessionProvider()->get('id'));
			if ($user) {	
				static::getInstance()->setCurrentUser($user);
				return true;
			}
			return false;
		} elseif ($permalogin = static::getInstance()->cookieProvider()->get('permalogin')) {
			$userData = static::getInstance()->userProvider()->findByPermanentLogin($permalogin);
			if ($user) {	
				static::getInstance()->setCurrentUser($user);
				return true;
			}
			return false;
		}

		return false;
	}

	/**
	 * Get instance of currently logged in user
	 * 
	 * @return Onge\UserManager\User\UserInterface
	 */
	public static function currentUser() {
		return static::getInstance()->getCurrentUser();

	}

	/**
	 * Get instance of currently logged in user
	 * 
	 * @return Onge\UserManager\User\UserInterface
	 */
	public function getCurrentUser() {
		return $this->currentUser;
	}


	/**
	 * Set currently logged in user
	 * 
	 * @param User\UserInterface $user User container
	 */
	public function setCurrentUser(User\UserInterface $user) {
		return $this->currentUser = $user;
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

	/**
	 * Authenticate and log in user
	 * @param  string       $login     	user login
	 * @param  string       $password  	user password
	 * @param  bool|boolean $permanent 	if true, create cookie with permanent login code
	 * @return int|bool                	id of logged in user, false on fail
	 */
	public static function authenticate(string $login, string $password, bool $permanent = false) {
		if ($userId = static::getInstance()->userProvider()->authenticate($login, $password)) {
			static::getInstance()->sessionProvider()->login($userId);

			if ($permanent) {
				$user = static::getInstance()->userProvider()->newUser(static::getInstance()->userProvider()->findById($userId));
				$authCode = static::getInstance()->userProvider()->randomString();
				$user->setAuthCode($authCode);

				// permanent auth cookie should always be secure and httponly
				if (static::getInstance()->cookieProvider()->setPermanent('permalogin', $authCode, array('secure' => static::getInstance()->getPermaloginSecure(), 'httponly' => static::getInstance()->getPermaloginHttpOnly()))) {
					static::getInstance()->userProvider()->save($user);
				} else {
					// fail silently
					// throw new UserManagerException('Unable to set permanent cookie');
				}
			}
			return $userId;
		}
		return false;
	}

	/**
	 * terminate user session and clear permanent login code
	 */
	public static function logout() {
		$user = static::currentUser();
		if ($user instanceof User\UserInterface) {
			static::getInstance()->sessionProvider()->logout();
			static::getInstance()->cookieProvider()->unset('permalogin', array('secure' => static::getInstance()->getPermaloginSecure(), 'httponly' => static::getInstance()->getPermaloginHttpOnly()));
			$user->clearAuthCode();
			static::getInstance()->userProvider()->save($user);
		}
	}

	public static function attempt($login = null) {
		return static::getInstance()->protectionProvider()->attempt($login);
	}

	public static function protect($login = null) {
		return static::getInstance()->protectionProvider()->protect($login);
	}

	public static function slowDown($login = null) {
		return static::getInstance()->protectionProvider()->slowDown($login);
	}

	public static function activateByCode($code) {
		return static::getInstance()->userProvider()->activateByCode($code);
	}

	public static function refreshActivationCode($email) {
		return static::getInstance()->userProvider()->refreshActivationCode($email);
	}

	public static function passwordResetCode($email) {
		return static::getInstance()->userProvider()->passwordResetCode($email);
	}

	public function getPermaloginSecure() {
		return $this->permaloginSecure;
	}

	public static function setPermaloginSecure(bool $value) {
		static::getInstance()->permaloginSecure = $value;
	}

	public function getPermaloginHttpOnly() {
		return $this->permaloginHttpOnly;
	}

	public static function setPermaloginHttpOnly(bool $value) {
		static::getInstance()->permaloginHttpOnly = $value;
	}
}