<?php

session_start();

// we need to parse out the page parameter... need to use REGEX
$page_url = preg_replace('/&page=\d*/', '', $_SERVER['REQUEST_URI']);


// NOTE: delete next statement, for DEBUG purposes only
// $_SESSION['username'] = "captainhoji";
// $_SESSION['signedIn'] = true;
// var_dump($_SESSION);
// echo "<hr>";
// session_destroy();

require "databaseConnection.php";

$termExists = false;

if (isset($_GET['searchTerm']) && !empty($_GET['searchTerm'])) {
    $searchTerm = $_GET["searchTerm"];
    $urlEncodedTerm = urlencode($searchTerm);
    $escapedTerm = mysqli_real_escape_string($mysqli, $searchTerm);

    $termExists = true;
    #$sql_stocks = "SELECT * FROM Stocks WHERE companyName LIKE '%$searchTerm%' OR symbol LIKE '%$searchTerm%';";
}
else
{
    $searchTerm = "";
    $urlEncodedTerm = "";

    $termExists = false;
    #$sql_stocks = "SELECT * FROM Stocks";
}
$sql_num_stocks = "SELECT COUNT(*) AS count FROM Stocks";
if($termExists) {
    $sql_num_stocks = $sql_num_stocks . " WHERE companyName LIKE '%$searchTerm%' OR symbol LIKE '%$searchTerm%'";
}
$sql_num_stocks = $sql_num_stocks . ";";

$results_num_stocks = $mysqli->query($sql_num_stocks);
if ( $results_num_stocks == false ) {
    echo $mysqli->error;
    exit();
}

// How many results per page??
$results_per_page = 25; // arbitrary
$first_page = 1;

// Get the result (count)
$row = $results_num_stocks->fetch_assoc();
$num_results = $row['count'];

$last_page = ceil($num_results / $results_per_page);

// Current page?
if( isset($_GET['page']) && !empty($_GET['page'])) {
    $current_page = $_GET['page'];
}
else {
    $current_page = $first_page;
}

// Error checking - out of bounds?
if($current_page < $first_page) {
    // force back to the first page
    $current_page = $first_page;
}
elseif($current_page > $last_page) {
    $current_page = $last_page;
}

// Calculate the start index - it's a pattern!
$start_index = ($current_page - 1) * $results_per_page;

// Get actual results
$sql_stocks = "SELECT * FROM Stocks";
if($termExists) {
    $sql_stocks = $sql_stocks . " WHERE companyName LIKE '%$searchTerm%' OR symbol LIKE '%$searchTerm%'";
}
$sql_stocks = $sql_stocks . " LIMIT " .$start_index. ", " .$results_per_page. ";";

$results_stocks = $mysqli->query($sql_stocks);
if(!$results_stocks) {
    echo $mysqli->error;
    exit();
}

// $results_stocks = $mysqli->query($sql_stocks);
// if (!$results_stocks) {
//     echo "SQL ERROR: " . $mysqli->error;
//     exit();
// }

// STEP 4: Close DB Connection (NOT YET)
// $mysqli->close();
?>

<?xml version="1.0" encoding="utf-8"?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Ambrish Parekh">

    <title>StockOverflow | Stocks</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/logo.css">
    <!-- Custom styles for this template -->
    <!-- <link href="starter-template.css" rel="stylesheet"> -->

    <style>
        main {
            padding: 1em
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-expand-sm navbar-light bg-light">
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
                <li class="nav-item active">
                    <a class="nav-link" href="stocks.php">Stocks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rankings.php">Rankings</a>
                </li>
                <?php if(isset($_SESSION) && isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="mymoney.php">My Money</a>
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

        <form class="form-inline d-flex justify-content-center" name="searchForm" action="stocks.php" method="GET">
            <!-- <div class="form-group mx-sm-3 mb-2"> -->
            <!-- <div class="form-row"> -->
                <input type="text" class="form-control mx-sm-3 mb-3 w-auto mr-3" name="searchTerm"
                <?php if ($searchTerm): ?>
                    value="<?php echo $searchTerm; ?>"
                <?php else: ?>
                    placeholder="Search Here"
                <?php endif; ?>
                >

                <input class="btn btn-primary mb-3" type="submit" value="Search!">
                <!-- </div> -->
            <!-- </div> -->
        </form>
        <div class="col-12">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item">
                        <a class="page-link" href="
                        <?php if(isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . $first_page;
                        } else {
                            echo $page_url . "?searchTerm=&page=" . $first_page;
                        } ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php if(isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . ($current_page - 1);
                        } else {
                            echo $page_url . "?searchTerm=&page=" . ($current_page - 1);
                        } ?>">Previous</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href=""><?php echo $current_page; ?></a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php if(isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . ($current_page + 1);
                        } else {
                            echo $page_url . "?searchTerm=&page=" . ($current_page + 1);
                        } ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php if (isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . $last_page;
                        } else {
                            echo $page_url . "?searchTerm=&page=" . $last_page;
                        } ?>">Last</a>
                    </li>
                </ul>
            </nav>
        </div> <!-- .col -->
        <div class="col-12 mb-3" style="text-align: center;">
            Showing
            <?php echo $start_index + 1; ?>
            to
            <?php echo ($start_index + $results_stocks->num_rows); ?>
            of
            <?php echo $num_results; ?>
            result(s).
        </div> <!-- .col -->

        <?php if ($results_stocks->num_rows == 0): ?>
            No Stocks Found.
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Company Name</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $results_stocks->fetch_assoc()): ?>
                    <tr class="priceData" data-symbol="<?php echo $row['symbol']; ?>">
                        <td><?php echo $row['symbol']; ?></td>
                        <td><?php echo $row['companyName']; ?></td>
                        <td class="priceHere text-success">$$$</td>
                        <td>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#<?php echo str_replace('%', '_', urlencode($row['symbol'])); ?>">...</button>

                            <!-- Modal -->
                            <div class="modal fade" id="<?php echo str_replace('%', '_', urlencode($row['symbol'])); ?>" tabindex="-1" role="document" aria-labelledby="<?php echo str_replace('%', '_', urlencode($row['symbol'])); ?>CenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <div class="justify-content-center ml-auto mr-auto" style="text-align: center;">
                                                <?php echo $row['symbol']; ?>
                                                <br>
                                                <?php echo $row['companyName']; ?>
                                            </div>
                                            <!-- Follow or Unfollow Functionality here -->
                                            <?php if(isset($_SESSION) && isset($_SESSION['signedIn']) && !empty($_SESSION['signedIn']) && $_SESSION["signedIn"] === TRUE): ?>
                                                <?php
                                                    $username = $_SESSION["username"];

                                                    $sql_follow = "SELECT * FROM Investments WHERE i_symbol=\"".$row['symbol']."\" AND i_username=\"$username\";";
                                                    $results_follow = $mysqli->query($sql_follow);
                                                    if (!$results_follow) {
                                                        echo "SQL ERROR while fetching investment info: " . $mysqli->error;
                                                        exit();
                                                    }
                                                ?>
                                                <button type="button" class="btn btn-info" data-dismiss="modal" id="followButton" data-symbol="<?php echo urlencode($row['symbol']) ?>">
                                                    <?php if($results_follow->num_rows == 0): ?>
                                                        Follow
                                                    <?php else: ?>
                                                        Unfollow
                                                    <?php endif; ?>
                                                </button>
                                            <?php else: ?>
                                                <a href="index.php">Sign in to invest in this stock</a>
                                            <?php endif; ?>
                                            <!-- <button type="button" class="btn btn-info" data-dismiss="modal" id="followButton">
                                                Follow
                                            </button> -->
                                            <!-- <a href="login.php">Sign in to invest in this stock</a>  -->
                                        </div>
                                        <div class="modal-body" style="font-weight: normal;">
                                            <div class="row">
                                                <div class="col priceHere text-success">
                                                    Curr Price
                                                </div>
                                                <div class="col changeHere text-success">
                                                    Price Change
                                                </div>
                                                <div class="col pctHere text-success">
                                                    %Change
                                                </div>
                                                <div class="col timeHere">
                                                    Time
                                                </div>
                                            </div>
                                            <hr>
                                            <strong>CEO: </strong><?php echo $row['CEO']; ?><br>
                                            <strong>Industry: </strong><?php echo $row['industry']; ?><br>
                                            <strong>Market: </strong><?php echo $row['market']; ?><br>
                                            <strong>Company Description: </strong><br>
                                            <p><?php echo $row['companyDescription']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="col-12 mb-3" style="text-align: center;">
            Showing
            <?php echo $start_index + 1; ?>
            to
            <?php echo ($start_index + $results_stocks->num_rows); ?>
            of
            <?php echo $num_results; ?>
            result(s).
        </div> <!-- .col -->
        <div class="col-12">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item">
                        <a class="page-link" href="
                        <?php if(isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . $first_page;
                        } else {
                            echo $page_url . "?searchTerm=&page=" . $first_page;
                        } ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php if(isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . ($current_page - 1);
                        } else {
                            echo $page_url . "?searchTerm=&page=" . ($current_page - 1);
                        } ?>">Previous</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href=""><?php echo $current_page; ?></a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php if(isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . ($current_page + 1);
                        } else {
                            echo $page_url . "?searchTerm=&page=" . ($current_page + 1);
                        } ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php if (isset($_GET['searchTerm'])) {
                            echo $page_url . "&page=" . $last_page;
                        } else {
                            echo $page_url . "?searchTerm=&page=" . $last_page;
                        } ?>">Last</a>
                    </li>
                </ul>
            </nav>
        </div> <!-- .col -->
    </main>

<?php $mysqli->close(); ?>

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
    $("button.btn.btn-info").click(function() {
        symb = decodeURI($(this).data("symbol"));
        var but = $(this);
        console.log(symb);
        $.ajax({
			url: "followStock.php",
			data: {
				symbol: symb
			},
			success: function(result) {
				console.log(result);
				if (result == "Followed") {
					but.html("Unfollow");
					confirm("Stock Followed");
				}
				else if (result == "Unfollowed") {
					but.html("Follow");
					confirm("Stock Unfollowed");
				}
				else if (result == "amountNotZero") {
					confirm("You cannot unfollow stocks that you possess");
				}
				else {
					confirm("There was an error");
				}
			}
		});
	});


    window.myStocks = new Array();
    window.myPrices = new Array();
    $(".priceData").each(function() {
        window.myStocks.push($(this).data("symbol"));
    });

    // console.log("My Stocks");
    // console.log(window.myStocks);

    for(let i = 0; i < window.myStocks.length; i++) {
        $.ajax({
            url: "https://api.iextrading.com/1.0/stock/" + window.myStocks[i] + "/quote?displayPercent=true",
            success: function(result) {
                // console.log(result);
                window.myPrices.push({
                    symbol: window.myStocks[i],
                    price: result.latestPrice,
                    change: result.change,
                    changePercent: result.changePercent,
                    time: result.latestTime
                });
            },
            error: function() {
                console.log("There was an error during this fucking ajax call");
            }
        });
    }
    // console.log("My Prices");
    // console.log(window.myPrices);
    // console.log(myPrices[0]);
    // console.log(Object.keys(myPrices));

    // let j = 0;
    $(".priceData").each(function() {
        var divSymb = $(this).data("symbol");
        // j++;
        // console.log(divSymb);
        // console.log(window.myPrices[0].price);
        // var rando = myPrices["A"];
        for(let ind = 0; ind < window.myPrices.length; ind++) {
            if(window.myPrices[ind].symbol == divSymb) {
                var strPrice = String(window.myPrices[ind].price);
                $(this).find(".priceHere").text(strPrice);
            }
        }
        // console.log(window.myPrices.price)
        // $(this).find(".priceHere").text("kill me now");

    });



    // 1 sec
    setInterval(function() {
        for(let i = 0; i < window.myStocks.length; i++) {
            // var page_url = "https://api.iextrading.com/1.0/stock/" + myStocks[i] + "/quote";
            $.ajax({
                url: "https://api.iextrading.com/1.0/stock/" + window.myStocks[i] + "/quote?displayPercent=true",
                success: function(result) {
                    // console.log(result);
                    window.myPrices.push({
                        symbol: window.myStocks[i],
                        price: result.latestPrice,
                        change: result.change,
                        changePercent: result.changePercent,
                        time: result.latestTime
                    });
                },
                error: function() {
                    console.log("There was an error during this fucking ajax call");
                }
            });
        }
        // console.log("My Prices");
        // console.log(window.myPrices);
        // console.log(myPrices[0]);
        // console.log(Object.keys(myPrices));

        // let j = 0;
        $(".priceData").each(function() {
            var divSymb = $(this).data("symbol");
            // j++;
            // console.log(divSymb);
            // console.log(window.myPrices[0].price);
            // var rando = myPrices["A"];
            for(let ind = 0; ind < window.myPrices.length; ind++) {
                if(window.myPrices[ind].symbol == divSymb) {
                    if(window.myPrices[ind].price) {
                        var Price = (window.myPrices[ind].price).toFixed(2);
                        var strPrice = String(Price);
                    } else {
                        var strPrice = "N/A";
                    }
                    if(window.myPrices[ind].change) {
                        var Change = (window.myPrices[ind].change).toFixed(2);
                        var strChange = String(Change);
                    } else {
                        var strChange = "N/A";
                    }
                    if(window.myPrices[ind].changePercent) {
                        var ChangPercent = (window.myPrices[ind].changePercent).toFixed(2);
                        var strChangPercent = String(ChangPercent) + "\%";
                    } else {
                        var strChangPercent = "N/A";
                    }

                    if(window.myPrices[ind].price < $(this).find(".priceHere").text()) {
                        $(this).find(".priceHere").toggleClass("text-danger text-success");
                    }
                    if(window.myPrices[ind].price > $(this).find(".priceHere").text()) {
                        $(this).find(".priceHere").toggleClass("text-danger text-success");
                    }
                    if(window.myPrices[ind].change < 0) {
                        $(this).find(".changeHere").addClass("text-danger");
                    }
                    if(window.myPrices[ind].change > 0) {
                        $(this).find(".changeHere").removeClass("text-danger");
                    }
                    if(window.myPrices[ind].changePercent < 0) {
                        $(this).find(".pctHere").addClass("text-danger");
                    }
                    if(window.myPrices[ind].changePercent > 0) {
                        $(this).find(".pctHere").removeClass("text-danger");
                    }
                    $(this).find(".priceHere").text(strPrice);
                    $(this).find(".changeHere").text(strChange);
                    $(this).find(".pctHere").text(strChangPercent);
                    $(this).find(".timeHere").text(window.myPrices[ind].time);
                }
            }
            // console.log(window.myPrices.price)
            // $(this).find(".priceHere").text("kill me now");

        });

    }, 1000);

    </script>
</body>
</html>
