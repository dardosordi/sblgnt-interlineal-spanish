<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';
include 'books.php';

include 'translit.php';
include 'helpers.php';


include 'concordance.php';


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
	$content .= sprintf('	<dt>Audio:</dt><dd><audio src="/ogg/grk/%04dg.ogg" class="player" onclick="this.play();"></audio></dd>', $number);

	$content .= '</dl>';


	if (!empty($concordance[$number])) {
		$content .= '<table class="concordance">
<tr>
	<th>Palabra</th>
	<th>Morfología</th>
	<th width="200">Traducción</th>
	<th>Referencias</th>
</tr>';


		$refs = array();
		$lines = array();

		foreach ($concordance[$number] as $lemma => $words) {
			foreach($words as $i => $word) {
				list($book, $chapter, $verse) = parse_ref($word['ref']);

				$url = url_for($books[$book]['dir'], $chapter) . '#v' . $verse;
				$ref = sprintf('<a href="%s" target="_blank">%s</a>', $url, ucwords($word['ref']));

				$key = $word['morph'].'|'.$lemma.'|'.$word['spa'];
				$key = strtolower($key);

				$refs[$key][] = $ref;
				$lines[$key] = $word;
			}
		}

		ksort($lines);
		foreach ($lines as $key => $word) {
			$content .= sprintf("\n\t<tr><td>%s</td><td><span title=\"%s\">%s</span></td><td>%s</td><td>%s</td></tr>", 
				$word['word'],
				label_rmac($word['morph'], $rmac),
				$word['morph'],
				$word['spa'],
				implode(', ', $refs[$key])
			);
		}

$content .= '</table>';

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
