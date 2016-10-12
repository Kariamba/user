<?php
	/**
	* OZ\User login demo
	*/
	session_start();
	require_once('./../user.class.php');

	/* make it short */
	use OZ\User as User;
	
	/* Mysql access */
	$sql_driver = 'mysqli';
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
	
	/* login routine */
	$login_error = array();
	if(isset($_POST['enter'])) {
		$login = !empty($_POST['login']) ? $_POST['login'] : '';
		$password = !empty($_POST['password']) ? $_POST['password'] : '';
		
		$error_flag = false;
		
		if(empty($login)) {
			/* login is required */
			$login_error['login'] = 'Login is required';
			$error_flag = true;
		}
		
		if(empty($password)) {
			/* password is required */
			$login_error['password'] = 'Password is required';
			$error_flag = true;
		}
		
		/* all checks passed */
		if(!$error_flag) {
			if(User::login($login, $password)) {
				/* redirect to user account */
				header('Location: account.php');
				exit();
			}
			else {
				$login_error['general'] = 'Something wrong';
			}
		}
	}
	
?>

<html>
	<head>
		<title>User class demo. Login</title>
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
						<li class="active"><a href="login.php">Login <span class="sr-only">(current)</span></a></li>
						<li><a href="registration.php">Registration</a></li>
						<li><a href="recover.php">Recover account</a></li>
					</ul>
				</div>
			</div>
		</nav>
	
		<div class="container">
			<h1>Login</h1>
		
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-4 col-sm-offset-3 col-md-offset-4">
					<form action="" method="post">
						<div class="form-group">
							<label for="login">Login</label>
							<input type="text" class="form-control" name="login" id="login" placeholder="Login" value="<?php echo $login; ?>"/>
							<?php if(!empty($login_error['login'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $login_error['login']; ?></div>
							<?php } ?>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" class="form-control" name="password" id="password" placeholder="Password" value=""/>
							<?php if(!empty($login_error['password'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $login_error['password']; ?></div>
							<?php } ?>
						</div>
						<button type="submit" name="enter" class="btn btn-primary">Login</button>
						<?php if(!empty($login_error['general'])) { ?>
							<br/><br/>
							<div class="alert alert-danger" role="alert"><?php echo $login_error['general']; ?></div>
						<?php } ?>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>