<?php
    session_start();

    //ContextOptions for API connection to get current price
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    require "databaseConnection.php";

    if (array_key_exists("signedIn", $_SESSION) && $_SESSION["signedIn"] === TRUE) {
        $username = $_SESSION["username"];

        include 'databaseConnection.php';

        // get balance of this user
        $sql = "SELECT * FROM Users WHERE u_username=\"$username\";";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }
        $balance = (float) $results->fetch_assoc()["u_balance"];
        $balance = $balance / 100.0;

        // Get investments of this user
        $sql = "SELECT * FROM Investments WHERE i_username=\"$username\";";
        $results = $mysqli->query($sql);
        if (!$results) {
            echo "SQL ERROR: " . $mysqli->error;
            exit();
        }

    }
    else {
        header("Location: index.php");
        exit();
    }
?>

<xml version="1.0" encoding="utf-8">
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

    <main class="">
        <div style="text-align:center;">
            <h1 class="display-3"><?php echo $_SESSION['username']; ?>&apos;s Profile</h1>
            <h3>My balance: $<?php echo number_format($balance, 2);?></h3>
        </div>
        <br>
        <h1 class="display-4">My Stocks</h1>
        <?php if ($results->num_rows >= 1): ?>
        <?php
            $symbolArr = array();
            $amountArr = array();
            while($row = $results->fetch_assoc()) {
                array_push($symbolArr, $row["i_symbol"]);
                array_push($amountArr, $row["i_amount"]);
            }
            $priceArr = array();
            $priceUrl = "https://api.iextrading.com/1.0/stock/market/batch?types=price&symbols=";
            for ($i = 0; $i < count($symbolArr); $i++) {
                $priceUrl = $priceUrl.urlencode($symbolArr[$i]).",";
                if ($i%100 == 99 || $i == count($symbolArr) - 1) {
                    $priceUrl = substr($priceUrl, 0, -1);
                    $priceJson = file_get_contents($priceUrl, false, stream_context_create($arrContextOptions));
                    $jsonArr = json_decode($priceJson, true);
                    $k = ((int)($i / 100)) * 100;
                    for ($j = 0; $j < count($jsonArr); $j++) {
                        $p = $jsonArr[$symbolArr[$k]];
                        array_push($priceArr, $p["price"]);
                        $k++;
                    }
                }
            }
        ?>
   		<table id="mytable" class="table table-striped">
            <tr>
                <th scope="col">Symbol</th>
                <th scope="col">Amount</th>
                <th scope="col">Price</th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
            <?php for ($i = 0; $i < count($symbolArr); $i++) { ?>
            <tr>
                <td scope="row"><?php echo $symbolArr[$i];?></td>
                <td><?php echo $amountArr[$i];?></td>
                <td class="priceColumn"></td>
                <td class="buyColumn">
                    <form class="form-inline" action="invest.php?symbol=<?php echo urlencode($symbolArr[$i]);?>" method="post">
                        <label for="buy">Buy</label>
                        <div class="input-group">
                            <input class="form-control ml-3" type="number" id="buy" name="buyAmount" min="1" value="1">
                            <div class="input-group-append">
                                <input class="btn btn-success" type="submit" name="submit" value="Buy">
                            </div>
                        </div>
                    </form>
                </td>
                <td class="buyError">
                <?php
                    if (array_key_exists("status", $_REQUEST)) {
                        $status = $_REQUEST["status"];
                        if ($status == "lowBalance" && urldecode($symbolArr[$i]) == $_REQUEST["symbol"]) {
                            echo "<strong class=\"text-danger\">Not enough money</strong>";
                        }
                    }
                ?>
                </td>
                <td class="sellColumn">
                <?php if ($amountArr[$i] != 0) { ?>
                    <form class="form-inline" action="invest.php?symbol=<?php echo urlencode($symbolArr[$i]);?>" method="post">
                        <label for="sell">Sell</label>
                        <div class="input-group">
                            <input class="form-control ml-3" type="number" id="sell" name="sellAmount" value="1" min="1" max="<?php echo $amountArr[$i];?>">
                            <div class="input-group-append">
                                <input class="btn btn-warning" type="submit" name="submit" value="Sell">
                            </div>
                        </div>
                    </form>
                <?php }?>
                </td>
                <td class="sellError"></td>
            </tr>
            <?php } ?>
        </table>
        <?php else: ?>
            <div class="text-danger">Follow a stock to start investing! Go to the Stocks page to get started!<div>
        <?php endif; ?>
    </main>
    <div class="d-flex justify-content-center align-items-center">
        <a class="btn btn-outline-dark mr-3 mb-3" href="mymoneyDark.php">Dark Theme</a>
        <a class="btn btn-outline-danger mb-3" href="index.php">Log Out</a>
    </div>

</body>
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
    <script>
    var symbolArr = <?php echo json_encode($symbolArr).";";?>
	var priceUrl = "https://api.iextrading.com/1.0/stock/market/batch?types=price&symbols=";
    for (i = 0; i < symbolArr.length; i++) {
        priceUrl = priceUrl + encodeURI(symbolArr[i]) + ",";
        if (i%100 == 99 || i == symbolArr.length - 1) {
            priceUrl = priceUrl.substring(0, priceUrl.length-1);
			$.ajax({
				url: priceUrl,
				success: function(result) {
					var k = Math.floor(i / 100) * 100;
					for (var symbol in result) {
						document.getElementById("mytable").rows[k+1].cells[2].innerHTML = "$" + result[symbol].price;
						k++;
					}
				}
			});
        	priceUrl = "https://api.iextrading.com/1.0/stock/market/batch?types=price&symbols=";
        }
    }
    </script>
</html>
