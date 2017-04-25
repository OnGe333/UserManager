<?php 
namespace Onge\UserManager\Protection;

interface ProtectionProviderInterface {
	public function attempt($login = null, $ip = null);

	public function lockdown($login = null, $ip = null);
}