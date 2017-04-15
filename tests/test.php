<?php
require_once (__DIR__ . '/../vendor/autoload.php');
require_once (__DIR__ . '/StorageMockup.php');

use PHPUnit\Framework\TestCase;
use Onge\UserManager\UserManager;

class UserManagerTest extends TestCase
{
	public $userContainerClass;

	public function setUp() {
		$session = new Onge\UserManager\Session\SessionMockup();
		$storage = new Onge\UserManager\User\Storage\StorageMockup();
		$this->userContainerClass = $storage->getContainerClass();

		$dependencies = array(
			'userProvider' => new Onge\UserManager\User\UserProvider($storage, $session)
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
}
?>