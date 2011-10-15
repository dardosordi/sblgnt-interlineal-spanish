<?php

error_reporting(E_ALL);

include 'rmac.php';
include 'strongs_greek.php';

include 'books.php';
include 'breaks.php';

include 'translit.php';
include 'helpers.php';

ini_set('memory_limit', '128M');

$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhgnt_path = dirname(dirname(__FILE__)) . '/morphgnt/';
$moprhdb_path = dirname(__FILE__) . '/morph/';

$works = array('mt');

foreach($works as $book) {

$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';


$xml = simplexml_load_file($filename);

$morphdb = array();
include($morphdb_filename);

$current_book = 1;
$current_chapter = 0;
$current_verse = 0;
$current_word = -1;

$concordance = array();

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



	++$current_word;

	if (isset($S['f']) && !intval($S['f'])) {
		break;
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
		if (!isset($concordance[$strongs_number])) {
			$concordance[$strongs_number] = array();
		}

		$s = (string)$S['s'];
		$k = (string)$S['k'];
		$t = (string)$S['t'];
		$a = (string)$S['a'];

		$translit = translit($k);

		if (!isset($concordance[$strongs_number][$translit])) {
			$concordance[$strongs_number][$translit] = array();
		}


		$spa = $a ? $a : '-';
		
		$concordance[$strongs_number][$translit][] = array(
			'word' => $k,
			'spa' => $spa,
			'morph' => $morph,
			'ref' => "$book $current_chapter:$current_verse"
		);
	}

}



}

ksort($concordance);

echo "<?php\n\$concordance = ";
var_export($concordance);
echo ';';

