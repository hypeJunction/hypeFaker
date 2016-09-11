<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');
$reply_count = (int) get_input('reply_count', 0);

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

$statuses = array('unsaved_draft', 'draft', 'published');

$entities = new \ElggBatch('elgg_get_entities_from_metadata', [
	'types' => 'object',
	'subtypes' => ['blog', 'bookmarks', 'file', 'page', 'page_top'],
	'limit' => 0,
	'metadata_names' => '__faker',
]);

foreach ($entities as $entity) {

	if ($entity->getContainerEntity() instanceof \ElggGroup) {
		$users = elgg_get_entities_from_relationship(array(
			'types' => 'user',
			'limit' => $count,
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
			'relationship' => 'member',
			'relationship_guid' => $entity->container_guid,
			'inverse_relationship' => true,
		));
	} else {
		$users = elgg_get_entities_from_relationship(array(
			'types' => 'user',
			'limit' => $count,
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
			'relationship' => 'friend',
			'relationship_guid' => $entity->owner_guid,
		));
	}

	if (!$users) {
		$users = [];
	}

	$users[] = $entity->getOwnerEntity();

	for ($i = 0; $i < $count; $i++) {

		$owner = $users[array_rand($users, 1)];

		if (!$entity->canComment($owner->guid)) {
			$error++;
			continue;
		}
		
		$comment = new \ElggComment();
		$comment->subtype = 'comment';
		$comment->owner_guid = $owner->guid;
		$comment->container_guid = $entity->guid;
		$comment->description = $faker->text(rand(25, 1000));
		$comment->access_id = $entity->access_id;
		$comment->time_created = rand($entity->time_created, time());

		if ($comment->save()) {
			$success++;

			for ($k = 0; $k < $reply_count; $k++) {
				$owner = $users[array_rand($users, 1)];

				if ($comment->canComment($owner->guid)) {
					$reply = new \ElggComment();
					$reply->subtype = 'comment';
					$reply->owner_guid = $owner->guid;
					$reply->container_guid = $comment->guid;
					$reply->description = $faker->text(rand(25, 1000));
					$reply->access_id = $entity->access_id;
					$reply->time_created = rand($comment->time_created, time());

					$reply->save() ? $success++ : $error++;
				}
			}
		} else {
			$error++;
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_comments:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_comments:success', array($success)));
}

forward(REFERER);
