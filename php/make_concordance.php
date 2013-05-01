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

$concordance = array();

foreach($books as $book => $book_data) {

$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';

if (!file_exists($filename)) {
	continue;
}

$xml = simplexml_load_file($filename);

$morphdb = array();
include($morphdb_filename);

$current_book = 1;
$current_chapter = 0;
$current_verse = 0;
$current_word = 0;

foreach($xml->xpath('//S') as $S) {

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
		}

		if (preg_match('/\\\\v ([0-9]+)/', $mark, $matches)) {
			$current_verse = $matches[1];
			$current_word = 0;
			$is_title = 0;
		}
	}
	if (isset($S['f']) && (string)$S['f'] == "0000000000000000000000") {
		if ($current_word == 1) {
			break;
		}
		$s = str_replace("\n", " ", var_export((array)($S), true));
		file_put_contents('php://stderr', "MISSING TRANSLATION: $book $current_chapter:$current_verse.$current_word $text $s\n");
		continue;
	}

	$greek = $S['s'];
	$skip = $greek == '—';

	$strongs_number = null;
	$morph = '-';
	$translit = '-';
	if ($current_chapter && $current_verse && !$skip) {
		if (isset($morphdb[$book][$current_chapter][$current_verse][$current_word]['word'])) {
			$greek = $morphdb[$book][$current_chapter][$current_verse][$current_word]['word'];
			$morph = $morphdb[$book][$current_chapter][$current_verse][$current_word]['morph'];
			$strongs_number = $morphdb[$book][$current_chapter][$current_verse][$current_word]['strongs'];
			$translit = $morphdb[$book][$current_chapter][$current_verse][$current_word]['translit'];
		} else {
			file_put_contents('php://stderr', "//MISSING $greek $book $current_chapter:$current_verse ($current_word)\n");
		}
		++$current_word;
	}

	if (empty($spa)) {
		$spa = '-';
	}

	if ($strongs_number) {
		if (!isset($concordance[$strongs_number])) {
			$concordance[$strongs_number] = array();
		}

		$s = (string)$S['s'];
		$k = (string)$S['k'];
		$t = (string)$S['t'];
		$a = (string)$S['a'];

		if (!isset($concordance[$strongs_number][$morph])) {
			$concordance[$strongs_number][$morph] = array();
		}

		$spa = $a ? $a : '-';
		
		$concordance[$strongs_number][$morph][] = array(
			'word' => $greek,
			'spa' => $spa,
			'morph' => $morph,
			'ref' => "$book $current_chapter:$current_verse"
		);
	}

}
		//file_put_contents('php://stderr', "//MISSING NUMBRER: $book $current_chapter:$current_verse\n");



}

ksort($concordance);

echo "<?php\n\$concordance = ";
var_export($concordance);
echo ';';

