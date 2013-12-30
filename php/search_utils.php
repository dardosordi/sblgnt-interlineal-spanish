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
	$backwards = false;
	if (!empty($parsed_query[$match_offset]['operator'])) {
		if (strpos('+', $parsed_query[$match_offset]['operator']) !== false) {
			$continue = false;
		}

		if (strpos('^', $parsed_query[$match_offset]['operator']) !== false) {
			$continue = false;
			$offset = 0;
		}

		if (strpos('$', $parsed_query[$match_offset]['operator']) !== false) {
			$continue = false;
			end($verse_data);
			$offset = key($verse_data);
		}
	}


	for($i = $offset; $i < $len; ++$i) {
		$word = &$verse_data[$i];

		if (match_word($word, $parsed_query[$match_offset])) {
			$word['match'] = true;
			if (!empty($parsed_query[$match_offset+1])) {
				if (!empty($parsed_query[$match_offset+1]['operator'])) {
					if (strpos('<', $parsed_query[$match_offset+1]['operator']) !== false) {
						$backwards = true;
					}
				}

				$next_offset = $i + 1;
				if ($backwards) {
					$next_offset = 0;
				}
				return match_verse($verse_data, $parsed_query, $next_offset, $match_offset + 1);
			}


			$all_ops = '';
			foreach($parsed_query as $matcher) {
				if (!empty($matcher['operator'])) {
					$all_ops .= $matcher['operator'];
				}
			}

			$match_count = 1;
			if ((strpos('<', $all_ops) === false)
				&& (strpos('^', $all_ops) === false)
				&& (strpos('$', $all_ops) === false)) {
					$match_count += match_verse($verse_data, $parsed_query, $i + 1, 0);
			}

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

