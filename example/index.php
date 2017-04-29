<?php
namespace Onge\UserManager;

$init = microtime(true);

require_once (__DIR__ . '/../vendor/autoload.php');

// your dependencies goes here
$dbCredentials = array(
		'driver'   => 'mysqli',
		'host'     => 'localhost',
		'username' => 'root',
//		'password' => _DB_PASS,
		'database' => 'onge_user_manager_example',
		'charset'  => 'utf8',
		'lazy'  => true,
	);

\dibi::connect($dbCredentials);

$sessionProvider = new Session\SessionProvider();

$dependencies = array(
	'userProvider' => new User\UserProvider(new User\Storage\Dibi(), $sessionProvider, new Cookie\CookieProvider(array('secure' => false))), 
	'protectionProvider' => new Protection\ProtectionProvider(new Protection\Storage\Attempt\Dibi(), new Protection\Storage\Lockdown\Dibi(), new Protection\Storage\Warning\Dibi(), $sessionProvider),
);

UserManager::prepareInstance($dependencies);
UserManager::setPermaloginSecure(false);

if (isset($_POST['sessionout'])) {
	UserManager::getSessionProvider()->set('id', null);
	echo '<br/> session cleared';
}

if (UserManager::check()) {

	if (isset($_GET['logout'])) {
		UserManager::logout();
		
		header('Location: ./', 302);
		exit;

	}
?>
	<h2>Logout</h2>
	Logout <a href="./?logout=1">here</a><br/>

	<form action="./" method="post">
		<button type="submit" name="sessionout">Clear session</button> but not cookie, to see if permanent login works
	</form>
<?php
} else {
?>
	<h2>Login</h2>
	<?php
	if (isset($_POST['login']) && isset($_POST['password'])) {
		if (UserManager::check()) {
			echo '<br/>Already logged in';
		} else {
			if (UserManager::warning($_POST['login'])) {
				echo '<br/>Warning issued! Too many failed attempts';
			}

			if ($wait = UserManager::lockdown($_POST['login'], '127.0.0.1')) {
				echo '<br/>Too many failed attempts, account locked since ' . $wait;
			} else {
				try {
					if (UserManager::authenticate($_POST['login'], $_POST['password'], isset($_POST['permanent']))) {
						echo '<br/>Success, refresh to see you logged in';
					} else {
						UserManager::attempt($_POST['login']);
						echo '<br/>Authentication failed';
					}
				} catch (UserManagerException $e) {
					echo '<br/>Error: ' . $e->getMessage();
				}
			}
		}
	}

	?>
	<form action="./" method="post">
		<div>
			<label>
				E-mail: <input type="text" name="login" value="<?php echo (isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '') ?>" />
			</label>
		</div>
		<div>
			<label>
				Password: <input type="text" name="password" value="<?php echo (isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''); // NEVER EVER try to put password to input - this is for testing purpose only ?>" />
			</label>
		</div>
		<div>
			<label>
				<input type="checkbox" name="permanent" value="1" /> login permanently
			</label>
		</div>
		<div>
			<input type="submit"/>
		</div>
	</form>
	<?php
}

if (UserManager::check()) {

} else {
	echo '<br/>Not authenticated';
}
?>
<h2>Find user by id</h2>
<?php
$user = UserManager::findById(2);
echo '<pre>';
var_dump($user);
echo '</pre>';
?>
<h2>Registration</h2>
<?php
if (isset($_POST['reg-email']) && isset($_POST['reg-password'])) {
	try {
		$user = UserManager::register(array('email' => $_POST['reg-email'], 'password' => $_POST['reg-password']));
		echo '<pre>';
		var_dump($user);
		echo '</pre>';

		echo '<br/>Use activation code: ' . $user->activationCode();
	} catch (UserManagerArgumentException $e) {
		echo '<br/>' . $e->getMessage();
	} catch (UserManagerException $e) {
		echo '<br/>Error: ' . $e->getMessage();
	}
}
?>
<form action="./" method="post">
	<div>
		<label>
			E-mail: <input type="text" name="reg-email" value="<?php echo (isset($_POST['reg-email']) ? htmlspecialchars($_POST['reg-email']) : '') ?>" />
		</label>
	</div>
	<div>
		<label>
			Password: <input type="text" name="reg-password" value="<?php echo (isset($_POST['reg-password']) ? htmlspecialchars($_POST['reg-password']) : ''); // NEVER EVER try to put password to input - this is for testing purpose only ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>

<h2>Activation</h2>
<?php
if (isset($_GET['reg-activate'])) {
	try {
		if (UserManager::activateByCode($_GET['reg-activate'])) {
			echo '<br/> user activated';
		} else {
			echo '<br/> invalid activation code. Need a refresh?';
		}
	} catch (UserManagerException $e) {
		echo '<br/>Error: ' . $e->getMessage();
	}
}

?>
<form action="./" method="get">
	<div>
		<label>
			Code: <input type="text" name="reg-activate" value="<?php echo (isset($_GET['reg-activate']) ? htmlspecialchars($_GET['reg-activate']) : '') ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>

<h2>Refresh activation</h2>
<?php
if (isset($_POST['reg-email-activate'])) {
	try {
		$code = UserManager::refreshActivationCode($_POST['reg-email-activate']);
		if ($code) {
			echo '<br/> your new activation code: ' . $code;
		} else {
			echo '<br/> user accout is activated or doesnt even exist';
		}
	} catch (UserManagerException $e) {
		echo '<br/>Error: ' . $e->getMessage();
	}
}

?>
<form action="./" method="post">
	<div>
		<label>
			E-mail: <input type="text" name="reg-email-activate" value="<?php echo (isset($_POST['reg-email-activate']) ? htmlspecialchars($_POST['reg-email-activate']) : '') ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>
<h2>Password reset - get reset code</h2>
<p>Code is for one time use. New one is generated only if old one does not exist, is already used or is too old</p>
<?php
if (isset($_POST['reset-email'])) {
	$code = UserManager::passwordResetCode($_POST['reset-email']);
	if ($code) {
		echo 'Your code (should be sent to email): ' . $code;
	} else {
		echo 'Email not found. User should not know about it, because this can be used to mine registered users emails.';
	}
}
?>
<form action="./" method="post">
	<div>
		<label>
			E-mail: <input type="text" name="reset-email" value="<?php echo (isset($_POST['reset-email']) ? htmlspecialchars($_POST['reset-email']) : '') ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>
<h2>Password reset - set password</h2>
<p></p>
<?php
if (isset($_GET['reset-code'])) {
	if (UserManager::validatePasswordResetCode($_GET['reset-code'])) {
		try {
			if (UserManager::resetPassword($_GET['reset-code'], $_POST['reset-password'])) {
				echo '<br/>Password has been set';
			} else {
				echo '<br/>Unable to set password';
			}
		} catch (UserManagerArgumentException $e) {
			echo '<br/>' . $e->getMessage();
		} catch (UserManagerException $e) {
			echo '<br/>Error: ' . $e->getMessage();
		}
?>
<h3>Reset password</h3>
<form action="./?reset-code=<?php echo urlencode(htmlspecialchars($_GET['reset-code'])); ?>" method="post">
	<div>
		<label>
			New password: <input type="text" name="reset-password" value="<?php echo (isset($_POST['reset-password']) ? htmlspecialchars($_POST['reset-password']) : '') ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>
<?php
	} else {
		echo 'Code is invalid';
	}
}

?>
<h3>Insert code</h3>
<form action="./" method="get">
	<div>
		<label>
			Code: <input type="text" name="reset-code" value="<?php echo (isset($_GET['reset-code']) ? htmlspecialchars($_GET['reset-code']) : '') ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>
<?php

echo '<br/>time: ' . number_format(microtime(true) - $init, 3);
echo '<br/>RAM: ' . number_format(memory_get_peak_usage()) . ' B';