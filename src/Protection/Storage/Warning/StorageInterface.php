<?php
namespace Onge\UserManager\Protection\Storage\Warning;

interface StorageInterface extends \Onge\UserManager\Protection\Storage\StorageInterface {
	/**
	 * create storage instance
	 * @param array $config associative array with settings
	 */
	public function __construct(array $config = []);

	/**
	 * delete old records
	 * @param  int    $time interval in minutes
	 */
	public function cleanup(int $time);

	/**
	 * try to find valid warning, if exist, return datetime of creation
	 * @param  string $login    login of user that issued warning
	 * @return string           datetime of warnting, if exists
	 */
	public function warningExist(string $login);

	/**
	 * create warning record
	 * @param int    $lifetime 	minutes, how long should be valid
	 * @param string $ip       	ip address
	 * @param string $login    	user login
	 */
	public function addWarning(int $lifetime, string $ip, string $login);

}