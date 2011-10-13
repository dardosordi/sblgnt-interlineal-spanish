<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';

include 'books.php';
include 'breaks.php';

include 'translit.php';
include 'helpers.php';


$book = isset($_GET['book']) ? $_GET['book'] : null;
$chapter = isset($_GET['chapter']) ? $_GET['chapter'] : null;
$end_chapter = isset($_GET['endchapter']) ? $_GET['endchapter'] : $chapter;


$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhgnt_path = dirname(dirname(__FILE__)) . '/morphgnt/';
$moprhdb_path = dirname(__FILE__) . '/morph/';


$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';


ini_set('memory_limit', '128M');
$xml = simplexml_load_file($filename);

$morphdb = array();
include($morphdb_filename);


$i = 0;
$open = array();

$current_book = 1;
$current_chapter = 0;
$current_verse = 0;
$current_word = -1;

$interlineal = array();

$text_title = array();
$is_title = false;

foreach($xml->xpath('//S') as $S) {
	if (isset($S['m'])) {
		$mark = (string)$S['m'];
		$matches = array();

		if (preg_match('/\\\\h/', $mark, $matches)) {
			$is_title = 1;
		}

		if (preg_match('/\\\\c ([0-9]+)/', $mark, $matches)) {
			$current_chapter = $matches[1];
			$current_word = -1;
			$is_title = 0;
		}

		if (preg_match('/\\\\v ([0-9]+)/', $mark, $matches)) {
			$current_verse = $matches[1];
			$current_word = -1;
			$is_title = 0;
		}
	}

	if ($is_title) {
		$text_title[] = (string)$S['s'];
	}

	++$current_word;

	if ($current_chapter > $end_chapter) {
		break;
	}

	if ($current_chapter < $chapter) {
		continue;
	}


	$interlineal[] = array(
		'c' => $current_chapter,
		'v' => $current_verse,
		'w' => $current_word,

		's' => (string)$S['s'],
		'k' => (string)$S['k'],
		't' => (string)$S['t'],
		'a' => (string)$S['a'],
	);
}

//$book_title = $books[$book]['title'];
$book_title = implode(' ', $text_title);
$chapter_range = $chapter == $end_chapter ? $chapter : "$chapter-$end_chapter";
$page_title = "$book_title $chapter_range - Interlineal Español";
$content = '';


$current_verse = 0;
$current_chapter = 0;

if (isset($breaks[$book][$current_chapter]['s'])) {
	$content .= $breaks[$book][$current_chapter]['s'];
}



foreach($interlineal as $S) {

	$morph = '';
	$strongs_number = '';
	$strongs_def = '';

	if ($S['v'] > 0) {

		if ($S['c'] != $current_chapter) {
			if (isset($breaks[$book][$current_chapter]['e'])) {
				$content .= $breaks[$book][$current_chapter]['e'];
			}

			$current_chapter = $S['c'];
			if (isset($breaks[$book][$current_chapter]['s'])) {
				$content .= $breaks[$book][$current_chapter]['s'];
			}
			$content .= sprintf('<span class="block aling-chapter"><span class="chapter">%s</span></span> ', $current_chapter);
		}

		if ($S['v'] != $current_verse) {
			$current_verse = $S['v'];
			if (isset($breaks[$book][$current_chapter][$current_verse])) {
				$content .= $breaks[$book][$current_chapter][$current_verse];
			}
			$content .= sprintf('<span class="block aling-verse"><span id="v%d" class="verse">%s</span></span> ', $current_verse, $current_verse);
		}
	}

	$greek = $S['s'];
	$clean = $S['k'];
	$translit = translit($S['k']);
	$spa = $S['t'];

	$current_word = $S['w'];
	if ($current_chapter && $current_verse) {
		$morph = '---';
		if (isset($morphdb[$book][$current_chapter][$current_verse][$current_word])) {
			$morph = $morphdb[$book][$current_chapter][$current_verse][$current_word]['morph'];
			$strongs_number = $morphdb[$book][$current_chapter][$current_verse][$current_word]['strongs'];
		}
	}



	if (empty($spa)) {
		$spa = '-';
	}

	if (isset($breaks[$book][$current_chapter]["$current_verse.$current_word"])) {
		$content .= $breaks[$book][$current_chapter]["$current_verse.$current_word"];
	}

	if ($strongs_number) {
		$strongs_def = sprintf('<a href="/strongs/G%d.html" title="%s">%d</a>',
			$strongs_number,
			h($strongs['greek'][$strongs_number]['strongs_def']),
			$strongs_number
		);
	}

	$content .= sprintf('<span class="block word">
		<span class="strongs">%s</span>
		<span class="morph" title="%s">%s</span>
		<span class="greek">%s</span>
		<span class="translit">%s</span>
		<span class="spa">%s</span>
	</span> ', $strongs_def, label_rmac($morph, $rmac), $morph, $greek, $translit, $spa);


}

if (isset($breaks[$book][$current_chapter]['e'])) {
	$content .= $breaks[$book][$current_chapter]['e'];
}

if (isset($breaks[$book][$current_chapter]['e'])) {
	$content .= $breaks[$book][$current_chapter]['e'];
}


$nav = array();
if ($chapter > 1) {
	$prev = url_for($books[$book]['dir'], $chapter - 1);
	$nav[] = array(
		'url' => $prev,
		'text' => sprintf('<span class="icon">&laquo;</span>'),
		'class' => 'prev',
	);
}

$currtent = url_for($books[$book]['dir'], $chapter - 1);
$nav[] = array(
//	'url' => $currtent,
	'text' => sprintf('%s %s', $book_title, $chapter == $end_chapter ? $chapter : "$chapter-$end_chapter"),
	'class' => 'current',
);



if ($end_chapter < $books[$book]['chapters']) {
	$next = url_for($books[$book]['dir'], $end_chapter + 1);
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

<h1><?= "$book_title" ?></h1>
<div class="interlineal">
<? echo $content; ?>
</div>
</div>
</body>
</html>

