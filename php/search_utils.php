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

function match_verse(&$verse_data, $parsed_query, $offset = 0, $match_offset = 0) {
	$len = count($verse_data);
	for($i = $offset; $i < $len; ++$i) {
		$word = &$verse_data[$i];
		if (match_word($word, $parsed_query[$match_offset])) {
			$word['match'] = true;
			if (!empty($parsed_query[$match_offset+1])) {
				return match_verse($verse_data, $parsed_query, $i + 1, $match_offset + 1);
			}
			return true;
		}
	}
	return false;
}

function match_word(&$word, &$matcher) {
	foreach ($matcher as $key => $value) {
		$modifier = null;
		if (!ctype_alpha($key[0])) {
			$modifier = $key[0];
			$key = substr($key, 1);
		}
		if ($modifier && strpos($modifier, '~!') !== false) {
			if (match_key($word, $key, $value)) {
				return false;
			}
		} else {
			if (!match_key($word, $key, $value)) {
				return false;
			}
		}
	}
	return true;
}

function match_key($word, $key, $value) {
	switch($key) {
		case 'strongs':
		case 'strong':
		case 's':
			return in_array($word['strong'], explode(',',$value));
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
			$rx = '/^('.str_replace(array('?', '*', ','), array('.', '.', '|'), $value).')/i';
			return preg_match($rx, $word['morph']);
		default:
			die("Search field $key not available\n");
	}
}

function match_pos($pos, $matcher) {
	$pos = str_split($pos);
	$matcher = str_split(strtoupper($matcher));
	$len = count($matcher);
	for ($i = 0; $i < $len ; $i++) {
		if (!isset($pos[$i])) {
			return false;
		}
		if (in_array($matcher, array('?','.','*'))) {
			continue;
		}
		if ($pos[$i] != $matcher[$i]) {
			return false;
		}
	}

	return true;
}

