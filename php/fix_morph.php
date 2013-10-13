<?php

error_reporting(E_ALL);
ini_set('memory_limit', '128M');

include 'translit.php';

$book = $argv[1];

$moprhdb_path = dirname(__FILE__) . '/morph/';
$morphdb_filename = $moprhdb_path . $book . '.php';

$morphdb = array();
include($morphdb_filename);

$word_map = array();

foreach($morphdb as $b => $book) {
	foreach($book as $c => $chapter) {
		foreach($chapter as $v => $verse) {
			foreach($verse as $w => $word) {
				if ($word['morph'] != 'XXX') {
					$key = $word['strongs'].':'.$word['morph'];
					$word_map[$word['word']][$key] = $word;
				}
			}
		}
	}
}

foreach($morphdb as $b => $book) {
	foreach($book as $c => $chapter) {
		foreach($chapter as $v => $verse) {
			foreach($verse as $w => $word) {
				$comment = '';
				if ($word['morph'] == 'XXX') {
					if (!empty($word_map[$word['word']])) {
						if (count($word_map[$word['word']]) == 1) {
							$word = current($word_map[$word['word']]);
							$morphdb[$b][$c][$v][$w] = current($word_map[$word['word']]);
							$comment = ' //FIXED';
						} else {
							$comment = ' //MULTI:';
							foreach ($word_map[$word['word']] as $k => $map) {
								$comment .= ' ' . $k;
							}
						}
					}
				}
				$word['translit'] = var_export(translit($word['word'], true), true);	
				$word['word'] = var_export($word['word'], true);
				echo "\$morphdb['$b'][$c][$v][$w] = array('word' => {$word['word']}, 'translit' => {$word['translit']}, 'morph' => '{$word['morph']}', 'strongs' => '{$word['strongs']}',);$comment\n";
			}
		}
	}
}



