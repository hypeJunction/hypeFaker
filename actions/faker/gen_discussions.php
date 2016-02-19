<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');
$reply_count = (int) get_input('reply_count', 5);

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

$statuses = array('open', 'closed');

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

		$discussion = new ElggObject();
		$discussion->subtype = 'discussion';
		$discussion->owner_guid = $owner->guid;
		$discussion->container_guid = $container->guid;
		$discussion->status = $statuses[array_rand($statuses, 1)];

		$access = array_rand($access_array, 1);
		$discussion->access_id = $access;

		$discussion->title = $faker->sentence(6);
		$discussion->description = $faker->text(500);
		$discussion->tags = $faker->words(5);

		$discussion->__faker = true;

		if ($discussion->save()) {
			$success++;
			if ($discussion->status == 'published') {
				elgg_create_river_item(array(
					'view' => 'river/object/discussion/create',
					'action_type' => 'create',
					'subject_guid' => $owner->guid,
					'object_guid' => $discussion->guid,
				));
			}

			if ($container instanceof ElggGroup) {
				$members = $container->getMembers(['limit' => 10]);
			} else if ($container instanceof ElggUser) {
				$members = $container->getFriends(['limit' => 10]);
			}

			for ($k = 0; $k < $count; $k++) {
				$replier = $members[array_rand($members, 1)];
				$reply = new ElggDiscussionReply();
				$reply->description = $faker->text();
				$reply->owner_guid = $replier->guid;
				$reply->container_guid = $discussion->guid;
				$reply->save();

				elgg_create_river_item(array(
					'view' => 'river/object/discussion_reply/create',
					'action_type' => 'reply',
					'subject_guid' => $replier->guid,
					'object_guid' => $reply->guid,
					'target_guid' => $discussion->guid,
				));
			}
		} else {
			$error++;
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_discussions:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_discussions:success', array($success)));
}

forward(REFERER);
