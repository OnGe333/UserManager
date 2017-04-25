<?php
namespace Onge\UserManager\Protection\Storage\Lockdown;

interface StorageInterface {
	public function __construct(array $config = []);
}