<?php 
namespace Onge\UserManager\Cookie;

interface CookieProviderInterface {
	public function __construct(array $cookieOptions = array(), $prefix = 'userManager_');

	public function set(string $name, $value, int $lifetime, array $cookieOptions = array());

	public function setPermanent(string $name, $value, array $cookieOptions = array());

	public function get($name);
}