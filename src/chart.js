// TODO
// need to figure out how we can do something like this
// http://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/highcharts/members/series-addpoint-append-and-shift/
// or like this
// https://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/stock/demo/dynamic-update/
// so the update per minute doesn't have to redraw everything

// We should graph stocks that are close in price so that the graph looks better for demo purposes

var datatext;
var stockSymbols = ['AAPL', 'MSFT', 'AVGO'];
var countStocks = 2; // initialize this to however many stocks the user was viewing in their last session/default number of stocks
var stockSeries = [];

var myChart = Highcharts.stockChart('container', {
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
                  // console.log(chartInfo.length);
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
                    if (marAvg === -1 && (k-1) >= 0)
                    {
                      var prevThing = chartInfo[k-1]; // may cause out of bounds error
                      marAvg = prevThing.marketAverage;
                      if (marAvg === -1)
                      {
                        marAvg = prevThing.averag;
                      }
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

          series[symbolIndex].addPoint([millis, marAvg], true, true); // why doesnt animation work? -- animations happen when we do myChart.redraw()
      }

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
  console.log("FOLLOWING NEW STOCK: " + newStockSymbol);
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
      url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + newStockSymbol + '&types=chart&range=1d&chartLast=60'
  })
  .done(function(results) {
      datatext = results;
      console.log("follow results-------");
      console.log(results);
  })
  .fail(function() {
      console.log("error");
  });

  stockSymbols = Object.keys(datatext);
  // we don't really need this for loop because only one new stock should be added at a time

  for (symbolIndex = 0; symbolIndex < stockSymbols.length; symbolIndex++)
  {
    var stockName = stockSymbols[symbolIndex];
    console.log("in follow, stockName " + stockName);
    var allStockInfo = datatext[stockName];
    var chartInfo = allStockInfo.chart;
    console.log("in follow, chartInfo below ");
    console.log(chartInfo);
    var outData = [];
    console.log("chartInfo length: " + chartInfo.length);
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
      if (marAvg === -1 && (k-2) >= 0)
      {
        var prevThing = chartInfo[k-2]; // may cause out of bounds error
        marAvg = prevThing.marketAverage;
        console.log("looking at previous and setting marAvg to " + marAvg);
        if (marAvg === -1)
        {
          marAvg = prevThing.average;
          console.log("looking at previous and setting marAvg to " + marAvg);
        }
      }
      console.log("in follow, marAvg " + marAvg);
      if (marAvg === -1)
      {
        marAvg = null;
      }
      outData[k] = {
        x: millis,
        y: marAvg
      };
    }
    console.log("outData");
    console.log(outData);
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

stockSymbols = followNewStock('GOOG');
// stockSymbols = followNewStock('ALGN');
//stockSymbols = followNewStock('ADBE');
//stockSymbols = followNewStock('NFLX');
// stockSymbols = followNewStock('MSFT');
// stockSymbols = followNewStock('FB');
// stockSymbols = followNewStock('AMAT');

// console.log("stock symbols before unfollowing FB " + stockSymbols);
// stockSymbols = unfollowStock('FB');
// console.log("stock symbols after unfollowing FB " + stockSymbols);
// After unfollowing a stock, the stockSymbols array looks like this (empty spot for the stock deleted)
// AAPL,,GOOG,MSFT
// We may need to fix this!!!!!!

// minute updates
updateChart();
