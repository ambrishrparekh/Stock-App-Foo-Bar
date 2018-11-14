<?php

$host = "303.itpwebdev.com";
$user = "parekha_db_user";
$pass = "uscItp2018";
$db = "parekha_StockApp";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    echo "MySQL Connection Error:" . $mysqli->connect_error;
    exit();
}
?>