<?php

error_reporting(E_ALL);
ini_set('memory_limit', '128M');

include 'translit.php';
include 'helpers.php';

$data = file($argv[1], FILE_IGNORE_NEW_LINES);
$replace = array('–' => '-', '…' => '...', ' ' => ' ');

$apparatus = array();
foreach ($data as $line) {
	$line = str_replace(array_keys($replace), array_values($replace), $line);
	if (!stripos($line, "\t")) {
		continue;
	}

	list($ref,$text) = explode("\t", $line);
	list($book, $ref_number) = explode(' ', strtolower($ref));
	list($chapter, $range) = explode(':', $ref_number);

	$entries = explode('•', $text);
	foreach($entries as $i => $entry) {
		$entry = trim($entry);
		$entries[$i] = $entry;
	}

	$verses = array();
	foreach(explode(',', $range) as $segment) {
		$matches = array();
		if (preg_match('#([0-9]+)-([0-9]+)#', $segment, $matches)) {
			for ($i = $matches[1]; $i <= $matches[2]; $i++) {
				$verses[] = $i;
			}
		} else {
			$verses[] = $segment;
		}
	}

	foreach($verses as $verse) {
		$apparatus[$book][$chapter][$verse] = $entries;
	}
}
unset($data);




echo "<?php\n\n";
foreach($apparatus as $b => $book) {
	foreach($book as $c => $chapter) {
		foreach($chapter as $v => $verse) {
			foreach($verse as $i => $entry) {
				$text = var_export($entry, true);
				echo "\$apparatus['$b'][$c][$v][$i] = $text;\n";
			}
		}
	}
}




echo ";\n";

