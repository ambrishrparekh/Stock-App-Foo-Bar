<?php
$host = "localhost";
$user = "root";
$pass = "root";
$db = "StockApp";

$mysqli = new mysqli($host, $user, $pass, $db, 3306);

if ($mysqli->connect_errno) {
    echo "MySQL Connection Error:" . $mysqli->connect_error;
    exit();
}
?>