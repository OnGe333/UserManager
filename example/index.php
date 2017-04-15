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

$dependencies = array(
	'userProvider' => new User\UserProvider(new User\Storage\Dibi(), new Session\SessionProvider()),
//	'protectionProvider' => new Protection\ProtectionProvider(new Protection\Storage\Dibi()),
);
echo '<pre>';
var_dump($_SESSION);
echo '</pre>';

UserManager::prepareInstance($dependencies);

if (UserManager::check()) {

} else {
	echo '<br/>Not authenticated';
}
?>
<h2>Find user by id</h2>
<?php
$user = UserManager::findById(4);
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
<form action="" method="post">
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
<form action="" method="get">
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
<form action="" method="post">
	<div>
		<label>
			E-mail: <input type="text" name="reg-email-activate" value="<?php echo (isset($_POST['reg-email-activate']) ? htmlspecialchars($_POST['reg-email-activate']) : '') ?>" />
		</label>
	</div>
	<div>
		<input type="submit"/>
	</div>
</form>

<h2>Login</h2>
<?php
if (isset($_POST['login']) && isset($_POST['password'])) {
	if (UserManager::check()) {
		echo '<br/>Already logged in';
	} else {
		try {
			if (UserManager::authenticate($_POST['login'], $_POST['password'])) {
				echo '<br/>Success';
			} else {
				echo '<br/>Authentication failed';
			}
		} catch (UserManagerException $e) {
			echo '<br/>Error: ' . $e->getMessage();
		}		
	}
}

?>
<form action="" method="post">
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
		<input type="submit"/>
	</div>
</form>
<?php
echo '<br/>' . number_format(microtime(true) - $init, 3);
echo '<br/>' . number_format(memory_get_peak_usage()) . ' B';