$(function() {
	$('body').append('<div id="panel"></div>');
	$('#panel').append('<label><input type="checkbox" name="strongs"> Strongs</label>');
	$('#panel').append(' - <label><input type="checkbox" name="morph"> Morphology</label>');
	$('#panel').append(' - <label><input type="checkbox" name="translit"> Transliteration</label>');
	$('#panel').append(' - <label><input type="checkbox" name="spa"> Spanish</label>');

	if (!window.location.href.match(/strongs=0/)) {
		$('#panel input[name=strongs]').attr('checked', true);
	}

	if (!window.location.href.match(/morph=0/)) {
		$('#panel input[name=morph]').attr('checked', true);
	}

	if (!window.location.href.match(/translit=0/)) {
		$('#panel input[name=translit]').attr('checked', true);
	}

	if (!window.location.href.match(/spa=0/)) {
		$('#panel input[name=spa]').attr('checked', true);
	}

	$('#panel input').change(function () {
		var sel = '.' + this.name;

		if ($(this).attr('checked')) {
			$(sel).show();
		} else {
			$(sel).hide();
		}
	});

	$('.greek').click(function (e) {
		if (!e.ctrlKey) {
			window.open($(this).parent().find('.strongs a').attr('href'));
		}

		var strongs = $(this).parent().find('.strongs a').text();
		$('.strongs a').each(function (i, e) {
			if ($(e).text() == strongs) {
				$(e).parent().parent().addClass('marked');
			}
		});

	});

	$('.greek').each(function (i, e) {
		var morph = $(this).parent().find('.morph').text();
		var strongs = $(this).parent().find('.strongs a').text();

		$(e).attr('title', morph + ' : ' + strongs);
	});

	var matches = /G([0-9]+)/.exec(document.referrer);
	if (matches.length) {
		$('.strongs a').each(function (i, e) {
			if ($(e).text() == matches[1]) {
				$(e).parent().parent().addClass('highlight');
			}
		});

	}

});
