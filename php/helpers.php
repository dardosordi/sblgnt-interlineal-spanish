<?php

function pr($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

function label_lmac($morph) {
	$dict = array(
		'part' => array(
			'N' => 'Sustantivo',
			'J' => 'Adjetivo',
			'D' => 'Artículo',
			'R' => 'Pronombre',
			'V' => 'Verbo',
			'C' => 'Conjunción',
			'B' => 'Adverbio',
			'I' => 'Interjección',
			'P' => 'Preposición',
			'T' => 'Partícula',
			'X' => 'Indeclinable',
		),
		'case' => array(
			'N' => 'Nominativo',
			'D' => 'Dativo',
			'G' => 'Genitivo',
			'A' => 'Acusativo',
			'V' => 'Vocativo',
		),
		'number' => array(
			'S' => 'Singular',
			'P' => 'Plural',
			'D' => 'Dual',
		),
		'gender' => array(
			'M' => 'Masculino',
			'F' => 'Femenino',
			'N' => 'Neutro',
		),
		'person' => array(
			'1' => 'Primera Persona',
			'2' => 'Segunda Persona',
			'3' => 'Tercera Persona',
			'-' => '',
		),
		'pronoun_type' => array(
			'R' => 'Relativo',
			'C' => 'Reciproco',
			'D' => 'Demostrativo',
			'K' => 'Correlativo',
			'I' => 'Interrogativo',
			'X' => 'Indefinido',
			'F' => 'Reflexivo',
			'S' => 'Posesivo',
			'P' => 'Personal',
		),
		'pronoun_subtype' => array(
			'A' => 'Atributivo',
			'P' => 'Predicativo',
		),
		'degree' => array(
			'C' => 'Comparativo',
			'S' => 'Superlativo',
			'O' => 'Otro',
		),
		'conjunction_type' => array(
			'L' => 'Lógica',
			'A' => 'Adverbial',
			'S' => 'Sustantiva',
		),
		'conjunction_subtype' => array(
			'L' => array(
				'A' => 'Ascensiva',
				'N' => 'Conectiva',
				'C' => 'Contrastiva',
				'K' => 'Correlativa',
				'D' => 'Disyuntiva',
				'M' => 'Enfática',
				'X' => 'Explicatoria',
				'I' => 'Inferencial',
				'T' => 'Transicional',
			),
			'A' => array(
				'Z' => 'Causal',
				'M' => 'Comparativa',
				'N' => 'Concesiva',
				'C' => 'Condicional',
				'D' => 'Declarativa',
				'L' => 'Local',
				'P' => 'de Propósito',
				'R' => 'de Resultado',
				'T' => 'Temporal',
			),
			'S' => array(
				'C' => 'de Contenido',
				'E' => 'Expexegética',
			),
		),
		'adverb_type' => array(
			'C' => 'Condicional',
			'K' => 'Correlativo',
			'E' => 'Enfático',
			'X' => 'Indefinido',
			'I' => 'Interrogativo',
			'N' => 'Negativo',
			'P' => 'de Lugar',
			'S' => 'Superlativo',
		),
		'particle_type' => array(
			'C' => 'Condicional',
			'K' => 'Correlativa',
			'E' => 'Enfática',
			'X' => 'Indefinida',
			'I' => 'Interrogativa',
			'N' => 'Negativa',
			'P' => 'de Lugar',
			'S' => 'Superlativa',
		),
		'indeclinable_type' => array(
			'L' => 'Letra',
			'P' => 'Nombre Propio',
			'N' => 'Numeral',
			'F' => 'Palabra Extrangera',
			'O' => 'Otro',
		),
		'tense' => array(
			'P' => 'Presente',
			'I' => 'Imperfecto',
			'F' => 'Futuro',
			'T' => 'Futuro Perfecto',
			'A' => 'Aoristo',
			'R' => 'Perfecto',
			'L' => 'Pluscuamperfecto',
		),
		'voice' => array(
			'A' => 'Activo',
			'M' => 'Medio',
			'P' => 'Pasivo',
			'U' => 'Medio-Pasivo',
		),
		'mood' => array(
			'I' => 'Indicativo',
			'S' => 'Subjuntivo',
			'O' => 'Optativo',
			'M' => 'Imperativo',
			'N' => 'Infinitivo',
			'P' => 'Participio',
		),
	);

	$fields = array('part');
	switch ($morph[0]) {
		case 'J': $fields = array('part', '-', 'case', 'number', 'gender', '-', 'degree'); break;
		case 'N': $fields = array('part', '-', 'case', 'number', 'gender'); break;
		case 'D': $fields = array('part', '-', 'case', 'number', 'gender'); break;
		case 'R': $fields = array('part', 'pronoun_type', '-', 'person', '-', 'case', 'number', 'gender', '-', 'pronoun_subtype'); break;
		case 'V': $fields = array('part', '-', 'tense', 'voice', 'mood', '-', 'person','number', 'case', 'gender'); break;
		case 'C': $fields = array('part', 'conjunction_type', 'conjunction_subtype'); break;
		case 'B': $fields = array('part', 'adverb_type'); break;
		case 'T': $fields = array('part', 'particle_type'); break;
		case 'X': $fields = array('part', 'indeclinable_type'); break;
		case 'I': $fields = array('part'); break;
		case 'P': $fields = array('part'); break;
		default: return $morph;
	}

	$desc = array();
	$i = 0;
	foreach ($fields as $field) {
		if ($field == '-') {
			$desc[] = '-';
			continue;
		}
		if (isset($morph[$i])) {
			$key = $morph[$i];
			if ($field == 'conjunction_subtype') {
				$key0 = $morph[$i-1];
				if (isset($dict[$field][$key0][$key])) {
					$desc[] = $dict[$field][$key0][$key];
				}
			} else if (isset($dict[$field][$key])) {
				$desc[] = $dict[$field][$key];
			}
		}
		$i++;
	}

	return trim(str_replace('-  -', '-', implode(' ', $desc)), '-');
}

function label_rmac($morph, $rmac) {
	if (empty($morph) || $morph == '-') {
		return;
	}

	$data = $rmac[$morph];

	$out = $data['part'];
	if ($morph[0] == 'V') {
		$keys = array('tense', 'voice', 'mood', 'case', 'person', 'number', 'gender', 'degree', 'form');
		$breaks = array(0, 3, 4, 7, 8);
	} else {
		$keys = array('person', 'case', 'number', 'gender', 'degree', 'form');
		$breaks = array(0, 1, 4, 5);
	}

	foreach ($keys as $i => $k) {
		if (isset($data[$k])) {
			if (in_array($i, $breaks)) {
				$out .= ' -';
			}
			$out .= ' ' . $data[$k];
		}
	}
	return ucwords($out);
}

function format_strongs($text) {
	$url = url_for('strongs', '$1');
	return preg_replace('/([GH][1-9][0-9]*)/', '<a class="strongs" href="'.$url.'">$1</a>', $text);
}

function parse_ref($ref) {
	$matches = array();
	if (preg_match('/([123]?[a-z]+) ([0-9]+):([0-9]+)/i', $ref, $matches)) {
		return array($matches[1], $matches[2], $matches[3]);
	}
	return array(false, false, false);
}

function get_menu($menu, $here = null) {
	if (empty($menu)) {
		return;
	}
	$html = array();
	$last = array_pop($menu);
	$last['class'] = empty($last['class']) ? 'last' : $last['class'] . ' last';
	$menu[] = $last;
	$menu[0]['class'] = empty($menu[0]['class']) ? 'first' : $menu[0]['class'] . ' first';

	foreach ($menu as $item) {
		$text = $item['text'];
		unset($item['text']);
		$class = '';
		if (isset($item['url'])) {
			$url = $item['url'];
			$path = parse_url($url, PHP_URL_PATH);
			if ($path == $here) {
				$class = ' class="on"';
			}
			unset($item['url']);
			$html[] = sprintf('<li%s>%s</li>', $class, link_to($text, $url, $item));
		} else {
			$html[] = sprintf('<li%s><span %s>%s</span></li>', $class, html_attributes($item), $text);
		}
	}

	return implode($html, "\n");
}

function link_to($title, $url, $attributes = array()) {
	$attributes['href'] = $url;
	$out = sprintf('<a%s>%s</a>', html_attributes($attributes), $title);
	return $out;
}

function html_attributes($attributes) {
	$out = '';
	foreach ($attributes as $name => $value) {
		$out .= ' '. $name . '="' . h($value) .'"';
	}
	return $out;
}

function url_for($type) {
	$args = func_get_args();

	return '/'.implode('/', $args) . '.html';
}

function h($text) {
	if (is_array($text)) {
		return array_map('h', $text);
	}
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}


