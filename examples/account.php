<?php
	/**
	* OZ\User account demo
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
		$user = User::getByID($_SESSION['user']['id']);
	}
	else {
		/* redirect to user account */
		header('Location: login.php');
		exit();
	}
	
	/* user data routine */
	$data_error = array();
	$data_update = false;
	if(isset($_POST['update_data'])) {
		$user['name'] = !empty($_POST['name']) ? $_POST['name'] : '';
		$user['mail'] = !empty($_POST['mail']) ? $_POST['mail'] : '';
		if(User::update($user['id'], $user)) {
			$data_update = true;
		}
		else {
			$data_error['general'] = implode('<br/>', User::getError());
		}
	}

	/* login update */
	$login_error = array();
	$login_update = false;
	if(isset($_POST['update_login'])) {
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
		else {
			$hash = User::passwordGet($user['id']);
			if(!User::passwordCheck($password, $hash)) {
				$login_error['password'] = 'Wrong password';
				$error_flag = true;
			}
		}
		
		if(!$error_flag) {
			if(User::loginUpdate($user['id'], $login)) {
				$login_update = true;
				$user['login'] = $login;
			}
			else {
				$login_error['general'] = implode('<br/>', User::getError());
			}
		}
	}
	
	/* password update */
	$password_error = array();
	$password_update = false;
	if(isset($_POST['update_password'])) {
		$password = !empty($_POST['password']) ? $_POST['password'] : '';
		$password_new = !empty($_POST['password_new']) ? $_POST['password_new'] : '';
		$password_key = !empty($_POST['password_key']) ? $_POST['password_key'] : '';
		
		$error_flag = false;
		
		if(empty($password)) {
			/* password is required */
			$password_error['password'] = 'Password is required';
			$error_flag = true;
		}
		else {
			$hash = User::passwordGet($user['id']);
			if(!User::passwordCheck($password, $hash)) {
				$password_error['password'] = 'Wrong password';
				$error_flag = true;
			}
		}
		
		if(empty($password_new)) {
			/* password is required */
			$password_error['password_new'] = 'Password is required';
			$error_flag = true;
		}
		else if($password_new != $password_key) {
			/* check password key */
			$password_error['password_key'] = 'Passwords do not match';
			$error_flag = true;
		}
		
		if(!$error_flag) {
			if(User::passwordUpdate($user['id'], $password_new)) {
				$password_update = true;
			}
			else {
				$password_error['general'] = implode('<br/>', User::getError());
			}
		}
	}
?>

<html>
	<head>
		<title>User class demo. Account</title>
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
						<li><a href="#"><?php echo $user['name']; ?></a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
			</div>
		</nav>
	
		<div class="container">
			<h1>Account</h1>
		
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-4">
					<div class="panel panel-default">
						<div class="panel-heading">User data</div>
						<div class="panel-body">
							<form action="" method="post">
								<div class="form-group">
									<label for="name">Name</label>
									<input type="text" class="form-control" name="name" id="name" placeholder="Name" value="<?php echo $user['name']; ?>"/>
								</div>
								<div class="form-group">
									<label for="mail">Mail</label>
									<input type="text" class="form-control" name="mail" id="mail" placeholder="Mail" value="<?php echo $user['mail']; ?>"/>
								</div>
								<button type="submit" name="update_data" class="btn btn-primary">Update</button>
								<?php if(!empty($data_error['general'])) { ?>
									<br/><br/>
									<div class="alert alert-danger" role="alert"><?php echo $data_error['general']; ?></div>
								<?php } ?>
								<?php if(!empty($data_update)) { ?>
									<br/><br/>
									<div class="alert alert-success" role="alert">Data saved</div>
								<?php } ?>
							</form>
						</div>
					</div>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-4">
					<div class="panel panel-default">
						<div class="panel-heading">New login</div>
						<div class="panel-body">
							<form action="" method="post">
								<div class="form-group">
									<label for="login">New login</label>
									<input type="text" class="form-control" name="login" id="login" placeholder="New login" value="<?php echo $user['login']; ?>"/>
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
								<button type="submit" name="update_login" class="btn btn-primary">Update</button>
								<?php if(!empty($login_error['general'])) { ?>
									<br/><br/>
									<div class="alert alert-danger" role="alert"><?php echo $login_error['general']; ?></div>
								<?php } ?>
								<?php if(!empty($login_update)) { ?>
									<br/><br/>
									<div class="alert alert-success" role="alert">Login updated</div>
								<?php } ?>
							</form>
						</div>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-6 col-md-4">
					<div class="panel panel-default">
						<div class="panel-heading">New password</div>
						<div class="panel-body">
							<form action="" method="post">
								<div class="form-group">
									<label for="password">Old password</label>
									<input type="password" class="form-control" name="password" id="password" placeholder="Old password" value=""/>
									<?php if(!empty($password_error['password'])) { ?>
										<br/>
										<div class="alert alert-danger" role="alert"><?php echo $password_error['password']; ?></div>
									<?php } ?>
								</div>
								<div class="form-group">
									<label for="password_new">New password</label>
									<input type="password" class="form-control" name="password_new" id="password_new" placeholder="New password" value=""/>
									<?php if(!empty($password_error['password_new'])) { ?>
										<br/>
										<div class="alert alert-danger" role="alert"><?php echo $password_error['password_new']; ?></div>
									<?php } ?>
								</div>
								<div class="form-group">
									<label for="password_key">Confirm password</label>
									<input type="password" class="form-control" name="password_key" id="password_key" placeholder="Confirm password" value=""/>
									<?php if(!empty($password_error['password_key'])) { ?>
										<br/>
										<div class="alert alert-danger" role="alert"><?php echo $password_error['password_key']; ?></div>
									<?php } ?>
								</div>
								<button type="submit" name="update_password" class="btn btn-primary">Update</button>
								<?php if(!empty($password_error['general'])) { ?>
									<br/><br/>
									<div class="alert alert-danger" role="alert"><?php echo $password_error['general']; ?></div>
								<?php } ?>
								<?php if(!empty($password_update)) { ?>
									<br/><br/>
									<div class="alert alert-success" role="alert">Password updated</div>
								<?php } ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>