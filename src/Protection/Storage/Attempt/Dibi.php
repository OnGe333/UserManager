<?php
namespace Onge\UserManager\Protection\Storage\Attempt;

class Dibi implements StorageInterface {
	/**
	 * database table name
	 * @var string
	 */
	protected $table;

	/**
	 * database column name
	 * @var string
	 */
	protected $ip;

	/**
	 * database column name
	 * @var string
	 */
	protected $login;

	/**
	 * database column name
	 * @var string
	 */
	protected $attempt_time;

	/**
	 * @param array $config		associative array with settings
	 */
	public function __construct(array $config = []) {
		$this->table = isset($config['table']) ? $config['table'] : 'attempt';
		$this->ip = isset($config['ip']) ? $config['ip'] : 'ip';
		$this->login = isset($config['login']) ? $config['login'] : 'login';
		$this->attempt_time = isset($config['attempt_time']) ? $config['attempt_time'] : 'attempt_time';
	}

	/**
	 * get total number of attempts in time
	 * @param  int    $time minutes
	 * @return int    		number of attempts
	 */
	public function totalAttempts(int $time) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))')->fetchSingle();
	}

	/**
	 * get total number of attempts for login in time
	 * @param  int    	$time  	minutes
	 * @param  string 	$login 	user login
	 * @return int    			number of attempts
	 */
	public function totalAttemptsLogin(int $time, string $login) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE)) AND %n', $this->login, ' = %s', $login, '')->fetchSingle();
	}

	/**
	 * get total number of attempts for ip address in time
	 * @param  int    	$time  	minutes
	 * @param  string 	$ip 	attempt ip
	 * @return int    			number of attempts
	 */
	public function totalAttemptsIp(int $time, string $ip) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE)) AND %n', $this->ip, ' = %s', $ip, '')->fetchSingle();
	}

	/**
	 * record suspicious attempt
	 * @param string 	$ip		attempt ip
	 * @param string 	$login	attempting user login
	 */
	public function addAttempt($ip, $login) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %n', $this->ip, ' = %s', $ip, ', %n', $this->login, ' = %s', $login, ', attempt_time = NOW()');
	}

	/**
	 * delete expired suspictious attempts
	 * @param  int    $time 	minutes, how long should be attempts kept
	 */
	public function cleanup(int $time) {
		return \dibi::query('DELETE FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' < (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))');
	}
}