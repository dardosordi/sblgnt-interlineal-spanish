<?php
/*
@TODO
- operador proximidad
- listas para strongs
- regexp para texto
*/
error_reporting(E_ALL);
set_time_limit(0);
include 'rmac.php';
include 'strongs_greek.php';
include 'books.php';
include 'breaks.php';
include 'translit.php';
include 'helpers.php';
include 'config.php';

include 'search_utils.php';


ini_set('memory_limit', '256M');
$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhdb_path = dirname(__FILE__) . '/morph/';
$index_path   = dirname(__FILE__) . '/rindex/';
if ($use_logos) {
	$moprhdb_path = dirname(__FILE__) . '/lmorph/';
	$index_path   = dirname(__FILE__) . '/lindex/';
}
$is_cli = PHP_SAPI == 'cli';
$query = '';
$selected_books = array();
if ($is_cli) {
	if (empty($argv[1])) {
		echo "Usage: search.php <query>\n";
		exit(1);
	}
	$query = $argv[1];
} else {
	if (!empty($_REQUEST['q'])) {
		$query = $_REQUEST['q'];
	}
	if (!empty($_REQUEST['books'])) {
		$selected_books = $_REQUEST['books'];
	}
	$show_morph = isset($_GET['morph']) ? $_GET['morph'] : true;
	$show_translit = isset($_GET['translit']) ? $_GET['translit'] : false;
	$show_strongs = isset($_GET['strongs']) ? $_GET['strongs'] : true;
	$show_spa = isset($_GET['spa']) ? $_GET['spa'] : true;
	$show_greek = isset($_GET['greek']) ? $_GET['greek'] : true;
}

$parsed_query = parse_query($query);
if (empty($parsed_query)) {
    die("Invalid query");
}

$found = array();
$available_books = array();

$total_found = 0;
$total_verses = 0;

foreach($books as $book => $book_data) {
	$index_filename = $index_path . $book . '.php';
	$index_filename_js = $index_path . $book . '.json';
	$index_filename_ser = $index_path . $book . '.ser';

	if (!file_exists($index_filename)) {
		continue;
	}

	$available_books[] = $book;
	if (empty($query)) {
		continue;
	}

	if (!empty($selected_books) && !in_array($book, $selected_books)) {
		continue;
	}

	$index = array();
	$index[$book] = json_decode(file_get_contents($index_filename_js), true);

	foreach($index[$book] as $c => $chapter) {
		foreach ($chapter as $v => $verse) {
			if ($count = match_verse($verse, $parsed_query)) {
				if ($is_cli) {
					echo "$book $c:$v x$count\n";
				}
				$found["$book $c:$v"] = array($count, $verse);
				$total_found += $count;
				++$total_verses;
			}
		}
	}
}

if ($is_cli) {
	exit(0);
}

$title = $page_title = 'Buscar';

$content = '';

if (!empty($query)) {
	$content .= sprintf('<div class="result">Se encontraron <b>%d</b> ocurrencias en <b>%d</b> versículos.</div>', $total_found, $total_verses);
}

$content .= '<div class="interlineal">';
foreach($found as $ref => $item) {
	list($count, $verse_data) = $item;

	list($book, $chapter, $verse) = parse_ref($ref);
	$url = url_for($books[$book]['dir'], $chapter) . '#v' . $verse;
	$content .= sprintf('<div class="search-result book %s" data-count="%d">', $book, $count);
	$content .= '<span class="verse-text">';
	$content .= '<span class="block">';
	if ($show_strongs) {
		$content .= '<span class="strongs">&nbsp;</span>';
	}
	if ($show_morph) {
		$content .= '<span class="morph">&nbsp;</span>';
	}
	if ($show_greek) {
		$content .= sprintf('<span class="verse"><a href="%s" target="_blank">%s</a> %s</span>', $url, ucfirst($ref), $count > 1 ? " x$count" : '');
	}
	if ($show_translit) {
		$content .= '<span class="translit">&nbsp;</span>';
	}
	if ($show_spa) {
		$content .= '<span class="spa">&nbsp;</span>';
	}
	$content .= '</span> ';
	$content .= '</span> ';
	foreach ($verse_data as $i => $word) {
		$strongs_number = substr($word['strong'], 1);
		$morph = $word['morph'];
		$spa = $word['spa'];
		$translit = $word['translit'];
		$greek = $word['greek'];
		if ($strongs_number) {
			$strongs_def = sprintf('<a href="/strongs/G%d.html" title="%s">%d</a>',
				$strongs_number,
				h($strongs['greek'][$strongs_number]['lemma'] . ': '.$strongs['greek'][$strongs_number]['strongs_def']),
				$strongs_number
			);
		}
		$extra_class = $strongs_number ? ' G'.$strongs_number . ' ' . $morph : '';
		if (!empty($word['match'])) {
			$extra_class .= ' highlight';
		}
		$content .= sprintf('<span class="block word%s">', $extra_class);
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
	}
	$content .= '</div>';
}

$content .= '</div>';
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title><? echo $page_title; ?></title>
<link rel="stylesheet" type="text/css" href="/css/style.css"/>
<script type="text/javascript" src="/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/js/interlineal.js"></script>
<script type="text/javascript" src="/js/highcharts.min.js"></script>
<script type="text/javascript" src="/js/search.js"></script>
</head>
<body>
<div id="content">
<h1><?= "$title" ?></h1>
<div class="strongs-page">
<form action="<?= $_SERVER['PHP_SELF']?>" style="text-align:center;margin:0 0 4em;">
	<input type="text" name="q" value="<?= h($query) ?>" style="width:600px;">
	<button type="submit">Buscar</button>
	<div class="books">
	<? foreach($available_books as $book):
		$book_data = $books[$book];
	?>
		<label>
			<input name="books[]" type="checkbox" value="<?= $book ?>" <?= in_array($book, $selected_books) ? 'checked="checked"' : ''; ?>>
			<?= $book_data['title'] ?>
		</label>
	<? endforeach; ?>
	</div>
</form>
<? echo $content; ?>
</div>
</div>
</body>
</html>
