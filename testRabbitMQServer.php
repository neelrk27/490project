#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('log_errors', TRUE);
ini_set('error_log', '/home/it490/490/rabbitmqphp_example/logging/log.txt');
ini_set('log_errors_max_len', 1024);


function doLogin($username,$password)
{
    // lookup username in databas
	error_log("Login Request has been received");
	error_log("Username : $username");
	error_log("Password : $password");
	$password_hash = password_hash($password, PASSWORD_DEFAULT);
	$mydb = new mysqli('192.168.1.138','user','password','MOVIE_DB');
	
if ($mydb->errno != 0) {
		echo "Failed to connect : ".$mydb->error.PHP_EOL;
		exit(0);
}

echo "Connected to database".PHP_EOL;
#$query = mysqli_query($mydb,"SELECT * FROM users where username = '$username' and password = '$password_hash' ");	
#$query = mysqli_query($mydb, "SELECT * FROM users where username = '$username'");

$query = "SELECT * from users where username = '$username'";
$result = mysqli_query($mydb, $query);
$count = mysqli_num_rows($result);
if ($count == 1) {
	$row = mysqli_fetch_array($result);
	if (password_verify($password, $row['password'])){
	echo "User in database..VERIFIED";
	error_log("User information is valid...");
	return true;
	}else{
	echo " Not in database...Please relogin";
	error_log("User information is not valid...");
	return false;
	}
}

}

function doRegister($userName,$userPass)
{
	error_log("Register request has been recieved");

	$hash_password = password_hash($userPass, PASSWORD_DEFAULT);
        //lookup username in database
	//Connect to DB
        $mydb = new mysqli('192.168.1.138','user','password','MOVIE_DB');
        if ($mydb->errno != 0){
                echo "<br><br>Failed to connect to database: ".$mydb->error.PHP_EOL;
                exit(0);
        }
        echo "<br><br>Successfully connected to database".PHP_EOL;
	//Check is user already exists
        $query = mysqli_query($mydb,"SELECT * FROM users WHERE username = '$userName' ");
        $count = mysqli_num_rows($query);
	//Check if credentials match the database....if there is a match then the user already has an account
        if ($count == 1){
		//Credentials match existing database records
		echo "<br><br>YOU ALREADY HAVE AN ACCOUNT";
		error_log("Account is already created");
                return false;
        }else{
		//Create new user account if its unique
	        $query2 = mysqli_query($mydb,"INSERT INTO users VALUES (NULL,'$userName', '$hash_password')");
		echo "<br><br>ACCOUNT HAS BEEN MADE";
		error_log("Account is created for : $userName");
                return true;
        }
       
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
     case "register":
      return doRegister($request['username'],$request['password']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
#error_log("test");
echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>
