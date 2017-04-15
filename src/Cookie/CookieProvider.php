<?php 
namespace Onge\UserManager\Cookie;
use Onge\UserManagerException;

class CookieProvider implements CookieProviderInterface {
	protected $prefix;

	protected $path;

	protected $domain;

	protected $secure;
	
	protected $httponly;

	public function __construct(array $cookieOptions = array(), $prefix = 'userManager_') {
		$this->prefix = $prefix;

		$this->path = isset($cookieOptions['path']) ? $cookieOptions['path'] : "";
		$this->domain = isset($cookieOptions['domain']) ? $cookieOptions['domain'] : "";
		$this->secure = isset($cookieOptions['secure']) ? $cookieOptions['secure'] : true;
		$this->httponly = isset($cookieOptions['httponly']) ? $cookieOptions['httponly'] : true;
	}

	public function set(string $name, $value, int $lifetime = 3600, array $cookieOptions = array()) {
		$path = isset($cookieOptions['path']) ? $cookieOptions['path'] : $this->path;
		$domain = isset($cookieOptions['domain']) ? $cookieOptions['domain'] : $this->domain;
		$secure = isset($cookieOptions['secure']) ? $cookieOptions['secure'] : $this->secure;
		$httponly = isset($cookieOptions['httponly']) ? $cookieOptions['httponly'] : $this->httponly;

		if ($secure && empty($_SERVER['HTTPS'])) {
			throw new UserManagerException('Tried to set secured cookie via unsecured connection. Cookie not set');
		} else {
			return setcookie($name, json_encode($value), (time() + $lifetime), $path, $domain, $secure, $httponly);
		}
	}

	public function setPermanent(string $name, $value, array $cookieOptions = array()) {
		// lifetime slightly over one year
		return $this->set($name, $value, time() + 32000000, $cookieOptions)
	}

	public function get($name) {
		return json_decode($_COOKIE[$this->prefix . $name]);
	}
}