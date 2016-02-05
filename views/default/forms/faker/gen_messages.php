<?php

echo '<div class="elgg-head">';
echo elgg_view_title(elgg_echo('faker:gen_messages'));
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_messages:count') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'count',
	'value' => 5,
));
echo '</div>';

echo '</div>';

echo '<div class="elgg-foot mtm">';
echo elgg_view('input/submit');
echo '</div>';