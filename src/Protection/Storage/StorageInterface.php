<?php
namespace Onge\UserManager\Protection\Storage;

interface StorageInterface {
	/**
	 * create storage instance
	 * @param array $config associative array with settings
	 */
	public function __construct(array $config = []);
}