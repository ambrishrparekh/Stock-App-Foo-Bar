var datatext;
var stockSymbols = ['AAPL', 'MSFT', 'AVGO'];
var countStocks = 3; // initialize this to however many stocks the user was viewing in their last session/default number of stocks
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
        enabled: true
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
