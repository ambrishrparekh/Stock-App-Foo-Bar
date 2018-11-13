<?php
    session_start();

    require "databaseConnection.php";

    if (isset($_SESSION) && isset($_SESSION['signedIn']) && $_SESSION["signedIn"] === TRUE) {
        $username = $_SESSION["username"];

        include 'databaseConnection.php';

        // Check if the username already exists
        $sql = "SELECT * FROM Investments WHERE i_username=\"$username\";";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }

        // STEP 4: Close DB Connection (NOT YET)
        $mysqli->close();
    }
    else {
        header("Location: index.php");
        exit();
    }
?>

<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Ambrish Parekh">

    <title>StockOverflow | My Money</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/logo.css">
    <!-- Custom styles for this template -->
    <!-- <link href="starter-template.css" rel="stylesheet"> -->

    <style>
        main {
            padding: 1em
        }
        .input-group > .form-control {
            width: 6rem;
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="navbar-brand" href="#" style="width: 200px;">
            <?php include "components/nav.html"; ?>
        </div>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse text-center" id="navbarText">
            <ul class="navbar-nav nav-fill w-100">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="stocks.php">Stocks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rankings.php">Rankings</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="mymoney.php">My Money</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="mx-auto">
        <?php if ($results->num_rows >= 1): ?>
   		<table class="table table-striped">
            <tr>
                <th scope="col">Symbol</th>
                <th scope="col">Amount</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
            <?php while($row = $results->fetch_assoc()): ?>
            <tr>
                <td scope="row"><?php echo $row["i_symbol"];?></td>
                <td><?php echo $row["i_amount"];?></td>
                <td class="">
                    <form class="form-inline" action="invest.php?symbol=<?php echo $row["i_symbol"];?>" method="post">
                        <label for="buy">Buy</label>
                        <div class="input-group">
                            <input class="form-control ml-3" type="number" id="buy" name="buyAmount" min="0">
                            <div class="input-group-append">
                                <input class="btn btn-success" type="submit" value="Buy">
                            </div>
                        </div>
                    </form>
                </td>
                <td class="">
                    <form class="form-inline" action="invest.php?symbol=<?php echo $row["i_symbol"];?>" method="post">
                        <label for="sell">Sell</label>
                        <div class="input-group">
                            <input class="form-control ml-3" type="number" id="sell" name="sellAmount" min="0" max="<?php echo $row["i_amount"];?>">
                            <div class="input-group-append">
                                <input class="btn btn-warning" type="submit" value="Sell">
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <div class="text-danger">Follow a stock to start investing! Go to the Stocks page to get started!<div>
        <?php endif; ?>
    </main>
    <div class="row justify-content-center align-items-center">
        <a class="btn btn-outline-danger" href="index.php">Log Out</a>
    </div>

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</body>
</html>
