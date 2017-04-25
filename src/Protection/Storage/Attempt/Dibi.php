<?php
namespace Onge\UserManager\Protection\Storage\Attempt;

class Dibi implements StorageInterface {
	protected $table;
	
	protected $ip;

	protected $login;

	protected $attempt_time;

	public function __construct(array $config = []) {
		$this->table = isset($config['table']) ? $config['table'] : 'attempt';
		$this->ip = isset($config['ip']) ? $config['ip'] : 'ip';
		$this->login = isset($config['login']) ? $config['login'] : 'login';
		$this->attempt_time = isset($config['attempt_time']) ? $config['attempt_time'] : 'attempt_time';
	}

	public function totalAttempts(int $time) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))')->fetchSingle();
	}

	public function totalAttemptsLogin(int $time, string $login) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE)) AND %n', $this->login, ' = %s', $login, '')->fetchSingle();
	}

	public function totalAttemptsIp(int $time, string $ip) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE)) AND %n', $this->ip, ' = %s', $ip, '')->fetchSingle();
	}

	public function addAttempt($ip, $login) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %n', $this->ip, ' = %s', $ip, ', %n', $this->login, ' = %s', $login, ', attempt_time = NOW()');
	}

	public function cleanup(int $time) {
		return \dibi::query('DELETE FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' < (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))');
	}
}