<?php
/*
@TODO
- implementar matcheo morph
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
include 'parsetok.php';
ini_set('memory_limit', '256M');
$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhdb_path = dirname(__FILE__) . '/morph/';
if ($use_logos) {
	$moprhdb_path = dirname(__FILE__) . '/lmorph/';
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
$found = array();
$available_books = array();
foreach($books as $book => $book_data) {
$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';
if (!file_exists($filename)) {
	continue;
}
$available_books[] = $book;
if (empty($query)) {
	continue;
}
if (!empty($selected_books) && !in_array($book, $selected_books)) {
	continue;
}
$xml = simplexml_load_file($filename);
$morphdb = array();
include($morphdb_filename);
$current_book = 1;
$current_chapter = 0;
$current_verse = 0;
$current_word = -1;
$concordance = array();
$verse_data = array();
foreach($xml->xpath('//S') as $S) {
	if (isset($S['m'])) {
		$mark = (string)$S['m'];
		$matches = array();
		if (preg_match('/\\\\h/', $mark, $matches)) {
			$is_title = 1;
		}
		if (preg_match('/\\\\c ([0-9]+)/', $mark, $matches)) {
			if (!empty($verse_data)) {
				if (match_verse($verse_data, $parsed_query)) {
					if ($is_cli) {
						echo "$book $current_chapter:$current_verse\n";
					}
					$found["$book $current_chapter:$current_verse"] = $verse_data;
				}
			}
			$current_chapter = $matches[1];
			$current_word = -1;
			$is_title = 0;
			$verse_data = array();
		}
		if (preg_match('/\\\\v ([0-9]+)/', $mark, $matches)) {
			if (!empty($verse_data)) {
				if (match_verse($verse_data, $parsed_query)) {
					if ($is_cli) {
						echo "$book $current_chapter:$current_verse\n";
					}
					$found["$book $current_chapter:$current_verse"] = $verse_data;
				}
			}
			$current_verse = $matches[1];
			$current_word = -1;
			$is_title = 0;
			$verse_data = array();
			if ($is_cli) {
				echo "$book $current_chapter:$current_verse\r";
			}
		}
	}
	++$current_word;
	if (!isset($S['f']) || substr((string)$S['f'], -1, 1) == "0") {
		//echo "//MISSING NUMBRER: $book $current_chapter:$current_verse\n";
		//file_put_contents('php://stderr', "//MISSING NUMBRER: $book $current_chapter:$current_verse.$current_word $text\n");
		continue;
	}
	$strongs_number = null;
	$morph = null;
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
	if ($strongs_number) {
		$s = (string)$S['s'];
		$k = (string)$S['k'];
		$t = (string)$S['t'];
		$a = (string)$S['a'];
		$translit = translit($k);
		$spa = $t ? $t : '-';
		$lemma = $strongs['greek'][$strongs_number]['lemma'];
		$verse_data[] = array(
			'spa' => $spa,
			'greek' => $s,
			'lemma' => $lemma,
			'translit' => $translit,
			'strong' => "G$strongs_number",
			'morph' => $morph,
		);
		//echo "$book $current_chapter:$current_verse $k G$strongs_number $morph $lemma \"$spa\"\n";
	}
}
if (!empty($verse_data)) {
	if (match_verse($verse_data, $parsed_query)) {
		if ($is_cli) {
			echo "$book $current_chapter:$current_verse\n";
		}
		$found["$book $current_chapter:$current_verse"] = $verse_data;
	}
}
//break;
}
function parse_query($query) {
	$tokens = getTokens(
		$string = $query,
		$offset = 0,
		$stringDelimiters = '\'"',
		$keywordDelimiter = ':'
	);
	$tree = generateTree($tokens);
	return $tree;
}
function match_verse(&$verse_data, $parsed_query, $offset = 0) {
	for($i = $offset; $i < count($verse_data); ++$i) {
		$word = &$verse_data[$i];
		if (match_word($word, $parsed_query[0])) {
			$word['match'] = true;
			if (!empty($parsed_query[1])) {
				return match_verse($verse_data, array_slice($parsed_query, 1), $i + 1);
			}
			return true;
		}
	}
	return false;
}
function match_word($word, $matcher) {
	$match = true;
	foreach ($matcher as $key => $value) {
		$modifier = null;
		if (!preg_match('/[a-z0-9_]/', $key[0])) {
			$modifier = $key[0];
			$key = substr($key, 1);
		}
		if (strpos($modifier, '~!') !== false) {
			$match = !match_key($word, $key, $value);
		} else {
			$match = match_key($word, $key, $value);
		}
		if (!$match) {
			return false;
		}
	}
	return true;
}
function match_key($word, $key, $value) {
	switch($key) {
		case 'strongs':
		case 'strong':
		case 's':
			return $word['strong'] == $value;
		case 'spa':
		case 'translation':
		case 'a':
			return stripos($word['spa'], $value) !== false;
		case 'grc':
		case 'grk':
		case 'greek':
		case 'g':
			return stripos($word['greek'], $value) !== false;
		default:
			die("Search field $key not available\n");
	}
}
if ($is_cli) {
	exit(0);
}
$title = $page_title = 'Buscar';
$content = '';
foreach($found as $ref => $verse_data) {
	list($book, $chapter, $verse) = parse_ref($ref);
	$url = url_for($books[$book]['dir'], $chapter) . '#v' . $verse;
	$content .= '<div class="search-result">';
	$content .= '<span class="verse-text">';
	$content .= '<span class="block">';
	if ($show_strongs) {
		$content .= '<span class="strongs">&nbsp;</span>';
	}
	if ($show_morph) {
		$content .= '<span class="morph">&nbsp;</span>';
	}
	if ($show_greek) {
		$content .= sprintf('<span class="verse"><a href="%s" target="_blank">%s</a></span>', $url, ucfirst($ref));
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
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title><? echo $page_title; ?></title>
<link rel="stylesheet" type="text/css" href="/css/style.css"/>
<script type="text/javascript" src="/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/js/interlineal.js"></script>
</head>
<body>
<div id="content">
<h1><?= "$title" ?></h1>
<div class="strongs-page">
<form action="<?= $_SERVER['PHP_SELF']?>" style="text-align:center;margin:0 0 4em;">
	<input type="text" name="q" value="<?= h($query) ?>" style="width:600px;">
	<button type="submit">Buscar</button>
	<div>
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
