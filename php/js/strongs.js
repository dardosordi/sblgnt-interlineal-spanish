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
