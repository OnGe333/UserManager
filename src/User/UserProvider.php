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
	 * @return array
	 */
	public function findById($id) {
		return $this->storageProvider->findById($id);
	}

	/**
	 * get user data by login
	 * 
	 * @param  int 	$id 	id of user
	 * @return array
	 */
	public function findByLogin($login) {
		return $this->storageProvider->findByLogin($login);
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

		if (!isset($data['password'])) {
			throw new UserManagerArgumentException(_('Password is required'));
			return false;
		}

		if (mb_strlen($data['password']) < $this->passwordLength) {
			throw new UserManagerArgumentException(sprintf(_('Password length is insufficient. Minimal length is %d characters.'), $this->passwordLength));
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
		if ($data = $this->storageProvider->findByActivationCode($code)) {
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