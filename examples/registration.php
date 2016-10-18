<?php
	/**
	* OZ\User registration demo
	*/
	session_start();
	require_once('./../user.class.php');

	/* make it short */
	use OZ\User as User;
	
	/* Mysql access */
	$sql_driver = 'mysql';
	$sql_host = 'localhost';
	$sql_name = 'opensource.my';
	$sql_user = 'root';
	$sql_pass = '';
	User::init($sql_driver, $sql_host, $sql_name, $sql_user, $sql_pass);
	
	/* check current user */
	$user = false;
	if(User::check()) {
		/* redirect to user account */
		header('Location: account.php');
		exit();
	}

	/* default values */
	$login = '';
	$password = '';
	$password_key = '';
	
	/* registration routine */
	$registration_error = array();
	if(isset($_POST['registrtion'])) {
		$login = !empty($_POST['login']) ? $_POST['login'] : '';
		$password = !empty($_POST['password']) ? $_POST['password'] : '';
		$password_key = !empty($_POST['password_key']) ? $_POST['password_key'] : '';
		
		$error_flag = false;
		
		if(empty($login)) {
			/* login is required */
			$registration_error['login'] = 'Login is required';
			$error_flag = true;
		}
		else if(User::loginExists($login)) {
			/* login already exists */
			$registration_error['login'] = 'Login exists';
			$error_flag = true;
		}
		
		if(empty($password)) {
			/* password is required */
			$registration_error['password'] = 'Password is required';
			$error_flag = true;
		}
		else if($password != $password_key) {
			/* check password key */
			$registration_error['password_key'] = 'Passwords do not match';
			$error_flag = true;
		}
		
		/* all checks passed */
		if(!$error_flag) {
			$user_data = array(
				'login' => $login,
				'pass' => $password
			);
			$userID = User::add($user_data);
			if(!empty($userID)) {
				/* registration done */
				/* login user and redirect to account */
				if(User::login($login, $password)) {
					/* redirect to user account */
					header('Location: account.php');
					exit();
				}
			}
			else {
				$registration_error['general'] = implode('<br/>', User::getError());
			}
		}
	}
	
?>

<html>
	<head>
		<title>User class demo. Registartion</title>
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"/>
	</head>
	<body>
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#">OZ\User demo</a>
				</div>

				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li><a href="login.php">Login</a></li>
						<li class="active"><a href="registration.php">Registration <span class="sr-only">(current)</span></a></li>
						<li><a href="recover.php">Recover account</a></li>
					</ul>
				</div>
			</div>
		</nav>
	
		<div class="container">
			<h1>Registration</h1>
		
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-4 col-sm-offset-3 col-md-offset-4">
					<form action="" method="post">
						<div class="form-group">
							<label for="login">Login</label>
							<input type="text" class="form-control" name="login" id="login" placeholder="Login" value="<?php echo $login; ?>"/>
							<?php if(!empty($registration_error['login'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $registration_error['login']; ?></div>
							<?php } ?>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" class="form-control" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>"/>
							<?php if(!empty($registration_error['password'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $registration_error['password']; ?></div>
							<?php } ?>
						</div>
						<div class="form-group">
							<label for="password_key">Confirm password</label>
							<input type="password" class="form-control" name="password_key" id="password_key" placeholder="Confirm password" value="<?php echo $password_key; ?>"/>
							<?php if(!empty($registration_error['password_key'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $registration_error['password_key']; ?></div>
							<?php } ?>
						</div>
						<button type="submit" name="registrtion" class="btn btn-primary">Registration</button>
						<?php if(!empty($registration_error['general'])) { ?>
							<br/><br/>
							<div class="alert alert-danger" role="alert"><?php echo $registration_error['general']; ?></div>
						<?php } ?>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>