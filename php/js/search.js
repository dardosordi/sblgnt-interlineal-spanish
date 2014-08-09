$(function() {
	var books = {'mt': 'Mt', 'mk': 'Mk', 'lk': 'Lk', 'jn': 'Jn', 'ac': 'Ac', 'ro': 'Ro', '1co': '1 Co'};

	if (!$(".result").length) {
		return;
	}

	var books_enabled = books;
	var selected = $('.books input[checked=checked]');

	if (selected.length) {
		books_enabled = {};
		selected.each(function(i, e) {
			var key = $(e).attr('value');
			books_enabled[key] = books[key];
		});
	}

	$(".result").after('<div id="chart"></div>');

	var categories = [];
	var series = [{
        name: 'Encontrados',
        data: [],
        dataLabels: {
            enabled: true
        }
    }];
	for (var x in books_enabled) {
		categories.push(books[x]);
		var count = 0;
		$('.book.'+x).each(function() {
			count += parseInt($(this).attr('data-count'));
		});
		series[0].data.push(count);
	}

    $('#chart').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Resultado'
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
                text: 'Ocurrencias',
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
            },
            series: {
                pointWidth: 100
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
