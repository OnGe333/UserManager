<?php
namespace Onge\UserManager\Protection\Storage\Attempt;

interface StorageInterface extends \Onge\UserManager\Protection\Storage\StorageInterface {
	/**
	 * @param array $config		associative array with configuration
	 */
	public function __construct(array $config = []);

	/**
	 * get total number of attempts in time
	 * @param  int    $time minutes
	 * @return int    		number of attempts
	 */
	public function totalAttempts(int $time);

	/**
	 * get total number of attempts for login in time
	 * @param  int    	$time  	minutes
	 * @param  string 	$login 	user login
	 * @return int    			number of attempts
	 */
	public function totalAttemptsLogin(int $time, string $login);

	/**
	 * get total number of attempts for ip address in time
	 * @param  int    	$time  	minutes
	 * @param  string 	$ip 	attempt ip
	 * @return int    			number of attempts
	 */
	public function totalAttemptsIp(int $time, string $ip);

	/**
	 * record suspicious attempt
	 * @param string 	$ip		attempt ip
	 * @param string 	$login	attempting user login
	 */
	public function addAttempt($ip, $login);

	/**
	 * delete expired suspictious attempts
	 * @param  int    $time 	minutes, how long should be attempts kept
	 */
	public function cleanup(int $time);
}