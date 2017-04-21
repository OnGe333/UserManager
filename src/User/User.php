<?php
namespace Onge\UserManager\User;

class User implements UserInterface {
	protected $id;
	protected $email;
	protected $password;
	protected $active;
	protected $activationCode;
	protected $activationTime;
	protected $createdTime;
	protected $passwordResetCode;
	protected $passwordResetTime;
	protected $lastLogin;
	protected $permanentAuthCode;

	protected $passwordCost = 10; // password_hash cost value, see: http://php.net/manual/en/function.password-hash.php

	public function __construct(array $data) {
		$this->id = isset($data['id']) ? $data['id'] : null;
		$this->email = isset($data['email']) ? $data['email'] : (isset($data['login']) ? $data['login'] : null);
		$this->password = isset($data['password']) ? $data['password'] : null;
		$this->active = isset($data['active']) ? $data['active'] : null;
		$this->activationCode = isset($data['activation_code']) ? $data['activation_code'] : (isset($data['activationCode']) ? $data['activationCode'] : null);
		$this->activationTime = isset($data['activation_time']) ? $data['activation_time'] : (isset($data['activationTime']) ? $data['activationTime'] : null);
		$this->createdTime = isset($data['created_time']) ? $data['created_time'] : (isset($data['createdTime']) ? $data['createdTime'] : null);
		$this->passwordResetCode = isset($data['password_reset_code']) ? $data['password_reset_code'] : (isset($data['passwordResetCode']) ? $data['passwordResetCode'] : null);
		$this->passwordResetTime = isset($data['password_reset_time']) ? $data['password_reset_time'] : (isset($data['passwordResetTime']) ? $data['passwordResetTime'] : null);
		$this->lastLogin = isset($data['last_login']) ? $data['last_login'] : (isset($data['lastLogin']) ? $data['lastLogin'] : null);
		$this->permanentAuthCode = isset($data['permanent_auth_code']) ? $data['permanent_auth_code'] : (isset($data['permanentAuthCode']) ? $data['permanentAuthCode'] : null);

	}

	public function getData() {
		return array(
//			'id' = $this->id,	// shoud not be updated
			'email' => $this->email,
			'password' => $this->password,
			'active' => $this->active,
			'activation_code' => $this->activationCode,
			'activation_time' => $this->activationTime,
//			'createdTime' = $this->createdTime,  	// shoud not be updated
			'password_reset_code' => $this->passwordResetCode,
			'password_reset_time' => $this->passwordResetTime,
			'last_login' => $this->lastLogin,
			'permanent_auth_code' => $this->permanentAuthCode,
			);
	}

	public function randomString($length = 48) {
		$bytes = random_bytes($length);

		$string = mb_substr(str_replace(array('/', '+', '='), (mt_rand(0, 1) ? '-' : '_'), base64_encode($bytes)), 0, $length);

		if (mb_strlen($string) == $length) {
			return $string;
		} else {
			throw new UserManagerException(_('Unable to generate random string.'));
		}
	}
	
	public function id() {
		return $this->id;
	}

	public function setId($value) {
		$this->id = $value;
	}

	public function login() {
		return $this->email;
	}

	public function setLogin($value) {
		$this->email = $value;
	}

	public function email() {
		return $this->email;
	}

	public function setEmail($value) {
		$this->email = $value;
	}

	public function setPassword($value) {
		$this->password = password_hash($value, PASSWORD_DEFAULT, array('cost' => $this->passwordCost));
	}

	public function checkPassword($value) {
		$verified = password_verify($value, $this->password);

		if ($verified) {
			if (password_needs_rehash($this->password, PASSWORD_DEFAULT, array('cost' => $this->passwordCost))) {
				$this->setPassword($value);
			}
			return true;
		}

		return false;
	}

	public function active() {
		return $this->active;
	}

	public function setActive($active = true) {
		$this->active = $active;
	}

	public function activationCode() {
		return $this->activationCode;
	}

	public function invalidateActivationCode() {
		$this->activationCode = null;
	}

	public function activationTime() {
		return $this->activationTime;
	}

	public function passwordResetCode() {
		return $this->passwordResetCode;
	}

	public function passwordResetTime() {
		return $this->passwordResetTime;
	}

	public function setPasswordResetTime() {
		$this->passwordResetTime = date('Y-m-d H:i:s');
	}

	public function setActivationCode($code) {
		$this->activationCode = $code;
	}

	public function setPasswordResetCode($code) {
		$this->passwordResetCode = $code;
	}

	public function authCode() {
		return $this->permanentAuthCode;
	}

	public function setAuthCode($code) {
		$this->permanentAuthCode = $code;
	}

	public function clearAuthCode() {
		$this->permanentAuthCode = null;
	}

	public function clearPasswordReset() {
		$this->passwordResetCode = null;
		$this->passwordResetTime = null;
	}

	public function lastLogin() {
		return $this->lastLogin;
	}

	public function setLastLogin() {
		$this->lastLogin = date('Y-m-d H:i:s');
	}
}