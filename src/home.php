<?php

session_start();

// NOTE: delete next statement, for DEBUG purposes only
// $_SESSION['username'] = "captainhoji";
// $_SESSION['signedIn'] = true;
// var_dump($_SESSION);
// echo "<hr>";
// session_destroy();

require "databaseConnection.php";

$symbolArr = array();
if (array_key_exists("signedIn", $_SESSION) && $_SESSION["signedIn"] === TRUE) {
    $username = $_SESSION["username"];

    include 'databaseConnection.php';

    // get balance of this user
    $sql = "SELECT i_symbol FROM Investments WHERE i_username=\"$username\";";
    $results = $mysqli->query($sql);
    if (!$results) {
        echo "SQL ERROR: " . $mysqli->error;
        exit();
    }
    if ($results->num_rows > 0) {
        for ($i = 0; $i < 5; $i++) {
            if (!$row = $results->fetch_assoc()) {
                break;
            }
            array_push($symbolArr, $row["i_symbol"]);
        }
    }
}

if (count($symbolArr) == 0) {
    $symbolArr = array("AAPL", "GOOG", "FB", "AMZN");
}

$mysqli->close();

// STEP 4: Close DB Connection (NOT YET)
// $mysqli->close();
?>

<xml version="1.0" encoding="utf-8">
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Ambrish Parekh">

    <title>StockOverflow | Home</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/logo.css">
    <!-- Custom styles for this template -->
    <!-- <link href="starter-template.css" rel="stylesheet"> -->

    <style>
    main {
        padding: 1em
    }
    #containerTop {
        height: 500px;
        min-width: 310px;
    }
    #containerBottom {
        height: 500px;
        min-width: 310px;
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
                <li class="nav-item active">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
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

    <div id="containerTop">

    </div>

    <div id="containerBottom">

    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
    <!-- <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script> -->
    <!-- <script src="../../assets/js/vendor/popper.min.js"></script> -->
    <!-- <script src="../../dist/js/bootstrap.min.js"></script> -->

    <script
      src="http://code.jquery.com/jquery-3.3.1.min.js"
      integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
      crossorigin="anonymous"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/stock/modules/export-data.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script type="text/javascript">
    var datatext;
    var stockSymbols = <?php echo json_encode($symbolArr);?>;
    var countStocks = <?php echo count($symbolArr);?>; // initialize this to however many stocks the user was viewing in their last session/default number of stocks
    var stockSeries = [];

    var myChart = Highcharts.stockChart('containerBottom', {
        chart: {
            events: {
                // initial load for adding new series/ initial series will be seperate function
                // load is the per minute update
                load: function() {
                    var series = this.series;

                    var symbolString = "";

                    for (var j = 0; j < stockSymbols.length-1; j++)
                    {
                      symbolString += stockSymbols[j] + ",";
                    }
                    symbolString += stockSymbols[stockSymbols.length - 1];

                    $.ajax({
                        method: 'GET',
                        async: false,
                        url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + symbolString + '&types=chart&range=5y'
                    })
                    .done(function(results) {
                        datatext = results;
                    })
                    .fail(function() {
                        console.log("error");
                    });

                    stockSymbols = Object.keys(datatext);

                    for (symbolIndex = 0; symbolIndex < stockSymbols.length; symbolIndex++)
                    {
                      var stockName = stockSymbols[symbolIndex];
                      var allStockInfo = datatext[stockName];
                      var chartInfo = allStockInfo.chart;
                      var outData = [];

                      for (var k = 0; k < chartInfo.length; k++)
                      {
                        var thing = chartInfo[k];
                        var datething = new Date(thing.date);
                        var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);

                        outData[k] = {
                          x: millis,
                          y: thing.close
                        };
                      }

                      var jObj = JSON.parse(JSON.stringify(outData));

                      stockSeries[symbolIndex] = outData;
                      // set id here?
                    }
                }
            },
        },
        yAxis: {
                labels: {
                formatter: function () {
                        return (this.value > 0 ? ' + ' : '') + this.value + '%';
                    }
                },
                plotLines: [{
                    value: 0,
                    width: 2,
                    color: 'silver'
                }]
    },

            plotOptions: {
                series: {
                    compare: 'percent',
                    showInNavigator: true,
                    turboThreshold: 2000
                }
            },

        tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
                valueDecimals: 2,
                split: true
            },

        rangeSelector: {
            buttons: [ {
                type: 'all',
                text: '5y'
            }, {
                count: 1,
                type: 'year',
                text: '12m'
            }, {
                count: 1,
                type: 'month',
                text: '30d'
            }, {
                count: 7,
                type: 'day',
                text: '7d'
            }],
            selected: 0
        },

        title: {
            text: 'Historical Stock Data'
        },

        exporting: {
            enabled: false
        }
    });

    // add initial stocks to the graph
    for (var i = 0; i < stockSeries.length; i++)
    {
      myChart.addSeries({
        name: stockSymbols[i],
        id: stockSymbols[i],
        data: stockSeries[i]
      }, false);
    }
    myChart.redraw();




    </script>
    <script type="text/javascript">
    var datatext;
    var stockSymbols = <?php echo json_encode($symbolArr);?>;
    var countStocks = <?php echo count($symbolArr);?>; // initialize this to however many stocks the user was viewing in their last session/default number of stocks
    var stockSeries = [];

    var myChart = Highcharts.stockChart('containerTop', {
        chart: {
            events: {
                // initial load for adding new series/ initial series will be seperate function
                // load is the per minute update
                load: function() {
                    var series = this.series;

                    var symbolString = "";

                    for (var j = 0; j < stockSymbols.length-1; j++)
                    {
                      symbolString += stockSymbols[j] + ",";
                    }
                    symbolString += stockSymbols[stockSymbols.length - 1];

                    $.ajax({
                        method: 'GET',
                        async: false,
                        url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + symbolString + '&types=chart&range=1d&chartLast=60'
                    })
                    .done(function(results) {
                        datatext = results;
                    })
                    .fail(function() {
                        console.log("error");
                    });

                    stockSymbols = Object.keys(datatext);

                    for (symbolIndex = 0; symbolIndex < stockSymbols.length; symbolIndex++)
                    {
                      var stockName = stockSymbols[symbolIndex];
                      var allStockInfo = datatext[stockName];
                      var chartInfo = allStockInfo.chart;
                      var outData = [];

                      for (var k = 0; k < chartInfo.length; k++)
                      {
                        var thing = chartInfo[k];
                        var month = thing.date.substring(4, 6);
                        var mint = parseInt(month);
                        mint--;
                        var correctMonth = mint.toString();
                        var datething = new Date(thing.date.substring(0, 4),correctMonth, thing.date.substring(6, 8), thing.minute.substring(0, 2), thing.minute.substring(3, 5), '00', '00');
                        var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);

                        var marAvg = thing.marketAverage;
                        if (marAvg === -1)
                        {
                          marAvg = thing.average;
                        }
                        if (marAvg === -1)
                        {
                          marAvg = null;
                        }

                        outData[k] = {
                          x: millis,
                          y: marAvg
                        };
                      }

                      var jObj = JSON.parse(JSON.stringify(outData));

                      stockSeries[symbolIndex] = outData;
                      // set id here?
                    }
                }
            },
        },
        yAxis: {
                labels: {
                formatter: function () {
                        return (this.value > 0 ? ' + ' : '') + this.value + '%';
                    }
                },
                plotLines: [{
                    value: 0,
                    width: 2,
                    color: 'silver'
                }]
    },

            plotOptions: {
                series: {
                    compare: 'percent',
                    showInNavigator: true
                }
            },

        tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
                valueDecimals: 2,
                split: true
            },

        rangeSelector: {
            buttons: [ {
                type: 'all',
                text: '60m'
            }, {
                count: 45,
                type: 'minute',
                text: '45m'
            }, {
                count: 30,
                type: 'minute',
                text: '30m'
            }, {
                count: 15,
                type: 'minute',
                text: '15m'
            }],
            inputEnabled: false,
            selected: 0
        },

        title: {
            text: 'Current Stock Prices'
        },

        exporting: {
            enabled: false
        }
    });

    // update function, at 1 minute mark
    function updateChart () {
      console.log("in updateChart");
      console.log(stockSymbols);
      var series = myChart.series;
      console.log(series);

      var symbolString = "";

      for (var j = 0; j < stockSymbols.length-1; j++)
      {
        symbolString += stockSymbols[j] + ",";
      }
      symbolString += stockSymbols[stockSymbols.length - 1];

      setInterval(function() {
          $.ajax({
              method: 'GET',
              async: false,
              url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + symbolString + '&types=chart&range=1d&chartLast=1'
          })
          .done(function(results) {
              datatext = results;
          })
          .fail(function() {
              console.log("error");
          });

          stockSymbols = Object.keys(datatext);

          for (var symbolIndex = 0; symbolIndex < stockSymbols.length; symbolIndex++) {
              var stockName = stockSymbols[symbolIndex];
              var allStockInfo = datatext[stockName];
              var chartInfo = allStockInfo.chart;

              var thing = chartInfo[0];
              var month = thing.date.substring(4,6);
              var mint = parseInt(month);
              mint--;
              var correctMonth = mint.toString();
              var datething = new Date(thing.date.substring(0,4), correctMonth, thing.date.substring(6,8), thing.minute.substring(0,2), thing.minute.substring(3,5), '00', '00');
              var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);

              var marAvg = thing.marketAverage;
              if (marAvg === -1)
              {
                marAvg = thing.average;
              }

              if (marAvg === -1)
              {
                marAvg = null;
              }

              var outPoint = {
                x: millis,
                y: marAvg
              };

              series[symbolIndex].addPoint([millis, marAvg], true, true); // why doesnt animation work? -- animations happen when we do myChart.redraw()
          }

          var animOptions = Highcharts.AnimationOptionsObject;
          animOptions.duration = 1000;

          myChart.redraw(animOptions);

          console.log("reloaded, at 1min");
      }, 60000);
    }

    // add initial stocks to the graph
    for (var i = 0; i < stockSeries.length; i++)
    {
      myChart.addSeries({
        name: stockSymbols[i],
        id: stockSymbols[i],
        data: stockSeries[i]
      }, false);
    }
    myChart.redraw();

    // minute updates
    updateChart();

    </script>
</body>
</html>
