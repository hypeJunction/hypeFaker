<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');

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

		$access_array = get_write_access_array($owner->guid);
		$access_id = array_rand($access_array, 1);

		$bookmark = new ElggObject();
		$bookmark->subtype = 'bookmarks';
		$bookmark->owner_guid = $owner->guid;
		$bookmark->container_guid = $container->guid;
		$bookmark->title = $faker->sentence(6);
		$bookmark->description = $faker->text(500);
		$bookmark->tags = $faker->words(5);
		$bookmark->address = $faker->url;
		$bookmark->access_id = $access_id;
		$bookmark->__faker = true;

		if ($bookmark->save()) {
			$success++;
			elgg_create_river_item(array(
				'view' => 'river/object/bookmarks/create',
				'action_type' => 'create',
				'subject_guid' => $owner->guid,
				'object_guid' => $bookmark->getGUID(),
			));
		} else {
			$error++;
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_bookmarks:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_bookmarks:success', array($success)));
}

forward(REFERER);
