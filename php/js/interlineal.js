$(function() {
	$('body').append('<div id="panel"></div>');
	$('#panel').append('<label><input type="checkbox" name="strongs"> Strongs</label>');
	$('#panel').append(' - <label><input type="checkbox" name="morph"> Morphology</label>');
	$('#panel').append(' - <label><input type="checkbox" name="translit"> Transliteration</label>');
	$('#panel').append(' - <label><input type="checkbox" name="spa"> Spanish</label>');

	$('#panel').append(' - <a href="javascript:prev();">&lt;</a>');
	$('#panel').append(' <a href="javascript:next();">&gt;</a>');

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
		var morph = $(this).parent().find('.morph');
		var strongs = $(this).parent().find('.strongs a');
		var spa = $(this).parent().find('.spa');

		$(e).attr('title', spa.text() + ' [' + morph.attr('title') + '. G' + strongs.text() + ', ' + strongs.attr('title') + ']');
	});

	var matches = /G([0-9]+)/.exec(document.referrer);
	if (matches) {
		$('.strongs a').each(function (i, e) {
			if ($(e).text() == matches[1]) {
				$(e).parent().parent().addClass('highlight');
			}
		});

	}

	$('sup a, .note a').click(function(e) {
		e.stopPropagation();
		$('.note, sup').removeClass('highlight');
		var ref = $(this).attr('href');
		$(this).parent().addClass('highlight');
		$(ref).addClass('highlight');
	});

	var verseMatch = /#v([0-9]+)/.exec(window.location.hash);
	if (verseMatch) {
		selectVerse(verseMatch[1]);
	}

});

function selectVerse(n) {
	var id = '#v' + n;
	var verse = $(id);

	if (verse.length) {
		var selected = 	$('.selected').removeClass('selected');

		verse.parent().parent().addClass('selected');
		verse.attr('id', '_' + id.substr(1))
		window.location.hash = id;
		verse.attr('id', id.substr(1))

		$('html,body').animate({scrollTop: verse.offset().top - 100}, 'slow');
	}
}

function prev() {
	var verse = 2;
	var verseMatch = /#v([0-9]+)/.exec(window.location.hash);
	if (verseMatch) {
		verse = parseInt(verseMatch[1]);
	}

	while(--verse > 0) {
		if ($('#v' + verse).length) {
			break;
		}
	}

	selectVerse(verse);

}

function next() {
	var verse = 0;
	var verseMatch = /#v([0-9]+)/.exec(window.location.hash);
	if (verseMatch) {
		verse = parseInt(verseMatch[1]);
	}

	var maxVerse = parseInt($('.verse:last').attr('id').substr(1)) + 1;
	while(++verse < maxVerse) {
		if ($('#v' + verse).length) {
			break;
		}
	}

	selectVerse(verse);
}

