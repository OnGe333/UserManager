<?php
namespace Onge\UserManager\Protection\Storage\Warning;

class Dibi implements StorageInterface {
	protected $table;

	protected $ip;

	protected $login;

	protected $resolved;

	protected $warning_time;

	public function __construct(array $config = []) {
		$this->table = isset($config['table']) ? $config['table'] : 'warning';
		$this->ip = isset($config['ip']) ? $config['ip'] : 'ip';
		$this->login = isset($config['login']) ? $config['login'] : 'login';
		$this->resolved = isset($config['resolved']) ? $config['resolved'] : 'resolved';
		$this->warning_time = isset($config['warning_time']) ? $config['warning_time'] : 'warning_time';
	}

	public function cleanup(int $time) {
		return \dibi::query('DELETE FROM %n', $this->table, 'WHERE %n', $this->warning_time, ' < (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))');
	}

	public function warningExist(int $lifetime, string $login) {
		return \dibi::query('SELECT %n', $this->warning_time, ' FROM %n', $this->table, 'WHERE %n', $this->login, ' = %s', $login, 'AND %n', $this->resolved, ' = 0 AND %n', $this->warning_time, ' > DATE_SUB(NOW(), INTERVAL %i', $lifetime, 'MINUTE) ORDER BY warning_time DESC LIMIT 1')->fetchSingle();
	}

	public function addWarning(int $lifetime, string $ip, string $login) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %n', $this->ip, ' = %s,', $ip,' %n', $this->login, ' = %s', $login, ', %n', $this->warning_time, ' = DATE_ADD(NOW(), INTERVAL %i', $lifetime, 'MINUTE)');
	}

}