<?php

/**
 * UI for generating fake data
 */
elgg_load_js('admin.faker');

$forms = elgg_view_form('faker/gen_users');

$fake_user_count = elgg_get_entities_from_metadata(array(
	'types' => 'user',
	'metadata_names' => '__faker',
	'count' => true
		));

if ($fake_user_count) {
	$fakes = elgg_list_entities_from_metadata(array(
		//'types' => 'user',
		'metadata_names' => '__faker',
	));

	$forms .= elgg_view_form('faker/gen_friends');
	if (elgg_is_active_plugin('groups')) {
		$forms .= elgg_view_form('faker/gen_groups');
		$fake_group_count = elgg_get_entities_from_metadata(array(
			'types' => 'group',
			'metadata_names' => '__faker',
			'count' => true
		));
		if ($fake_group_count) {
			$forms .= elgg_view_form('faker/gen_group_members');
		}
	}

	if (elgg_is_active_plugin('blog')) {
		$forms .= elgg_view_form('faker/gen_blogs');
	}
}

$content = '<div id="faker-log">' . $fakes . '</div>';
$delete = elgg_view('output/url', array(
	'text' => elgg_echo('faker:delete'),
	'href' => 'action/faker/delete',
	'is_action' => true,
	'class' => 'elgg-button elgg-button-action elgg-requires-confirmation',
		));

echo '<div class="clearfix">';
echo '<div class="elgg-col elgg-col-1of2">';
echo '<div class="pam">';
echo $forms;
echo '</div>';
echo '</div>';
echo '<div class="elgg-col elgg-col-1of2">';
echo '<div class="pam">';
echo elgg_view_module('aside', elgg_echo('faker:data'), $content, array(
	'footer' => $delete
));
echo '</div>';
echo '</div>';
echo '</div>';
