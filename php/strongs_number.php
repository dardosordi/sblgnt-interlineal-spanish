<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';
include 'books.php';

include 'translit.php';
include 'helpers.php';

include 'lexicon_index.php';

include 'config.php';

if ($use_logos) {
	include 'lconcordance.php';
} else {
	include 'concordance.php';
}



$id = isset($_GET['id']) ? $_GET['id'] : null;

$langKey = $id[0];
$lang = $langKey == 'G' ? 'greek' : 'hebbrew';
$number = substr($id, 1);

$title = sprintf('Strongs %s%d', $langKey, $number);
$page_title = $title;
$content = '';


if (isset($strongs[$lang][$number])) {

	$data = $strongs[$lang][$number];

	$translit = translit($data['lemma'], true);
	$page_title .= sprintf(' - %s (%s)', $data['lemma'], $translit);

	$content .= '<dl class="strongs-entry">';
	$content .= sprintf('	<dt>Lema:</dt><dd><span class="greek">%s</span> (%s)</dd>', $data['lemma'], $translit);
	$content .= sprintf('	<dt>Definición corta:</dt><dd>%s</dd>', format_strongs($data['kjv_def']));
	$content .= sprintf('	<dt>Definición:</dt><dd>%s</dd>', format_strongs($data['strongs_def']));
	$content .= sprintf('	<dt>Derivación:</dt><dd>%s</dd>', isset($data['derivation']) ? format_strongs($data['derivation']) : 'Palabra raíz');

	$lexicon['perseus'] = sprintf('http://www.perseus.tufts.edu/hopper/morph?l=%s&la=greek', urlencode($data['lemma']));
	$lexicon['studybible'] = sprintf('http://studybible.info/strongs/%s%s', strtoupper($langKey), $number);
	$lexicon['blueletterbible'] = sprintf('http://www.blueletterbible.org/lang/lexicon/lexicon.cfm?Strongs=%s%s', strtoupper($langKey), $number);
	$lexicon['biblos'] = sprintf('http://concordances.org/%s/%s.htm', $lang, $number);
	$lexicon['katabiblon'] = sprintf('http://lexicon.katabiblon.com/index.php?lemma=%s', urlencode($data['lemma']));
	$lexicon['greek-dictionary'] = sprintf('http://www.teknia.com/greek-dictionary/%s', urlencode($translit));
	$lexicon['studylight'] = sprintf('http://new.studylight.org/lex/grk/gwview.cgi?n=%s', $number);
	$lexicon['biblestudytools'] = sprintf('http://www.biblestudytools.net/Lexicons/Greek/grk.cgi?search=%s&version=nas', $number);
	$lexicon['logeion'] = sprintf('http://logeion.uchicago.edu/index.html#%s', urlencode($data['lemma']));
	$lexicon['lmpg'] = sprintf('http://dge.cchs.csic.es/lmpg/%s', urlencode($data['lemma']));

	$lex_url = 'http://biblestudyaids.net/nt/a&g/';
	$lex_url = '/lexicon/';
	$lexicon['DAG'] = $lex_url . $lexicon_index[$number];


	$content .= sprintf('	<dt>Lexicos:</dt><dd><a href="%s" target="_blank">perseus</a>, <a href="%s" target="_blank">studybible.info</a>, <a href="%s" target="_blank">blueletterbible.org</a>, <a href="%s" target="_blank">katabiblon.com</a>, <a href="%s" target="_blank">biblos.com</a>, <a href="%s" target="_blank">greek-dictionary.net</a>, <a href="%s" target="_blank">studylight.org</a>, <a href="%s" target="_blank">biblestudytools.net</a>, <a href="%s" target="_blank">logeion</a>, <a href="%s" target="_blank">lmpg</a>, <a href="%s" target="_blank">DAG</a></dd>', 
		h($lexicon['perseus']), h($lexicon['studybible']), h($lexicon['blueletterbible']), h($lexicon['katabiblon']), h($lexicon['biblos']), h($lexicon['greek-dictionary']), h($lexicon['studylight']), h($lexicon['biblestudytools']), h($lexicon['logeion']), h($lexicon['lmpg']), h($lexicon['DAG']));

	$content .= sprintf('	<dt>Audio:</dt><dd><audio src="/ogg/grk/%04dg.ogg" class="player" onclick="this.play();"></audio></dd>', $number);


	$content .= '</dl>';


	if (!empty($concordance[$number])) {
		$content .= '<table class="concordance">
<thead>
<tr>
	<th>Palabra</th>
	<th>Morfología</th>
	<th width="200">Traducción</th>
	<th>Referencias</th>
</tr>
</thead>
<tbody>
';


		$refs = array();
		$lines = array();

		foreach ($concordance[$number] as $lemma => $words) {
			foreach($words as $i => $word) {
				list($book, $chapter, $verse) = parse_ref($word['ref']);

				$url = url_for($books[$book]['dir'], $chapter) . '#v' . $verse;
				$ref = sprintf('<a href="%s" target="_blank">%s</a>', $url, ucwords($word['ref']));

				$key = $word['morph'].'|'.$word['spa'];
				$key = strtolower($key);

				$refs[$key][] = $ref;
				$lines[$key] = $word;
			}
		}

		ksort($lines);
		foreach ($lines as $key => $word) {
			$content .= sprintf("\n\t<tr><td>%s</td><td><span title=\"%s\">%s</span></td><td>%s</td><td>%s</td></tr>", 
				sprintf('<a href="http://lexicon.katabiblon.com/index.php?lemma=%s" target="_blank">%s</a>', $word['word'], $word['word']),
				$use_logos ? label_lmac($word['morph'], $rmac) : label_rmac($word['morph'], $rmac),
				$word['morph'],
				$word['spa'],
				implode(', ', $refs[$key])
			);
		}

$content .= '<tbody>
</table>';

	}

	$prev_number = $number;
	while(--$prev_number > 1) {
		if (isset($strongs[$lang][$prev_number])) {
			break;
		}	
	}

	$next_number = $number;
	while(++$next_number && $next_number < 5624) {
		if (isset($strongs[$lang][$next_number])) {
			break;
		}	
	}

}



$nav = array();

if (isset($strongs[$lang][$prev_number])) {
	$prev = url_for('strongs', sprintf('%s%d', $langKey, $prev_number));
	$nav[] = array(
		'url' => $prev,
		'text' => sprintf('<span class="icon">&laquo;</span>'),
		'class' => 'prev',
	);
}

$currtent = url_for('strongs', sprintf('%s%d', $langKey, $number));
$nav[] = array(
	'text' => sprintf('%s%d', $langKey, $number),
	'class' => 'current',
);



if (isset($strongs[$lang][$next_number])) {
	$next = url_for('strongs', sprintf('%s%d', $langKey, $next_number));
	$nav[] = array(
		'url' => $next,
		'text' => sprintf('<span class="icon">&raquo;</span>'),
		'class' => 'next',
	);
}

$nav = get_menu($nav);

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title><? echo $page_title; ?></title>
<link rel="stylesheet" type="text/css" href="/css/style.css"/>
<script type="text/javascript" src="/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="/js/strongs.js"></script>
<? if (!empty($prev)): ?><link rel="prev" href="<?= h($prev) ?>">
<? endif ?>
<? if (!empty($next)): ?><link rel="next" href="<?= h($next) ?>">
<? endif ?>
</head>
<body>
<div id="content">
<div id="nav"><ul><?= $nav ?></ul></div>

<h1><?= "$title" ?></h1>
<div class="strongs-page">
<? echo $content; ?>
</div>
</div>
</body>
</html>
