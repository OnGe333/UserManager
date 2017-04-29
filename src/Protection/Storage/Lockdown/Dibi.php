<?php
namespace Onge\UserManager\Protection\Storage\Lockdown;

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
	protected $expires;

	/**
	 * create storage instance
	 * @param array $config associative array with settings
	 */
	public function __construct(array $config = []) {
		$this->table = isset($config['table']) ? $config['table'] : 'lockdown';
		$this->ip = isset($config['ip']) ? $config['ip'] : 'ip';
		$this->login = isset($config['login']) ? $config['login'] : 'login';
		$this->expires = isset($config['expires']) ? $config['expires'] : 'expires';
	}

	/**
	 * delete old records by provided timestamp
	 * @param  int    $time timestamp - will delete all older records
	 * @return mixed
	 */
	public function cleanup(int $time) {
		return \dibi::query('DELETE FROM %n', $this->table, 'WHERE %n', $this->expires, ' < (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))');
	}

	/**
	 * is there lockdown in effect for IP?
	 * @param  string  $ip IP address to lock down
	 * @return boolean     
	 */
	public function isLockedIp(string $ip) {
		return \dibi::query('SELECT %n', $this->expires, ' FROM %n', $this->table, 'WHERE %n', $this->ip, ' = %s', $ip, 'AND %n', $this->expires, ' > NOW() ORDER BY expires DESC LIMIT 1')->fetchSingle();
	}

	/**
	 * is there lockdown in effect for user login?
	 * @param  string  $login user login to lock down
	 * @return boolean     
	 */
	public function isLockedLogin(string $login) {
		return \dibi::query('SELECT %n', $this->expires, ' FROM %n', $this->table, 'WHERE %n', $this->login, ' = %s', $login, 'AND %n', $this->expires, ' > NOW() ORDER BY expires DESC LIMIT 1')->fetchSingle();
	}

	/**
	 * lock user by login and/or IP. IP or login may be null, but at least one of those variables must be set
	 * @param  int    $lifetime how long shall lockdown last in minutes
	 * @param  string $ip       IP address to lock
	 * @param  string $login    user login to lock
	 * @return mixed            false on failure
	 */
	public function lock(int $lifetime, string $ip = null, string $login = null) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %n', $this->ip, ' = %s,', $ip,' %n', $this->login, ' = %s', $login, ', %n', $this->expires, ' = DATE_ADD(NOW(), INTERVAL %i', $lifetime, 'MINUTE)');
	}

}