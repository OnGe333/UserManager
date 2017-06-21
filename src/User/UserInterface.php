<?php
namespace Onge\UserManager\User;

interface UserInterface {
	/**
	 * @param array $data	associative array with user data
	 */
	public function __construct(array $data);

	/**
	 * get user data for storage
	 *
	 * @return array	all updateable user data
	 */
	public function getData();

	/**
	 * get user id
	 *
	 * @return mixed	user id
	 */
	public function id();

	/**
	 * set user id
	 *
	 * @param mixed $value
	 */
	public function setId($value);

	/**
	 * get user login
	 *
	 * @return mixed	user login
	 */
	public function login();

	/**
	 * set user login
	 *
	 * @param mixed $value
	 */
	public function setLogin($value);

	/**
	 * get user email
	 *
	 * @return string	user email
	 */
	public function email();

	/**
	 * set user email
	 *
	 * @param string 	$value
	 */
	public function setEmail($value);

	/**
	 * set password - password is immediately hashed and can not be retrived
	 *
	 * @param string $value
	 */
	public function setPassword($value);

	/**
	 * verify if password is valid
	 *
	 * @param  string 	$value	password to check
	 * @return bool        		true if valid, otherwise false
	 */
	public function checkPassword($value);

	/**
	 * is user active?
	 *
	 * @return bool
	 */
	public function active();

	/**
	 * set user active flag
	 *
	 * @param boolean $active true/false
	 */
	public function setActive($value);

	/**
	 * get current activation code
	 *
	 * @return string
	 */
	public function activationCode();

	/**
	 * delete current activation code, so it is no longer valid
	 *
	 * @return string
	 */
	public function invalidateActivationCode();

	/**
	 * get activation time
	 *
	 * @return string 	activation datetime
	 */
	public function activationTime();

	/**
	 * get current password reset code
	 *
	 * @return string
	 */
	public function passwordResetCode();

	public function passwordResetTime();

	public function setPasswordResetTime();

	public function setActivationCode($code);

	public function setPasswordResetCode($code);

	public function authCode();

	public function setAuthCode($code);

	public function clearAuthCode();

	public function clearPasswordReset();

	public function lastLogin();

	public function setLastLogin();
}