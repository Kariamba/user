<?php
	/**
  * User basic routine.
  *
  * Class provides basic routine for users entry:
  *		- insert / update / delete / select DB entries
	*		- login / logout / check / recover
	*
  * @author		Oleg Zorin <zorinoa@yandex.ru>
	* @link			http://oleg.zorin.ru Oleg Zorin home page
	*
	* @license https://opensource.org/licenses/GPL-3.0 GNU Public License, version 3
	*
	* @package	OZ\
  * @version	1.0
	*/
	
	namespace OZ;
	
	abstract class User {
		/** @var int const KEY_LIFETIME		User idle session lifetime */
		const KEY_LIFETIME = 3600;				/* 1 hour */
		
		/** @var object $_db							Copy of PDO class */
		private static $_db;
		/** @var bool $_init							Initialization flag */
		private static $_init;
		
		/** @var array $_definition				Definition of DB users table */
		private static $_definition = array(
			'table' => 'users',														/* table */
			'id' => 'userID',															/* field of user identifier */
			'login' => 'login',														/* field of user login */
			'pass' => 'password',													/* field of user password */
			'key' => 'session_key',												/* field of user session key */
			'fields' => array('group', 'name', 'mail')		/* add your fields here */
		);
		
		/** @var array $_error_list				List of latest errors */
		private static $_error_list = array();
		
		/**
		* Initialization method. Connect to DB and set initialization flag.
		*
		* @param string $db_drvr		DB connection driver.
		* @param string $db_host		DB server host.
		* @param string $db_name		DB name.
		* @param string $db_user		DB user.
		* @param string $db_pass		DB password.
		*
		* @return bool Returns initialization flag value.
		*/
		static function init($db_drvr, $db_host, $db_name, $db_user, $db_pass) {
			try {
				switch($db_drvr) {
					case 'mysql': self::$_db = new \PDO('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_pass); break;
					case 'mssql': self::$_db = new \PDO('mssql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_pass); break;
					case 'sybase': self::$_db = new \PDO('sybase:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_pass); break;
					default: self::$_db = new \PDO('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_pass); break;
				}
				self::$_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); 
			}  
			catch(\PDOException $e) {  
				die($e->getMessage());
			}
			if(!empty(self::$_definition['table']) && !empty(self::$_definition['id']) && !empty(self::$_definition['login']) && !empty(self::$_definition['pass'])) {
				self::$_init = true;
			}
			return self::$_init;
		}
		
		/**
		* Get user data (except password) by ID.
		*
		* @param string $userID			User ID.
		*
		* @return array|false Returns array of user data or false if error occurred.
		*/
		static function getByID($userID) {
			$user = false;
			if(self::$_init) {
				$res = self::$_db->prepare('SELECT * FROM `' . self::$_definition['table'] . '` WHERE `' . self::$_definition['id'] . '` = :id;');
				$res->setFetchMode(\PDO::FETCH_ASSOC);
				$res->execute(array('id' => $userID));
				if($lot = $res->fetch()) {
					/* main fields, except password */
					$user = array(
						'id' => $lot[self::$_definition['id']],
						'login' => $lot[self::$_definition['login']],
						'key' =>  $lot[self::$_definition['key']],
					);
					/* other fields */
					if(!empty(self::$_definition['fields'])) {
						foreach(self::$_definition['fields'] as $field) {
							$user[$field] = !empty($lot[$field]) ? $lot[$field] : '';
						}
					}
				}
			}
			return $user;
		}
		
		/**
		* Get user data (except password) by Login.
		*
		* @param string $login			User login.
		*
		* @return array|false Returns array of user data or false if error occurred.
		*/
		static function getByLogin($login) {
			$user = false;
			if(self::$_init) {
				$res = self::$_db->prepare('SELECT * FROM `' . self::$_definition['table'] . '` WHERE `' . self::$_definition['login'] . '` = :login;');
				$res->setFetchMode(\PDO::FETCH_ASSOC);
				$res->execute(array('login' => $login));
				if($lot = $res->fetch()) {
					/* main fields, except password */
					$user = array(
						'id' => $lot[self::$_definition['id']],
						'login' => $lot[self::$_definition['login']],
						'key' =>  $lot[self::$_definition['key']],
					);
					/* other fields */
					if(!empty(self::$_definition['fields'])) {
						foreach(self::$_definition['fields'] as $field) {
							$user[$field] = !empty($lot[$field]) ? $lot[$field] : '';
						}
					}
				}
			}
			return $user;
		}
		
		/**
		* Get users list.
		*
		* @return array Returns array of users data.
		*/
		static function getList() {
			$list = array();
			if(self::$_init) {
				$res = self::$_db->query('SELECT * FROM `' . self::$_definition['table'] . '` ORDER BY `' . self::$_definition['id'] . '`;');
				$res->setFetchMode(\PDO::FETCH_ASSOC);
				while($lot = $res->fetch()) {
					/* main fields, except password */
					$user = array(
						'id' => $lot[self::$_definition['id']],
						'login' => $lot[self::$_definition['login']]
					);
					/* other fields */
					if(!empty(self::$_definition['fields'])) {
						foreach(self::$_definition['fields'] as $field) {
							$user[$field] = !empty($lot[$field]) ? $lot[$field] : '';
						}
					}
					$list[] = $user;
				}
			}
			return $list;
		}

		/**
		* Add new user.
		*
		* @param array $data			User data array with keys according definition.
		*
		* @return int|false Returns new user ID or false if error occurred.
		*/
		static function add($data) {
			$result = false;
			self::$_error_list = array();
			if(self::$_init) {
				$user_data = array(
					'login' => !empty($data['login']) ? $data['login'] : '',
					'pass' => !empty($data['pass']) ? self::passwordCreate($data['pass']) : ''
				);
				$sql_set = array(
					'`' . self::$_definition['login'] . '` = :login',
					'`' . self::$_definition['pass'] . '` = :pass'
				);

				if(!empty(self::$_definition['fields'])) {
					foreach(self::$_definition['fields'] as $field) {
						$user_data[$field] = !empty($data[$field]) ? $data[$field] : null;
						$sql_set[] = '`' . $field . '` = :' . $field;
					}
				}
				if(!empty($user_data['login']) && !empty($user_data['pass'])) {
					if(!self::loginExists($user_data['login'])) {
						$res = self::$_db->prepare('INSERT INTO `' . self::$_definition['table'] . '` SET ' . implode(', ', $sql_set) . ';');
						$res->setFetchMode(\PDO::FETCH_ASSOC);
						if($res->execute($user_data)) {
							$result = self::$_db->lastInsertId();
						}
						else {
							self::$_error_list[] = 'DB error';
						}
					}
					else {
						self::$_error_list[] = 'Login alrady exists';
					}
				}
				else {
					self::$_error_list[] = 'Login and Password are required fields';
				}
			}
			return $result;
		}
		
		/**
		* Update user data (excep password and login).
		*
		* @param int $userID			User ID.
		* @param array $data			User data array with keys according definition. Some data could be skipped.
		*
		* @return boolean Returns result of update.
		*/
		static function update($userID, $data) {
			$result = false;
			self::$_error_list = array();
			if(self::$_init) {
				$user_data = array('id' => $userID);
				$sql_set = array();
				if(!empty($data)) {
					foreach($data as $key => $val) {
						if(in_array($key, self::$_definition['fields'])) {
							$user_data[$key] = !empty($val) ? $val : null;
							$sql_set[] = '`' . $key . '` = :' . $key;
						}
					}
					$res = self::$_db->prepare('UPDATE `' . self::$_definition['table'] . '` SET ' . implode(', ', $sql_set) . ' WHERE `' . self::$_definition['id'] . '` = :id;');
					$res->setFetchMode(\PDO::FETCH_ASSOC);
					if($res->execute($user_data)) {
						$result = true;
					}
					else {
						self::$_error_list[] = 'DB error';
					}
				}
			}
			return $result;
		}
		
		/**
		* Update user login.
		*
		* @param int $userID			User ID.
		* @param string $login		User login.
		*
		* @return boolean Returns result of update.
		*/
		static function loginUpdate($userID, $login) {
			$result = false;
			self::$_error_list = array();
			if(self::$_init) {
				if(!empty($userID) && !empty($login)) {
					if(!self::loginExists($login, $userID)) {
						$res = self::$_db->prepare('UPDATE `' . self::$_definition['table'] . '` SET `' . self::$_definition['login'] . '` = :login WHERE `' . self::$_definition['id'] . '` = :id;');
						$res->setFetchMode(\PDO::FETCH_ASSOC);
						if($res->execute(array('id' => $userID, 'login' => $login))) {
							$result = true;
						}
						else {
							self::$_error_list[] = 'DB error';
						}
					}
					else {
						self::$_error_list[] = 'Login alrady exists';
					}
				}
				else {
					self::$_error_list[] = 'User ID and Login are required fields';
				}
			}
			return $result;
		}

		/**
		* Check login existing. Skip checking current user.
		*
		* @param string $login		User login.
		* @param int $userID			Current user ID. Default: 0
		*
		* @return boolean Returns result of check.
		*/
		static function loginExists($login, $userID = 0) {
			$result = false;
			if(self::$_init) {
				$res = self::$_db->prepare('SELECT `' . self::$_definition['id'] . '` FROM `' . self::$_definition['table'] . '` WHERE `' . self::$_definition['login'] . '` = :login AND `' . self::$_definition['id'] . '` <> :id;');
				$res->setFetchMode(\PDO::FETCH_ASSOC);
				$res->execute(array('id' => $userID, 'login' => $login));
				if($res->rowCount() > 0) {
					$result = true;
				}
			}
			return $result;
		}

		/**
		* Create hash of user password.
		*
		* @param string $pass			User password.
		*
		* @return string Returns hash of user password.
		*/
		static function passwordCreate($pass) {
			$salt = md5(microtime(true));
			$hash = $salt . md5($pass . $salt);
			return $hash;
		}

		/**
		* Check user password.
		*
		* @param string $pass			User password.
		* @param string $hash			Hash of user password.
		*
		* @return string Returns result of check.
		*/		
		static function passwordCheck($pass, $hash) {
			$result = false;
			$salt = substr($hash, 0, 32);
			if($hash == $salt . md5($pass . $salt)) {
				$result = true;
			}
			return $result;
		}
		
		/**
		* Update user password.
		*
		* @param int $userID			User ID.
		* @param string $pass			User password.
		*
		* @return boolean Returns result of update.
		*/
		static function passwordUpdate($userID, $pass) {
			$result = false;
			self::$_error_list = array();
			if(self::$_init) {
				if(!empty($userID) && !empty($pass)) {
					$res = self::$_db->prepare('UPDATE `' . self::$_definition['table'] . '` SET `' . self::$_definition['pass'] . '` = :pass WHERE `' . self::$_definition['id'] . '` = :id;');
					$res->setFetchMode(\PDO::FETCH_ASSOC);
					if($res->execute(array('id' => $userID, 'pass' => self::passwordCreate($pass)))) {
						$result = true;
					}
					else {
						self::$_error_list[] = 'DB error';
					}
				}
				else {
					self::$_error_list[] = 'User ID and Password are required fields';
				}
			}
			return $result;
		}
		
		/**
		* Get user password hash.
		*
		* @param int $userID			User ID.
		*
		* @return string|false Returns user password hash or false.
		*/
		static function passwordGet($userID) {
			$result = false;
			if(self::$_init) {
				$res = self::$_db->prepare('SELECT `' . self::$_definition['id'] . '`, `' . self::$_definition['pass'] . '` FROM `' . self::$_definition['table'] . '` WHERE `' . self::$_definition['id'] . '` = :id;');
				$res->setFetchMode(\PDO::FETCH_ASSOC);
				$res->execute(array('id' => $userID));
				if($lot = $res->fetch()) {
					$result = $lot[self::$_definition['pass']];
				}
			}
			return $result;
		}
		
		/**
		* Log in user in system. Start user session.
		*
		* @param string $login		User login.
		* @param string $pass			User password.
		*
		* @return boolean Returns result of log in.
		*/
		static function login($login, $pass) {
			$result = false;
			if(self::$_init) {
				$res = self::$_db->prepare('SELECT `' . self::$_definition['id'] . '`, `' . self::$_definition['pass'] . '` FROM `' . self::$_definition['table'] . '` WHERE `' . self::$_definition['login'] . '` = :login;');
				$res->setFetchMode(\PDO::FETCH_ASSOC);
				$res->execute(array('login' => $login));
				if($lot = $res->fetch()) {
					if(self::passwordCheck($pass, $lot[self::$_definition['pass']])) {
						$result = true;
						$key = md5(microtime(true));
						$_SESSION['user']['id'] = $lot[self::$_definition['id']];
						$_SESSION['user']['key'] = $key;
						$_SESSION['user']['time'] = time() + self::KEY_LIFETIME;
						$res = self::$_db->prepare('UPDATE `' . self::$_definition['table'] . '` SET `' . self::$_definition['key'] . '` = :key WHERE `' . self::$_definition['id'] . '` = :id;');
						$res->setFetchMode(\PDO::FETCH_ASSOC);
						$res->execute(array('key' => $key, 'id' => $lot[self::$_definition['id']]));
					}
				}
			}
			return $result;
		}
		
		/**
		* Log out user. Stop user session.
		*
		* @return true
		*/
		static function logout() {
			$_SESSION['user'] = null;
			return true;
		}
		
		/**
		* Set random user password.
		*
		* @param string $login		User login.
		*
		* @return string|false Returns new user password or false if error occurred.
		*/
		static function recover($login) {
			$result = false;
			if(self::$_init) {
				$pass = substr(md5(microtime(true)), 0, 6);
				$user = self::getByLogin($login);
				if(!empty($user['id'])) {
					if($result = self::passwordUpdate($user['id'], $pass)) {
						$result = $pass;
					}
				}
			}
			return $result;
		}

		/**
		* Check user session.
		*
		* @return boolean Returns result od check. If false than log out user.
		*/
		static function check() {
			$result = false;
			if(self::$_init) {
				$now = time();
				if(!empty($_SESSION['user']['id']) && !empty($_SESSION['user']['key'])) {
					if($now < $_SESSION['user']['time']) {
						$now = time();
						$user = self::getByID($_SESSION['user']['id']);
						if($user['key'] == $_SESSION['user']['key']) {
							$_SESSION['user']['time'] = time() + self::KEY_LIFETIME;
							$result = true;
						}
					}
					
					if(!$result) {
						self::logout();
					}
				}
			}
			return $result;
		}

		/**
		* Return list of latest errors.
		*
		* @return array Returns list of latest errors.
		*/
		static function getError() {
			return self::$_error_list;
		}
		
	}