<?php

error_reporting(E_ALL);

include 'strongs_greek.php';

include 'books.php';
include 'breaks.php';

include 'translit.php';
include 'helpers.php';


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


$xml_path = dirname(dirname(__FILE__)) . '/lexham/';

$filename = $xml_path . $books[$book]['xml'];


ini_set('memory_limit', '128M');
$xml = simplexml_load_file($filename);

$i = 0;
$open = array();

$current_book = 1;
$current_chapter = 1;
$current_verse = 1;
$current_word = -1;

$interlineal = array();

$text_title = array();
$is_title = false;

$content = '';
$notes = '';

$pre_title = '';
foreach($xml as $entry) {
	$type = $entry->getName();
	if ($type == 'line') {
		set_chapter($entry, $current_chapter);
	}

	if ($type == 'book_title') {
		$text_title = array($entry[0]);
		continue;
	}

	if ($type == 'title') {
		$pre_title .= '<h4>' . $entry[0] . '</h4>';
		continue;
	}

	if ($current_chapter < $chapter) {
		$pre_title = '';
		continue;
	}

	if ($current_chapter > $end_chapter) {
		break;
	}

	if ($pre_title) {
		$content .= $pre_title;
		$pre_title = '';
	}


	switch ($type) {
		case 'title':
			$content .= '<h4>' . $entry[0] . '</h4>';
			break;

		case 'note':
			$id = $entry['id'];
			$notes .= '<div class="note" id="note'.$id.'"><strong>'.$id.'</strong> ' . $entry[0] . ' <a href="#ref'.$id.'">^</a></div>';
			$notes .= "\n";
			break;

		case 'line':
			$content .= '<div class="line">';
			$content .= "\n";
			$content .= format_line($entry);
			$content .= '</div>';
			break;
			
		default:
	}

	$content .= "\n";
}
$content .= $notes;

function set_chapter(SimpleXmlElement $line, &$current_chapter) {
	if ($line->chapter) {
		$current_chapter = (int) $line->chapter[0];
	}
}

function format_line(SimpleXmlElement $line) {
	$content = '';

	$i = 0;
	foreach($line as $x => $entry) {
		$type = $entry->getName();
		switch ($type) {
			case 'chapter':
				$current_chapter = (int) $entry[0];
				$content .= '<span class="block">';
				$content .= sprintf('<span id="c%d" class="chapter">%s</span>', $current_chapter, $current_chapter);
				$content .= '<span>&nbsp;</span>';
				$content .= '<span>&nbsp;</span>';
				$content .= '<span>&nbsp;</span>';
				$content .= '</span> ';
				break;

			case 'verse':
				$current_verse = (int) $entry[0];
				if ($current_verse > 1) {
				$content .= '<span class="block">';
				$content .= sprintf('<span id="v%d" class="verse">%s</span>', $current_verse, $current_verse);
				$content .= '<span class="greek">&nbsp;</span>';
				$content .= '<span class="strongs">&nbsp;</span>';
				$content .= '<span class="morph">&nbsp;</span>';
				$content .= '</span> ';
				}
				break;

			case 'word':
				$content .= '<span class="block reverse">';
				$spa = (string) $entry[0];
				$note = '';
				if ($entry->sup) {
					$id = $entry->sup[0];
					$note = '<sup id="ref'.$id.'"><a href="#note'.$id.'">'.$id.'</a></sup>';
				}

				if (preg_match('#_#', $spa)) {
					$spa = '<em>' . str_replace('_', ' ', $spa) . '</em>';
				}

				$content .= sprintf('<span class="spa">%s</span>', $spa . $note);
				$content .= format_greek($entry);
				$content .= '</span> ';
				break;

			case 'sup':
				break;

			default: $content .= 'UNKNOWN: ' . $type;
		}
		$i++;
		$content .= "\n";
	}

	return $content;
}

function format_greek(SimpleXmlElement $word) {
	global $strongs;

	$content = '<span class="aligned">';

	$is_group = count($word->greek) > 1;

	if ($is_group) {
		$content .= '<span class="greek-word">';
		$content .= '<span class="group-open">‹</span>';
		$content .= '<span class="translit">&nbsp;</span>';
		$content .= '<span class="morph">&nbsp;</span>';
		$content .= '<span class="strongs">&nbsp;</span>';
		$content .= '</span>';
	}

	foreach($word as $entry) {
		$type = $entry->getName();
		switch ($type) {
			case 'skip':
				$content .= '<span class="greek-word">';
				$content .= '<span class="skip">*</span>';
				$content .= '<span class="translit">&nbsp;</span>';
				$content .= '<span class="morph">&nbsp;</span>';
				$content .= '<span class="strongs">&nbsp;</span>';
				$content .= '</span>';
				break;

			case 'right':
				$content .= '<span class="greek-word">';
				$content .= '<span class="arrow-right">→</span>';
				$content .= '<span class="translit">&nbsp;</span>';
				$content .= '<span class="morph">&nbsp;</span>';
				$content .= '<span class="strongs">&nbsp;</span>';
				$content .= '</span>';
				break;

			case 'left':
				$content .= '<span class="greek-word">';
				$content .= '<span class="arrow-left">←</span>';
				$content .= '<span class="translit">&nbsp;</span>';
				$content .= '<span class="morph">&nbsp;</span>';
				$content .= '<span class="strongs">&nbsp;</span>';
				$content .= '</span>';
				break;

			case 'pointer':
				$direction = $entry['direction'];
				$arrow = $direction == 'left' ? '◂' : '▸';
				$arrow .= '<span class="number">' . $entry['number'] . '</span>';
				$content .= '<span class="greek-word">';
				$content .= '<span class="pointer-'.$direction.'">'.$arrow.'</span>';
				$content .= '<span class="translit">&nbsp;</span>';
				$content .= '<span class="morph">&nbsp;</span>';
				$content .= '<span class="strongs">&nbsp;</span>';
				$content .= '</span>';
				break;

			case 'greek':
				$greek = (string) $entry[0];
				$number = (string) $entry['number'];
				$strongs_number = (int)$entry['strongs'];
				$morph = (string) $entry['morph'];
				$translit = (string) $entry['translit'];

				if (isset($strongs['greek'][$strongs_number])) {
					$strongs_def = sprintf('<a href="/strongs/G%d.html" title="%s">%d</a>',
						$strongs_number,
						h($strongs['greek'][$strongs_number]['strongs_def']),
						$strongs_number
					);
				} else {
					$strongs_def = 'MISSING: ' . $strongs_number;
				}

				$content .= '<span class="greek-word">';
				$content .= '<span class="greek">';
				$content .= $greek;
				$content .= '</span>';
				$content .= '<sub>' . $number . '</sub>' ;

				$content .= '<span class="translit">' . $translit . '</span>';
				//$content .= '<span class="morph" title="">' . $morph . '</span>';
				$content .= sprintf('<span class="morph" title="%s">%s</span>', label_lmac($morph), $morph);
				$content .= '<span class="strongs">' . $strongs_def . '</span>';
				$content .= '</span>';
				break;

			case 'sup':
				break;

			default: '';
				$content .= 'NODE: '. $type . ' ';
		}
	}

	if ($is_group) {
		$content .= '<span class="greek-word">';
		$content .= '<span class="group-close">›</span>';
		$content .= '<span class="translit">&nbsp;</span>';
		$content .= '<span class="morph">&nbsp;</span>';
		$content .= '<span class="strongs">&nbsp;</span>';
		$content .= '</span>';
	}

	$content .= '</span>';

	return $content;
}

//$book_title = $books[$book]['title'];
$book_title = implode(' ', $text_title);
$chapter_range = $chapter == $end_chapter ? $chapter : "$chapter-$end_chapter";
$page_title = "$book_title $chapter_range - Interlineal Español";

$nav = array();
if ($chapter > 1) {
	$prev = url_for('lexham', $books[$book]['dir'], $chapter - 1) . $query_string;
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
	$next = url_for('lexham', $books[$book]['dir'], $end_chapter + 1) . $query_string;
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

