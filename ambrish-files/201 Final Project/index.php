<?php
    session_start();
    $_SESSION['signedIn'] = false;
    $_SESSION['username'] = "";

    $error = "";

    if(isset($_POST) && isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $_POST["username"];
        $password = $_POST["password"];

        require "databaseConnection.php";

        // Check if the username already exists
        $sql = "SELECT * FROM Users WHERE u_username='" . $username . "';";
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

                header("Location: home.php");
            }
        }
        $error = "Invalid username or password.";
        $mysqli->close();
    } else {
        $error = "Enter information into all fields.";
    }

?>

<!DOCTYPE html>
<html class="container-fluid h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Ambrish Parekh">

    <title>StockOverflow | Log In</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/logo.css">

    <style>
    body {
        padding-top: 3em;
    }
    main {
        margin-top: 3em;
    }
    #login {
        font-size: 4rem;
        font-weight: 400;
    }
    </style>
</head>
<body class="h-100" style="background-image: linear-gradient(to top, #e6e9f0 0%, #eef1f5 100%);">

<style>
    body {
        padding-top: 3em;
    }
    main {
        margin-top: 3em;
    }
    #login {
        font-size: 4rem;
        font-weight: 400;
    }
    .form-control {
        background-color: whitesmoke;
    }
    </style>

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
            <div id="login">LOG IN</div>
        </div>
        <div class="row align-items-center justify-content-center">
            <form action="index.php" method="post">
                <div class="form-group">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <div class="form-group" style="text-align: center;">
                    <button class="btn btn-primary" type="submit">Log In</button>
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
            <a href="register.php">Register as New User</a>
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
