<!-- 
Basic outline of signin page.

Valid username and password will redirect the page to "graphs.php", which does not exist yet.
Else the page will show error message.
 -->
<?php 
    session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign in</title>
    <style>

    </style>
</head>
<body>
	<form name="registerForm" action="signin.php" method="post">
		Username: <input type="text" name="username" placeholder="username"><br />
		Password: <input type="password" name="password" placeholder="password"><br />
		<input type="submit" name="signin" value="Sign In"><a href="register.php">Sign Up</a>
	</form>
	<hr>
<?php        
    if (array_key_exists("signin", $_POST)) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        // clear the post array
        $_POST = array();
        
        include 'databaseConnection.php';
        
        // Check if the username already exists
        $sql = "SELECT * FROM Users WHERE u_username=\"$username\";";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
        
    	if ($results->num_rows == 1) {
    	    $row = $results->fetch_assoc();
    	    if (password_verify($password, $row["u_password"])) {
    	        // executed when sign in is successful
                $_SESSION["signedIn"] = TRUE;
                $_SESSION["username"] = $username;
                $mysqli->close();
    	        header("Location: /stockAppPHP/graphs.php");
    	        exit();
    	    }
        }
        echo "<strong>Invalid username or password<strong>";
        $mysqli->close();
    }
    
?>
</body>
</html>
