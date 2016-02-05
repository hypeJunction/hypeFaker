<?php

use Faker\Factory;

set_time_limit(0);

function add_page($owner, $container, $parent = null) {

	$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
	$faker = Factory::create($locale);

	$access_array = get_write_access_array($owner->guid);
	$access_id = array_rand($access_array, 1);

	$write_access_array = get_write_access_array($owner->guid);
	unset($write_access_array[ACCESS_PUBLIC]);
	$write_access_id = array_rand($write_access_array, 1);

	$page = new ElggObject();
	$page->subtype = ($parent) ? 'page' : 'page_top';
	$page->owner_guid = $owner->guid;
	$page->container_guid = $container->guid;
	$page->title = $faker->sentence(6);
	$page->description = $faker->text(500);
	$page->tags = $faker->words(5);
	$page->access_id = $access_id;
	$page->write_access_id = $write_access_id;
	$page->__faker = true;

	if ($parent) {
		$page->parent_guid = $parent->guid;
	}

	if ($page->save()) {
		$page->annotate('page', $page->description, $page->access_id, $page->owner_guid);

		elgg_create_river_item(array(
			'view' => 'river/object/page/create',
			'action_type' => 'create',
			'subject_guid' => $page->owner_guid,
			'object_guid' => $page->getGUID(),
		));

		// add some revisions
		$users = elgg_get_entities_from_metadata(array(
			'types' => 'user',
			'limit' => rand(1, 10),
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
		));
		foreach ($users as $user) {
			if ($page->canAnnotate($user->guid, 'page')) {
				$last_revision = $faker->text(500);
				$page->annotate('page', $last_annotation, $page->access_id, $user->guid);
			}
		}

		if (!empty($last_revision)) {
			$page->description = $last_revision;
			$page->save();
		}

		return $page;
	}

	return false;
}

$success = $error = 0;

$count = (int) get_input('count');
$max_children = (int) get_input('max_children');

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

for ($i = 0; $i < $count; $i++) {

	$users = elgg_get_entities_from_metadata(array(
		'types' => 'user',
		'limit' => 1,
		'order_by' => 'RAND()',
		'metadata_names' => '__faker',
	));
	$owner = $users[0];

	$containers = array($owner);

	$groups = $owner->getGroups(array(), 100);
	if ($groups) {
		$containers = array_merge($containers, $groups);
	}

	foreach ($containers as $container) {

		elgg_set_page_owner_guid($container->guid);

		$parent = add_page($owner, $container, null);

		if ($parent) {
			$sucess++;
			$children_count = rand(0, $max_children);
			if ($children_count) {
				for ($i = 0; $i < $children_count; $i++) {
					if ($subparent = add_page($owner, $container, $parent)) {
						$success++;
						$subparent_children_count = rand(0, $max_children);
						for ($i = 0; $i < $subparent_children_count; $i++) {
							if (add_page($owner, $container, $subparent)) {
								$success++;
							}
						}
					}
				}
			}
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_pages:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_pages:success', array($success)));
}

forward(REFERER);
