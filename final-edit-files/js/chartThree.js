var datatext;
var stockSymbols = ['AAPL', 'MSFT', 'AVGO'];
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
