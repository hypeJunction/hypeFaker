<?php

namespace hypeJunction\Faker;

echo '<div class="elgg-head">';
echo elgg_view_title(elgg_echo('faker:gen_pages'));
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_pages:count') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'count',
	'value' => 10,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('faker:gen_pages:max_children') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'max_children',
	'value' => 3,
));
echo '</div>';

echo '</div>';

echo '<div class="elgg-foot mtm">';
echo elgg_view('input/submit');
echo '</div>';