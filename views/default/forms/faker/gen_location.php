<?php

echo '<div class="elgg-head">';
echo elgg_view_title(elgg_echo('faker:gen_location'));
echo '</div>';

echo '<div class="elgg-body">';
echo '<div>';
echo elgg_echo('faker:gen_location:real');
echo '</div>';

echo '</div>';

echo '<div class="elgg-foot mtm">';
echo elgg_view('input/submit');
echo '</div>';