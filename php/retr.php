<?php

error_reporting(E_ALL);
ini_set('memory_limit', '128M');

include 'translit.php';

$book = $argv[1];

$moprhdb_path = dirname(__FILE__) . '/morph/';
$morphdb_filename = $moprhdb_path . $book . '.php';

$morphdb = array();
$lmoprhdb_path = dirname(__FILE__) . '/lmorph/';
$lmorphdb_filename = $moprhdb_path . $book . '.php';
include($lmorphdb_filename);
$lmorphdb = $morphdb;

$morphdb = array();
include($morphdb_filename);

foreach($morphdb as $b => $book) {
	foreach($book as $c => $chapter) {
		foreach($chapter as $v => $verse) {
			foreach($verse as $w => $word) {
				$word['translit'] = var_export(translit($word['word'], true), true);	
				$word['word'] = var_export($lmorphdb[$b][$c][$v][$w]['word'], true);
				echo "\$morphdb['$b'][$c][$v][$w] = array('word' => {$word['word']}, 'translit' => {$word['translit']}, 'morph' => '{$word['morph']}', 'strongs' => '{$word['strongs']}',);\n";
			}
		}
	}
}




