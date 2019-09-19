<?php
// vvv DO NOT MODIFY/REMOVE vvv

// check current php version to ensure it meets 2300's requirements
function check_php_version()
{
  if (version_compare(phpversion(), '7.0', '<')) {
    define(VERSION_MESSAGE, "PHP version 7.0 or higher is required for 2300. Make sure you have installed PHP 7 on your computer and have set the correct PHP path in VS Code.");
    echo VERSION_MESSAGE;
    throw VERSION_MESSAGE;
  }
}
check_php_version();

function config_php_errors()
{
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 0);
  error_reporting(E_ALL);
}
config_php_errors();

// open connection to database
function open_or_init_sqlite_db($db_filename, $init_sql_filename)
{
  if (!file_exists($db_filename)) {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (file_exists($init_sql_filename)) {
      $db_init_sql = file_get_contents($init_sql_filename);
      try {
        $result = $db->exec($db_init_sql);
        if ($result) {
          return $db;
        }
      } catch (PDOException $exception) {
        // If we had an error, then the DB did not initialize properly,
        // so let's delete it!
        unlink($db_filename);
        throw $exception;
      }
    } else {
      unlink($db_filename);
    }
  } else {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }
  return null;
}

function exec_sql_query($db, $sql, $params = array())
{
  $query = $db->prepare($sql);
  if ($query and $query->execute($params)) {
    return $query;
  }
  return null;
}
// ^^^ DO NOT MODIFY/REMOVE ^^^



$db = open_or_init_sqlite_db('secure/gallery.sqlite', 'secure/init.sql');


//SOURCE: CODE REFERENCED FROM PROFESSOR HARMS' LOGIN CODE - LAB 8

define('SESSION_COOKIE_DURATION', 60*60*1); // Session Expires after One Hour
$session_messages = array();

function log_in($username, $password) {
  global $db;
  global $current_user;
  global $session_messages;

  if ( isset($username) && isset($password) ) {

    $sql = "SELECT * FROM users WHERE username = :username;"; //cross reference username with user table, usernames are unique
    $params = array(
      ':username' => $username
    );
    $records = exec_sql_query($db, $sql, $params)->fetchAll();
    if ($records) {
      $user_account = $records[0];
      //verify hashed password
      if ( password_verify($password, $user_account['password']) ) {

        //Create a session
        $session = session_create_id();

        //Insert session id into the sessions table
        $sql = "INSERT INTO sessions (user_id, session) VALUES (:user_id, :session);";
        $params = array(
          ':user_id' => $user_account['id'],
          ':session' => $session
        );

        $result = exec_sql_query($db, $sql, $params);
        if ($result) {

          setcookie("session", $session, time() + SESSION_COOKIE_DURATION);
          //assigning current user var to the user_account to access info in future
          $current_user = $user_account;
          return $current_user;

        } else {
          array_push($session_messages, "Log in Unsuccessful.");
        }
      } else {
        array_push($session_messages, "Invalid username or password.");
      }
    } else {
      array_push($session_messages, "Invalid username or password.");
    }
  } else {
    array_push($session_messages, "No username or password given.");
  }
  $current_user = NULL;
  return NULL;
}

//users
function find_user($user_id) {
  global $db;

  $sql = "SELECT * FROM users WHERE id = :user_id;";
  $params = array(
    ':user_id' => $user_id
  );
  $user_records = exec_sql_query($db, $sql, $params)->fetchAll();
  if ($user_records) {
    return $user_records[0];
  }
  return NULL;
}

//sessions
function find_session($session) {
  global $db;

  if (isset($session)) {
    $sql = "SELECT * FROM sessions WHERE session = :session;"; //check with sessions table to grab session
    $params = array(
      ':session' => $session
    );
    $session_records = exec_sql_query($db, $sql, $params)->fetchAll();
    if ($session_records) {
      return $session_records[0];
    }
  }
  return NULL;
}

function session_login() {
  global $db;
  global $current_user;

  if (isset($_COOKIE["session"])) {
    $session = $_COOKIE["session"];
    $session_record = find_session($session);

    if ( isset($session_record) ) {
      $current_user = find_user($session_record['user_id']);
      setcookie("session", $session, time() + SESSION_COOKIE_DURATION); //statement to renew cookie for 1 hour
      return $current_user;
    }
  }
  $current_user = NULL;
  return NULL;
}

function is_user_logged_in() {
  global $current_user;
  return ($current_user != NULL); // if true, a user is logged in
  //access current user info by referencing this var
}

function log_out() {
  global $current_user;
  //expire cookie
  setcookie('session', '', time() - SESSION_COOKIE_DURATION);
  $current_user = NULL;

}

// Check if valid user login form
if ( isset($_POST['login_submit']) && isset($_POST['username']) && isset($_POST['password']) ) {
  $username = trim( $_POST['username'] );
  $password = trim( $_POST['password'] );

  log_in($username, $password);
}
else {
  session_login();
}
//Check for logout
if ( isset($current_user) && ( isset($_GET['logout']) || isset($_POST['logout']) ) ) {
  log_out();

}

//SOURCE: CODE REFERENCED FROM PROFESSOR KYLE HARMS' LOGIN CODE - LAB 8

?>
