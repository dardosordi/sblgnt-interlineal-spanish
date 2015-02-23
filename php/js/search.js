$(function() {
	var books = {'mt': 'Mt', 'mk': 'Mk', 'lk': 'Lk', 'jn': 'Jn', 'ac': 'Ac', 'ro': 'Ro', '1co': '1 Co'};

	if (!$(".result").length) {
		return;
	}

    $(".result").after('<div id="charts" style="margin-bottom: 4em; overflow: auto;"></div>');

    $("#charts").append('<div id="chart1" style="width:50%;float:left;"></div>');

	var books_enabled = books;
	var selected = $('.books input[checked=checked]');

	if (selected.length) {
		books_enabled = {};
		selected.each(function(i, e) {
			var key = $(e).attr('value');
			books_enabled[key] = books[key];
		});
	}

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

    $('#chart1').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Libros'
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
                pointWidth: 50
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


    $("#charts").append('<div id="chart2" style="width:50%;float:left;"></div>');
    var categories2 = [];
	var series2 = [{
        name: 'Strongs',
        data: [],
        dataLabels: {
            enabled: true
        }
    }];

    $('.highlight').each(function(i, e) {
        var strongs = 'G' + $('.strongs', e).text();
        if (categories2.indexOf(strongs) == -1) {
            categories2.push(strongs);
        }
    });

    categories2.sort(function(a, b) {
        return parseInt(a.substring(1)) > parseInt(b.substring(1));
    });

    $(categories2).each(function(i, e) {
        series2[0].data.push($('.highlight.'+e).length);
    });

    $('#chart2').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Strongs'
        },
        xAxis: {
            categories: categories2,
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
                pointWidth: 50
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
        series: series2
    });

});
