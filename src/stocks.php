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
    $mSearchTerm = $_GET["searchTerm"];
    $searchTerm = str_replace(' ', '-', $mSearchTerm); // Replaces all spaces with hyphens.
    $searchTerm = preg_replace('/[^A-Za-z0-9#^+\-]/', '', $searchTerm);
    
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
    echo "<br />$sql_num_stocks";
    exit();
}

// How many results per page??
$results_per_page = 25; // arbitrary
$first_page = 1;
$current_page = 1;

// Get the result (count)
$row = $results_num_stocks->fetch_assoc();
$num_results = $row['count'];

$last_page = ceil($num_results / $results_per_page);
if ($last_page == 0) {
    $last_page = 1;
}

// Current page?
if( isset($_GET['page']) && !empty($_GET['page'])) {
    $current_page = $_GET['page'];
}

// Error checking - out of bounds?
if($current_page < $first_page) {
    // force back to the first page
    $current_page = $first_page;
}
else if($current_page > $last_page) {
    $current_page = $last_page;
}

// Calculate the start index - it's a pattern!
$start_index = ($current_page - 1) * $results_per_page;

// Get actual results
$sql_stocks = "SELECT * FROM Stocks";
if($termExists) {
    $sql_stocks = $sql_stocks . " WHERE companyName LIKE \"%$escapedTerm%\" OR symbol LIKE \"%$escapedTerm%\"";
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
	<style>
	.autocomplete {
        position: relative;
        display: inline-block;
    }
    .autocomplete-items {
      position: absolute;
      border: 1px solid #d4d4d4;
      border-bottom: none;
      border-top: none;
      z-index: 99;
      top: 100%;
      left: 0;
      right: 0;
    }
    .autocomplete-items div {
      padding: 10px;
      cursor: pointer;
      background-color: #fff; 
      border-bottom: 1px solid #d4d4d4; 
    }
    .autocomplete-items div:hover {
      /*when hovering an item:*/
      background-color: #e9e9e9; 
    }
    .autocomplete-active {
      /*when navigating through the items using the arrow keys:*/
      background-color: DodgerBlue !important; 
      color: #ffffff; 
    }
	</style>
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

        <form class="form-inline d-flex justify-content-center" name="searchForm" 
        	action="stocks.php" method="GET" autocomplete="off">
            <!-- <div class="form-group mx-sm-3 mb-2"> -->
            <!-- <div class="form-row"> -->
            <div class="autocomplete">
                <input id="searchBar" type="text" class="form-control mx-sm-3 mb-3 w-auto mr-3" name="searchTerm"
                <?php if ($searchTerm): ?>
                    value="<?php echo $searchTerm; ?>"
                <?php else: ?>
                    placeholder="Search Here"
                <?php endif; ?>
                >
            </div>

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
    <script>
    var symbols = ["A","AA","AAAU","AABA","AAC","AADR","AAL","AAMC","AAME","AAN","AAOI","AAON","AAP","AAPL","AAT","AAU","AAWW","AAXJ","AAXN","AB","ABAC","ABB","ABBV","ABC","ABCB","ABCD","ABDC","ABEO","ABEV","ABG","ABIL","ABIO","ABM","ABMD","ABR","ABR-A","ABR-B","ABR-C","ABT","ABTX","ABUS","ABX","AC","ACA","ACAD","ACB","ACBI","ACC","ACCO","ACER","ACES","ACET","ACGL","ACGLO","ACGLP","ACH","ACHC","ACHN","ACHV","ACIA","ACIM","ACIU","ACIW","ACLS","ACM","ACMR","ACN","ACNB","ACOR","ACP","ACRE","ACRS","ACRX","ACSI","ACST","ACT","ACTG","ACU","ACV","ACWF","ACWI","ACWV","ACWX","ACY","ADAP","ADBE","ADC","ADES","ADI","ADIL","ADILW","ADM","ADMA","ADMP","ADMS","ADNT","ADOM","ADP","ADRA","ADRD","ADRE","ADRO","ADRU","ADS","ADSK","ADSW","ADT","ADTN","ADUS","ADVM","ADX","ADXS","AE","AEB","AED","AEE","AEF","AEG","AEGN","AEH","AEHR","AEIS","AEL","AEM","AEMD","AEO","AEP","AER","AERI","AES","AET","AETI","AEY","AEYE","AEZS","AFB","AFC","AFG","AFH","AFHBL","AFI","AFIF","AFIN","AFK","AFL","AFMD","AFSI","AFSI-A","AFSI-B","AFSI-C","AFSI-D","AFSI-E","AFSI-F","AFT","AFTY","AG","AGCO","AGD","AGEN","AGF","AGFS","AGFSW","AGG","AGGE","AGGP","AGGY","AGI","AGIO","AGLE","AGM","AGM-A","AGM-B","AGM-C","AGM.A","AGMH","AGN","AGNC","AGNCB","AGNCN","AGND","AGO","AGO-B","AGO-E","AGO-F","AGQ","AGR","AGRO","AGRX","AGS","AGT","AGTC","AGX","AGYS","AGZ","AGZD","AHC","AHH","AHL","AHL-C","AHL-D","AHPA","AHPAU","AHPAW","AHPI","AHT","AHT-D","AHT-F","AHT-G","AHT-H","AHT-I","AI","AI-B","AIA","AIEQ","AIF","AIG","AIG+","AIHS","AIIQ","AIMC","AIMT","AIN","AINC","AINV","AIPT","AIQ","AIR","AIRG","AIRI","AIRR","AIRT","AIT","AIV","AIV-A","AIW","AIY","AIZ","AIZP","AJG","AJRD","AJX","AJXA","AKAM","AKAO","AKBA","AKCA","AKER","AKG","AKO.A","AKO.B","AKP","AKR","AKRX","AKS","AKTS","AKTX","AL","ALACU","ALB","ALBO","ALCO","ALD","ALDR","ALDX","ALE","ALEX","ALFA","ALG","ALGN","ALGR","ALGRR","ALGRU","ALGRW","ALGT","ALIM","ALJJ","ALK","ALKS","ALL","ALL-A","ALL-B","ALL-D","ALL-E","ALL-F","ALL-G","ALLE","ALLK","ALLO","ALLT","ALLY","ALLY-A","ALNA","ALNY","ALO","ALOT","ALP-Q","ALPN","ALQA","ALRM","ALRN","ALSK","ALSN","ALT","ALTR","ALTS","ALTY","ALV","ALX","ALXN","ALYA","ALZH","AM","AMAG","AMAL","AMAT","AMBA","AMBC","AMBCW","AMBO","AMBR","AMC","AMCA","AMCN","AMCX","AMD","AME","AMED","AMEH","AMG","AMGN","AMGP","AMH","AMH-D","AMH-E","AMH-F","AMH-G","AMH-H","AMID","AMJ","AMJL","AMKR","AMLP","AMMA","AMN","AMNB","AMOT","AMOV","AMP","AMPE","AMPH","AMR","AMRB","AMRC","AMRH","AMRHW","AMRK","AMRN","AMRS","AMRWW","AMRX","AMS","AMSC","AMSF","AMSWA","AMT","AMTB","AMTBB","AMTD","AMTX","AMU","AMUB","AMWD","AMX","AMZA","AMZN","AN","ANAB","ANAT","ANCB","ANCX","ANDE","ANDX","ANET","ANF","ANFI","ANGI","ANGL","ANGO","ANH","ANH-A","ANH-B","ANH-C","ANIK","ANIP","ANIX","ANSS","ANTM","ANY","AOA","AOBC","AOD","AOK","AOM","AON","AOR","AOS","AOSL","AP","APA","APAM","APB","APC","APD","APDN","APEI","APEN","APF","APH","APHA","APHB","APLE","APLS","APO","APO-A","APO-B","APOG","APOP","APOPW","APPF","APPN","APPS","APRI","APRN","APT","APTI","APTO","APTS","APTV","APTX","APU","APVO","APWC","APY","AQ","AQB","AQMS","AQN","AQST","AQUA","AQXP","AR","ARA","ARAV","ARAY","ARC","ARCB","ARCC","ARCE","ARCH","ARCI","ARCM","ARCO","ARCT","ARCW","ARD","ARDC","ARDM","ARDS","ARDX","ARE","ARE-D","ARES","ARES-A","AREX","ARGD","ARGO","ARGT","ARGX","ARI","ARI-C","ARII","ARKG","ARKK","ARKQ","ARKR","ARKW","ARL","ARLO","ARLP","ARMK","ARNA","ARNC","AROC","AROW","ARPO","ARQL","ARR","ARR-A","ARR-B","ARRS","ARRY","ARTNA","ARTW","ARTX","ARVN","ARVR","ARW","ARWR","ARYA","ARYAU","ARYAW","ASA","ASB","ASB+","ASB-C","ASB-D","ASB-E","ASC","ASCMA","ASEA","ASET","ASFI","ASG","ASGN","ASH","ASHR","ASHS","ASHX","ASIX","ASLN","ASM","ASMB","ASML","ASNA","ASND","ASNS","ASPN","ASPS","ASPU","ASR","ASRT","ASRV","ASRVP","AST","ASTC","ASTE","ASUR","ASV","ASX","ASYS","AT","ATAI","ATAX","ATEC","ATEN","ATGE","ATH","ATHM","ATHN","ATHX","ATI","ATIS","ATISW","ATKR","ATLC","ATLO","ATMP","ATNI","ATNM","ATNX","ATO","ATOM","ATOS","ATR","ATRA","ATRC","ATRI","ATRO","ATRS","ATSG","ATTO","ATTU","ATU","ATUS","ATV","ATVI","ATXI","AU","AUBN","AUDC","AUG","AUGR","AUMN","AUO","AUPH","AUSE","AUSF","AUTL","AUTO","AUY","AVA","AVAL","AVAV","AVB","AVCO","AVD","AVDL","AVEO","AVGO","AVGR","AVH","AVID","AVK","AVLR","AVNS","AVNW","AVP","AVRO","AVT","AVX","AVXL","AVY","AVYA","AWF","AWI","AWK","AWP","AWR","AWRE","AWSM","AWX","AX","AXAS","AXDX","AXE","AXGN","AXJL","AXJV","AXL","AXNX","AXON","AXP","AXR","AXS","AXS-D","AXS-E","AXSM","AXTA","AXTI","AXU","AY","AYI","AYR","AYTU","AYX","AZN","AZO","AZPN","AZRE","AZRX","AZUL","AZZ","B","BA","BAB","BABA","BABY","BAC","BAC+A","BAC-A","BAC-B","BAC-C","BAC-E","BAC-K","BAC-L","BAC-W","BAC-Y","BAF","BAH","BAK","BALB","BAM","BANC","BANC-D","BANC-E","BAND","BANF","BANFP","BANR","BANX","BAP","BAR","BAS","BASI","BATRA","BATRK","BATT","BAX","BB","BBAX","BBBY","BBC","BBCA","BBD","BBDC","BBDO","BBEU","BBF","BBGI","BBH","BBJP","BBK","BBL","BBN","BBOX","BBP","BBRC","BBRE","BBSI","BBT","BBT-D","BBT-E","BBT-F","BBT-G","BBT-H","BBU","BBVA","BBW","BBX","BBY","BC","BCAC","BCACR","BCACU","BCACW","BCBP","BCC","BCD","BCE","BCEI","BCH","BCI","BCLI","BCM","BCML","BCNA","BCO","BCOM","BCOR","BCOV","BCPC","BCRH","BCRX","BCS","BCS-D*","BCTF","BCV","BCV-A","BCX","BDC","BDC-B","BDCL","BDCS","BDCZ","BDD","BDGE","BDJ","BDL","BDN","BDR","BDRY","BDSI","BDX","BDXA","BE","BEAT","BECN","BEDU","BEF","BEL","BELFA","BELFB","BEMO","BEN","BEP","BERN","BERY","BF.A","BF.B","BFAM","BFC","BFIN","BFIT","BFK","BFO","BFOR","BFR","BFRA","BFS","BFS-C","BFS-D","BFST","BFY","BFZ","BG","BGB","BGCP","BGFV","BGG","BGH","BGI","BGIO","BGNE","BGR","BGS","BGSF","BGT","BGX","BGY","BH","BH.A","BHB","BHBK","BHC","BHE","BHF","BHFAL","BHGE","BHK","BHLB","BHP","BHR","BHR-B","BHTG","BHV","BHVN","BIB","BIBL","BICK","BID","BIDU","BIF","BIG","BIIB","BIKR","BIL","BILI","BIO","BIO.B","BIOC","BIOL","BIOS","BIP","BIS","BIT","BITA","BIV","BIZD","BJ","BJJN","BJK","BJO","BJRI","BJUL","BJZ","BK","BK-C","BKC","BKCC","BKD","BKE","BKEP","BKEPP","BKF","BKH","BKI","BKJ","BKK","BKLN","BKN","BKNG","BKS","BKSC","BKT","BKTI","BKU","BKYI","BL","BLBD","BLCM","BLCN","BLD","BLDP","BLDR","BLE","BLES","BLFS","BLH","BLHY","BLIN","BLK","BLKB","BLL","BLMN","BLMT","BLNK","BLNKW","BLOK","BLPH","BLRX","BLUE","BLV","BLW","BLX","BMA","BMCH","BME","BMI","BML-G","BML-H","BML-J","BML-L","BMLP","BMO","BMRA","BMRC","BMRN","BMS","BMTC","BMY","BNCL","BND","BNDC","BNDW","BNDX","BNED","BNFT","BNGO","BNGOW","BNO","BNS","BNSO","BNTC","BNY","BOCH","BOCT","BOE","BOH","BOIL","BOJA","BOKF","BOKFL","BOLD","BOM","BOMN","BOND","BOOM","BOON","BOOT","BORN","BOS","BOSC","BOSS","BOTJ","BOTZ","BOUT","BOX","BOXL","BP","BPFH","BPFHW","BPI","BPK","BPL","BPMC","BPMP","BPMX","BPOP","BPOPM","BPOPN","BPR","BPRAP","BPRN","BPT","BPTH","BPY","BQH","BR","BRAC","BRACR","BRACU","BRACW","BRC","BREW","BRF","BRFS","BRG","BRG-A","BRG-C","BRG-D","BRGL","BRID","BRK.A","BRK.B","BRKL","BRKR","BRKS","BRN","BRO","BRPA","BRPAR","BRPAU","BRPAW","BRQS","BRS","BRSS","BRT","BRX","BRY","BRZU","BSA","BSAC","BSAE","BSBE","BSBR","BSCE","BSCI","BSCJ","BSCK","BSCL","BSCM","BSCN","BSCO","BSCP","BSCQ","BSCR","BSCS","BSD","BSDE","BSE","BSET","BSGM","BSIG","BSJI","BSJJ","BSJK","BSJL","BSJM","BSJN","BSJO","BSJP","BSJQ","BSL","BSM","BSMX","BSQR","BSRR","BST","BSTC","BSTI","BSV","BSVN","BSX","BT","BTA","BTAI","BTAL","BTE","BTEC","BTG","BTI","BTN","BTO","BTT","BTU","BTX","BTZ","BUD","BUI","BURG","BURL","BUSE","BUY","BUYN","BUZ","BV","BVAL","BVN","BVNSC","BVSN","BVX","BVXV","BW","BWA","BWB","BWEN","BWFG","BWG","BWL.A","BWX","BWXT","BWZ","BX","BXC","BXE","BXG","BXMT","BXMX","BXP","BXP-B","BXS","BY","BYD","BYFC","BYLD","BYM","BYSI","BZF","BZH","BZM","BZQ","BZUN","C","C+A","C-J","C-K","C-L","C-N","C-S","CAAP","CAAS","CABO","CAC","CACC","CACG","CACI","CADC","CADE","CAE","CAF","CAG","CAH","CAI","CAI-A","CAI-B","CAJ","CAKE","CAL","CALA","CALF","CALL","CALM","CALX","CAMP","CAMT","CANE","CANF","CANG","CAPE","CAPL","CAPR","CAR","CARA","CARB","CARG","CARO","CARS","CART","CARV","CARZ","CASA","CASH","CASI","CASM","CASS","CASY","CAT","CATB","CATC","CATH","CATM","CATO","CATS","CATY","CATYW","CAW","CB","CBA","CBAK","CBAN","CBAY","CBB","CBB-B","CBD","CBFV","CBH","CBIO","CBK","CBL","CBL-D","CBL-E","CBLI","CBLK","CBM","CBMB","CBMG","CBND","CBNK","CBOE","CBON","CBPO","CBPX","CBRE","CBRL","CBS","CBS.A","CBSH","CBSHP","CBT","CBTX","CBU","CBZ","CC","CCA","CCB","CCBG","CCC","CCC+","CCC=","CCCL","CCD","CCEP","CCF","CCH=","CCI","CCI-A","CCIH","CCJ","CCK","CCL","CCLP","CCM","CCMP","CCNE","CCNI","CCO","CCOI","CCOR","CCR","CCRC","CCRN","CCS","CCT","CCU","CCXI","CCZ","CDAY","CDC","CDE","CDEV","CDK","CDL","CDLX","CDMO","CDMOP","CDNA","CDNS","CDOR","CDR","CDR-B","CDR-C","CDTI","CDTX","CDW","CDXC","CDXS","CDZI","CE","CEA","CECE","CECO","CEE","CEF","CEFL","CEFS","CEI","CEIX","CEL","CELC","CELG","CELGZ","CELH","CELP","CEM","CEMB","CEMI","CEN","CENT","CENTA","CENX","CEO","CEPU","CEQP","CERC","CERN","CERS","CET","CETV","CETX","CETXP","CETXW","CEV","CEVA","CEW","CEY","CEZ","CF","CFA","CFBI","CFBK","CFFI","CFFN","CFG","CFMS","CFO","CFR","CFR-A","CFRX","CFX","CG","CGA","CGBD","CGC","CGEN","CGIX","CGNX","CGO","CGVIC","CGW","CHA","CHAD","CHAP","CHAU","CHCI","CHCO","CHCT","CHD","CHDN","CHE","CHEF","CHEK","CHEKZ","CHEP","CHFC","CHFS","CHGG","CHGX","CHH","CHI","CHIE","CHII","CHIM","CHIQ","CHIX","CHK","CHK-D","CHKE","CHKP","CHKR","CHL","CHMA","CHMG","CHMI","CHMI-A","CHN","CHNA","CHNR","CHRA","CHRS","CHRW","CHS","CHSCL","CHSCM","CHSCN","CHSCO","CHSCP","CHSP","CHT","CHTR","CHU","CHUY","CHW","CHY","CI","CIA","CIB","CIBR","CIC","CIC=","CID","CIDM","CIEN","CIF","CIFS","CIG","CIG.C","CIGI","CII","CIK","CIL","CIM","CIM-A","CIM-B","CIM-C","CINF","CINR","CIO","CIO-A","CIR","CISN","CIT","CIVB","CIVBP","CIVEC","CIVI","CIX","CIZ","CIZN","CJ","CJJD","CJNK","CKH","CKPT","CKX","CL","CLAR","CLB","CLBK","CLBS","CLCT","CLD","CLDC","CLDR","CLDT","CLDX","CLF","CLFD","CLGN","CLGX","CLH","CLI","CLIR","CLIRW","CLIX","CLLS","CLM","CLMT","CLNC","CLNE","CLNY","CLNY-B","CLNY-E","CLNY-G","CLNY-H","CLNY-I","CLNY-J","CLPR","CLPS","CLR","CLRB","CLRBW","CLRBZ","CLRG","CLRO","CLS","CLSD","CLSN","CLTL","CLUB","CLVS","CLW","CLWT","CLX","CLXT","CM","CMA","CMA+","CMBS","CMC","CMCL","CMCM","CMCO","CMCSA","CMCT","CMCTP","CMD","CMDY","CME","CMF","CMFN","CMG","CMI","CMLS","CMO","CMO-E","CMP","CMPR","CMRE","CMRE-B","CMRE-C","CMRE-D","CMRE-E","CMRX","CMS","CMS-B","CMSA","CMSS","CMSSR","CMSSU","CMSSW","CMT","CMTA","CMTL","CMU","CN","CNA","CNAC","CNACR","CNACU","CNACW","CNAT","CNBKA","CNC","CNCE","CNCR","CNDT","CNET","CNF","CNFR","CNHI","CNHX","CNI","CNK","CNMD","CNNE","CNO","CNOB","CNP","CNP-B","CNQ","CNS","CNSL","CNST","CNTF","CNTY","CNX","CNXM","CNXN","CNXT","CNY","CNYA","CO","COCP","CODA","CODI","CODI-A","CODI-B","CODX","COE","COF","COF+","COF-C","COF-D","COF-F","COF-G","COF-H","COF-P","COG","COHN","COHR","COHU","COKE","COL","COLB","COLD","COLL","COLM","COM","COMB","COMG","COMM","COMT","CONE","CONN","COO","COOP","COP","COPX","COR","CORE","CORI","CORN","CORP","CORR","CORR-A","CORT","CORV","COST","COT","COTY","COUP","COWB","COWN","COWNZ","COWZ","CP","CPA","CPAC","CPAH","CPB","CPE","CPE-A","CPER","CPF","CPG","CPHC","CPHI","CPI","CPIX","CPK","CPL","CPLG","CPLP","CPRT","CPRX","CPS","CPSH","CPSI","CPSS","CPST","CPT","CPTA","CPTAG","CPTAL","CQP","CQQQ","CR","CRAI","CRAK","CRAY","CRBN","CRBP","CRC","CRCM","CRD.A","CRD.B","CREE","CREG","CRESY","CRF","CRH","CRHM","CRI","CRIS","CRK","CRL","CRM","CRMD","CRMT","CRNT","CRNX","CROC","CRON","CROP","CROX","CRR","CRS","CRSP","CRT","CRTO","CRUS","CRUSC","CRVL","CRVS","CRWS","CRY","CRZO","CS","CSA","CSB","CSBR","CSCO","CSD","CSF","CSFL","CSGP","CSGS","CSII","CSIQ","CSL","CSLT","CSM","CSML","CSOD","CSPI","CSQ","CSS","CSSE","CSSEP","CSTE","CSTM","CSTR","CSU","CSV","CSWC","CSWCL","CSWI","CSX","CTAC","CTACU","CTACW","CTAS","CTB","CTBB","CTBI","CTDD","CTEK","CTG","CTHR","CTIB","CTIC","CTK","CTL","CTLT","CTMX","CTO","CTR","CTRC","CTRE","CTRL","CTRN","CTRP","CTRV","CTS","CTSH","CTSO","CTT","CTWS","CTXR","CTXRW","CTXS","CTY","CUB","CUBA","CUBE","CUBI","CUBI-C","CUBI-D","CUBI-E","CUBI-F","CUE","CUI","CUK","CULP","CUMB","CUO","CUR","CURE","CURO","CUT","CUTR","CUZ","CVA","CVBF","CVCO","CVCY","CVE","CVEO","CVGI","CVGW","CVI","CVIA","CVLT","CVLY","CVM","CVNA","CVON","CVR","CVRR","CVRS","CVS","CVTI","CVU","CVV","CVX","CVY","CW","CWAI","CWB","CWBC","CWBR","CWCO","CWEB","CWEN","CWEN.A","CWH","CWI","CWK","CWS","CWST","CWT","CX","CXDC","CXE","CXH","CXO","CXP","CXSE","CXW","CY","CYAD","CYAN","CYB","CYBE","CYBR","CYCC","CYCCP","CYD","CYH","CYHHZ","CYOU","CYRN","CYRX","CYTK","CYTR","CYTX","CYTXW","CYTXZ","CZA","CZFC","CZNC","CZR","CZWI","CZZ","D","DAC","DAG","DAIO","DAKT","DAL","DALI","DALT","DAN","DAR","DARE","DATA","DAUD","DAVA","DAVE","DAX","DB","DBA","DBAP","DBAW","DBB","DBC","DBD","DBE","DBEF","DBEM","DBEU","DBEZ","DBGR","DBJP","DBKO","DBL","DBLV","DBO","DBP","DBS","DBUK","DBV","DBVT","DBX","DCAR","DCF","DCHF","DCI","DCIX","DCO","DCOM","DCP","DCP-B","DCP-C","DCPH","DCUD","DD-A","DD-B","DDBI","DDD","DDE","DDEZ","DDF","DDG","DDIV","DDJP","DDLS","DDM","DDMX","DDMXU","DDMXW","DDP","DDS","DDT","DDWM","DE","DEA","DECK","DEEF","DEF","DEFA","DEI","DELT","DEM","DEMG","DENN","DEO","DERM","DES","DESC","DESP","DEST","DEUR","DEUS","DEW","DEWJ","DEX","DEZU","DF","DFBH","DFBHU","DFBHW","DFE","DFEN","DFFN","DFIN","DFJ","DFND","DFNL","DFP","DFRG","DFS","DFVL","DFVS","DG","DGAZ","DGBP","DGICA","DGICB","DGII","DGL","DGLD","DGLY","DGP","DGRE","DGRO","DGRS","DGRW","DGS","DGSE","DGT","DGX","DGZ","DHDG","DHF","DHI","DHIL","DHR","DHS","DHT","DHVW","DHX","DHXM","DHY","DIA","DIAL","DIAX","DIG","DIM","DIN","DINT","DIOD","DIS","DISCA","DISCB","DISCK","DISH","DIT","DIV","DIVA","DIVB","DIVC","DIVO","DIVY","DJCI","DJCO","DJD","DJP","DJPY","DK","DKL","DKS","DKT","DL","DLA","DLB","DLBR","DLBS","DLHC","DLN","DLNG","DLNG-A","DLPH","DLPN","DLPNW","DLR","DLR-C","DLR-G","DLR-H","DLR-I","DLR-J","DLS","DLTH","DLTR","DLX","DM","DMB","DMF","DMLP","DMO","DMPI","DMRC","DMRI","DMRL","DMRM","DMRS","DNB","DNBF","DNI","DNJR","DNKN","DNL","DNLI","DNN","DNOW","DNP","DNR","DO","DOC","DOCU","DOD","DOG","DOGS","DOGZ","DOL","DOMO","DON","DOO","DOOO","DOOR","DORM","DOTA","DOTAR","DOTAU","DOTAW","DOV","DOVA","DOX","DPG","DPK","DPLO","DPST","DPW","DPZ","DQ","DQML","DRAD","DRD","DRE","DRH","DRI","DRIO","DRIP","DRIV","DRN","DRNA","DRQ","DRR","DRRX","DRSK","DRUA","DRV","DRW","DRYS","DS","DS-B","DS-C","DS-D","DSE","DSGX","DSI","DSKE","DSL","DSLV","DSM","DSPG","DSS","DSTL","DSU","DSUM","DSW","DSWL","DSX","DSX-B","DTD","DTE","DTEA","DTEC","DTF","DTH","DTJ","DTN","DTO","DTQ","DTRM","DTUL","DTUS","DTV","DTW","DTY","DTYL","DTYS","DUC","DUG","DUK","DUKB","DUKH","DURA","DUSA","DUSL","DUST","DVA","DVAX","DVCR","DVD","DVEM","DVHL","DVLU","DVMT","DVN","DVOL","DVP","DVY","DVYA","DVYE","DVYL","DWAQ","DWAS","DWAT","DWCH","DWCR","DWDP","DWFI","DWIN","DWLD","DWLV","DWM","DWMC","DWMF","DWPP","DWSH","DWSN","DWT","DWTR","DWX","DX","DX-A","DX-B","DXB","DXC","DXCM","DXD","DXF","DXGE","DXJ","DXJF","DXJS","DXLG","DXPE","DXR","DXYN","DY","DYB","DYLS","DYNC","DYNT","DYSL","DYY","DZK","DZSI","DZZ","E","EA","EAB","EAD","EAE","EAF","EAGG","EAGL","EAGLU","EAGLW","EAI","EARN","EARS","EASG","EASI","EAST","EAT","EB","EBAY","EBAYL","EBF","EBIX","EBMT","EBND","EBR","EBR.B","EBS","EBSB","EBTC","EC","ECA","ECC","ECCA","ECCB","ECCX","ECCY","ECF","ECF-A","ECH","ECHO","ECL","ECNS","ECOL","ECOM","ECON","ECOR","ECPG","ECR","ECT","ECYT","ED","EDAP","EDBI","EDC","EDD","EDEN","EDF","EDGE","EDI","EDIT","EDIV","EDN","EDNT","EDOG","EDOM","EDOW","EDRY","EDTX","EDTXU","EDTXW","EDU","EDUC","EDV","EDZ","EE","EEA","EEB","EEFT","EEH","EEI","EELV","EEM","EEMA","EEMD","EEMO","EEMS","EEMV","EEMX","EEP","EEQ","EES","EET","EEV","EEX","EFA","EFAD","EFAS","EFAV","EFAX","EFBI","EFC","EFF","EFFE","EFG","EFII","EFL","EFNL","EFO","EFOI","EFR","EFSC","EFT","EFU","EFV","EFX","EFZ","EGAN","EGBN","EGF","EGHT","EGI","EGIF","EGL","EGLE","EGN","EGO","EGOV","EGP","EGPT","EGRX","EGY","EHC","EHI","EHIC","EHT","EHTH","EIA","EIDO","EIDX","EIG","EIGI","EIGR","EIM","EIO","EIP","EIRL","EIS","EIV","EIX","EKAR","EKSO","EL","ELAN","ELC","ELD","ELF","ELGX","ELJ","ELLI","ELLO","ELMD","ELOX","ELP","ELS","ELSE","ELTK","ELU","ELVT","ELY","EMAG","EMAN","EMB","EMBH","EMCB","EMCF","EMCG","EMCI","EMD","EMDV","EME","EMEM","EMES","EMF","EMFM","EMGF","EMHY","EMI","EMIF","EMIH","EMITF","EMJ","EMKR","EML","EMLC","EMLP","EMMF","EMMS","EMN","EMO","EMP","EMQQ","EMR","EMSH","EMTL","EMTY","EMX","EMXC","ENB","ENBA","ENBL","ENDP","ENFC","ENFR","ENG","ENIA","ENIC","ENJ","ENLC","ENLK","ENO","ENOR","ENPH","ENR","ENS","ENSG","ENSV","ENT","ENTA","ENTG","ENTR","ENTX","ENTXW","ENV","ENVA","ENX","ENY","ENZ","ENZL","EOCC","EOD","EOG","EOI","EOLS","EOS","EOT","EP-C","EPAM","EPAY","EPC","EPD","EPE","EPHE","EPI","EPIX","EPM","EPOL","EPP","EPR","EPR-C","EPR-E","EPR-G","EPRF","EPRT","EPS","EPU","EPV","EPZM","EQAL","EQBK","EQC","EQC-D","EQGP","EQH","EQIX","EQL","EQLT","EQM","EQNR","EQR","EQRR","EQS","EQT","EQT#","EQWL","EQWM","EQWS","ERA","ERC","ERF","ERH","ERI","ERIC","ERIE","ERII","ERJ","ERM","EROS","ERUS","ERX","ERY","ERYP","ES","ESBA","ESBK","ESCA","ESE","ESEA","ESES","ESG","ESGD","ESGE","ESGF","ESGG","ESGL","ESGN","ESGR","ESGRP","ESGS","ESGU","ESGV","ESGW","ESIO","ESL","ESLT","ESML","ESNC","ESND","ESNT","ESP","ESPO","ESPR","ESQ","ESRT","ESRX","ESS","ESSA","ESTA","ESTC","ESTE","ESTR","ESTRW","ESV","ESXB","ET","ETB","ETFC","ETG","ETH","ETHO","ETJ","ETM","ETN","ETO","ETP-C","ETP-D","ETR","ETRN#","ETSY","ETTX","ETV","ETW","ETX","ETY","EUDG","EUDV","EUFL","EUFN","EUFX","EUM","EUMV","EUO","EURL","EURN","EURZ","EUSA","EUSC","EUXL","EV","EVA","EVBG","EVBN","EVC","EVER","EVF","EVFM","EVFTC","EVG","EVGBC","EVGN","EVH","EVI","EVIX","EVJ","EVK","EVLMC","EVLO","EVLV","EVM","EVN","EVO","EVOK","EVOL","EVOP","EVP","EVR","EVRG","EVRI","EVSTC","EVT","EVTC","EVV","EVX","EVY","EW","EWA","EWBC","EWC","EWCO","EWD","EWEM","EWG","EWGS","EWH","EWI","EWJ","EWK","EWL","EWM","EWMC","EWN","EWO","EWP","EWQ","EWRE","EWS","EWSC","EWT","EWU","EWUS","EWV","EWW","EWX","EWY","EWZ","EWZS","EXAS","EXC","EXD","EXEL","EXFO","EXG","EXI","EXIV","EXK","EXLS","EXP","EXPD","EXPE","EXPI","EXPO","EXPR","EXR","EXT","EXTN","EXTR","EYE","EYEG","EYEN","EYES","EYESW","EYLD","EYPT","EZA","EZJ","EZM","EZPW","EZT","EZU","F","FAAR","FAB","FAD","FAF","FALN","FAM","FAMI","FAN","FANG","FANH","FANZ","FARM","FARO","FAS","FAST","FAT","FATE","FAUS","FAX","FAZ","FB","FBC","FBGX","FBHS","FBIO","FBIOP","FBIZ","FBK","FBM","FBMS","FBNC","FBND","FBP","FBR","FBSS","FBT","FBZ","FC","FCA","FCAL","FCAN","FCAP","FCAU","FCB","FCBC","FCBP","FCCO","FCCY","FCE.A","FCEF","FCEL","FCF","FCFS","FCG","FCN","FCNCA","FCO","FCOM","FCOR","FCPT","FCSC","FCT","FCTR","FCVT","FCX","FDBC","FDC","FDD","FDEF","FDEU","FDHY","FDIS","FDIV","FDL","FDLO","FDM","FDMO","FDN","FDNI","FDP","FDRR","FDS","FDT","FDTS","FDUS","FDUSL","FDVV","FDX","FE","FEDU","FEI","FEIM","FELE","FELP","FEM","FEMB","FEMS","FEN","FENC","FENG","FENY","FEO","FEP","FET","FEU","FEUL","FEUZ","FEX","FEYE","FEZ","FF","FFA","FFBC","FFBCW","FFBW","FFC","FFEU","FFG","FFHG","FFHL","FFIC","FFIN","FFIU","FFIV","FFNW","FFR","FFSG","FFTG","FFTI","FFTY","FFWM","FG","FGB","FGBI","FGD","FGEN","FGM","FGP","FHB","FHK","FHLC","FHN","FHN-A","FI","FIBK","FIBR","FICO","FID","FIDI","FIDU","FIEE","FIEG","FIF","FIHD","FII","FILL","FINU","FINX","FINZ","FIS","FISI","FISK","FISV","FIT","FITB","FITBI","FIV","FIVA","FIVE","FIVN","FIW","FIX","FIXD","FIXX","FIYY","FIZZ","FJP","FKO","FKU","FL","FLAG","FLAT","FLAU","FLAX","FLBL","FLBR","FLC","FLCA","FLCH","FLCO","FLDM","FLDR","FLEE","FLEH","FLEU","FLEX","FLFR","FLGB","FLGE","FLGR","FLGT","FLHK","FLHY","FLIA","FLIC","FLIN","FLIO","FLIR","FLIY","FLJH","FLJP","FLKR","FLKS","FLL","FLLA","FLLV","FLM","FLMB","FLMI","FLMN","FLMX","FLN","FLNT","FLO","FLOT","FLOW","FLQD","FLQE","FLQG","FLQH","FLQL","FLQM","FLQS","FLR","FLRN","FLRT","FLRU","FLS","FLSA","FLSW","FLT","FLTB","FLTR","FLTW","FLWS","FLXN","FLXS","FLY","FLZA","FM","FMAO","FMAT","FMB","FMBH","FMBI","FMC","FMCI","FMCIU","FMCIW","FMDG","FMF","FMHI","FMK","FMN","FMNB","FMO","FMS","FMX","FMY","FN","FNB","FNB-E","FNCB","FNCL","FND","FNDA","FNDB","FNDC","FNDE","FNDF","FNDX","FNF","FNG","FNGD","FNGO","FNGU","FNGZ","FNHC","FNI","FNJN","FNK","FNKO","FNLC","FNSR","FNV","FNWB","FNX","FNY","FOANC","FOCS","FOE","FOF","FOLD","FOMX","FONE","FONR","FOR","FORD","FORK","FORM","FORR","FORTY","FOSL","FOX","FOXA","FOXF","FPA","FPAC","FPAC+","FPAC=","FPAY","FPAYW","FPE","FPEI","FPF","FPH","FPI","FPI-B","FPL","FPRX","FPX","FPXE","FPXI","FQAL","FR","FRA","FRAC","FRAK","FRAN","FRBA","FRBK","FRC","FRC-D","FRC-E*","FRC-F","FRC-G","FRC-H","FRC-I","FRD","FRED","FREL","FRGI","FRI","FRLG","FRME","FRN","FRO","FRPH","FRPT","FRSH","FRSX","FRT","FRT-C","FRTA","FSAC","FSACU","FSACW","FSB","FSBC","FSBW","FSCT","FSD","FSFG","FSI","FSIC","FSLR","FSM","FSMB","FSNN","FSP","FSS","FSTA","FSTR","FSV","FSZ","FT","FTA","FTAG","FTAI","FTC","FTCH","FTCS","FTD","FTDR","FTEC","FTEK","FTEO","FTF","FTFT","FTGC","FTHI","FTI","FTK","FTLB","FTLS","FTNT","FTNW","FTR","FTRI","FTS","FTSD","FTSI","FTSL","FTSM","FTSV","FTV","FTV-A","FTVA","FTXD","FTXG","FTXH","FTXL","FTXN","FTXO","FTXR","FUD","FUE","FUL","FULT","FUMB","FUN","FUNC","FUND","FUSB","FUT","FUTY","FUV","FV","FVAL","FVC","FVCB","FVD","FVE","FVL","FWDB","FWDD","FWDI","FWONA","FWONK","FWP","FWRD","FXA","FXB","FXC","FXCH","FXD","FXE","FXF","FXG","FXH","FXI","FXL","FXN","FXO","FXP","FXR","FXS","FXSG","FXU","FXY","FXZ","FYC","FYLD","FYT","FYX","G","GAA","GAB","GAB-D","GAB-G","GAB-H","GAB-J","GABC","GAIA","GAIN","GAINL","GAINM","GAL","GALT","GAM","GAM-B","GAMR","GARD","GARS","GASL","GASS","GASX","GATX","GAZB","GBAB","GBCI","GBDC","GBF","GBIL","GBL","GBLI","GBLIL","GBNK","GBR","GBT","GBX","GCAP","GCBC","GCC","GCE","GCI","GCO","GCOW","GCP","GCV","GCV-B","GCVRZ","GD","GDDY","GDEN","GDI","GDL","GDL-C","GDO","GDOT","GDP","GDS","GDV","GDV-A","GDV-D","GDV-G","GDVD","GDX","GDXJ","GDXS","GDXX","GE","GEC","GECC","GECCL","GECCM","GEF","GEF.B","GEL","GEM","GEMP","GEN","GENC","GENE","GENY","GEO","GEOS","GER","GERN","GES","GEVO","GEX","GF","GFA","GFED","GFF","GFI","GFN","GFNCP","GFNSL","GFY","GG","GGAL","GGB","GGG","GGM","GGN","GGN-B","GGO","GGO-A","GGT","GGT-B","GGT-E","GGZ","GGZ-A","GH","GHC","GHDX","GHG","GHII","GHL","GHM","GHY","GHYB","GHYG","GIB","GIFI","GIG","GIG=","GIGB","GIGM","GIG^","GII","GIII","GIL","GILD","GILT","GIM","GIS","GJH","GJO","GJP","GJR","GJS","GJT","GJV","GKOS","GLAC","GLACR","GLACU","GLACW","GLAD","GLADN","GLBS","GLBZ","GLD","GLDD","GLDI","GLDM","GLDW","GLF","GLG","GLIBA","GLIBP","GLL","GLMD","GLNG","GLO","GLOB","GLOG","GLOG-A","GLOP","GLOP-A","GLOP-B","GLOW","GLP","GLP-A","GLPG","GLPI","GLQ","GLRE","GLT","GLTR","GLU","GLU-A","GLUU","GLV","GLW","GLYC","GM","GM+B","GMDA","GME","GMED","GMF","GMFL","GMLP","GMLPP","GMO","GMOM","GMRE","GMRE-A","GMS","GMTA","GMZ","GNAF","GNBC","GNC","GNCA","GNE","GNE-A","GNK","GNL","GNL-A","GNMA","GNMK","GNMX","GNPX","GNR","GNRC","GNRX","GNT","GNT-A","GNTX","GNTY","GNUS","GNW","GOAT","GOAU","GOEX","GOF","GOGL","GOGO","GOL","GOLD","GOLF","GOOD","GOODM","GOODO","GOODP","GOOG","GOOGL","GOOS","GORO","GOV","GOVNI","GOVT","GPAQ","GPAQU","GPAQW","GPC","GPI","GPIC","GPJA","GPK","GPL","GPM","GPMT","GPN","GPOR","GPP","GPRE","GPRK","GPRO","GPS","GPX","GQRE","GRA","GRAF","GRAF+","GRAF=","GRAM","GRBIC","GRBK","GRC","GREK","GRES","GRF","GRFS","GRI","GRID","GRIF","GRIN","GRMN","GRMY","GRNB","GRNQ","GROW","GRP=","GRPN","GRSH","GRSHU","GRSHW","GRTS","GRU","GRUB","GRVY","GRX","GRX-A","GRX-B","GS","GS-A","GS-B","GS-C","GS-D","GS-J","GS-K","GS-N","GSAH","GSAH+","GSAH=","GSAT","GSB","GSBC","GSBD","GSC","GSD","GSEU","GSEW","GSG","GSH","GSHD","GSIE","GSIT","GSJY","GSK","GSKY","GSL","GSL-B","GSLC","GSM","GSP","GSS","GSSC","GSUM","GSV","GSVC","GSY","GT","GTE","GTES","GTHX","GTIM","GTIP","GTLS","GTN","GTN.A","GTO","GTS","GTT","GTX","GTXI","GTY","GTYH","GTYHU","GTYHW","GUDB","GULF","GUNR","GURE","GURU","GUSH","GUT","GUT-A","GUT-C","GV","GVA","GVAL","GVI","GVIP","GVP","GWB","GWGH","GWPH","GWR","GWRE","GWRS","GWW","GWX","GXC","GXF","GXG","GYB","GYC","GYLD","GYRO","GZT","H","HA","HABT","HACK","HAE","HAFC","HAIN","HAIR","HAL","HALL","HALO","HAO","HAP","HAS","HASI","HAUD","HAWX","HAYN","HBAN","HBANN","HBANO","HBB","HBCP","HBI","HBIO","HBK","HBM","HBMD","HBNC","HBP","HCA","HCAP","HCAPZ","HCC","HCCH","HCCHR","HCCHU","HCCHW","HCCI","HCFT","HCFT-A","HCHC","HCI","HCKT","HCLP","HCM","HCP","HCSG","HCXZ","HD","HDAW","HDB","HDEF","HDG","HDGE","HDLV","HDMV","HDP","HDS","HDSN","HDV","HE","HE-U","HEAR","HEB","HEBT","HECO","HEDJ","HEEM","HEES","HEFA","HEI","HEI.A","HELE","HEP","HEQ","HES","HES-A","HESM","HEWC","HEWG","HEWI","HEWJ","HEWL","HEWP","HEWU","HEWW","HEWY","HEZU","HF","HFBC","HFBL","HFC","HFFG","HFGIC","HFRO","HFWA","HFXE","HFXI","HFXJ","HGH","HGI","HGSD","HGSH","HGV","HHC","HHS","HI","HIBB","HIE","HIFR","HIFS","HIG","HIG+","HIHO","HII","HIIQ","HIL","HILO","HIMX","HIO","HIPS","HIVE","HIW","HIX","HJLI","HJLIW","HJPX","HJV","HK","HK+","HL","HL-B","HLF","HLG","HLI","HLIT","HLNE","HLT","HLTH","HLX","HMC","HMG","HMHC","HMI","HMLP","HMLP-A","HMN","HMNF","HMNY","HMOP","HMST","HMSY","HMTA","HMTV","HMY","HNDL","HNGR","HNI","HNNA","HNP","HNRG","HNW","HOFT","HOG","HOLD","HOLI","HOLX","HOMB","HOME","HOML","HON","HONE","HONR","HOPE","HOS","HOV","HOVNP","HP","HPE","HPF","HPI","HPJ","HPP","HPQ","HPS","HPT","HQCL","HQH","HQL","HQY","HR","HRB","HRC","HRI","HRL","HRS","HRTG","HRTX","HRZN","HSBC","HSBC-A","HSC","HSCZ","HSDT","HSGX","HSIC","HSII","HSKA","HSON","HSPX","HSRT","HST","HSTM","HSY","HT","HT-C","HT-D","HT-E","HTA","HTAB","HTBI","HTBK","HTBX","HTD","HTFA","HTGC","HTGM","HTGX","HTH","HTHT","HTLD","HTLF","HTRB","HTUS","HTY","HTZ","HUBB","HUBG","HUBS","HUD","HUM","HUN","HUNT","HUNTU","HUNTW","HURC","HURN","HUSA","HUSE","HUSV","HUYA","HVBC","HVT","HVT.A","HWBK","HWC","HWCC","HWKN","HX","HXL","HY","HYAC","HYACU","HYACW","HYB","HYD","HYDB","HYDD","HYDW","HYEM","HYG","HYGH","HYGS","HYGV","HYHG","HYI","HYIH","HYLB","HYLD","HYLS","HYLV","HYMB","HYND","HYRE","HYS","HYT","HYUP","HYXE","HYXU","HYZD","HZN","HZNP","HZO","I","IAC","IAE","IAF","IAG","IAGG","IAI","IAK","IAM","IAMXR","IAMXW","IART","IAT","IAU","IAUF","IBA","IBB","IBCD","IBCE","IBCP","IBD","IBDC","IBDD","IBDH","IBDK","IBDL","IBDM","IBDN","IBDO","IBDP","IBDQ","IBDR","IBDS","IBDT","IBIO","IBKC","IBKCO","IBKCP","IBKR","IBM","IBMH","IBMI","IBMJ","IBMK","IBML","IBMM","IBN","IBND","IBOC","IBP","IBTX","IBUY","ICAD","ICAN","ICBK","ICCC","ICCH","ICD","ICE","ICF","ICFI","ICHR","ICL","ICLK","ICLN","ICLR","ICOL","ICON","ICOW","ICPT","ICSH","ICUI","ICVT","IDA","IDCC","IDE","IDEV","IDHD","IDHQ","IDIV","IDLB","IDLV","IDMO","IDN","IDOG","IDRA","IDSA","IDSY","IDT","IDTI","IDU","IDV","IDX","IDXG","IDXX","IEA","IEAWW","IEC","IECS","IEDI","IEF","IEFA","IEFN","IEHS","IEI","IEIH","IEMD","IEME","IEMG","IEMV","IEO","IEP","IESC","IETC","IEUR","IEUS","IEV","IEX","IEZ","IFEU","IFF","IFFT","IFGL","IFIX","IFLY","IFMK","IFN","IFRA","IFRX","IFV","IG","IGA","IGBH","IGD","IGE","IGEB","IGF","IGHG","IGI","IGIB","IGIH","IGLB","IGLD","IGM","IGN","IGOV","IGR","IGRO","IGSB","IGT","IGV","IGVT","IHC","IHD","IHDG","IHE","IHF","IHG","IHI","IHIT","IHT","IHTA","IHY","IHYD","IHYV","IID","IIF","IIGD","IIGV","III","IIIN","IIIV","IIJI","IIM","IIN","IIPR","IIPR-A","IIVI","IJH","IJJ","IJK","IJR","IJS","IJT","IKNX","ILF","ILMN","ILPT","ILTB","IMAX","IMDZ","IMFC","IMFP","IMGN","IMH","IMI","IMKTA","IMLP","IMMP","IMMR","IMMU","IMMY","IMO","IMOM","IMOS","IMPV","IMRN","IMRNW","IMTB","IMTE","IMTM","IMV","IMXI","INAP","INB","INBK","INBKL","INCO","INCY","INDA","INDB","INDL","INDS","INDU","INDUU","INDUW","INDY","INF","INFI","INFN","INFO","INFR","INFU","INFY","ING","INGN","INGR","INKM","INN","INN-D","INN-E","INNT","INO","INOD","INOV","INPX","INR","INS","INSE","INSG","INSI","INSM","INSP","INST","INSW","INSY","INT","INTC","INTF","INTG","INTL","INTT","INTU","INTX","INUV","INVA","INVE","INVH","INWK","INXN","INXX","IO","IONS","IOO","IOR","IOSP","IOTS","IOVA","IP","IPAC","IPAR","IPAY","IPB","IPCI","IPDN","IPE","IPFF","IPG","IPGP","IPHI","IPHS","IPI","IPIC","IPKW","IPL-D","IPO","IPOA","IPOA=","IPOS","IPWR","IQ","IQDE","IQDF","IQDG","IQDY","IQI","IQLT","IQV","IR","IRBO","IRBT","IRCP","IRDM","IRDMB","IRET","IRET-C","IRIX","IRL","IRM","IRMD","IROQ","IRR","IRS","IRT","IRTC","IRWD","ISBC","ISCA","ISCF","ISD","ISDR","ISDS","ISDX","ISEM","ISF","ISG","ISHG","ISIG","ISMD","ISNS","ISR","ISRA","ISRG","ISRL","ISSC","ISTB","ISTR","ISZE","IT","ITA","ITB","ITCB","ITCI","ITE","ITEQ","ITG","ITGR","ITI","ITIC","ITM","ITOT","ITP","ITRI","ITRM","ITRN","ITT","ITUB","ITW","IUS","IUSB","IUSG","IUSS","IUSV","IVAC","IVAL","IVC","IVE","IVENC","IVFGC","IVFVC","IVH","IVLU","IVOG","IVOO","IVOV","IVR","IVR-A","IVR-B","IVR-C","IVV","IVW","IVZ","IWB","IWC","IWD","IWF","IWL","IWM","IWN","IWO","IWP","IWR","IWS","IWV","IWX","IWY","IX","IXC","IXG","IXJ","IXN","IXP","IXUS","IYC","IYE","IYF","IYG","IYH","IYJ","IYK","IYLD","IYM","IYR","IYT","IYW","IYY","IYZ","IZEA","IZRL","JACK","JAG","JAGX","JAKK","JASN","JAX","JAZZ","JBGS","JBHT","JBK","JBL","JBLU","JBN","JBR","JBSS","JBT","JCAP","JCAP-B","JCE","JCI","JCO","JCOM","JCP","JCS","JCTCF","JD","JDD","JDIV","JDST","JE","JE-A","JEC","JEF","JELD","JEMD","JEQ","JETS","JFR","JG","JGH","JHA","JHB","JHD","JHDG","JHEM","JHG","JHI","JHMA","JHMC","JHMD","JHME","JHMF","JHMH","JHMI","JHML","JHMM","JHMS","JHMT","JHMU","JHS","JHSC","JHX","JHY","JILL","JJAB","JJCB","JJEB","JJGB","JJMB","JJPB","JJSB","JJSF","JJTB","JJUB","JKD","JKE","JKF","JKG","JKH","JKHY","JKI","JKJ","JKK","JKL","JKS","JLL","JLS","JMBS","JMEI","JMF","JMIN","JMLP","JMM","JMOM","JMP","JMPB","JMPD","JMST","JMT","JMU","JMUB","JNCE","JNJ","JNK","JNPR","JNUG","JOB","JOBS","JOE","JOF","JONE","JOUT","JP","JPBI","JPC","JPED","JPEH","JPEM","JPEU","JPGB","JPGE","JPHF","JPHY","JPI","JPIH","JPIN","JPLS","JPM","JPM-A","JPM-B","JPM-D","JPM-E","JPM-F","JPM-G","JPM-H","JPMB","JPME","JPMF","JPMV","JPN","JPNL","JPS","JPSE","JPST","JPT","JPUS","JPXN","JQC","JQUA","JRI","JRJC","JRO","JRS","JRSH","JRVR","JSD","JSM","JSMD","JSML","JSYN","JSYNR","JSYNU","JT","JTA","JTD","JTPY","JUST","JVA","JVAL","JW.A","JW.B","JWN","JXI","JYNT","K","KAAC","KAACU","KAACW","KAI","KALA","KALL","KALU","KALV","KAMN","KANG","KAR","KARS","KB","KBA","KBAL","KBE","KBH","KBLM","KBLMR","KBLMU","KBLMW","KBR","KBSF","KBWB","KBWD","KBWP","KBWR","KBWY","KCAP","KCAPL","KCCB","KCE","KCNY","KDMN","KDP","KE","KEG","KELYA","KELYB","KEM","KEMQ","KEN","KEP","KEQU","KERX","KEX","KEY","KEY-I","KEY-J","KEYS","KEYW","KF","KFFB","KFRC","KFS","KFY","KFYP","KGC","KGJI","KGRN","KHC","KIDS","KIE","KIM","KIM-I","KIM-J","KIM-K","KIM-L","KIM-M","KIN","KINS","KIO","KIQ","KIRK","KKR","KKR-A","KKR-B","KL","KLAC","KLDW","KLIC","KMB","KMDA","KMED","KMF","KMG","KMI","KMM","KMPA","KMPH","KMPR","KMT","KMX","KN","KNDI","KNG","KNL","KNOP","KNOW","KNSA","KNSL","KNX","KO","KOD","KODK","KOF","KOIN","KOL","KOLD","KOMP","KONA","KOOL","KOP","KOPN","KORP","KORS","KORU","KOS","KOSS","KPFS","KPTI","KR","KRA","KRC","KRE","KREF","KRG","KRMA","KRNT","KRNY","KRO","KRP","KRYS","KSA","KSM","KSS","KST","KSU","KT","KTCC","KTF","KTH","KTN","KTOS","KTOV","KTP","KTWO","KURA","KURE","KVHI","KW","KWEB","KWR","KXI","KYN","KYN-F","KZIA","KZR","L","LABD","LABL","LABU","LAC","LACQ","LACQU","LACQW","LAD","LADR","LAIX","LAKE","LALT","LAMR","LANC","LAND","LANDP","LARK","LASR","LAUR","LAWS","LAZ","LAZY","LB","LBAI","LBC","LBDC","LBJ","LBRDA","LBRDK","LBRT","LBTYA","LBTYB","LBTYK","LBY","LC","LCA","LCAHU","LCAHW","LCI","LCII","LCNB","LCUT","LD","LDF","LDL","LDOS","LDP","LDRI","LDRS","LDUR","LE","LEA","LEAD","LEAF","LECO","LEDS","LEE","LEG","LEGR","LEJU","LEMB","LEN","LEN.B","LENS","LEO","LEU","LEVB","LEVL","LEXEA","LEXEB","LFAC","LFACU","LFACW","LFC","LFEQ","LFUS","LFVN","LGC","LGC=","LGCY","LGF.A","LGF.B","LGI","LGIH","LGL","LGLV","LGND","LH","LHC","LHC=","LHCG","LHO","LHO-I","LHO-J","LIFE","LII","LILA","LILAK","LIN","LINC","LIND","LINDW","LINK","LION","LIQT","LIT","LITB","LITE","LIVE","LIVN","LIVX","LJPC","LKFN","LKM","LKOR","LKQ","LKSD","LL","LLEX","LLIT","LLL","LLNW","LLQD","LLY","LM","LMAT","LMB","LMBS","LMFA","LMHA","LMHB","LMLP","LMNR","LMNX","LMRK","LMRKN","LMRKO","LMRKP","LMST","LMT","LN","LNC","LNC+","LND","LNDC","LNG","LNGR","LNN","LNT","LNTH","LOAC","LOACR","LOACU","LOACW","LOAN","LOB","LOCO","LODE","LOGC","LOGI","LOGM","LOMA","LONE","LOOP","LOPE","LOR","LORL","LOUP","LOV","LOVE","LOW","LOWC","LOXO","LPCN","LPG","LPI","LPL","LPLA","LPNT","LPSN","LPT","LPTH","LPTX","LPX","LQD","LQDA","LQDH","LQDI","LQDT","LRAD","LRCX","LRET","LRGE","LRGF","LRN","LSAF","LSBK","LSCC","LSI","LSST","LSTR","LSXMA","LSXMB","LSXMK","LTBR","LTC","LTHM","LTL","LTM","LTN","LTN=","LTN^","LTPZ","LTRPA","LTRPB","LTRX","LTS","LTS-A","LTSL","LTXB","LUB","LULU","LUNA","LUV","LVHB","LVHD","LVHE","LVHI","LVIN","LVL","LVS","LVUS","LW","LWAY","LX","LXFR","LXFT","LXP","LXP-C","LXRX","LXU","LYB","LYG","LYL","LYTS","LYV","LZB","M","MA","MAA","MAA-I","MAB","MAC","MACK","MAG","MAGA","MAGS","MAIN","MAMS","MAN","MANH","MANT","MANU","MAR","MARA","MARK","MARPS","MAS","MASI","MAT","MATW","MATX","MAV","MAXR","MAYS","MB","MBB","MBCN","MBFI","MBFIO","MBG","MBI","MBII","MBIN","MBIO","MBOT","MBRX","MBSD","MBT","MBTF","MBUU","MBWM","MC","MCA","MCB","MCBC","MCC","MCD","MCEF","MCEP","MCF","MCFT","MCHI","MCHP","MCHX","MCI","MCK","MCN","MCO","MCR","MCRB","MCRI","MCRN","MCRO","MCS","MCV","MCX","MCY","MD","MDB","MDC","MDCA","MDCO","MDGL","MDGS","MDGSW","MDIV","MDLQ","MDLX","MDLY","MDLZ","MDP","MDR","MDRX","MDSO","MDT","MDU","MDWD","MDY","MDYG","MDYV","MEAR","MED","MEDP","MEET","MEI","MEIP","MELI","MELR","MEN","MEOH","MER-K","MERC","MESA","MESO","MET","MET-A","MET-E","METC","MEXX","MFA","MFA-B","MFAC","MFAC+","MFAC=","MFC","MFCB","MFD","MFDX","MFEM","MFG","MFGP","MFIN","MFINL","MFL","MFM","MFMS","MFNC","MFO","MFSF","MFT","MFUS","MFV","MG","MGA","MGC","MGEE","MGEN","MGF","MGI","MGIC","MGK","MGLN","MGM","MGNX","MGP","MGPI","MGRC","MGTA","MGTX","MGU","MGV","MGY","MGYR","MH-A","MH-C","MH-D","MHD","MHE","MHF","MHH","MHI","MHK","MHLA","MHLD","MHN","MHNC","MHO","MIC","MICR","MICT","MIDD","MIDU","MIDZ","MIE","MIK","MILN","MIME","MIN","MINC","MIND","MINDP","MINI","MINT","MITK","MITL","MITT","MITT-A","MITT-B","MIW","MIXT","MIY","MJ","MJCO","MKC","MKC.V","MKGI","MKL","MKSI","MKTX","MLAB","MLCO","MLHR","MLI","MLM","MLN","MLNT","MLNX","MLP","MLPA","MLPB","MLPC","MLPE","MLPG","MLPI","MLPO","MLPQ","MLPX","MLPY","MLPZ","MLQD","MLR","MLSS","MLTI","MLVF","MMAC","MMC","MMD","MMDM","MMDMR","MMDMU","MMDMW","MMI","MMIN","MMIT","MMLP","MMM","MMP","MMS","MMSI","MMT","MMTM","MMU","MMV","MMYT","MN","MNA","MNDO","MNE","MNGA","MNI","MNK","MNKD","MNLO","MNOV","MNP","MNR","MNR-C","MNRO","MNST","MNTA","MNTX","MO","MOAT","MOBL","MOC","MOD","MODN","MOFG","MOG.A","MOG.B","MOGLC","MOGO","MOH","MOM","MOMO","MOO","MOR","MORL","MORN","MORT","MOS","MOSC","MOSC=","MOSY","MOTI","MOTS","MOV","MOXC","MPA","MPAA","MPAC","MPACU","MPACW","MPB","MPC","MPLX","MPO","MPV","MPVD","MPW","MPWR","MPX","MQT","MQY","MRAM","MRBK","MRC","MRCC","MRCY","MRGR","MRIN","MRK","MRKR","MRLN","MRNS","MRO","MRRL","MRSN","MRT","MRTN","MRTX","MRUS","MRVL","MS","MS-A","MS-E","MS-F","MS-G","MS-I","MS-K","MSA","MSB","MSBF","MSBI","MSC","MSCI","MSD","MSEX","MSF","MSFT","MSG","MSGN","MSI","MSL","MSM","MSN","MSON","MSTR","MSUS","MSVB","MT","MTB","MTB+","MTB-C","MTBC","MTBCP","MTCH","MTD","MTDR","MTEC","MTECU","MTECW","MTEM","MTEX","MTFB","MTFBW","MTG","MTH","MTL","MTLS","MTN","MTNB","MTOR","MTP","MTR","MTRN","MTRX","MTSC","MTSI","MTSL","MTT","MTUM","MTW","MTX","MTZ","MU","MUA","MUB","MUC","MUDS","MUDSU","MUDSW","MUE","MUFG","MUH","MUI","MUJ","MUNI","MUR","MUS","MUSA","MUST","MUX","MVBF","MVC","MVCD","MVF","MVIN","MVIS","MVO","MVT","MVV","MWA","MX","MXC","MXDE","MXDU","MXE","MXF","MXI","MXIM","MXL","MXWL","MYC","MYD","MYE","MYF","MYFW","MYGN","MYI","MYJ","MYL","MYN","MYND","MYNDW","MYO","MYOK","MYOS","MYOV","MYRG","MYSZ","MYY","MZA","MZOR","MZZ","NAC","NACP","NAD","NAII","NAIL","NAK","NAKD","NAN","NANO","NANR","NAO","NAOV","NAP","NAT","NATH","NATI","NATR","NAUH","NAV","NAV-D","NAVB","NAVG","NAVI","NAZ","NBB","NBD","NBEV","NBH","NBHC","NBIX","NBL","NBLX","NBN","NBO","NBR","NBR-A","NBRV","NBTB","NBW","NBY","NC","NCA","NCB","NCBS","NCI","NCLH","NCMI","NCNA","NCOM","NCR","NCS","NCSM","NCTY","NCV","NCV-A","NCZ","NDAQ","NDLS","NDP","NDRA","NDRAW","NDSN","NE","NEA","NEAR","NEBU","NEBUU","NEBUW","NEE","NEE-I","NEE-J","NEE-K","NEE-R","NEM","NEN","NEO","NEOG","NEON","NEOS","NEP","NEPT","NERV","NES","NESR","NESRW","NETE","NETS","NEU","NEV","NEW","NEWA","NEWM","NEWR","NEWT","NEWTI","NEXA","NEXT","NFBK","NFC","NFC+","NFC=","NFEC","NFG","NFJ","NFLT","NFLX","NFO","NFRA","NFTY","NFX","NG","NGD","NGE","NGG","NGHC","NGHCN","NGHCO","NGHCP","NGL","NGL-B","NGLS-A","NGS","NGVC","NGVT","NH","NHA","NHC","NHF","NHI","NHLD","NHLDW","NHS","NHTC","NI","NIB","NICE","NICK","NID","NIE","NIHD","NIM","NINE","NIO","NIQ","NITE","NIU","NJR","NJV","NK","NKE","NKG","NKSH","NKTR","NKX","NL","NLNK","NLR","NLS","NLSN","NLY","NLY-C","NLY-D","NLY-F","NLY-G","NLY-H","NM","NM-G","NM-H","NMFC","NMI","NMIH","NMK-B","NMK-C","NML","NMM","NMR","NMRD","NMRK","NMS","NMT","NMY","NMZ","NNA","NNBR","NNC","NNDM","NNI","NNN","NNN-E","NNN-F","NNVC","NNY","NOA","NOAH","NOBL","NOC","NODK","NOG","NOK","NOM","NOMD","NORW","NOV","NOVN","NOVT","NOW","NP","NPK","NPN","NPO","NPTN","NPV","NQP","NR","NRC","NRCG","NRE","NRG","NRIM","NRK","NRO","NRP","NRT","NRZ","NS","NS-A","NS-B","NS-C","NSA","NSA-A","NSC","NSEC","NSIT","NSL","NSP","NSPR","NSPR+","NSPR+B","NSS","NSSC","NSTG","NSU","NSYS","NTAP","NTB","NTC","NTCT","NTEC","NTES","NTG","NTGN","NTGR","NTIC","NTIP","NTLA","NTN","NTNX","NTP","NTR","NTRA","NTRI","NTRP","NTRS","NTRSP","NTSX","NTWK","NTX","NTZ","NUAG","NUAN","NUBD","NUDM","NUE","NUEM","NUGT","NULG","NULV","NUM","NUMG","NUMV","NUO","NURE","NURO","NUS","NUSA","NUSC","NUV","NUVA","NUW","NVAX","NVCN","NVCR","NVDA","NVEC","NVEE","NVFY","NVG","NVGS","NVIV","NVLN","NVMI","NVMM","NVO","NVR","NVRO","NVS","NVT","NVTA","NVTR","NVUS","NWBI","NWE","NWFL","NWHM","NWL","NWLI","NWN","NWPX","NWS","NWSA","NWY","NX","NXC","NXE","NXEO","NXEOU","NXGN","NXJ","NXN","NXP","NXPI","NXQ","NXR","NXRT","NXST","NXTD","NXTM","NYCB","NYCB-A","NYCB-U","NYF","NYH","NYMT","NYMTN","NYMTO","NYMTP","NYMX","NYNY","NYT","NYV","NZF","O","OAK","OAK-A","OAK-B","OAS","OASI","OASM","OBAS","OBCI","OBE","OBLN","OBNK","OBOR","OBSV","OC","OCC","OCCI","OCFC","OCIO","OCLR","OCN","OCSI","OCSL","OCSLL","OCUL","OCX","ODC","ODFL","ODP","ODT","OEC","OEF","OESX","OEUR","OEW","OFC","OFED","OFG","OFG-A","OFG-B","OFG-D","OFIX","OFLX","OFS","OFSSL","OGCP","OGE","OGEN","OGIG","OGS","OHAI","OHGI","OHI","OHRP","OI","OIA","OIBR.C","OIH","OII","OIIM","OILB","OILD","OILK","OILU","OILX","OIS","OKDCC","OKE","OKTA","OLBK","OLD","OLED","OLEM","OLLI","OLN","OLO","OLP","OMAB","OMAD","OMAD=","OMC","OMCL","OMED","OMER","OMEX","OMF","OMFL","OMFS","OMI","OMN","OMOM","OMP","ON","ONB","ONCE","ONCS","ONCY","ONDK","ONE","ONEO","ONEQ","ONEV","ONEY","ONLN","ONS","ONTL","ONTX","ONTXW","ONVO","OOMA","OPB","OPBK","OPER","OPES","OPESU","OPESW","OPGN","OPHC","OPHT","OPK","OPNT","OPOF","OPP","OPRA","OPRX","OPTN","OPTT","OPY","OQAL","OR","ORA","ORAN","ORBC","ORBK","ORC","ORCL","ORG","ORGS","ORI","ORIG","ORIT","ORLY","ORM","ORMP","ORN","ORPN","ORRF","ORTX","OSB","OSBC","OSBCP","OSCV","OSG","OSIR","OSIS","OSIZ","OSK","OSLE","OSMT","OSN","OSPN","OSS","OSTK","OSUR","OTEL","OTEX","OTIC","OTIV","OTTR","OTTW","OUNZ","OUSA","OUSM","OUT","OVAS","OVBC","OVID","OVLC","OVLU","OVLY","OVOL","OXBR","OXBRW","OXFD","OXLC","OXLCM","OXLCO","OXM","OXSQ","OXSQL","OXY","OYLD","OZK","OZM","P","PAA","PAAS","PAC","PACA","PACB","PACQ","PACQU","PACQW","PACW","PAF","PAG","PAGG","PAGP","PAGS","PAH","PAHC","PAI","PAK","PALL","PAM","PANL","PANW","PAR","PARR","PATI","PATK","PAVE","PAVM","PAVMW","PAVMZ","PAWZ","PAYC","PAYX","PB","PBA","PBB","PBBI","PBCT","PBCTP","PBD","PBDM","PBE","PBEE","PBF","PBFX","PBH","PBHC","PBI","PBI-B","PBIP","PBJ","PBND","PBP","PBPB","PBR","PBR.A","PBS","PBSK","PBSM","PBT","PBTP","PBUS","PBW","PBY","PBYI","PCAR","PCB","PCEF","PCF","PCG","PCG-A","PCG-B","PCG-C","PCG-D","PCG-E","PCG-G","PCG-H","PCG-I","PCH","PCI","PCK","PCM","PCMI","PCN","PCOM","PCQ","PCRX","PCSB","PCTI","PCTY","PCY","PCYG","PCYO","PDBC","PDCE","PDCO","PDD","PDEX","PDFS","PDI","PDLB","PDLI","PDM","PDN","PDP","PDS","PDT","PDVW","PE","PEB","PEB-C","PEB-D","PEBK","PEBO","PED","PEG","PEGA","PEGI","PEI","PEI-B","PEI-C","PEI-D","PEIX","PEJ","PEK","PEN","PENN","PEO","PEP","PER","PERI","PES","PESI","PETQ","PETS","PETX","PETZ","PETZC","PEX","PEXL","PEY","PEZ","PFBC","PFBI","PFD","PFE","PFF","PFFA","PFFD","PFFL","PFFR","PFG","PFGC","PFH","PFI","PFIE","PFIG","PFIN","PFIS","PFL","PFLT","PFM","PFMT","PFN","PFNX","PFO","PFPT","PFS","PFSI","PFSW","PFXF","PG","PGAL","PGC","PGF","PGHY","PGJ","PGLC","PGMB","PGNX","PGP","PGR","PGRE","PGTI","PGX","PGZ","PH","PHAS","PHB","PHD","PHDG","PHG","PHI","PHII","PHIIK","PHK","PHM","PHO","PHT","PHX","PHYL","PHYS","PI","PICB","PICK","PICO","PID","PIE","PIH","PIHPP","PII","PILL","PIM","PIN","PINC","PIO","PIR","PIRS","PIXY","PIY","PIZ","PJC","PJH","PJP","PJT","PJUL","PK","PKB","PKBK","PKD","PKE","PKG","PKI","PKO","PKOH","PKW","PKX","PLAB","PLAG","PLAN","PLAY","PLBC","PLCE","PLCY","PLD","PLG","PLLL","PLM","PLND","PLNT","PLOW","PLPC","PLSE","PLT","PLTM","PLUG","PLUS","PLW","PLX","PLXP","PLXS","PLYA","PLYM","PLYM-A","PM","PMBC","PMD","PME","PMF","PML","PMM","PMO","PMOM","PMR","PMT","PMT-A","PMT-B","PMTS","PMX","PNBK","PNC","PNC+","PNC-P","PNC-Q","PNF","PNFP","PNI","PNM","PNNT","PNQI","PNR","PNRG","PNRL","PNTR","PNW","POCT","PODD","POL","POLA","POOL","POPE","POR","POST","POWI","POWL","PPA","PPBI","PPC","PPDF","PPDM","PPEM","PPG","PPH","PPIH","PPL","PPLC","PPLN","PPLT","PPMC","PPR","PPSC","PPSI","PPT","PPTY","PPX","PQG","PQLC","PRA","PRAA","PRAH","PRAN","PRB","PRCP","PRE-F","PRE-G","PRE-H","PRE-I","PREF","PRF","PRFT","PRFZ","PRGO","PRGS","PRGX","PRH","PRI","PRID","PRIF-A","PRIF-B","PRIM","PRK","PRLB","PRME","PRMW","PRN","PRNB","PRNT","PRO","PROV","PRPH","PRPL","PRPO","PRQR","PRS","PRSC","PRSP","PRSS","PRT","PRTA","PRTH","PRTHU","PRTHW","PRTK","PRTO","PRTS","PRTY","PRU","PRVB","PS","PSA","PSA-A","PSA-B","PSA-C","PSA-D","PSA-E","PSA-F","PSA-G","PSA-U","PSA-V","PSA-W","PSA-X","PSA-Y","PSA-Z","PSAU","PSB","PSB-U","PSB-V","PSB-W","PSB-X","PSB-Y","PSC","PSCC","PSCD","PSCE","PSCF","PSCH","PSCI","PSCM","PSCT","PSCU","PSDO","PSEC","PSET","PSF","PSI","PSJ","PSK","PSL","PSLV","PSMB","PSMC","PSMG","PSMM","PSMT","PSO","PSP","PSQ","PSR","PST","PSTG","PSTI","PSX","PSXP","PT","PTC","PTCT","PTE","PTEN","PTEU","PTF","PTGX","PTH","PTI","PTIE","PTLA","PTLC","PTMC","PTN","PTNQ","PTNR","PTR","PTSI","PTVCA","PTVCB","PTX","PTY","PUB","PUI","PUK","PUK-A","PULM","PULS","PUMP","PUTW","PUW","PVAC","PVAL","PVBC","PVG","PVH","PVI","PVL","PVTL","PW","PW-A","PWB","PWC","PWOD","PWR","PWS","PWV","PWZ","PXD","PXE","PXF","PXH","PXI","PXJ","PXLG","PXLV","PXLW","PXMG","PXMV","PXQ","PXR","PXS","PXSG","PXSV","PXUS","PY","PYDS","PYN","PYPE","PYPL","PYS","PYT","PYX","PYZ","PZA","PZC","PZD","PZG","PZI","PZN","PZT","PZZA","QABA","QADA","QADB","QAI","QARP","QAT","QBAK","QCLN","QCOM","QCRH","QD","QDEF","QDEL","QDF","QDIV","QDYN","QED","QEFA","QEMM","QEP","QES","QGEN","QGRO","QGTA","QHC","QID","QINT","QIWI","QLC","QLD","QLS","QLTA","QLYS","QMN","QMOM","QNST","QQEW","QQQ","QQQC","QQQE","QQQX","QQXT","QRHC","QRTEA","QRTEB","QRVO","QSR","QSY","QTEC","QTM","QTNA","QTNT","QTRH","QTRX","QTS","QTS-A","QTS-B","QTT","QTUM","QTWO","QUAD","QUAL","QUIK","QUMU","QUOT","QURE","QUS","QVAL","QVM","QWLD","QXGG","QXMI","QXRR","QXTR","QYLD","R","RA","RAAX","RACE","RAD","RADA","RAIL","RALS","RAMP","RAND","RARE","RARX","RAVE","RAVI","RAVN","RBA","RBB","RBBN","RBC","RBCAA","RBCN","RBIN","RBNC","RBS","RBS-S","RBUS","RC","RCD","RCG","RCI","RCII","RCKT","RCKY","RCL","RCM","RCMT","RCON","RCS","RCUS","RDC","RDCM","RDFN","RDHL","RDI","RDIB","RDIV","RDN","RDNT","RDS.A","RDS.B","RDUS","RDVT","RDVY","RDWR","RDY","RE","RECN","REDU","REDV","REED","REEM","REET","REFA","REFR","REG","REGI","REGL","REGN","REI","REK","RELL","RELV","RELX","REM","REML","REMX","REN","RENN","REPH","REPL","RES","RESI","RESN","RETA","RETL","RETO","REV","REVG","REW","REX","REXR","REXR-A","REXR-B","REZ","REZI","RF","RF-A","RF-B","RFAP","RFCI","RFDA","RFDI","RFEM","RFEU","RFFC","RFG","RFI","RFIL","RFL","RFP","RFUN","RFV","RGA","RGCO","RGEN","RGI","RGLB","RGLD","RGLS","RGNX","RGR","RGS","RGSE","RGT","RH","RHE","RHE-A","RHI","RHP","RHS","RHT","RIBT","RIBTW","RICK","RIDV","RIF","RIG","RIGL","RIGS","RILY","RILYG","RILYL","RILYZ","RINF","RING","RIO","RIOT","RISE","RIV","RIVE","RJA","RJF","RJI","RJN","RJZ","RKDA","RL","RLGT","RLGT-A","RLGY","RLH","RLI","RLJ","RLJ-A","RLM","RLY","RM","RMAX","RMBL","RMBS","RMCF","RMD","RMED","RMI","RMNI","RMR","RMT","RMTI","RNDB","RNDM","RNDV","RNEM","RNET","RNG","RNGR","RNLC","RNMC","RNN","RNP","RNR","RNR-C","RNR-E","RNR-F","RNSC","RNST","RNWK","ROAD","ROAM","ROBO","ROBT","ROCK","RODI","RODM","ROG","ROGS","ROIC","ROK","ROKU","ROL","ROLL","ROM","ROOF","ROP","RORE","ROSE","ROSEU","ROST","ROUS","ROX","ROYT","RP","RPAI","RPD","RPG","RPIBC","RPM","RPT","RPT-D","RPUT","RPV","RQI","RRC","RRD","RRGB","RRI","RRR","RRTS","RS","RSG","RSLS","RSP","RST","RSX","RSXJ","RSYS","RTEC","RTH","RTIX","RTL","RTM","RTN","RTRX","RTTR","RUBI","RUBY","RUN","RUSHA","RUSHB","RUSL","RUSS","RUTH","RVEN","RVI","RVLT","RVNC","RVNU","RVP","RVRS","RVSB","RVT","RWGE","RWGE=","RWJ","RWK","RWL","RWLK","RWM","RWO","RWR","RWT","RWW","RWX","RXD","RXI","RXII","RXIIW","RXL","RXN","RXN-A","RY","RY-T","RYAAY","RYAM","RYAM-A","RYB","RYE","RYF","RYH","RYI","RYJ","RYN","RYT","RYTM","RYU","RZA","RZB","RZG","RZV","S","SA","SAA","SABR","SACH","SAEX","SAFE","SAFM","SAFT","SAGE","SAGG","SAH","SAIA","SAIC","SAIL","SAL","SALM","SALT","SAM","SAMG","SAN","SAN-B","SAND","SANM","SANW","SAP","SAR","SASR","SATS","SAUC","SAVE","SB","SB-C","SB-D","SBAC","SBB","SBBP","SBBX","SBCF","SBFG","SBFGP","SBGI","SBGL","SBH","SBI","SBIO","SBLK","SBLKZ","SBM","SBNA","SBNY","SBNYW","SBOT","SBOW","SBPH","SBR","SBRA","SBS","SBSI","SBT","SBUX","SC","SCA","SCAC","SCACU","SCACW","SCAP","SCC","SCCO","SCD","SCE-B","SCE-C","SCE-D","SCE-E","SCE-G","SCE-H","SCE-J","SCE-K","SCE-L","SCG","SCHA","SCHB","SCHC","SCHD","SCHE","SCHF","SCHG","SCHH","SCHK","SCHL","SCHM","SCHN","SCHO","SCHP","SCHR","SCHV","SCHW","SCHW-C","SCHW-D","SCHX","SCHZ","SCI","SCID","SCIF","SCIJ","SCIN","SCIU","SCIX","SCJ","SCKT","SCL","SCM","SCO","SCON","SCOR","SCPH","SCS","SCSC","SCTO","SCVL","SCWX","SCX","SCYX","SCZ","SD","SDCI","SDD","SDEM","SDG","SDGA","SDI","SDIV","SDLP","SDOG","SDOW","SDP","SDPI","SDR","SDRL","SDS","SDT","SDVY","SDY","SDYL","SE","SEA","SEAC","SEAS","SEB","SECO","SECT","SEDG","SEE","SEED","SEF","SEIC","SEII","SELB","SELF","SEM","SEMG","SEND","SENEA","SENEB","SENS","SEP","SERV","SES","SESN","SF","SF-A","SFB","SFBC","SFBS","SFE","SFET","SFHY","SFIG","SFIX","SFL","SFLY","SFM","SFNC","SFS","SFST","SFUN","SG","SGA","SGB","SGBX","SGC","SGDJ","SGDM","SGEN","SGGB","SGH","SGLB","SGLBW","SGMA","SGMO","SGMS","SGOC","SGOL","SGRP","SGRY","SGU","SGYP","SGZA","SH","SHAG","SHAK","SHBI","SHE","SHEN","SHG","SHI","SHIP","SHIPW","SHLO","SHLX","SHM","SHO","SHO-E","SHO-F","SHOO","SHOP","SHOS","SHPG","SHSP","SHV","SHW","SHY","SHYD","SHYG","SHYL","SIBN","SIC","SID","SIEB","SIEN","SIF","SIFI","SIFY","SIG","SIGA","SIGI","SIJ","SIL","SILC","SILJ","SILV","SIM","SIMO","SINA","SINO","SINT","SIR","SIRI","SITC","SITC-A","SITC-J","SITC-K","SITE","SITO","SIVB","SIVR","SIX","SIZ","SIZE","SJB","SJI","SJIU","SJM","SJNK","SJR","SJT","SJW","SKF","SKIS","SKM","SKOR","SKT","SKX","SKY","SKYS","SKYW","SKYY","SLAB","SLB","SLCA","SLCT","SLDB","SLF","SLG","SLG-I","SLGL","SLGN","SLIM","SLM","SLMBP","SLNO","SLP","SLQD","SLRC","SLS","SLT","SLV","SLVO","SLVP","SLX","SLY","SLYG","SLYV","SM","SMAR","SMB","SMBC","SMBK","SMCP","SMDD","SMDV","SMED","SMEZ","SMFG","SMG","SMH","SMHD","SMHI","SMI","SMIN","SMIT","SMLF","SMLL","SMLP","SMLV","SMM","SMMD","SMMF","SMMT","SMMU","SMMV","SMN","SMP","SMPL","SMRT","SMSI","SMTA","SMTC","SMTS","SMTX","SN","SNA","SNAP","SNBR","SNCR","SND","SNDE","SNDR","SNDX","SNE","SNES","SNFCA","SNGX","SNGXW","SNH","SNHNI","SNHNL","SNHY","SNLN","SNMP","SNN","SNNA","SNOA","SNP","SNPS","SNR","SNSR","SNSS","SNV","SNV-D","SNX","SNY","SO","SOCL","SODA","SOFO","SOGO","SOHO","SOHOB","SOHOK","SOHOO","SOHU","SOI","SOIL","SOJB","SOJC","SOL","SOLO","SOLOW","SON","SONA","SONC","SONO","SOR","SORL","SOVB","SOXL","SOXS","SOXX","SOYB","SP","SPA","SPAB","SPAQ","SPAQ+","SPAQ=","SPAR","SPB","SPCB","SPDN","SPDV","SPDW","SPE","SPE-B","SPEM","SPEX","SPFF","SPG","SPG-J","SPGI","SPH","SPHB","SPHD","SPHQ","SPHS","SPI","SPIB","SPKE","SPKEP","SPLB","SPLG","SPLK","SPLP","SPLP-A","SPLV","SPMD","SPMO","SPMV","SPN","SPNE","SPNS","SPOK","SPOT","SPPI","SPPP","SPR","SPRO","SPRT","SPSB","SPSC","SPSM","SPTL","SPTM","SPTN","SPTS","SPUU","SPVM","SPVU","SPWH","SPWR","SPXB","SPXC","SPXE","SPXL","SPXN","SPXS","SPXT","SPXU","SPXV","SPXX","SPY","SPYB","SPYD","SPYG","SPYV","SPYX","SQ","SQBG","SQLV","SQM","SQNS","SQQQ","SR","SRAX","SRC","SRC-A","SRCE","SRCI","SRCL","SRDX","SRE","SRE-A","SRE-B","SRET","SREV","SRF","SRG","SRG-A","SRI","SRLN","SRLP","SRNE","SRPT","SRRA","SRRK","SRS","SRT","SRTS","SRTSW","SRTY","SRV","SRVR","SSB","SSBI","SSC","SSD","SSFN","SSG","SSI","SSKN","SSL","SSLJ","SSNC","SSNT","SSO","SSP","SSRM","SSTI","SSTK","SSW","SSW-D","SSW-E","SSW-G","SSW-H","SSW-I","SSWA","SSWN","SSY","SSYS","ST","STAA","STAF","STAG","STAG-C","STAR","STAR-D","STAR-G","STAR-I","STAY","STBA","STBZ","STC","STCN","STE","STFC","STG","STI","STI+A","STI+B","STI-A","STIM","STIP","STK","STKL","STKS","STL","STL-A","STLD","STLR","STLRU","STLRW","STM","STML","STMP","STN","STND","STNE","STNG","STNL","STNLU","STNLW","STON","STOR","STOT","STPP","STPZ","STRA","STRL","STRM","STRO","STRS","STRT","STT","STT-C","STT-D","STT-E","STT-G","STWD","STX","STXB","STZ","STZ.B","SU","SUB","SUI","SUM","SUMR","SUN","SUNS","SUNW","SUP","SUPN","SUPV","SURF","SUSA","SUSB","SUSC","SVA","SVBI","SVM","SVMK","SVRA","SVT","SVVC","SVXY","SWAN","SWCH","SWI","SWIR","SWJ","SWK","SWKS","SWM","SWN","SWP","SWX","SWZ","SXC","SXCP","SXE","SXI","SXT","SYBT","SYBX","SYE","SYF","SYG","SYK","SYKE","SYLD","SYMC","SYN","SYNA","SYNC","SYNH","SYNL","SYPR","SYRS","SYV","SYX","SYY","SZC","SZK","SZNE","SZO","T","TA","TAC","TACO","TACOW","TACT","TAGS","TAHO","TAIL","TAIT","TAL","TALO","TALO+","TAN","TANH","TANNI","TAO","TAOP","TAP","TAP.A","TAPR","TARO","TAST","TAT","TATT","TAXF","TAYD","TBB","TBBK","TBF","TBI","TBIO","TBK","TBLTU","TBLU","TBNK","TBPH","TBRG","TBRGU","TBRGW","TBT","TBX","TCBI","TCBIL","TCBIP","TCBIW","TCBK","TCCO","TCDA","TCF","TCF+","TCF-D","TCFC","TCGP","TCI","TCMD","TCO","TCO-J","TCO-K","TCON","TCP","TCPC","TCRD","TCRZ","TCS","TCTL","TCX","TD","TDA","TDAC","TDACU","TDC","TDE","TDF","TDG","TDI","TDIV","TDJ","TDOC","TDS","TDTF","TDTT","TDW","TDY","TEAM","TECD","TECH","TECK","TECL","TECS","TEDU","TEF","TEI","TEL","TELL","TEN","TENB","TENX","TEO","TER","TERM","TERP","TESS","TETF","TEUM","TEVA","TEX","TFI","TFIV","TFLO","TFLT","TFSL","TFX","TG","TGA","TGB","TGC","TGE","TGEN","TGH","TGI","TGLS","TGNA","TGP","TGP-A","TGP-B","TGS","TGT","TGTX","THC","THD","THFF","THG","THGA","THM","THO","THQ","THR","THRM","THS","THST","THW","TI","TI.A","TIBR","TIBRU","TIBRW","TIER","TIF","TIK","TILE","TILT","TIP","TIPT","TIPX","TIPZ","TIS","TISA","TISI","TITN","TIVO","TJX","TK","TKAT","TKC","TKKS","TKKSR","TKKSU","TKKSW","TKR","TLDH","TLEH","TLF","TLGT","TLH","TLI","TLK","TLND","TLP","TLRA","TLRD","TLRY","TLSA","TLT","TLTD","TLTE","TLYS","TM","TMCX","TMCXU","TMCXW","TMDI","TMF","TMFC","TMHC","TMK","TMO","TMP","TMQ","TMSR","TMST","TMUS","TMV","TNA","TNAV","TNC","TNDM","TNET","TNK","TNP","TNP-B","TNP-C","TNP-D","TNP-E","TNP-F","TNXP","TOCA","TOK","TOL","TOLZ","TOO","TOO-A","TOO-B","TOO-E","TOPS","TORC","TOT","TOTA","TOTAR","TOTAU","TOTAW","TOTL","TOUR","TOWN","TOWR","TPB","TPC","TPCO","TPGH","TPGH=","TPH","TPHS","TPIC","TPL","TPNL","TPOR","TPR","TPRE","TPVG","TPVY","TPX","TPYP","TPZ","TQQQ","TR","TRC","TRCB","TRCH","TRCO","TREC","TREE","TREX","TRGP","TRHC","TRI","TRIB","TRIL","TRIP","TRK","TRMB","TRMD","TRMK","TRMT","TRN","TRNO","TRNS","TROV","TROW","TROX","TRP","TRPX","TRQ","TRS","TRST","TRT","TRTN","TRTX","TRTY","TRU","TRUE","TRUP","TRV","TRVG","TRVN","TRX","TRXC","TS","TSBK","TSC","TSCAP","TSCO","TSE","TSEM","TSG","TSI","TSLA","TSLF","TSLX","TSM","TSN","TSQ","TSRI","TSRO","TSS","TST","TSU","TTAC","TTAI","TTC","TTD","TTEC","TTEK","TTGT","TTI","TTM","TTMI","TTNP","TTOO","TTP","TTPH","TTS","TTT","TTTN","TTWO","TU","TUES","TUP","TUR","TURN","TUSA","TUSK","TUZ","TV","TVC","TVE","TVIX","TVPT","TVTY","TWI","TWIN","TWLO","TWLV","TWLVR","TWLVU","TWLVW","TWM","TWMC","TWN","TWNK","TWO","TWO-A","TWO-B","TWO-C","TWO-D","TWO-E","TWOU","TWST","TWTR","TX","TXMD","TXN","TXRH","TXT","TY","TYBS","TYD","TYG","TYHT","TYL","TYME","TYNS","TYO","TYPE","TZA","TZAC","TZACU","TZACW","TZOO","UA","UAA","UAE","UAG","UAL","UAMY","UAN","UAUD","UAVS","UBA","UBCP","UBFO","UBG","UBIO","UBNK","UBNT","UBOH","UBOT","UBP","UBP-G","UBP-H","UBR","UBS","UBSH","UBSI","UBT","UBX","UCBI","UCC","UCFC","UCHF","UCI","UCIB","UCO","UCON","UCTT","UDBI","UDN","UDOW","UDR","UE","UEC","UEIC","UEPS","UEUR","UEVM","UFAB","UFCS","UFI","UFPI","UFPT","UFS","UG","UGA","UGAZ","UGBP","UGE","UGI","UGL","UGLD","UGP","UHAL","UHS","UHT","UIHC","UIS","UITB","UIVM","UJB","UJPY","UJUL","UL","ULBI","ULBR","ULE","ULH","ULST","ULTA","ULTI","ULVM","UMBF","UMC","UMDD","UMH","UMH-B","UMH-C","UMH-D","UMPQ","UMRX","UN","UNAM","UNB","UNF","UNFI","UNG","UNH","UNIT","UNL","UNM","UNMA","UNP","UNT","UNTY","UNVR","UOCT","UONE","UONEK","UPL","UPLD","UPRO","UPS","UPV","UPW","UPWK","UQM","URA","URBN","URE","URG","URGN","URI","UROV","URR","URTH","URTY","USA","USAC","USAI","USAK","USAP","USAS","USAT","USATP","USAU","USB","USB-A","USB-H","USB-M","USB-O","USB-P","USCI","USCR","USD","USDP","USDU","USEG","USEQ","USFD","USFR","USG","USHY","USIG","USL","USLB","USLM","USLV","USM","USMC","USMF","USMV","USNA","USO","USOD","USOI","USOU","USPH","USRT","UST","USTB","USV","USVM","USX","UTES","UTF","UTG","UTHR","UTI","UTL","UTMD","UTRN","UTSI","UTSL","UTX","UUP","UUU","UUUU","UVE","UVSP","UVV","UVXY","UWM","UWN","UWT","UXI","UXIN","UYG","UYM","UZA","UZC","V","VAC","VALE","VALQ","VALU","VALX","VAM","VAMO","VAR","VAW","VB","VBF","VBFC","VBIV","VBK","VBLT","VBND","VBR","VBTX","VC","VCEL","VCF","VCIT","VCLT","VCNX","VCR","VCRA","VCSH","VCTR","VCV","VCYT","VDC","VDE","VEA","VEAC","VEACU","VEACW","VEC","VECO","VEDL","VEEV","VEGA","VEGI","VEON","VER","VER-F","VERI","VERU","VESH","VET","VETS","VEU","VFC","VFH","VFL","VFLQ","VFMF","VFMO","VFMV","VFQY","VFVA","VG","VGFO","VGI","VGIT","VGK","VGLT","VGM","VGR","VGSH","VGT","VGZ","VHC","VHI","VHT","VIA","VIAB","VIAV","VICI","VICL","VICR","VIDI","VIG","VIGI","VIIX","VIOG","VIOO","VIOT","VIOV","VIPS","VIRC","VIRT","VIS","VISI","VIV","VIVE","VIVO","VIXM","VIXY","VJET","VKI","VKQ","VKTX","VLGEA","VLO","VLP","VLRS","VLRX","VLT","VLU","VLUE","VLY","VLYPO","VLYPP","VLYWW","VMBS","VMC","VMI","VMIN","VMM","VMO","VMOT","VMW","VNCE","VNDA","VNE","VNET","VNLA","VNM","VNO","VNO-K","VNO-L","VNO-M","VNOM","VNQ","VNQI","VNRX","VNTR","VO","VOC","VOD","VOE","VONE","VONG","VONV","VOO","VOOG","VOOV","VOT","VOX","VOXX","VOYA","VPG","VPL","VPU","VPV","VQT","VRA","VRAY","VRCA","VREX","VRIG","VRML","VRNA","VRNS","VRNT","VRP","VRRM","VRRMW","VRS","VRSK","VRSN","VRTS","VRTSP","VRTU","VRTV","VRTX","VSAT","VSDA","VSEC","VSGX","VSH","VSI","VSL","VSLR","VSM","VSMV","VSS","VST","VSTM","VSTO","VT","VTA","VTC","VTEB","VTGN","VTHR","VTI","VTIP","VTIQ","VTIQU","VTIQW","VTL","VTN","VTNR","VTR","VTRB","VTSI","VTV","VTVT","VTWG","VTWO","VTWV","VUG","VUSE","VUZI","VV","VVC","VVI","VVPR","VVR","VVUS","VVV","VWO","VWOB","VXF","VXRT","VXUS","VXX","VXXB","VXZ","VXZB","VYGR","VYM","VYMI","VZ","VZA","W","WAAS","WAB","WABC","WAFD","WAFDW","WAGE","WAIR","WAL","WALA","WASH","WAT","WATT","WB","WBA","WBAI","WBAL","WBC","WBIA","WBIB","WBIC","WBID","WBIE","WBIF","WBIG","WBIH","WBII","WBIL","WBIR","WBIY","WBK","WBND","WBS","WBS-F","WBT","WCC","WCFB","WCG","WCHN","WCN","WD","WDAY","WDC","WDFC","WDIV","WDR","WDRW","WEA","WEAT","WEBK","WEC","WELL","WELL-I","WEN","WERN","WES","WETF","WEX","WEYS","WF","WFC","WFC-L","WFC-N","WFC-O","WFC-P","WFC-Q","WFC-R","WFC-T","WFC-V","WFC-W","WFC-X","WFC-Y","WFE-A","WFHY","WFIG","WFT","WGO","WGP","WH","WHD","WHF","WHG","WHLM","WHLR","WHLRD","WHLRP","WHLRW","WHR","WIA","WIFI","WIL","WILC","WIN","WINA","WING","WINS","WIP","WIRE","WISA","WIT","WIW","WIX","WK","WKHS","WLDN","WLDR","WLFC","WLH","WLK","WLKP","WLL","WLTW","WM","WMB","WMC","WMCR","WMGI","WMGIZ","WMK","WMS","WMT","WMW","WNC","WNEB","WNS","WOMN","WOOD","WOR","WOW","WP","WPC","WPG","WPG-H","WPG-I","WPM","WPP","WPRT","WPS","WPX","WRB","WRB-B","WRB-D","WRB-E","WRD","WRE","WREI","WRI","WRK","WRLD","WRLS","WRLSR","WRLSU","WRLSW","WRN","WSBC","WSBF","WSC","WSFS","WSM","WSO","WSO.B","WSR","WST","WSTG","WSTL","WTBA","WTFC","WTFCM","WTFCW","WTI","WTID","WTIU","WTM","WTMF","WTR","WTS","WTT","WTTR","WTW","WU","WUBA","WVE","WVFC","WVVI","WVVIP","WWD","WWE","WWR","WWW","WY","WYDE","WYND","WYNN","WYY","X","XAN","XAN-C","XAR","XBI","XBIO","XBIT","XCEM","XDIV","XEC","XEL","XELA","XELB","XENE","XENT","XERS","XES","XFLT","XGTI","XHB","XHE","XHR","XHS","XIN","XINA","XITK","XKCP","XKFF","XKFS","XKII","XKST","XLB","XLC","XLE","XLF","XLG","XLI","XLK","XLNX","XLP","XLRE","XLRN","XLU","XLV","XLY","XME","XMLV","XMPT","XMX","XNCR","XNET","XNTK","XOG","XOM","XOMA","XON","XONE","XOP","XOXO","XPER","XPH","XPL","XPO","XPP","XRAY","XRF","XRLV","XRT","XRX","XSD","XSHD","XSHQ","XSLV","XSOE","XSPA","XSW","XT","XTH","XTL","XTLB","XTN","XTNT","XUSA","XVZ","XWEB","XXII","XYF","XYL","Y","YANG","YAO","YCL","YCS","YECO","YELP","YETI","YEXT","YGYI","YI","YIN","YINN","YLCO","YLD","YLDE","YMAB","YMLI","YMLP","YNDX","YOGA","YORW","YPF","YRCW","YRD","YRIV","YTEN","YTRA","YUM","YUMA","YUMC","YVR","YXI","YY","YYY","Z","ZAGG","ZAYO","ZB-A","ZB-G","ZB-H","ZBH","ZBIO","ZBK","ZBRA","ZCAN","ZDEU","ZDGE","ZEAL","ZEN","ZEUS","ZEXIT","ZF","ZFGN","ZG","ZGBR","ZGNX","ZHOK","ZIEXT","ZION","ZIONW","ZIONZ","ZIOP","ZIV","ZIXI","ZJPN","ZKIN","ZLAB","ZMLP","ZN","ZNGA","ZNH","ZOES","ZOM","ZROZ","ZS","ZSAN","ZSL","ZTO","ZTR","ZTS","ZUMZ","ZUO","ZVZZT","ZWZZT","ZXIET","ZXZZT","ZYME","ZYNE"];
    function autocomplete(inp, arr) {
    	  /*the autocomplete function takes two arguments,
    	  the text field element and an array of possible autocompleted values:*/
    	  var currentFocus;
    	  /*execute a function when someone writes in the text field:*/
    	  inp.addEventListener("input", function(e) {
    	      var a, b, i, val = this.value;
    	      /*close any already open lists of autocompleted values*/
    	      closeAllLists();
    	      if (!val) { return false;}
    	      currentFocus = -1;
    	      /*create a DIV element that will contain the items (values):*/
    	      a = document.createElement("DIV");
    	      a.setAttribute("id", this.id + "autocomplete-list");
    	      a.setAttribute("class", "autocomplete-items");
    	      /*append the DIV element as a child of the autocomplete container:*/
    	      this.parentNode.appendChild(a);
    	      /*for each item in the array...*/
    	      var count = 0;
    	      for (i = 0; i < arr.length && count < 30; i++) {
    	        /*check if the item starts with the same letters as the text field value:*/
    	        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
    	          /*create a DIV element for each matching element:*/
    	          b = document.createElement("DIV");
    	          /*make the matching letters bold:*/
    	          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
    	          b.innerHTML += arr[i].substr(val.length);
    	          /*insert a input field that will hold the current array item's value:*/
    	          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
    	          /*execute a function when someone clicks on the item value (DIV element):*/
    	          b.addEventListener("click", function(e) {
    	              /*insert the value for the autocomplete text field:*/
    	              inp.value = this.getElementsByTagName("input")[0].value;
    	              /*close the list of autocompleted values,
    	              (or any other open lists of autocompleted values:*/
    	              closeAllLists();
    	          });
    	          a.appendChild(b);
    	          count++;
    	        }
    	      }
    	  });
    	  /*execute a function presses a key on the keyboard:*/
    	  inp.addEventListener("keydown", function(e) {
    	      var x = document.getElementById(this.id + "autocomplete-list");
    	      if (x) x = x.getElementsByTagName("div");
    	      if (e.keyCode == 40) {
    	        /*If the arrow DOWN key is pressed,
    	        increase the currentFocus variable:*/
    	        currentFocus++;
    	        /*and and make the current item more visible:*/
    	        addActive(x);
    	      } else if (e.keyCode == 38) { //up
    	        /*If the arrow UP key is pressed,
    	        decrease the currentFocus variable:*/
    	        currentFocus--;
    	        /*and and make the current item more visible:*/
    	        addActive(x);
    	      } else if (e.keyCode == 13) {
    	        /*If the ENTER key is pressed, prevent the form from being submitted,*/
    	        e.preventDefault();
    	        if (currentFocus > -1) {
    	          /*and simulate a click on the "active" item:*/
    	          if (x) x[currentFocus].click();
    	        }
    	      }
    	  });
    	  function addActive(x) {
    	    /*a function to classify an item as "active":*/
    	    if (!x) return false;
    	    /*start by removing the "active" class on all items:*/
    	    removeActive(x);
    	    if (currentFocus >= x.length) currentFocus = 0;
    	    if (currentFocus < 0) currentFocus = (x.length - 1);
    	    /*add class "autocomplete-active":*/
    	    x[currentFocus].classList.add("autocomplete-active");
    	  }
    	  function removeActive(x) {
    	    /*a function to remove the "active" class from all autocomplete items:*/
    	    for (var i = 0; i < x.length; i++) {
    	      x[i].classList.remove("autocomplete-active");
    	    }
    	  }
    	  function closeAllLists(elmnt) {
    	    /*close all autocomplete lists in the document,
    	    except the one passed as an argument:*/
    	    var x = document.getElementsByClassName("autocomplete-items");
    	    for (var i = 0; i < x.length; i++) {
    	      if (elmnt != x[i] && elmnt != inp) {
    	      x[i].parentNode.removeChild(x[i]);
    	    }
    	  }
    	}
    	/*execute a function when someone clicks in the document:*/
    	document.addEventListener("click", function (e) {
    	    closeAllLists(e.target);
    	});
    	}
    </script>
   	<script>
		autocomplete(document.getElementById("searchBar"), symbols);
	</script>
</body>
</html>
