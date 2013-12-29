<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';

include 'books.php';
include 'breaks.php';

include 'translit.php';
include 'helpers.php';

ini_set('memory_limit', '256M');

$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhdb_path = dirname(__FILE__) . '/morph/';
$index_path = dirname(__FILE__) . '/rindex/';


$index = array();

foreach($books as $book => $book_data) {

$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';
$book_index = $index_path . $book . '.php';

if (!file_exists($filename)) {
	continue;
}

if (file_exists($book_index) && (filemtime($book_index) > filemtime($filename))) {
	echo "Skip $book\n";
	continue;
}

$xml = simplexml_load_file($filename);

$morphdb = array();
include($morphdb_filename);

$index = array();

$current_book = 1;
$current_chapter = 0;
$current_verse = 0;
$current_word = 0;

foreach($xml->xpath('//S') as $S) {
	$is_chapter = false;

	if (isset($S['m'])) {
		$mark = (string)$S['m'];
		$matches = array();

		if (preg_match('/\\\\h/', $mark, $matches)) {
			$is_title = 1;
		}

		if (preg_match('/\\\\c ([0-9]+)/', $mark, $matches)) {
			$current_chapter = $matches[1];
			$current_word = 0;
			$is_title = 0;
			$is_chapter = 1;
		}

		if (preg_match('/\\\\v ([0-9]+)/', $mark, $matches)) {
			$current_verse = $matches[1];
			$current_word = 0;
			$is_title = 0;
		}
	}

	$greek = (string)$S['s'];
	$skip = preg_match('#^(—|\[|\]|[0-9]+)$#', $greek);

	if ($skip) {
		continue;
	}

	if (!$skip && isset($S['f']) && (string)$S['f'] == "0000000000000000000000") {
		if ($current_word <= 1) {
			break;
		}
		$s = str_replace("\n", " ", var_export((array)($S), true));
		file_put_contents('php://stderr', "MISSING TRANSLATION: $book $current_chapter:$current_verse.$current_word $s\n");
		continue;
	}

	$strongs_number = null;

	$word_index = $current_word;
	if ($current_chapter && $current_verse && !$skip) {
		if (isset($morphdb[$book][$current_chapter][$current_verse][$current_word])) {
			$morph = $morphdb[$book][$current_chapter][$current_verse][$current_word]['morph'];
			$strongs_number = $morphdb[$book][$current_chapter][$current_verse][$current_word]['strongs'];
		}
		++$current_word;
	}

	if ($strongs_number) {
		if (!isset($index[$strongs_number])) {
			$index[$strongs_number] = array();
		}

		$s = (string)$S['s'];
		$k = (string)$S['k'];
		$t = (string)$S['t'];
		$a = (string)$S['a'];

		$greek = $s;
		$translit = translit($k);
		$spa = $t ? $t : '-';
		$lemma = $strongs['greek'][$strongs_number]['lemma'];

		$index[$book][$current_chapter][$current_verse][$word_index] = array(
			'spa' => $spa,
			'greek' => $greek,
			'lemma' => $lemma,
			'translit' => $translit,
			'strong' => "G$strongs_number",
			'morph' => $morph,
		);
	}

}


ksort($index);

echo "Built $book\n";
$out = "<?php\n\n";

foreach($index as $b => $book) {
	foreach ($book as $c => $chapter) {
		foreach ($chapter as $v => $verse) {
			foreach($verse as $w => $word) {
				$out .= "\$index['$b'][$c][$v][$w] = " . var_export($word, true) . ";\n";
			}
		}
	}
}

file_put_contents($book_index, $out);


}

$out = "<?php\n\$index_path = dirname(__FILE__) . '/rindex/';\n\n\$index = array();\n";
foreach($books as $book => $book_data) {

	$book_index = $index_path . $book . '.php';
	if (!file_exists($book_index)) {
		continue;
	}

	$out .= "include \$index_path . '$book.php';\n";
}

file_put_contents(dirname(__FILE__).'/rindex.php', $out);
