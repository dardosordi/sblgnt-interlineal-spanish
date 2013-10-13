<?php

error_reporting(E_ALL);
ini_set('memory_limit', '128M');

include 'translit.php';

$book = $argv[1];


$morphdb_path = dirname(__FILE__) . '/morph/';
$morphdb_filename = $morphdb_path . $book . '.php';

$morphdb = array();
$lmorphdb_path = dirname(__FILE__) . '/lmorph/';
$lmorphdb_filename = $lmorphdb_path . $book . '.php';

include($lmorphdb_filename);
$lmorphdb = $morphdb;

$morphdb = array();
include($morphdb_filename);

foreach($morphdb as $b => $book) {
	foreach($book as $c => $chapter) {
		foreach($chapter as $v => $verse) {
			foreach($verse as $w => $word) {
				if (!isset($lmorphdb[$b][$c][$v][$w])) {
					echo "$b $c:$v.$w {$morphdb[$b][$c][$v][$w]['word']} MISSING\n";
					continue;
				}
				if ($morphdb[$b][$c][$v][$w]['strongs'] != $lmorphdb[$b][$c][$v][$w]['strongs']) {
					echo "$b $c:$v.$w {$morphdb[$b][$c][$v][$w]['strongs']} {$morphdb[$b][$c][$v][$w]['word']} => {$lmorphdb[$b][$c][$v][$w]['strongs']} {$lmorphdb[$b][$c][$v][$w]['word']}\n";
				}

			}
		}
	}
}


