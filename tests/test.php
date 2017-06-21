<?php
require_once (__DIR__ . '/../vendor/autoload.php');
require_once (__DIR__ . '/UserStorageMockup.php');
require_once (__DIR__ . '/SessionMockup.php');
require_once (__DIR__ . '/CookieMockup.php');

use PHPUnit\Framework\TestCase;
use Onge\UserManager\UserManager;

class UserManagerTest extends TestCase
{
	public $userContainerClass;

	public function setUp() {
		$session = new Onge\UserManager\Session\SessionMockup();
		$userStorage = new Onge\UserManager\User\Storage\StorageMockup();
		$cookie = new Onge\UserManager\Cookie\CookieMockup();
		$this->userContainerClass = $userStorage->getContainerClass();

		$dependencies = array(
			'userProvider' => new Onge\UserManager\User\UserProvider($userStorage),
			'sessionProvider' => $session,
			'cookieProvider' => $cookie,
		);

		UserManager::prepareInstance($dependencies);
	}

	public function testFindById() {
		$user = UserManager::findById(1);
		$this->assertInstanceOf($this->userContainerClass, $user);

		return $user;
	}

	/**
	 * @depends testFindById
	 */
	public function testUser1($user) {
		$this->assertEquals(1, $user->id());
		$this->assertEquals('test@test.com', $user->login());
		$this->assertEquals($user->login(), $user->email());
		$this->assertEquals(1, $user->active());
		$this->assertEquals('activation_code', $user->activationCode());
		$this->assertEquals('2017-01-01', $user->activationTime());
		$this->assertEquals('2017-02-02', $user->passwordResetTime());
		$this->assertEquals('password_reset_code', $user->passwordResetCode());
	}

	/**
	 * @depends testFindById
	 * @expectedException Error
	 */
	public function testPasswordInaccessability($user) {
		$user->password;
	}

	/**
	 * @depends testFindById
	 * @expectedException Error
	 */
	public function testPasswordMethodInaccessability($user) {
		$user->password();
	}

	public function testRandomString() {
		$this->assertEquals(48, mb_strlen(UserManager::getUserProvider()->randomString()));
		$length = 1;
		$this->assertEquals($length, mb_strlen(UserManager::getUserProvider()->randomString($length)));
		$length = 8;
		$this->assertEquals($length, mb_strlen(UserManager::getUserProvider()->randomString($length)));
		$length = 20;
		$this->assertEquals($length, mb_strlen(UserManager::getUserProvider()->randomString($length)));
		$length = 40;
		$this->assertEquals($length, mb_strlen(UserManager::getUserProvider()->randomString($length)));
		$length = 100;
		$this->assertEquals($length, mb_strlen(UserManager::getUserProvider()->randomString($length)));
	}

	public function testUserSetter() {
		$data = array(
			'id' => 1,
			'email' => 'test@test.cz',
			'active' => 1
			);
		$user = UserManager::getUserProvider()->newUser($data);

		$this->assertInstanceOf($this->userContainerClass, $user);

		foreach ($data AS $key => $value) {
			$this->assertEquals($value, $user->{$key}());
		}
	}
}
?>