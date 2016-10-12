# User

Universal class for basic user routine. Class definition for your project purpose is required.

Class provides:
- user database routine (select / insert / update / delete)
- user account routine (login / logout / recover / check)

Probably the best way is to use the ideas of methods realization in your own class.

PHP Tested: 5.6.19, 7.0.11


## CONTENTS

	1. CLASS DEFINITION
	2. PUBLIC METHODS
		2.1. User::init()
		2.2. User::getByID()
		2.3. User::getByLogin()
		2.4. User::getList()
		2.5. User::add()
		2.6. User::update()
		2.7. User::delete()
		2.8. User::loginUpdate()
		2.9. User::loginExists()
		2.10. User::passwordHash()
		2.11. User::passwordCheck()
		2.12. User::passwordUpdate()
		2.13. User::passwordGet()
		2.14. User::login()
		2.15. User::logout()
		2.16. User::recover()
		2.17. User::check()
		2.18. User::getError()

* * *


## 1. CLASS DEFINITION

Class definition is provided in $_definition variable:

	private static $_definition = array(
	  'table' => 'users',                         /* users table name */
	  'id' => 'userID',                           /* user identifier field name */
	  'login' => 'login',                         /* user login field name*/
	  'pass' => 'password',                       /* user password field name */
	  'key' => 'session_key',                     /* user session key field name */
	  'fields' => array('group', 'name', 'mail')  /* add your fields here */
	);
	
* * *
	
	
## 2. CLASS DEFINITION

### 2.1. User::init($db_drvr, $db_host, $db_name, $db_user, $db_pass)

Initialization of class. Should be called before other methods.

$db_drvr - database driver.

$db_host - database server.

$db_host - database name.

$db_user - database user.

$db_pass - database password.


### 2.2. User::getByID($userID)

Get user data (except password) by ID. 

To get hash of user password use User::passwordGet() method (see section 2.13).

### 2.3. User::getByLogin($login)

Get user data (except password) by login. 

To get hash of user password use User::passwordGet() method (see section 2.13).

It's good idea to use chache here.

### 2.4. User::getList()

Get users list.

### 2.5. User::add($data)

Add new user.

$data - user data array with keys according definition.

Password should be provided as is. Method will create hash and write it into database.

### 2.6. User::update($userID, $data)

Update user data (except login and password).

$data - user data array with keys according definition. Some data could be skipped.

To update login use User::loginUpdate() method (see section 2.8).

To update password use User::passwordUpdate() method (see section 2.12).

### 2.7. User::delete($userID)

Delete user by ID.

### 2.8. User::loginUpdate($userID, $login)

Update user login.

### 2.9. User::loginExists($login, $userID = 0)

Check if $login exists in database. User with $userID skipped.

### 2.10. User::passwordHash($pass)

Create user password hash using random seed.

### 2.11. User::passwordCheck($pass, $hash)

Check user password.

### 2.12. User::passwordUpdate($userID, $pass)

Update user password. Password should be provided as is. Method will create hash and write it into database.

### 2.13. User::passwordGet()

Get user password hash from database.

### 2.14. User::login($login, $pass)

Login user in system.

### 2.15. User::logout()

Logout user.

### 2.16. User::recover()

Create new password and update database.

### 2.17. User::check()

Check current user.

### 2.18. User::getError()

Return list of latest errors as array.