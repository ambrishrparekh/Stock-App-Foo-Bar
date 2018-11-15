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
?>
<?xml version="1.0" encoding="utf-8"?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="author" content="Ambrish Parekh">

    <title>StockOverflow | Home</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/logoDark.css">
    <!-- Custom styles for this template -->
    <!-- <link href="starter-template.css" rel="stylesheet"> -->

    <style>
    main {
        padding: 1em
    }
    #containerTop {
        height: 450px;
        min-width: 310px;
    }
    #containerBottom {
        height: 450px;
        min-width: 310px;
    }
    </style>

</head>

<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="navbar-brand" href="#" style="width: 200px;">
            <?php include "components/navDark.html"; ?>
        </div>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse text-center" id="navbarText">
            <ul class="navbar-nav nav-fill w-100">
                <li class="nav-item active">
                    <a class="nav-link" href="homeDark.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="stocksDark.php">Stocks</a>
                </li>
                <li class="nav-item">
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
    <!-- <script type="text/javascript" src="js/chartTwoDark.js"></script>
    <script type="text/javascript" src="js/chartThreeDark.js"></script> -->
    <script type="text/javascript">
    var datatext;
    var stockSymbols = <?php echo json_encode($symbolArr);?>;
    var countStocks = <?php echo count($symbolArr);?>; // initialize this to however many stocks the user was viewing in their last session/default number of stocks
    var stockSeries = [];

    Highcharts.theme = {
        colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066',
            '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
        chart: {
            backgroundColor: {
                linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
                stops: [
                    [0, '#343a40'],
                    [1, '#343a40']
                ]
            },
            style: {
                fontFamily: '\'Unica One\', sans-serif'
            },
            plotBorderColor: '#606063'
        },
        title: {
            style: {
                color: '#E0E0E3',
                textTransform: 'uppercase',
                fontSize: '20px'
            }
        },
        subtitle: {
            style: {
                color: '#E0E0E3',
                textTransform: 'uppercase'
            }
        },
        xAxis: {
            gridLineColor: '#707073',
            labels: {
                style: {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            minorGridLineColor: '#505053',
            tickColor: '#707073',
            title: {
                style: {
                    color: '#A0A0A3'

                }
            }
        },
        yAxis: {
            gridLineColor: '#707073',
            labels: {
                style: {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            minorGridLineColor: '#505053',
            tickColor: '#707073',
            tickWidth: 1,
            title: {
                style: {
                    color: '#A0A0A3'
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.85)',
            style: {
                color: '#F0F0F0'
            }
        },
        plotOptions: {
            series: {
                dataLabels: {
                    color: '#B0B0B3'
                },
                marker: {
                    lineColor: '#333'
                }
            },
            boxplot: {
                fillColor: '#505053'
            },
            candlestick: {
                lineColor: 'white'
            },
            errorbar: {
                color: 'white'
            }
        },
        legend: {
            itemStyle: {
                color: '#E0E0E3'
            },
            itemHoverStyle: {
                color: '#FFF'
            },
            itemHiddenStyle: {
                color: '#606063'
            }
        },
        credits: {
            style: {
                color: '#666'
            }
        },
        labels: {
            style: {
                color: '#707073'
            }
        },

        drilldown: {
            activeAxisLabelStyle: {
                color: '#F0F0F3'
            },
            activeDataLabelStyle: {
                color: '#F0F0F3'
            }
        },

        navigation: {
            buttonOptions: {
                symbolStroke: '#DDDDDD',
                theme: {
                    fill: '#505053'
                }
            }
        },

        // scroll charts
        rangeSelector: {
            buttonTheme: {
                fill: '#505053',
                stroke: '#000000',
                style: {
                    color: '#CCC'
                },
                states: {
                    hover: {
                        fill: '#707073',
                        stroke: '#000000',
                        style: {
                            color: 'white'
                        }
                    },
                    select: {
                        fill: '#000003',
                        stroke: '#000000',
                        style: {
                            color: 'white'
                        }
                    }
                }
            },
            inputBoxBorderColor: '#505053',
            inputStyle: {
                backgroundColor: '#333',
                color: 'silver'
            },
            labelStyle: {
                color: 'silver'
            }
        },

        navigator: {
            handles: {
                backgroundColor: '#666',
                borderColor: '#AAA'
            },
            outlineColor: '#CCC',
            maskFill: 'rgba(255,255,255,0.1)',
            series: {
                color: '#7798BF',
                lineColor: '#A6C7ED'
            },
            xAxis: {
                gridLineColor: '#505053'
            }
        },

        scrollbar: {
            barBackgroundColor: '#808083',
            barBorderColor: '#808083',
            buttonArrowColor: '#CCC',
            buttonBackgroundColor: '#606063',
            buttonBorderColor: '#606063',
            rifleColor: '#FFF',
            trackBackgroundColor: '#404043',
            trackBorderColor: '#404043'
        },

        // special colors for some of the
        legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
        background2: '#505053',
        dataLabelsColor: '#B0B0B3',
        textColor: '#C0C0C0',
        contrastTextColor: '#F0F0F3',
        maskColor: 'rgba(255,255,255,0.3)'
    };

    // Apply the theme
    Highcharts.setOptions(Highcharts.theme);

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
            text: 'HISTORICAL STOCK DATA'
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

    Highcharts.theme = {
        colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066',
            '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
        chart: {
            backgroundColor: {
                linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
                stops: [
                    [0, '#343a40'],
                    [1, '#343a40']
                ]
            },
            style: {
                fontFamily: '\'Unica One\', sans-serif'
            },
            plotBorderColor: '#606063'
        },
        title: {
            style: {
                color: '#E0E0E3',
                textTransform: 'uppercase',
                fontSize: '20px'
            }
        },
        subtitle: {
            style: {
                color: '#E0E0E3',
                textTransform: 'uppercase'
            }
        },
        xAxis: {
            gridLineColor: '#707073',
            labels: {
                style: {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            minorGridLineColor: '#505053',
            tickColor: '#707073',
            title: {
                style: {
                    color: '#A0A0A3'

                }
            }
        },
        yAxis: {
            gridLineColor: '#707073',
            labels: {
                style: {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            minorGridLineColor: '#505053',
            tickColor: '#707073',
            tickWidth: 1,
            title: {
                style: {
                    color: '#A0A0A3'
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.85)',
            style: {
                color: '#F0F0F0'
            }
        },
        plotOptions: {
            series: {
                dataLabels: {
                    color: '#B0B0B3'
                },
                marker: {
                    lineColor: '#333'
                }
            },
            boxplot: {
                fillColor: '#505053'
            },
            candlestick: {
                lineColor: 'white'
            },
            errorbar: {
                color: 'white'
            }
        },
        legend: {
            itemStyle: {
                color: '#E0E0E3'
            },
            itemHoverStyle: {
                color: '#FFF'
            },
            itemHiddenStyle: {
                color: '#606063'
            }
        },
        credits: {
            style: {
                color: '#666'
            }
        },
        labels: {
            style: {
                color: '#707073'
            }
        },

        drilldown: {
            activeAxisLabelStyle: {
                color: '#F0F0F3'
            },
            activeDataLabelStyle: {
                color: '#F0F0F3'
            }
        },

        navigation: {
            buttonOptions: {
                symbolStroke: '#DDDDDD',
                theme: {
                    fill: '#505053'
                }
            }
        },

        // scroll charts
        rangeSelector: {
            buttonTheme: {
                fill: '#505053',
                stroke: '#000000',
                style: {
                    color: '#CCC'
                },
                states: {
                    hover: {
                        fill: '#707073',
                        stroke: '#000000',
                        style: {
                            color: 'white'
                        }
                    },
                    select: {
                        fill: '#000003',
                        stroke: '#000000',
                        style: {
                            color: 'white'
                        }
                    }
                }
            },
            inputBoxBorderColor: '#505053',
            inputStyle: {
                backgroundColor: '#333',
                color: 'silver'
            },
            labelStyle: {
                color: 'silver'
            }
        },

        navigator: {
            handles: {
                backgroundColor: '#666',
                borderColor: '#AAA'
            },
            outlineColor: '#CCC',
            maskFill: 'rgba(255,255,255,0.1)',
            series: {
                color: '#7798BF',
                lineColor: '#A6C7ED'
            },
            xAxis: {
                gridLineColor: '#505053'
            }
        },

        scrollbar: {
            barBackgroundColor: '#808083',
            barBorderColor: '#808083',
            buttonArrowColor: '#CCC',
            buttonBackgroundColor: '#606063',
            buttonBorderColor: '#606063',
            rifleColor: '#FFF',
            trackBackgroundColor: '#404043',
            trackBorderColor: '#404043'
        },

        // special colors for some of the
        legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
        background2: '#505053',
        dataLabelsColor: '#B0B0B3',
        textColor: '#C0C0C0',
        contrastTextColor: '#F0F0F3',
        maskColor: 'rgba(255,255,255,0.3)'
    };

    // Apply the theme
    Highcharts.setOptions(Highcharts.theme);


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
            text: 'CURRENT STOCK PRICES'
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
