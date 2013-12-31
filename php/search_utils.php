<?php

include 'parsetok.php';

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

	$continue = true;
	$invert = false;
	if (!empty($parsed_query[$match_offset]['operator'])) {
		$operator = $parsed_query[$match_offset]['operator'];

		if (strpos($operator, '+') !== false) {
			$continue = false;
		}

		if (strpos($operator, '^') !== false) {
			$continue = false;
			$offset = 0;
		}

		if (strpos($operator, '$') !== false) {
			$continue = false;
			end($verse_data);
			$offset = key($verse_data);
		}

		if (strpos($operator, '!') !== false) {
			$invert = true;
		}

		if (strpos($operator, '<') !== false) {
			$offset = 0;
		}
	}

	for($i = $offset; $i < $len; ++$i) {
		$word = &$verse_data[$i];
		if (match_word($word, $parsed_query[$match_offset]) xor $invert) {

			if (!empty($parsed_query[$match_offset+1])) {
				if ($match_count = match_verse($verse_data, $parsed_query, $i + 1, $match_offset + 1)) {
					$word['match'] = true;
					return $match_count;
				}

				if (!$continue) {
					return false;
				}
				continue;
			}


			$all_ops = '';
			foreach($parsed_query as $matcher) {
				if (!empty($matcher['operator'])) {
					$all_ops .= $matcher['operator'];
				}
			}

			$match_count = 1;
			if (empty($all_ops) || (strpos($all_ops, '<') === false)
				&& (strpos($all_ops, '^') === false)
				&& (strpos($all_ops, '$') === false)) {
					$match_count += match_verse($verse_data, $parsed_query, $i + 1, 0);
			}

			$word['match'] = true;
			return $match_count;
		}

		if (!$continue) {
			break;
		}
	}
	return false;
}

function match_word(&$word, &$matcher) {

	foreach ($matcher as $key => $value) {
		if ($key == 'operator') {
			continue;
		}
		$modifier = null;
		if (!ctype_alpha($key[0])) {
			$modifier = $key[0];
			$key = substr($key, 1);
		}
		if ($modifier && strpos($modifier, '!') !== false) {
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
			if ($value[0] == '/') {
				return preg_match($value, $word['spa']);
			}
			if ($value[0] == '@') {
				return strpos($word['spa'], substr($value, 1)) !== false;
			}
			if ($value[0] == '=') {
				return $word['spa'] == substr($value, 1);
			}
			return stripos($word['spa'], $value) !== false;
		case 'grc':
		case 'grk':
		case 'greek':
		case 'g':
			return stripos($word['greek'], $value) !== false;
		case 'igrc':
		case 'igrk':
		case 'igreek':
		case 'ig':
			return stripos(strip_diacritics($word['greek']), $value) !== false;
		case 'lemma':
		case 'l':
			return strtolower($word['lemma']) == $value;
		case 'translit':
		case 'tr':
		case 't':
			return strtolower($word['translit']) == str_replace(array('ô', 'ê'), array('ō', 'ē'), $value);
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
	$pos = $pos;
	$matcher = strtoupper($matcher);
	$len = strlen($matcher);
	for ($i = 0; $i < $len ; $i++) {
		if (!isset($pos[$i])) {
			return false;
		}
		if (in_array($matcher[$i], array('?','.','*'))) {
			continue;
		}
		if ($pos[$i] != $matcher[$i]) {
			return false;
		}
	}

	return true;
}

