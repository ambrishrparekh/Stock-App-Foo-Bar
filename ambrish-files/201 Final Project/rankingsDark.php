<?php

session_start();

// NOTE: delete next statement, for DEBUG purposes only
// $_SESSION['username'] = "captainhoji";
// $_SESSION['signedIn'] = true;
// var_dump($_SESSION);
// echo "<hr>";
// session_destroy();

require "databaseConnection.php";

$sql_rankings = "SELECT * FROM Rankings ORDER BY r_money DESC;";

$results_rankings = $mysqli->query($sql_rankings);
if (!$results_rankings) {
    echo "SQL ERROR: " . $mysqli->error;
    exit();
}

// STEP 4: Close DB Connection (NOT YET)
$mysqli->close();

$curr_rank = 0;
?>

<?xml version="1.0" encoding="utf-8"?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Ambrish Parekh">

    <title>StockOverflow | Rankings</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/logoDark.css">
    <!-- Custom styles for this template -->
    <!-- <link href="starter-template.css" rel="stylesheet"> -->

    <style>
        main {
            padding: 1em
        }
    </style>

</head>

<body class="bg-dark text-light">

    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <a class="navbar-brand" href="homeDark.php" style="width: 200px;">
            <?php include "components/navDark.html"; ?>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse text-center" id="navbarText">
            <ul class="navbar-nav nav-fill w-100">
                <li class="nav-item">
                    <a class="nav-link" href="homeDark.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="stocksDark.php">Stocks</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="rankingsDark.php">Rankings</a>
                </li>
                <?php if(isset($_SESSION) && isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="mymoneyDark.php">My Money</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Log In/Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main>
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Total Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $results_rankings->fetch_assoc()): ?>
                    <tr>
                        <td><?php $curr_rank = $curr_rank + 1; echo $curr_rank; ?></td>
                        <td><?php echo $row['r_username']; ?></td>
                        <td>$<?php echo $row['r_money']; ?></td>
                    </tr>
                <?php endwhile; ?>
        </table>
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</body>
</html>
