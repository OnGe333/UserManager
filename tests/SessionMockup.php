<?php 
namespace Onge\UserManager\Session;

class SessionMockup implements SessionProviderInterface {
	protected $prefix;

	protected $data;

	public function __construct($prefix = 'userManager_') {
		$this->prefix = $prefix;
	}

	public function check() {
		return (isset($this->data[$this->prefix . 'id']) && $this->data[$this->prefix . 'id'] > 0);
	}

	public function login($id) {
		if ($this->check()) {
			$this->logout();
		}

		return $this->data[$this->prefix . 'id'] = $id;
	}

	public function logout() {
		if (isset($this->data[$this->prefix . 'id'])) {
			unset($this->data[$this->prefix . 'id']);
		}
	}

	public function set($key, $value) {
		$this->data[$this->prefix . $key] = $value;
	}

	public function get($key) {
		if (isset($this->data[$this->prefix . $key])) {
			return $this->data[$this->prefix . $key];
		} else {
			return null;
		}
	}
}