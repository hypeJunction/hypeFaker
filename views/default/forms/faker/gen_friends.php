<?php

echo '<div class="elgg-head">';
echo elgg_view_title(elgg_echo('faker:gen_friends'));
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_friends:max') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'max',
	'value' => 10,
));
echo '</div>';
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_friends:reciprocal') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => 'reciprocal',
	'value' => 0,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	]
));
echo '</div>';
echo '</div>';

echo '<div class="elgg-foot mtm">';
echo elgg_view('input/submit');
echo '</div>';