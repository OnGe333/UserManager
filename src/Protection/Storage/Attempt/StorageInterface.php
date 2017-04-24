<?php
namespace Onge\UserManager\Protection\Storage\Attempt;

interface StorageInterface {
	public function __construct(array $config = []);
}