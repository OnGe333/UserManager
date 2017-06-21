<?php
namespace Onge\UserManager\User\Storage;

class StorageMockup implements StorageInterface {
	protected $containerClass;

	protected $data;

	public function __construct(array $config = []) {
		$this->containerClass = isset($config['containerClass']) ? $config['containerClass'] : 'Onge\\UserManager\\User\\User';
		
		$record['id'] = 1;
		$record['email'] = 'test@test.com';
		$record['password'] = 'password';
		$record['active'] = 1;
		$record['activation_code'] = 'activation_code';
		$record['activation_time'] = '2017-01-01';
		$record['password_reset_code'] = 'password_reset_code';
		$record['password_reset_time'] = '2017-02-02';

		$this->data[1] = $record;
	}

	public function container($data) {
		$class = $this->containerClass;

		return new $class($data);
	}

	public function findById($id) {
		if (isset($this->data[$id])) {
			return $this->data[$id];
		} else {
			return false;
		}
	}

	public function getContainerClass() {
		return $this->containerClass;
	}
}