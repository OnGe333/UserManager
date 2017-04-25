<?php 
namespace Onge\UserManager\Protection;

interface ProtectionProviderInterface {
	public function __construct(Storage\Attempt\StorageInterface $attemptStorage, Storage\Lockdown\StorageInterface $lockdownStorage, \Onge\UserManager\Session\SessionProviderInterface $sessionProvider, array $config = array());

	public function attempt($login = null, $ip = null);

	public function lockdown($login = null, $ip = null);

	public function slowDown($login);

	public function longestInterval();

	public function monitoredInterval();

	public function recordedInterval();

	public function lockdownInterval();

	public function lockdownLimit();

	public function getIp();
}