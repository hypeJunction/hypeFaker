<?php

/**
 * Faker
 *
 * @package hypeJunction
 * @subpackage Faker
 *
 * @author Ismayil Khayredinov <ismayil@hypejunction.com>
 */

require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'hypefaker_init');

/**
 * Initialize the plugin
 * @return void
 */
function hypefaker_init() {

	elgg_register_action('faker/delete', __DIR__ . '/actions/faker/delete.php', 'admin');
	elgg_register_action('faker/gen_users', __DIR__ . '/actions/faker/gen_users.php', 'admin');
	elgg_register_action('faker/gen_friends', __DIR__ . '/actions/faker/gen_friends.php', 'admin');
	elgg_register_action('faker/gen_groups', __DIR__ . '/actions/faker/gen_groups.php', 'admin');
	elgg_register_action('faker/gen_group_members', __DIR__ . '/actions/faker/gen_group_members.php', 'admin');
	elgg_register_action('faker/gen_blogs', __DIR__ . '/actions/faker/gen_blogs.php', 'admin');
	elgg_register_action('faker/gen_bookmarks', __DIR__ . '/actions/faker/gen_bookmarks.php', 'admin');
	elgg_register_action('faker/gen_files', __DIR__ . '/actions/faker/gen_files.php', 'admin');
	elgg_register_action('faker/gen_pages', __DIR__ . '/actions/faker/gen_pages.php', 'admin');
	elgg_register_action('faker/gen_wire', __DIR__ . '/actions/faker/gen_wire.php', 'admin');
	elgg_register_action('faker/gen_messages', __DIR__ . '/actions/faker/gen_messages.php', 'admin');
	elgg_register_action('faker/gen_discussions', __DIR__ . '/actions/faker/gen_discussions.php', 'admin');

	// Add an admin menu item
	elgg_register_menu_item('page', array(
		'name' => 'faker',
		'href' => 'admin/developers/faker',
		'text' => elgg_echo('admin:developers:faker'),
		'context' => 'admin',
		'section' => 'develop'
	));
}