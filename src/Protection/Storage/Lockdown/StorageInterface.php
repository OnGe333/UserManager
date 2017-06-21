<?php
namespace Onge\UserManager\Protection\Storage\Lockdown;

interface StorageInterface {
	/**
	 * create storage instance
	 * @param array $config associative array with settings
	 */
	public function __construct(array $config = []);

	/**
	 * delete old records by provided timestamp
	 * @param  int    $time timestamp - will delete all older records
	 * @return mixed  false on failure
	 */
	public function cleanup(int $time);

	/**
	 * is there lockdown in effect for IP?
	 * @param  string  $ip IP address to lock down
	 * @return boolean
	 */
	public function isLockedIp(string $ip);

	/**
	 * is there lockdown in effect for user login?
	 * @param  string  $login user login to lock down
	 * @return boolean
	 */
	public function isLockedLogin(string $login);

	/**
	 * lock user by login and/or IP. IP or login may be null, but at least one of those variables must be set
	 * @param  int    $lifetime how long shall lockdown last in minutes
	 * @param  string $ip       IP address to lock
	 * @param  string $login    user login to lock
	 * @return mixed            false on failure
	 */
	public function lock(int $lifetime, string $ip = null, string $login = null);
}