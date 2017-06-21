<?php
namespace Onge\UserManager\Protection\Storage\Warning;

class Dibi implements StorageInterface {
	/**
	 * database table name
	 * @var string
	 */
	protected $table;

	/**
	 * column name
	 * @var string
	 */
	protected $ip;

	/**
	 * column name
	 * @var string
	 */
	protected $login;

	/**
	 * column name
	 * @var string
	 */
	protected $resolved;

	/**
	 * column name
	 * @var string
	 */
	protected $warning_time;

	/**
	 * create storage instance
	 * @param array $config associative array with settings
	 */
	public function __construct(array $config = []) {
		$this->table = isset($config['table']) ? $config['table'] : 'warning';
		$this->ip = isset($config['ip']) ? $config['ip'] : 'ip';
		$this->login = isset($config['login']) ? $config['login'] : 'login';
		$this->resolved = isset($config['resolved']) ? $config['resolved'] : 'resolved';
		$this->warning_time = isset($config['warning_time']) ? $config['warning_time'] : 'warning_time';
	}

	/**
	 * delete old records
	 * @param  int    $time interval in minutes
	 */
	public function cleanup(int $time) {
		return \dibi::query('DELETE FROM %n', $this->table, 'WHERE %n', $this->warning_time, ' < (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))');
	}

	/**
	 * try to find valid warning, if exist, return datetime of creation
	 * @param  string $login    login of user that issued warning
	 * @return string           datetime of warnting, if exists
	 */
	public function warningExist(string $login) {
		return \dibi::query('SELECT %n', $this->warning_time, ' FROM %n', $this->table, 'WHERE %n', $this->login, ' = %s', $login, 'AND %n', $this->resolved, ' = 0 AND %n', $this->warning_time, ' > NOW() ORDER BY warning_time DESC LIMIT 1')->fetchSingle();
	}

	/**
	 * create warning record
	 * @param int    $lifetime 	minutes, how long should be valid
	 * @param string $ip       	ip address
	 * @param string $login    	user login
	 */
	public function addWarning(int $lifetime, string $ip, string $login) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %n', $this->ip, ' = %s,', $ip,' %n', $this->login, ' = %s', $login, ', %n', $this->warning_time, ' = DATE_ADD(NOW(), INTERVAL %i', $lifetime, 'MINUTE)');
	}

}