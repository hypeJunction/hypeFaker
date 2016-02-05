<?php

echo '<div class="elgg-head">';
echo elgg_view_title(elgg_echo('faker:gen_groups'));
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_groups:count') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'count',
	'value' => 2,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('faker:gen_groups:featured_count') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'count',
	'value' => 5,
));
echo '</div>';

echo '</div>';

echo '<div class="elgg-foot mtm">';
echo elgg_view('input/submit');
echo '</div>';