var datatext;
var stockSymbols;
var outData = [];

let chart = Highcharts.stockChart('container', {
    chart: {
        events: {
            // initial load for adding new series/ initial series will be seperatre function
            // load is the per minute update
            load: function() {
                var series = this.series;

                setInterval(function() {
                    $ajax.({
                        method: 'GET',
                        async: false,
                        url: 'https://api.iextrading.com/1.0/stock/market/batch?symbols=AAPL,FB&types=chart&range=1d&chartLast=1'
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
                }, 60000);
            }
        }
    }
});
