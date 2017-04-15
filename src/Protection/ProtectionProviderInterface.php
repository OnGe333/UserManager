<?php 
namespace Onge\UserManager\Protection;

interface ProtectionProviderInterface {
	public function attempt();

	public function protect();
}