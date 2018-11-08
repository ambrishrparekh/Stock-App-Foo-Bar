<!-- 
Basic outline of registration page.

These are currently implemented restrictions on username and password:
    - New username should not match any other username already in the database.
    - username and password should be between 8 and 20 characters (inclusive).
    - password and confirmPassword value should be same
 -->


<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <style>

    </style>
</head>
<body>
	<form name="registerForm" action="register.php" method="post">
		Username: <input type="text" name="username" placeholder="username"><br />
		Password: <input type="password" name="password" placeholder="password"><br />
		Confirm password: <input type="password" name="confirmPassword" placeholder="password"><br />
		<input type="submit" name="register" value="Sign Up">
	</form>
	<hr>
<?php        
    $defaultMoney = 100000; // currency in cents, equals to $1000

    if (array_key_exists("register", $_POST)) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $confirmPassword = $_POST["confirmPassword"];
        
        // clear the post array
        $_POST = array();
        
        $host = "localhost";
        $user = "root";
        $pass = "root";
        $db = "StockApp";
        
        $mysqli = new mysqli($host, $user, $pass, $db, 3306);
        
        if ($mysqli->connect_errno) {
            echo "MySQL Connection Error:" . $mysqli->connect_error;
            exit();
        }
        
        $malformed = false;
        if (strlen($username) < 8 || strlen($username) > 20) {
            // excecuted when username does not satisfy length restriction
            echo "<strong>Invalid username: must be between 8 and 20 characters<strong><br />";
            $malformed = true;
        }
        if (strlen($password) < 8 || strlen($password) > 20) {
            // excecuted when password does not satisfy length restriction
            echo "<strong>Invalid password: must be between 8 and 20 characters<strong><br />";
            $malformed = true;
        }
        if ($password != $confirmPassword) {
            // executed when password and confirmPassword do not match
            echo "<strong>Passwords you entered do not match<strong>";
            $malformed = true;
        }
        if ($malformed) {
            $mysqli->close();
            exit();
        }
        // Check if the username already exists
        $dupCheckSql = "SELECT * FROM Users WHERE u_username=\"$username\";";
        $results = $mysqli->query($dupCheckSql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
        
    	if ($results->num_rows > 0) {
    	    // executed when the username already exists
    	    echo "<strong>username already exists</strong>";
    	    $mysqli->close();
    	    exit();
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO Users(u_username, u_password, u_balance) VALUES" .
            "(\"$username\", \"$hash\", \"$defaultMoney\");";
        if ($mysqli->query($insertSql) === TRUE) {
            // executed when the user has succesfully registered.
            echo "<strong>Registration Complete!</strong>";            
        }
        else {
            echo "Error updating: " . $mysqli->error;
        }
      
        $mysqli->close();
    }
    
?>
</body>
</html>
