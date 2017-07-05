<?php
namespace Onge\UserManager\Session;

interface SessionProviderInterface {
	public function __construct($prefix = 'userManager_');

	public function check();

	public function login($id);

	public function logout();

	public function set($key, $value);

	public function get($key);
}