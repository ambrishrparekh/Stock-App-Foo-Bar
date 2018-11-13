// TODO
// need to figure out how we can do something like this
// http://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/highcharts/members/series-addpoint-append-and-shift/
// or like this
// https://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/stock/demo/dynamic-update/
// so the update per minute doesn't have to redraw everything

// We should graph stocks that are close in price so that the graph looks better for demo purposes

var datatext;
var stockSymbols = ['AAPL', 'AVGO', 'MSFT'];
var countStocks = 3; // initialize this to however many stocks the user was viewing in their last session/default number of stocks
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
                    url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + symbolString + '&types=chart&range=1d&chartLast=60',
                    success: function(results) {
                        datatext = results
                    }
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

                  for (var k = 0; k < 60; k++)
                  {
                    var thing = chartInfo[k];
                    var month = thing.date.substring(4, 6);
                    var mint = parseInt(month);
                    mint--;
                    var correctMonth = mint.toString();
                    var datething = new Date(thing.date.substring(0, 4),correctMonth, thing.date.substring(6, 8), thing.minute.substring(0, 2), thing.minute.substring(3, 5), '00', '00');
                    var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);
                    outData[k] = {
                      x: millis,
                      y: thing.marketAverage
                    };
                  }

                  var jObj = JSON.parse(JSON.stringify(outData));

                  stockSeries[symbolIndex] = outData;
                }
            }
        },
        type: 'line',
        zoomType: 'y'
    },
    yAxis: {
            scrollbar: {
                enabled: true,
                showFull: false
            },
            labels: {
            formatter: function () {
                    return (this.change > 0 ? ' + ' : '') + this.change + '%';
                }
            }
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
        enabled: true
    }
});

// update function, at 1 minute mark
function updateChart () {
  var series = myChart.series;

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
          url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + symbolString + '&types=chart&range=dynamic&chartLast=1',
          success: function(results) {
              datatext = results;
          }
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

          series[symbolIndex].addPoint([millis, thing.marketAverage], true, true); // why doesnt animation work? -- animations happen when we do myChart.redraw()
      }

      // NOTE: error occuring here
      var animOptions = Highcharts.AnimationOptionsObject;
      animOptions.duration = 1000;

      myChart.redraw(animOptions);

      console.log("reloaded, at 1min");
  }, 60000);
}

// this function will be called when the user chooses to follow a new stocks
// make sure the user is not surpassing the max number of stocks we want to display
function followNewStock(newStockSymbol)
{
  // TODO make sure that newStockSymbol is a real stock symbol available to us
  var copyStockSymbols = stockSymbols.slice();
  if (countStocks >= 5)
  {
    console.log("max number of stocks already reached when trying to add " + newStockSymbol);
    return copyStockSymbols;
  }

  // see if the stock is already in stockSymbols, if so, return

  // method 1 of looking for duplicate stocks (doesn't work)
  var alreadyFollowing = stockSymbols.includes(newStockSymbol);
  if (alreadyFollowing)
  {
    console.log("already following bool");
    return copyStockSymbols;
  }

  // method 2 of looking for duplicate stocks (doesn't work)
  for (var k = 0; k < stockSymbols.length; k++)
  {
    if (stockSymbols[k] === newStockSymbol)
    {
      console.log("already following for");
      return copyStockSymbols;
    }
  }

  // add the new stock if not already following
  copyStockSymbols.push(newStockSymbol);
  countStocks += 1;
  stockSymbols = copyStockSymbols;

  $.ajax({
      method: 'GET',
      async: false,
      url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + newStockSymbol + '&types=chart&range=1d&chartLast=60',
      success: function(results) {
          datatext = results;
      }
  })
  .fail(function() {
      console.log("error");
  });

  stockSymbols = Object.keys(datatext);
  // we don't really need this for loop because only one new stock should be added at a time

  for (symbolIndex = 0; symbolIndex < stockSymbols.length; symbolIndex++)
  {
    var stockName = stockSymbols[symbolIndex];
    var allStockInfo = datatext[stockName];
    var chartInfo = allStockInfo.chart;
    var outData = [];

    for (var k = 0; k < 60; k++)
    {
      var thing = chartInfo[k];
      var month = thing.date.substring(4, 6);
      var mint = parseInt(month);
      mint--;
      var correctMonth = mint.toString();
      var datething = new Date(thing.date.substring(0, 4),correctMonth, thing.date.substring(6, 8), thing.minute.substring(0, 2), thing.minute.substring(3, 5), '00', '00');
      var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);
      outData[k] = {
        x: millis,
        y: thing.marketAverage
      };
    }

    var jObj = JSON.parse(JSON.stringify(outData));

    myChart.addSeries({
      name: newStockSymbol,
      data: outData
    }, true); // true so the line will be redrawn
  }

  return copyStockSymbols;
}

function unfollowStock (stockName)
{
  var copyStockSymbols = stockSymbols.slice();
  if (copyStockSymbols.includes(stockName))
  {
    var stockIndex = copyStockSymbols.indexOf(stockName);
    delete copyStockSymbols[stockIndex];
    myChart.series[stockIndex].remove();
    countStocks -= 1;
  }
  else {
    console.log('stock not found, not removing');
  }
  return copyStockSymbols;
}

// add initial stocks to the graph
for (var i = 0; i < stockSeries.length; i++)
{
  myChart.addSeries({
    name: stockSymbols[i],
    data: stockSeries[i]
  }, false);
}
myChart.redraw();

// stockSymbols = followNewStock('GOOG');
// stockSymbols = followNewStock('ALGN');
//stockSymbols = followNewStock('ADBE');
// stockSymbols = followNewStock('NFLX');
// stockSymbols = followNewStock('MSFT');
// stockSymbols = followNewStock('FB');
// stockSymbols = followNewStock('AMAT');

console.log("stock symbols before unfollowing FB, AVGO, and GOOG " + stockSymbols);
// stockSymbols = unfollowStock('FB');
// stockSymbols = unfollowStock('AVGO');
// stockSymbols = unfollowStock('GOOG');
console.log("stock symbols after unfollowing FB, AVGO, and GOOG " + stockSymbols);
// After unfollowing a stock, the stockSymbols array looks like this (empty spot for the stock deleted)
// AAPL,,,MSFT
// We may need to fix this!!!!!!


// stockSymbols = followNewStock('DIA');
// stockSymbols = followNewStock('CRMT');
// stockSymbols = followNewStock('DWCH');

// minute updates
updateChart();
