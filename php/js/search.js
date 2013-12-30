$(function() {
	var books = {'mt': 'Mt', 'mk': 'Mk', 'lk': 'Lk', 'jn': 'Jn', 'ac': 'Ac', 'ro': 'Ro'};

	if (!$(".result").length) {
		return;
	}

	$(".result").after('<div id="chart"></div>');

	var categories = [];
	var series = [{name: 'Resultados', data: []}];
	for (var x in books) {
		categories.push(books[x]);
		series[0].data.push($('.book.'+x).length);
	}

    $('#chart').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Encontrados'
        },
        xAxis: {
            categories: categories,
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Versículos',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            valueSuffix: ''
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -100,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor: '#FFFFFF',
            shadow: true
        },
        credits: {
            enabled: false
        },
        series: series
    });

});
