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

	if (elgg_is_active_plugin('bookmarks')) {
		$forms .= elgg_view_form('faker/gen_bookmarks');
	}

	if (elgg_is_active_plugin('file')) {
		$forms .= elgg_view_form('faker/gen_files');
	}

	if (elgg_is_active_plugin('pages')) {
		$forms .= elgg_view_form('faker/gen_pages');
	}

	if (elgg_is_active_plugin('thewire')) {
		$forms .= elgg_view_form('faker/gen_wire');
	}

	if (elgg_is_active_plugin('messages')) {
		$forms .= elgg_view_form('faker/gen_messages');
	}
}

$fakes = elgg_list_entities_from_metadata(array(
	'metadata_names' => '__faker',
		));
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
?>
<style>
	#faker-log {
		min-height: 400px;
		overflow-y: scroll;
		padding: 10px;
		background: #f4f4f4;
		border: 1px solid #e8e8e8;
	}
</style>