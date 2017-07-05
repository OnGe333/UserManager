<?php
namespace Onge\UserManager\User;

interface UserProviderInterface {

	/**
	 * @param Storage\StorageInterface 	$storageProvider 	instance of storage provider
	 */
	public function __construct(Storage\StorageInterface $storageProvider);

	/**
	 * acces to storage provider
	 *
	 */
	public function getStorage();

	/**
	 * get user data by id
	 *
	 * @param  int 	$id 	id of user
	 * @return \Onge\UserManager\User\UserInterface
	 */
	public function findById($id);

	/**
	 * get user data by login
	 *
	 * @param  string 	$login
	 * @return \Onge\UserManager\User\UserInterface
	 */
	public function findByLogin($login);

	/**
	 * get user data by permanent login code
	 *
	 * @param  string 	$id 	id of user
	 * @return \Onge\UserManager\User\UserInterface
	 */
	public function findByPermanentLogin($code);

	/**
	 * check if password is valid
	 *
	 * @param  string 	$password	plaintext password
	 * @return bool					true if valid
	 */
	public function validatePassword($password);

	/**
	 * check registration data, throw UserManagerArgumentException if something wrong.
	 *
	 * @param  array  $data [description]
	 * @return bool
	 */
	public function validateRegistration(array $data);

	/**
	 * create user container with data
	 *
	 * @param  array 	$data 	fill containers
	 * @return UserInterface
	 */
	public function newUser($data);

	/**
	 * use activation code to activate user
	 *
	 * @param  string 	$code 	activation code
	 * @return bool 	return true on success, otherwise false
	 */
	public function activateByCode($code);

	/**
	 * create new activation code for user. Old code is replaced
	 *
	 * @param  string 	$email 	email of user
	 * @return string/false 	new code on success, otherwise false
	 */
	public function refreshActivationCode($email);

	/**
	 * check user credentials and log in if valid
	 *
	 * @param  string 	$login    	user login
	 * @param  string 	$password 	user password
	 * @return bool
	 */
	public function authenticate(string $login, string $password);

	/**
	 * create password reset code and return it.
	 * If code is already created and not expired, return allways same code
	 *
	 * @param  string $email 	email of reseting user
	 * @return string/false  	pasword reset code, false on failure
	 */
	public function passwordResetCode($email);

	/**
	 * check if password reset code exists and has not expired
	 * @param  string 	$code 	password reset code
	 * @return bool				true if exist and not expired, otherwise false
	 */
	public function validatePasswordResetCode($code);

	/**
	 * set new password with password reset code
	 *
	 * @param  string 	$code     	password reset code
	 * @param  string 	$password 	new password (plaintext)
	 * @return bool 				true on success, otherwise false
	 */
	public function resetPassword($code, $password);

	/**
	 * generate random url-safe string to use as code
	 *
	 * @param  integer 	$length       	length of string
	 * @param  mixed 	$uniqueColumn 	name of database column to check if string is unique, or false if check is not necessary
	 */
	public function randomString($length = 48, $uniqueColumn = false);

	/**
	 * save user data
	 *
	 * @param  UserInterface 	$user 	container with user data
	 * @return bool
	 */
	public function save(UserInterface $user);
}