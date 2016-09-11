<?php

echo '<div class="elgg-head">';
echo elgg_view_title(elgg_echo('faker:gen_comments'));
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_comments:count') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'count',
	'value' => 10,
));
echo '</div>';
echo '<div>';
echo '<label>' . elgg_echo('faker:gen_comments:reply_count') . '</label>';
echo elgg_view('input/text', array(
	'name' => 'reply_count',
	'value' => 0,
));
echo '</div>';
echo '</div>';

echo '<div class="elgg-foot mtm">';
echo elgg_view('input/submit');
echo '</div>';
