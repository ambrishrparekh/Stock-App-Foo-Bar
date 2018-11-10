var datatext;
var stockSymbols = ['AAPL', 'FB'];
var outData = [];
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
                console.log(symbolString);

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

                  for (var k = 0; k < 60; k++)
                  {
                    var thing = chartInfo[k];
                    console.log(thing.date);
                    var month = thing.date.substring(4, 6);
                    var mint = parseInt(month);
                    mint--;
                    var correctMonth = mint.toString();
                    var datething = new Date(thing.date.substring(0, 4),correctMonth, thing.date.substring(6, 8), thing.minute.substring(0, 2), thing.minute.substring(3, 5), '00', '00');
                    var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);
                    outData[k] = {
                      x: millis,
                      y: thing.average
                    };
                  }

                  var jObj = JSON.parse(JSON.stringify(outData));

                  stockSeries[symbolIndex] = outData;
                }
            }
        }
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
  console.log(symbolString);

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

          series[symbolIndex].addPoint([millis, thing.average], true, true);
      }

      console.log("reloaded, at 1min");
  }, 60000);
}

// this function will be called when the user chooses to follow a new stocks
// make sure the user is not surpassing the max number of stocks we want to display
function followNewStock(newStockSymbol)
{
  // *** see if the stock is already in stockSymbols, if so, return
  stockSymbols.push(newStockSymbol);
  $.ajax({
      method: 'GET',
      async: false,
      url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=' + newStockSymbol + '&types=chart&range=1d&chartLast=60'
  })
  .done(function(results) {
      datatext = results;
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
      console.log(thing.date);
      var month = thing.date.substring(4, 6);
      var mint = parseInt(month);
      mint--;
      var correctMonth = mint.toString();
      var datething = new Date(thing.date.substring(0, 4),correctMonth, thing.date.substring(6, 8), thing.minute.substring(0, 2), thing.minute.substring(3, 5), '00', '00');
      var millis = parseInt(Date.parse(datething.toISOString())) - (480*60000);
      outData[k] = {
        x: millis,
        y: thing.average
      };
    }

    var jObj = JSON.parse(JSON.stringify(outData));

    myChart.addSeries({
      name: newStockSymbol,
      data: outData
    }, true); // true so the line will be redrawn
  }
}

// add initial stocks to the graph
for (var i = 0; i < stockSeries.length; i++)
{
  console.log("adding this series:");
  console.log(stockSeries[i]);
  myChart.addSeries({
    name: stockSymbols[i],
    data: stockSeries[i]
  }, false);
}
myChart.redraw();

followNewStock('GOOG');

// minute updates
updateChart();
