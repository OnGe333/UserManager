<?php
namespace Onge\UserManager\Protection;

interface ProtectionProviderInterface {
	/**
	 * Set up dependencies and configurate variables.
	 *
	 * @param Storage\Attempt\StorageInterface                   $attemptStorage  storage provider instance
	 * @param Storage\Lockdown\StorageInterface                  $lockdownStorage storage provider instance
	 * @param \Onge\UserManager\Session\SessionProviderInterface $sessionProvider session provider instance
	 * @param array                                              $config          configuration associative array
	 */
	public function __construct(Storage\Attempt\StorageInterface $attemptStorage, Storage\Lockdown\StorageInterface $lockdownStorage, Storage\Warning\StorageInterface $warningStorage, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider, array $config = array());

	/**
	 * create record attempt - failed or otherwise suspictios
	 *
	 * @param  string $login login name of attempting user
	 * @param  string $ip    attempt IP address
	 * @return void
	 */
	public function attempt($login = null, $ip = null);

	/**
	 * create record attempt - failed or otherwise suspictios
	 *
	 * @param  string $login login name of attempting user
	 * @param  string $ip    attempt IP address
	 * @return void
	 */
	public function challenge($login = null, $ip = null);

	/**
	 * create record attempt - failed or otherwise suspictios
	 *
	 * @param  string $login login name of attempting user
	 * @return void
	 */
	public function warning($login);

	/**
	 * shall action be locked down?
	 * Checks number of failed attempts in monitored interval.
	 * If there is more attempts than lockdown limit, lockdown begins
	 * It is up to you to lock users action
	 *
	 * @param  string $login attempt login name
	 * @param  string $ip    attempt IP address
	 * @return string/false  false if there is no lockdown, otherwise return time when lockdown ends
	 */
	public function lockdown($login = null, $ip = null);

	/**
	 * Longest interval from settings in minutes. Useful for autocleanup
	 *
	 * @return int	minutes of interval
	 */
	public function longestInterval();

	/**
	 * get monitored interval
	 *
	 * @return int 	minutes of interval
	 */
	public function monitoredInterval();

	/**
	 * get recorded interval
	 *
	 * @return int 	minutes of interval
	 */
	public function recordedInterval();

	/**
	 * get lockdown interval
	 *
	 * @return int 	minutes of interval
	 */
	public function lockdownInterval();

	/**
	 * how many attempts before lockdown
	 *
	 * @return int number of attempts
	 */
	public function lockdownLimit();

	/**
	 * get IP address of request
	 *
	 * @return string IP address
	 */
	public function getIp();
}