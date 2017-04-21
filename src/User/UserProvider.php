<?php
namespace Onge\UserManager\User;
use Onge\UserManager\UserManagerException;
use Onge\UserManager\UserManagerArgumentException;

class UserProvider implements UserProviderInterface {
	protected $storageProvider;

//	protected $sessionProvider;

	protected $passwordLength = 8;

	public function __construct(Storage\StorageInterface $storageProvider/*, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider*/) {
		
		$this->storageProvider = $storageProvider;

//		$this->sessionProvider = $sessionProvider;
	}

	/**
	 * get user data by id
	 * 
	 * @param  int 	$id 	id of user
	 * @return \Onge\UserManager\User\UserInterface
	 */
	public function findById($id) {
		if ($data = $this->storageProvider->findById($id)) {
			return $this->newUser($data);
		}

		return null;
	}

	/**
	 * get user data by login
	 * 
	 * @param  string 	$login
	 * @return \Onge\UserManager\User\UserInterface
	 */
	public function findByLogin($login) {
		if ($data = $this->storageProvider->findByLogin($login)) {
			return $this->newUser($data);
		}

		return null;
	}

	/**
	 * get user data by permanent login code
	 * 
	 * @param  string 	$id 	id of user
	 * @return \Onge\UserManager\User\UserInterface
	 */
	public function findByPermanentLogin($code) {
		if ($data = $this->storageProvider->findByPermanentLogin($code)) {
			return $this->newUser($data);
		}

		return null;
	}

	public function validatePassword($password) {
		if (!isset($password)) {
			throw new UserManagerArgumentException(_('Password is required'));
			return false;
		}

		if (mb_strlen($password) < $this->passwordLength) {
			throw new UserManagerArgumentException(sprintf(_('Password length is insufficient. Minimal length is %d characters.'), $this->passwordLength));
			return false;
		}

		return true;
	}

	/**
	 * check registration data, throw UserManagerArgumentException if something wrong.
	 * 
	 * @param  array  $data [description]
	 * @return bool
	 */
	public function validateRegistration(array $data) {
		if (!isset($data['login']) && !isset($data['email'])) {
			throw new UserManagerArgumentException(_('Email is required'));
			return false;
		}

		if ($this->validatePassword($data['password']) === false) {
			return false;
		}

		if (isset($data['email'])) {
			if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
				throw new UserManagerArgumentException(_('Email is not valid.'));
				return false;
			}
		}

		if (isset($data['email'])) {
			if ($this->storageProvider->findByEmail($data['email'])) {
				throw new UserManagerArgumentException(_('Email is already used.'));
				return false;
			}
		}

		if (isset($data['login'])) {
			if ($this->storageProvider->findByLogin($data['login'])) {
				throw new UserManagerArgumentException(_('Login is already used.'));
				return false;
			}
		}

		return true;
	}

	/**
	 * create user container with data
	 * 
	 * @param  array 	$data 	fill containers
	 * @return UserInterface
	 */
	public function newUser($data) {
		$class = $this->storageProvider->getContainerClass();
		return new $class($data);
	}

	/**
	 * use activation code to activate user
	 * 
	 * @param  string 	$code 	activation code
	 * @return bool 	return true on success, otherwise false
	 */
	public function activateByCode($code) {
		if ($user = $this->storageProvider->findByActivationCode($code)) {
			$user = $this->newUser($data);
			$user->setActive(true);
			$user->invalidateActivationCode();
			return $this->save($user);
		} else {
			return false;
		}
	}

	/**
	 * create new activation code for user. Old code is replaced
	 * 
	 * @param  string 	$email 	email of user 
	 * @return string/false 	new code on success, otherwise false
	 */
	public function refreshActivationCode($email) {
		if ($data = $this->storageProvider->findByEmail($email)) {
			$user = $this->newUser($data);

			if($user->active()) {
				return false;
			} else {
				$user->setActivationCode();
				$this->save($user);
				return $user->activationCode();
			}
		} else {
			return false;
		}
	}

	/**
	 * check user credentials and log in if valid
	 * 
	 * @param  string 	$login    	user login
	 * @param  string 	$password 	user password
	 * @return bool
	 */
	public function authenticate(string $login, string $password) {
		if ($data = $this->storageProvider->findByLogin($login)) {
			$user = $this->newUser($data);

			if ($user->checkPassword($password)) {
				if ($user->active()) {				
					$user->setLastLogin();
					$this->save($user);
					return $user->id();
				}
			}
		}
		
		return false;
	}

	public function passwordResetCode($email) {
		if ($data = $this->storageProvider->findByEmail($email)) {
			$user = $this->newUser($data);
			if (is_null($user->passwordResetTime()) || (strtotime($user->passwordResetTime()) + 86400) < time()) {
				$user->setPasswordResetCode($this->randomString());
				$user->setPasswordResetTime();
				$this->save($user);
			}

			return $user->passwordResetCode();
		} else {
			return false;
		}		
	}

	public function validatePasswordResetCode($code) {
		if ($this->storageProvider->findByPasswordResetCode($code)) {
			return true;
		}

		return false;
	}

	public function resetPassword($code, $password) {
		if ($data = $this->storageProvider->findByPasswordResetCode($code)) {
			$user = $this->newUser($data);
			$user->setPassword($password);
			$user->clearPasswordReset();

			$this->save($user);
			return true;
		}

		return false;		
	}

	public function randomString($length = 48, $uniqueColumn = false) {
		while (true) {
			$bytes = random_bytes($length);

			$string = mb_substr(str_replace(array('/', '+', '='), (mt_rand(0, 1) ? '-' : '_'), base64_encode($bytes)), 0, $length);

			if ($uniqueColumn === false) {
				break;
			} else {
				if (!$this->storageProvider()->isUnique($string, $uniqueColumn)) {
					break;
				}
			}
		}

		if (mb_strlen($string) == $length) {
			return $string;
		} else {
			throw new UserManagerException(_('Unable to generate random string.'));
		}
	}

	/**
	 * save user data
	 * 
	 * @param  UserInterface 	$user 	container with user data
	 * @return bool
	 */
	public function save(UserInterface $user) {
		return $this->storageProvider->save($user);
	}
}