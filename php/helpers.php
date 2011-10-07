<?php

function label_rmac($morph, $rmac) {
	if (empty($morph)) {
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


