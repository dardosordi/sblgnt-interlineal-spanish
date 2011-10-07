<?php

error_reporting(E_ALL);

include 'translit.php';
include 'rmac.php';
include 'books.php';
include 'helpers.php';


$book = $argv[1];
$chapter = $argv[2];

$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhdb_path = dirname(__FILE__) . '/morph/parsed/';


$filename = $xml_path . $books[$book]['xml'];
$morphdb_filename = $moprhdb_path . $book . '.php';


ini_set('memory_limit', '128M');
$xml = simplexml_load_file($filename);

$morphdb = array();
include($morphdb_filename);

$current_book = 1;
$current_chapter = 0;
$current_verse = 0;
$current_word = -1;
$interlineal = array();
foreach($xml->xpath('//S') as $S) {
	if (isset($S['m'])) {
		$mark = (string)$S['m'];
		$matches = array();

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

	if ($current_chapter > $chapter) {
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
		'a' => (string)$S['t'],
	);
}


$parsed = array();

$current_verse = 0;
$current_chapter = $chapter;
foreach($interlineal as $S) {

	$morph = '';
	$strongs = '';

	if ($S['v'] > 0) {
		if ($S['v'] != $current_verse) {
			$current_verse = $S['v'];
		}
	}

	$greek = $S['s'];
	$clean = $S['k'];
	$translit = translit($S['k']);
	$spa = $S['t'];

	$current_word = $S['w'];
	if ($current_chapter && $current_verse) {
		$morph = 'XXX';

		for ($cw = $current_word; isset($morphdb[$book][$current_chapter][$current_verse][$cw]); $cw++) {
			$cw_t = translit($morphdb[$book][$current_chapter][$current_verse][$cw]['word']);
			if ($cw_t == $translit) {
				$morph = $morphdb[$book][$current_chapter][$current_verse][$cw]['morph'];
				$strongs = $morphdb[$book][$current_chapter][$current_verse][$cw]['strongs'];
				break;
			}
		}
		if ($morph == 'XXX') {
			for ($cw = $current_word; isset($morphdb[$book][$current_chapter][$current_verse][$cw]); $cw--) {
				$cw_t = translit($morphdb[$book][$current_chapter][$current_verse][$cw]['word']);
				if ($cw_t == $translit) {
					$morph = $morphdb[$book][$current_chapter][$current_verse][$cw]['morph'];
					$strongs = $morphdb[$book][$current_chapter][$current_verse][$cw]['strongs'];
					break;
				}
			}
		}
	}

	$parsed[$book][$current_chapter][$current_verse][$current_word] = array(
		'word' => $clean,
		'translit' => $translit,
		'morph' => $morph,
		'strongs' => $strongs,
	);

	echo "\$morphdb['$book'][$current_chapter][$current_verse][$current_word] = array('word' => '$clean', 'translit' => '$translit', 'morph' => '$morph', 'strongs' => '$strongs',);\n";
}


