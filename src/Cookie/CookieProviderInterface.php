<?php
namespace Onge\UserManager\Cookie;

interface CookieProviderInterface {
	/**
	 * @param array  $cookieOptions 	configuration associative array
	 * @param string $prefix        	cookie prefix - to prevent cookie name collision
	 */
	public function __construct(array $cookieOptions = array(), $prefix = 'userManager_');

	/**
	 * set cookie
	 * @param string      $name          	cookie name - will be prefixed
	 * @param mixed       $value         	cookie value
	 * @param int|integer $lifetime      	cookie lifetime
	 * @param array       $cookieOptions 	configuration associative array
	 */
	public function set(string $name, $value, int $lifetime, array $cookieOptions = array());

	/**
	 * set permanent cookie (with long lifetime)
	 * @param string $name          	cookie name
	 * @param mixed  $value         	cookie value
	 * @param array  $cookieOptions 	configuration associative array
	 */
	public function setPermanent(string $name, $value, array $cookieOptions = array());

	/**
	 * get cookie value
	 * @param  string $name 	cookie name
	 * @return mixed       		cookie value
	 */
	public function get($name);

	/**
	 * set cookie expiration to past and value to 0
	 * @param  string $name          	cookie name
	 * @param  array  $cookieOptions 	configuration associative array
	 */
	public function unset(string $name, array $cookieOptions = array());
}