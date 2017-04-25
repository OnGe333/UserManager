<?php
namespace Onge\UserManager\Protection\Storage\Lockdown;

class Dibi implements StorageInterface {
	protected $table;
	
	protected $ip;

	protected $login;

	protected $expires;

	public function __construct(array $config = []) {
		$this->table = isset($config['table']) ? $config['table'] : 'lockdown';
		$this->ip = isset($config['ip']) ? $config['ip'] : 'ip';
		$this->login = isset($config['login']) ? $config['login'] : 'login';
		$this->expires = isset($config['expires']) ? $config['expires'] : 'expires';
	}

	public function cleanup(int $time) {
		return \dibi::query('DELETE FROM %n', $this->table, 'WHERE %n', $this->expires, ' < (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))');
	}

	public function isLockedIp(string $ip) {
		return \dibi::query('SELECT %n', $this->expires, ' FROM %n', $this->table, 'WHERE %n', $this->ip, ' = %s', $ip, 'AND %n', $this->expires, ' > NOW() ORDER BY expires DESC LIMIT 1')->fetchSingle();
	}

	public function isLockedLogin(string $login) {
		return \dibi::query('SELECT %n', $this->expires, ' FROM %n', $this->table, 'WHERE %n', $this->login, ' = %s', $login, 'AND %n', $this->expires, ' > NOW() ORDER BY expires DESC LIMIT 1')->fetchSingle();
	}

	public function lock(int $lifetime, string $ip, string $login) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %n', $this->ip, ' = %s,', $ip,' %n', $this->login, ' = %s', $login, ', %n', $this->expires, ' = DATE_ADD(NOW(), INTERVAL %i', $lifetime, 'MINUTE)');
	}

}