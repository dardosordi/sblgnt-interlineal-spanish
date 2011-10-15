<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';
include 'books.php';

include 'translit.php';
include 'helpers.php';


include 'concordance.php';


$id = isset($_GET['id']) ? $_GET['id'] : null;

$key = $id[0];
$lang = $key == 'G' ? 'greek' : 'hebbrew';
$number = substr($id, 1);

$title = sprintf('Strongs %s%d', $key, $number);
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

	$content .= '</dl>';


	if (!empty($concordance[$number])) {
		$content .= '<table class="concordance">
<tr>
	<th>Palabra</th>
	<th>Morfología</th>
	<th>Traducción</th>
	<th>Referencia</th>
</tr>';

		foreach ($concordance[$number] as $lemma => $words) {
			foreach($words as $word) {
				list($book, $chapter, $verse) = parse_ref($word['ref']);

				$url = url_for($books[$book]['dir'], $chapter) . '#v' . $verse;
				$content .= sprintf("\n\t<tr><td>%s</td><td><span title=\"%s\">%s</span></td><td>%s</td><td><a href=\"%s\">%s</a></td></tr>", 
					$word['word'],
					label_rmac($word['morph'], $rmac),
					$word['morph'],
					$word['spa'],
					$url,
					$word['ref']
				);
			}
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
	$prev = url_for('strongs', sprintf('%s%d', $key, $prev_number));
	$nav[] = array(
		'url' => $prev,
		'text' => sprintf('<span class="icon">&laquo;</span>'),
		'class' => 'prev',
	);
}

$currtent = url_for('strongs', sprintf('%s%d', $key, $number));
$nav[] = array(
	'text' => sprintf('%s%d', $key, $number),
	'class' => 'current',
);



if (isset($strongs[$lang][$next_number])) {
	$next = url_for('strongs', sprintf('%s%d', $key, $next_number));
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
