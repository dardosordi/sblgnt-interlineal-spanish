<?php

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
		case 'lemma':
		case 'l':
			return stripos($word['lemma'], $value) !== false;
		case 'translit':
		case 'tr':
		case 't':
			return stripos($word['translit'], $value) !== false;
		case 'morph':
		case 'pos':
		case 'm':
			$values = explode(',', $value);
			foreach($values as $v) {
				if (match_pos($word['morph'], $v)) {
					return true;
				}
			}
			return false;
		default:
			die("Search field $key not available\n");
	}
}

function match_pos($pos, $matcher) {
	$pos = str_split($pos);
	$matcher = str_split(strtoupper($matcher));

	for ($i = 0; $i < count($matcher) ; $i++) {
		if (!isset($pos[$i])) {
			return false;
		}
		if (in_array($matcher, array('?','.','?'))) {
			continue;
		}
		if ($pos[$i] != $matcher[$i]) {
			return false;
		}
	}

	return true;
}

