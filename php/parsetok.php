<?php

/**
 * @author yanick.rochon@gmail.com
 *
 * @param string $string             the string to tokenize
 * @param int $offset                the starting offset
 * @param string $stringDelimiters    the characters to delimit token strings
 * @param string $keywordDelimiter the character(s) to delimit token string names
 * @return array
 */
function getTokens(
        $string, 
        $offset = 0, 
        $stringDelimiters = '\'"',
        $keywordDelimiter = ':',
        $groupDelimiters = '()'
	) 
{

    if ($offset >= strlen($string)) {
        //echo "offset out of range";
        return false;
    }

    $spaces = " \t\n\r";   // space characters

    // add string delimiters to spaces...
    $stringSpaces = $spaces . $keywordDelimiter;
    $delimiters = $stringSpaces . $stringDelimiters;

    //var_dump($stringSpaces);

    $string = ltrim(substr($string, $offset), $stringSpaces);
    $token_strings = array();

    //echo "String is : " . $string . "\n";

    // 1. split all tokens...
    while ($offset < strlen($string)) {
        $lastOffset = $offset;
        $escaped = false;

	if (false !== strpos($groupDelimiters, $char = $string[$offset])) {
		$token_strings[] = $char;
		++$offset;
		continue;
	}


        if (false !== strpos($stringDelimiters, $char = $string[$offset])) {
            $stringChar = $char;
        } else {
            $stringChar = null;
        }

        if (null !== $stringChar) {
            while (($offset < strlen($string)) && (($stringChar !== ($char = $string[++$offset])) || $escaped)) {
                //$offset++;
                $escaped = ('\\' === $char);
            }
            $offset++;
            //echo "*** stringed : " . substr($string, $lastOffset, $offset - $lastOffset) . "\n";
        } else {
            while (($offset < strlen($string)) && ((false === strpos($delimiters . $groupDelimiters, $char = $string[$offset])) || $escaped)) {
                $offset++;
                $escaped = ('\\' === $char);
            }
            //echo "*** Non-string : " . substr($string, $lastOffset, $offset - $lastOffset) . "\n";
        }
        //skip spaces...
        while (($offset < strlen($string)) && ((false !== strpos($stringSpaces, $char = $string[$offset])) || $escaped)) {
            $offset++;
            $escaped = ('\\' === $char);
        }

        $token_strings[] = substr($string, $lastOffset, $offset - $lastOffset);
        //echo "Next token = '" . end($token_strings) . "'\n";
    }

    $tokens = array();
    $tokenName = null;
    foreach ($token_strings as $token_str) {
        // clean $token_str
        $token_str = trim(stripslashes($token_str), $spaces);
        $str_value = trim($token_str, $stringDelimiters);

        // is it a token name?
        if (':' === substr($token_str, -1, 1)) {
            if (!empty($tokenName)) {
                $tokens[] = array($tokenName, '');
            }
            $tokenName = trim($token_str, $delimiters);
        } else {
            if (!empty($tokenName)) {
		$tokens[] = array($tokenName, $str_value);
                $tokenName = null;
            } else if (!empty($str_value)) {
		$tokens[] = $str_value;
            }
        }
    }
    if (!empty($tokenName)) {
        $tokens[$tokenName] = '';
    }

    return $tokens;
}

function generateTree($tokens) {
	if (empty($tokens)) {
		return array();
	}

	$is_group = false;
	$group = array();
	$tree = array();
	$operator = null;

	foreach($tokens as $key => $value) {
		if (!is_array($value)) {
			if ($value == '(') {
				$is_group = true;
				continue;
			}

			if ($value == ')') {
				if (!$is_group) {
					die("Closing not opened (\n");
				}
				$is_group = false;
				if (!empty($group)) {
					$tree[] = $group;
					$group = array();
				}
				continue;
			}

			if ($value == '&') {
				continue;
			}

			if ($value == '$') {
				end($tree);
				$i = key($tree);
				$tree[$i]['operator'] = isset($tree[$i]['operator']) ? $tree[$i]['operator'] . $value : $value;
				continue;
			}

			$matches = array();
			if (!$is_group && preg_match('/G?([0-9]+)/i', $value, $matches)) {
				$group['strong'] = 'G'.$matches[1];
				$tree[] = $group;
				$group = array();
				continue;
			}

			if (!$is_group && strlen($value) > 1) {
				$group['spa'] = $value;
				$tree[] = $group;
				$group = array();
				continue;
			}

			$group['operator'] = isset($group['operator']) ? $group['operator'] . $value : $value;
			continue;
		}

		$group[$value[0]] = $value[1];
		if ($is_group) {
			continue;
		}

		$tree[] = $group;
		$group = array();
	}

	return $tree;
}




