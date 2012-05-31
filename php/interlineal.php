<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';

include 'books.php';
include 'breaks.php';

include 'translit.php';
include 'helpers.php';

include 'config.php';

$book = isset($_GET['book']) ? $_GET['book'] : null;
$chapter = isset($_GET['chapter']) ? $_GET['chapter'] : null;
$end_chapter = isset($_GET['endchapter']) ? $_GET['endchapter'] : $chapter;

$show_morph = isset($_GET['morph']) ? $_GET['morph'] : true;
$show_translit = isset($_GET['translit']) ? $_GET['translit'] : true;
$show_strongs = isset($_GET['strongs']) ? $_GET['strongs'] : true;
$show_spa = isset($_GET['spa']) ? $_GET['spa'] : true;
$show_greek = isset($_GET['greek']) ? $_GET['greek'] : true;

$params = $_GET;
unset($params['book']);
unset($params['chapter']);
unset($params['endchapter']);
$query_string = (!empty($params) ? '?' : '') . http_build_query($params);


$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';

$apparatus_path = dirname(__FILE__) . '/apparatus/';
$moprhdb_path = dirname(__FILE__) . '/morph/';
if ($use_logos) {
	$moprhdb_path = dirname(__FILE__) . '/lmorph/';
}


$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';
$apparatus_filename = $apparatus_path . $book . '.php';

ini_set('memory_limit', '128M');
$xml = simplexml_load_file($filename);

$morphdb = array();
include($morphdb_filename);

$apparatus = array();
include($apparatus_filename);

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
$current_word = 0;
$current_note = 0;

if (isset($breaks[$book][$current_chapter]['s'])) {
	$content .= $breaks[$book][$current_chapter]['s'];
}

$note_id = 1;
$verse_markers = array();
$notes = array();

foreach($interlineal as $S) {

	$morph = '';
	$strongs_number = '';
	$strongs_def = '';

	if ($S['v'] > 0) {
		if ($S['c'] != $current_chapter) {
			$current_note = 0;
			$current_word = 0;
			if (isset($breaks[$book][$current_chapter]['e'])) {
				$content .= $breaks[$book][$current_chapter]['e'];
			}

			$current_chapter = $S['c'];
			if (isset($breaks[$book][$current_chapter]['s'])) {
				$content .= $breaks[$book][$current_chapter]['s'];
			}

			$content .= '<span class="block">';
			if ($show_greek) {
				$content .= sprintf('<span id="c%d" class="chapter">%s</span>', $current_chapter, $current_chapter);
			}
			if ($show_translit && $show_spa) {
				$content .= '<span>&nbsp;</span>';
			}
			$content .= '</span> ';


		}

		if ($S['v'] != $current_verse) {
			$current_note = 0;
			$current_word = 0;
			$current_verse = $S['v'];
			if (isset($breaks[$book][$current_chapter][$current_verse])) {
				$content .= $breaks[$book][$current_chapter][$current_verse];
			}

			$content .= '<span class="block">';
			if ($show_strongs) {
				$content .= '<span class="strongs">&nbsp;</span>';
			}
			if ($show_morph) {
				$content .= '<span class="morph">&nbsp;</span>';
			}
			if ($show_greek) {
				$content .= sprintf('<span id="v%d" class="verse">%s</span>', $current_verse, $current_verse);
			}
			if ($show_translit) {
				$content .= '<span class="translit">&nbsp;</span>';
			}
			if ($show_spa) {
				$content .= '<span class="spa">&nbsp;</span>';
			}
			$content .= '</span> ';
		}
	}

	$greek = $S['s'];
	$clean = $S['k'];
	//$translit = translit($S['k'], true);
	$spa = $S['t'];

	$skip = $greek == '—';

	//$current_word = $S['w'];
	$morph = '-';
	$translit = '-';
	if ($current_chapter && $current_verse && !$skip) {
		if (isset($morphdb[$book][$current_chapter][$current_verse][$current_word])) {
			$morph = $morphdb[$book][$current_chapter][$current_verse][$current_word]['morph'];
			$strongs_number = $morphdb[$book][$current_chapter][$current_verse][$current_word]['strongs'];
			$translit = $morphdb[$book][$current_chapter][$current_verse][$current_word]['translit'];
		}

		if (isset($apparatus[$book][$current_chapter][$current_verse])) {
			$markers = array();
			if (preg_match('#[⸀⸁⸂⸄⸃⸅]#u', $greek, $markers)) {
				$marker = $markers[0];
				$open_note = preg_match('#[⸀⸁⸂⸄]#u', $greek);
				if ($open_note) {
					$note = $apparatus[$book][$current_chapter][$current_verse][$current_note];
					$verse_markers[$marker] = array($current_note, $note_id);
					$use_note_id = $note_id;
					$ref = "$book $current_chapter:$current_verse";
					$notes[$note_id] = '<div class="note" id="note'.$note_id.'"><strong>'.$ref.'</strong> ' . $note . ' <a href="#ref'.$note_id.'">^</a></div>';
					$current_note++;
					$note_id++;
				} else {
					$marker_map = array('⸃' => '⸂', '⸅' => '⸄');
					$open_marker = $marker_map[$marker];
					$use_note = $verse_markers[$open_marker][0];
					$use_note_id = $verse_markers[$open_marker][1];
					$note = $apparatus[$book][$current_chapter][$current_verse][$use_note];
				}

				$greek = preg_replace('#([⸀⸁⸂⸄⸃⸅])#u', '<sup id="ref'.$use_note_id.'"><a href="#note'.$use_note_id.'" title="'.h($note).'">\1</a></sup>', $greek);
			}
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
			h($strongs['greek'][$strongs_number]['lemma'] . ': '.$strongs['greek'][$strongs_number]['strongs_def']),
			$strongs_number
		);
	}

	$content .= '<span class="block word">';
	if ($show_strongs) {
		$content .= sprintf('<span class="strongs">%s</span>', $strongs_def);
	}
	if ($show_morph) {
		$content .= sprintf('<span class="morph" title="%s">%s</span>', $use_logos ? label_lmac($morph, $rmac) : label_rmac($morph, $rmac), $morph);
	}
	if ($show_greek) {
		$content .= sprintf('<span class="greek">%s</span>', $greek);
	}
	if ($show_translit) {
		$content .= sprintf('<span class="translit">%s</span>', $translit);
	}
	if ($show_spa) {
		$content .= sprintf('<span class="spa">%s</span>', $spa);
	}
	$content .= '</span> ';

	if ($current_chapter && $current_verse && !$skip) {
		$current_word++;
	}

}

if (isset($breaks[$book][$current_chapter]['e'])) {
	$content .= $breaks[$book][$current_chapter]['e'];
}


foreach ($notes as $note) {
	$content .= $note;
}

$nav = array();
if ($chapter > 1) {
	$prev = url_for($books[$book]['dir'], $chapter - 1) . $query_string;
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
	$next = url_for($books[$book]['dir'], $end_chapter + 1) . $query_string;
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
<script type="text/javascript" src="/js/interlineal.js"></script>
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

