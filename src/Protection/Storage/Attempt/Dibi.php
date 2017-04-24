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

	public function totalAttempts($time) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE))')->fetchSingle();
	}

	public function totalAttemptsLoginSpecific($time, $login) {
		return \dibi::query('SELECT COUNT(%n', $this->attempt_time, ') FROM %n', $this->table, 'WHERE %n', $this->attempt_time, ' > (DATE_SUB(NOW(), INTERVAL %i', $time, ' MINUTE)) AND %n', $this->login, ' = %s', $login, '')->fetchSingle();
	}

	public function addAttempt(array $data = array()) {
		return \dibi::query('INSERT INTO %n', $this->table, 'SET %a', $data, ', attempt_time = NOW()');
	}
}