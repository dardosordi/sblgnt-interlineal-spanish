$(function() {
	var books = {'mt': 'Mt', 'mk': 'Mk', 'lk': 'Lk', 'jn': 'Jn', 'ac': 'Ac', 'ro': 'Ro', '1co': '1 Co'};


	$(".concordance").tablesorter(); 
	$(".concordance").after('<div id="chart"></div>');

	var word = $('.strongs-entry .greek:eq(0)').text();

	var categories = [];
	var series = [{
        name: word,
        data: [],
        dataLabels: {
            enabled: true
        }
    }];
	for (var x in books) {
		categories.push(books[x]);

		var count = 0;
		$('.book.'+x + ' a').each(function() {
			count += parseInt($(this).attr('data-count'));
		});
		series[0].data.push(count);
	}

    $('#chart').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Frecuencia de ' + word
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




	var checks = '';
	for (var book in books) {
		if ($('.book.'+book).length) {
			checks += '<label><input type="checkbox" checked="checked" name="'+book+'"> ' + books[book] + '</label> ';
		}
	}

	$('body').append('<div id="panel">'+checks+'</div>');
	$('#panel input').change(function() {
		var book = $(this).attr('name');

		$('.book.'+book).toggle();

		$('.concordance tr:gt(0)').hide();
		$('#panel input:checked').each(function(i, e) {
			var book = $(e).attr('name');
			console.log(book);
			$('.book.'+book).closest('tr').show();
		});

	});

	window.ref_cache = {};

	$('body').append('<div id="ref_tooltip" class="tooltip"></div>');

	var elems = $('.concordance td:nth-child(4) a');
	var strongs = $('.current').text();

	$('<div />').qtip(
	{
		content: ' ',
		position: {
			target: 'event',
			effect: false,
			viewport: $(window),
			adjust: {
				method: 'shift',
				y: 5
			}
		},
		show: {
			target: elems,
			delay: 100,
		},
		hide: {
			target: elems,
			delay: 200,
			fixed: true
		},
		style: {
			classes: 'qtip-shadow'
		},
		events: {
			show: function(event, api) {
				var target = $(event.originalEvent.target);

				if(target.length) {
					var href = $(target).attr('href');
					var title = $(target).text();
					var url = href.replace(/#.*/g, '');
					var ref = href.replace(/.*#/g, '');

					var morph = $(target).closest('td').prev().prev().text();

					if (ref_cache[url] && ref_cache[url][ref]) {
						api.set('content.text', window.ref_cache[url][ref]);
						$('.' + strongs + '.' + morph).addClass('selected');
						$('.' + strongs).addClass('highlight');
						$('.verse').html(title);
					} else {
						api.set('content.text', 'Cargando...');

						var verse_url = '/cache' + url.replace(/\.html/, ':' + ref.substr(1) + '.html');
						$.get(verse_url, function(response) {

							response = $(response);
							response.find('.strongs, .translit, .morph').remove();

							if (!window.ref_cache[url]) {
								window.ref_cache[url] = {};
							}

							$('.verse-text', response).each(function(i, e) {
								var verse = $('.verse', e).attr('id');
								window.ref_cache[url][verse] = $(e).html();
							});

							api.set('content.text', window.ref_cache[url][ref]);

							$('.' + strongs + '.' + morph).addClass('selected');
							$('.' + strongs).addClass('highlight');
							$('.verse').html(title);
						});
					}
				}
			}
		}
	});

});
