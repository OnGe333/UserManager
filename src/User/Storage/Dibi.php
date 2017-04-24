<?php
namespace Onge\UserManager\User\Storage;

class Dibi implements StorageInterface {
	protected $containerClass;

	protected $table;
	
	protected $id;

	protected $login;

	protected $email;

	protected $password;

	protected $activationCode;

	protected $passwordResetCode;

	protected $permanentLogin;

	public function __construct(array $config = []) {
		$this->containerClass = isset($config['containerClass']) ? $config['containerClass'] : 'Onge\\UserManager\\User\\User';
		$this->table = isset($config['table']) ?$config['table'] : 'user';
		$this->login = isset($config['login']) ?$config['login'] : 'email';
		$this->email = isset($config['email']) ?$config['email'] : 'email';
		$this->password = isset($config['password']) ? $config['password'] : 'password';
		$this->activationCode = isset($config['activationCode']) ? $config['activationCode'] : 'activation_code';
		$this->passwordResetCode = isset($config['passwordResetCode']) ? $config['passwordResetCode'] : 'password_reset_code';
		$this->permanentAuthCode = isset($config['permanentAuthCode']) ? $config['permanentAuthCode'] : 'permanent_auth_code';
		$this->id = isset($config['id']) ? $config['id'] : 'id';
	}

	public function getContainerClass() {
		return $this->containerClass;
	}

	public function findById($id) {
		$data = \dibi::query('SELECT * FROM %n', $this->table, 'WHERE %n', $this->id, ' = %i', $id)->fetch();
		
		if (empty($data)) {
			return false;
		} else {
			return $data->toArray();
		}
	}

	public function findByEmail($email) {
		$data = \dibi::query('SELECT * FROM %n', $this->table, 'WHERE %n', $this->email, ' = %s', $email)->fetch();
		
		if (empty($data)) {
			return false;
		} else {
			return $data->toArray();
		}
	}

	public function findByLogin($login) {
		$data = \dibi::query('SELECT * FROM %n', $this->table, 'WHERE %n', $this->login, ' = %s', $login)->fetch();
		
		if (empty($data)) {
			return false;
		} else {
			return $data->toArray();
		}
	}

	public function findByActivationCode($code) {
		$data = \dibi::query('SELECT * FROM %n', $this->table, 'WHERE %n', $this->activationCode, ' = %s', $code)->fetch();
		
		if (empty($data)) {
			return false;
		} else {
			return $data->toArray();
		}
	}

	public function findByPermanentLogin($code) {
		$data = \dibi::query('SELECT * FROM %n', $this->table, 'WHERE %n', $this->permanentAuthCode, ' = %s', $code)->fetch();
		
		if (empty($data)) {
			return false;
		} else {
			return $data->toArray();
		}
	}

	public function findByPasswordResetCode($code) {
		$data = \dibi::query('SELECT * FROM %n', $this->table, 'WHERE %n', $this->passwordResetCode, ' = %s', $code)->fetch();
		
		if (empty($data)) {
			return false;
		} else {
			return $data->toArray();
		}		
	}

	public function save(\Onge\UserManager\User\UserInterface $user) {
		$data = $user->getData();

		if (is_null($user->id())) {
			return \dibi::query('INSERT INTO %n', $this->table, 'SET %a', $data, ', created_time = NOW()');
		} else {
			return \dibi::query('UPDATE %n', $this->table, 'SET %a', $data, 'WHERE %n', $this->id, ' = %i', $user->id());
		}
	}

	public function isUnique($value, $column) {
		return \dibi::query('SELECT COUNT(*) FROM %n', $this->table, 'WHERE %n', $column, ' = %s', $value)->fetchSingle();
	}
}