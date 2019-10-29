#!/usr/bin/php
<?php
$dbip = "192.168.1.138:3306";
$username = "user";
$password = "password";
$dbname = "MOVIE_DB";

$dbconnect = new mysqli($dbip,$username,$password,$dbname);

if ($dbconnect->errno != 0) {
		echo "Failed to connect : ".$mydb->error.PHP_EOL;
		exit(0);
}

echo "Connected to database".PHP_EOL;

$insertquery=mysqli_query($dbconnect, "INSERT INTO users VALUES (NULL, 'test','password')");


?>
