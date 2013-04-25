$(function() {
	$(".concordance").tablesorter(); 

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
			delay: 100
		},
		hide: {
			target: elems,
			delay: 1000,
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

					var morph = $(target).parent().prev().prev().text();

					if (ref_cache[url]) {
						api.set('content.text', window.ref_cache[url][ref]);
						$('.' + strongs + '.' + morph).addClass('selected');
						$('.' + strongs).addClass('highlight');
						$('.verse').html(title);
					} else {
						api.set('content.text', 'Cargando...');

						$.get(url + '?strongs=0&morph=0&translit=0', function(response) {

							window.ref_cache[url] = {};

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
