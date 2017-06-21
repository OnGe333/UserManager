<?php
namespace Onge\UserManager\Session;

class SessionProvider implements SessionProviderInterface {
	protected $prefix;

	public function __construct($prefix = 'userManager_') {
		$this->prefix = $prefix;
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}

	public function check() {
		return (isset($_SESSION[$this->prefix . 'id']) && $_SESSION[$this->prefix . 'id'] > 0);
	}

	public function login($id) {
		if ($this->check()) {
			$this->logout();
		}

		return $_SESSION[$this->prefix . 'id'] = $id;
	}

	/**
	 * clear user session
	 *
	 * @return void
	 */
	public function logout() {
		if (isset($_SESSION[$this->prefix . 'id'])) {
			unset($_SESSION[$this->prefix . 'id']);
		}
	}

	public function set($key, $value) {
		$_SESSION[$this->prefix . $key] = $value;
	}

	public function get($key) {
		if (isset($_SESSION[$this->prefix . $key])) {
			return $_SESSION[$this->prefix . $key];
		} else {
			return null;
		}
	}
}