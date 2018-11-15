<?php
    session_start();
    $_SESSION['signedIn'] = false;
    $_SESSION['username'] = "";

    // DEBUG: Remove when done
    // var_dump($_POST);
    // echo "<hr>";
    // var_dump($_SESSION);
    // echo "<hr>";

    if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirmPassword'])) {
        if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirmPassword'])) {
            $error = "Please fill out all fields completely.<br/>";
        }
        else {
            $malformed = false;

            $username = $_POST["username"];
            $password = $_POST["password"];
            $confirmPassword = $_POST["confirmPassword"];

            $malformed = false;
            $error = "";
            if (strlen($username) < 8 || strlen($username) > 20) {
                // excecuted when username does not satisfy length restriction
                $error = $error . "Invalid username: must be between 8 and 20 characters.<br/>";
                $malformed = true;
            }
            if (strlen($password) < 8 || strlen($password) > 20) {
                // excecuted when password does not satisfy length restriction
                $error = $error . "Invalid password: must be between 8 and 20 characters<br/>";
                $malformed = true;
            }
            if ($password != $confirmPassword) {
                // executed when password and confirmPassword do not match
                $error = $error . "Passwords you entered do not match<br/>";
                $malformed = true;
            }

            if(!$malformed) {
                require "databaseConnection.php";

                $dupCheckSql = "SELECT * FROM Users WHERE u_username=\"$username\";";
                $results_dup = $mysqli->query($dupCheckSql);
                if (!$results_dup) {
                    echo "SQL ERROR: " . $mysqli->error;
                    exit();
                }

            	if ($results_dup->num_rows > 0) {
            	    // executed when the username already exists
            	    $error = $error . "Username already exists<br/>";
                } else {
                    $defaultMoney = 100000; // currency in cents, equals to $1000
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $insertSql = "INSERT INTO Users(u_username, u_password, u_balance) VALUES" .
                        "(\"$username\", \"$hash\", \"$defaultMoney\");";

                    $insertRank = "INSERT INTO Rankings(r_username, r_money) VALUES" .
                        "(\"$username\", $defaultMoney);";
                    if ($mysqli->query($insertSql) === TRUE && $mysqli->query($insertRank) === TRUE) {

                        // executed when the user has succesfully registered.
                        $_SESSION['signedIn'] = true;
                        $_SESSION['username'] = $username;

                        // TODO: for now it leads to STOCKS, but should lead to HOME
                        header('Location: home.php');
                    }
                    else {
                        $error = $error . "Error updating: " . $mysqli->error;
                    }
                }

                $mysqli->close();
            }
        }
    }
    else {
        $error = "Please fill out all fields completely.";
    }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="author" content="Ambrish Parekh">

        <title>StockOverflow | Register</title>

        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="css/logo.css">
        <style>
        body {
            background-color: #dadada;
            padding-top: 3em;
        }
        main {
            margin-top: 3em;
        }
        #register {
            font-size: 4rem;
            color: #333;
            font-weight: 400;
        }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4 col-md-3 col-sm-2"></div>
                <div class="col-lg-4 col-md-6 col-sm-8">
                    <?php include "components/nav.html"; ?>
                </div>
                <div class="col-lg-4 col-md-3 col-sm-2"></div>
            </div>
        </div>

        <main>
            <div class="row align-items-center justify-content-center">
                <div id="register">Register</div>
            </div>
            <div class="row align-items-center justify-content-center">
                <form name="registerForm" action="register.php" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="confirm-password" name="confirmPassword" placeholder="Confirm Password">
                    </div>
                    <div class="form-group" style="text-align: center;">
                        <button class="btn btn-primary" type="submit">Register</button>
                    </div>
                </form>
            </div>
            <div class="row align-items-center justify-content-center text-danger" style="text-align: center;">
                <?php
                    if(isset($error) && !empty($error)) {
                        echo $error;
                    }
                ?>
            </div>
            <div class="row align-items-center justify-content-center">
                <a href="index.php">Already a User? Log In Here</a>
            </div>
            <div class="row align-items-center justify-content-center">
                <a href="home.php">Continue as Guest</a>
            </div>
        </main>



        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
        <!-- <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script> -->
        <!-- <script src="../../assets/js/vendor/popper.min.js"></script> -->
        <!-- <script src="../../dist/js/bootstrap.min.js"></script> -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    </body>
</html>
