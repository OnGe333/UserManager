<?php
namespace Onge\UserManager\User;

interface UserInterface {
	public function id();

	public function setId($value);

	public function login();

	public function setLogin($value);

	public function setPassword($value);

}