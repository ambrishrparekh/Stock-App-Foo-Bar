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

Highcharts.theme = {
    colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066',
        '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
    chart: {
        backgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
            stops: [
                [0, '#2a2a2b'],
                [1, '#3e3e40']
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
