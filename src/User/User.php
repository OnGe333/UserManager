<?php
namespace Onge\UserManager\User;

class User implements UserInterface {
	/**
	 * user id
	 * @var int/null
	 */
	protected $id;

	/**
	 * user email
	 * @var string/null
	 */
	protected $email;

	/**
	 * hashed user password
	 * @var string/null
	 */
	protected $password;

	/**
	 * is user active?
	 * @var int/null 1/0
	 */
	protected $active;

	/**
	 * activation code for user. Should be null, if already activated
	 * @var string/null
	 */
	protected $activationCode;

	/**
	 * datetime of user account activation
	 * @var string/null
	 */
	protected $activationTime;

	/**
	 * datetime of user account creation
	 * @var string/null
	 */
	protected $createdTime;

	/**
	 * pasword reset code. Should be null, if password reset is not issued or expired
	 * @var string/null
	 */
	protected $passwordResetCode;

	/**
	 * datetime of password reset code generation
	 * @var string/null
	 */
	protected $passwordResetTime;

	/**
	 * last login datetime
	 * @var string/null
	 */
	protected $lastLogin;

	/**
	 * permanent auth code expected from cookie
	 * @var string/null
	 */
	protected $permanentAuthCode;

	/**
	 * password_hash cost value, see: http://php.net/manual/en/function.password-hash.php
	 * @var integer
	 */
	protected $passwordCost = 10;

	/**
	 * @param array $data	associative array with user data
	 */
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

	/**
	 * get user data for storage
	 *
	 * @return array	all updateable user data
	 */
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

	/**
	 * get user id
	 *
	 * @return mixed	user id
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * set user id
	 *
	 * @param mixed $value
	 */
	public function setId($value) {
		$this->id = $value;
	}

	/**
	 * get user login
	 *
	 * @return string	user login
	 */
	public function login() {
		return $this->email;
	}

	/**
	 * set user login
	 *
	 * @param mixed $value
	 */
	public function setLogin($value) {
		$this->email = $value;
	}

	/**
	 * get user email
	 *
	 * @return string	user email
	 */
	public function email() {
		return $this->email;
	}

	/**
	 * set user email
	 *
	 * @param string 	$value
	 */
	public function setEmail($value) {
		$this->email = $value;
	}

	/**
	 * set password - password is immediately hashed and can not be retrived
	 *
	 * @param string $value
	 */
	public function setPassword($value) {
		$this->password = password_hash($value, PASSWORD_DEFAULT, array('cost' => $this->passwordCost));
	}

	/**
	 * verify if password is valid
	 *
	 * @param  string 	$value	password to check
	 * @return bool        		true if valid, otherwise false
	 */
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

	/**
	 * is user active?
	 *
	 * @return bool
	 */
	public function active() {
		return $this->active;
	}

	/**
	 * set user active flag
	 *
	 * @param boolean $active true/false
	 */
	public function setActive($active = true) {
		$this->active = $active;
	}

	/**
	 * get current activation code
	 *
	 * @return string
	 */
	public function activationCode() {
		return $this->activationCode;
	}

	/**
	 * delete current activation code, so it is no longer valid
	 *
	 * @return string
	 */
	public function invalidateActivationCode() {
		$this->activationCode = null;
	}

	/**
	 * get activation time
	 *
	 * @return string 	activation datetime
	 */
	public function activationTime() {
		return $this->activationTime;
	}

	/**
	 * get current password reset code
	 *
	 * @return string
	 */
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