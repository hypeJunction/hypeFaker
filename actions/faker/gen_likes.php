<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

$entities = new \ElggBatch('elgg_get_entities_from_metadata', [
	'types' => 'object',
	'limit' => 0,
	'metadata_names' => '__faker',
]);

foreach ($entities as $entity) {

	if ($entity->getContainerEntity() instanceof \ElggGroup) {
		$users = elgg_get_entities_from_relationship(array(
			'types' => 'user',
			'limit' => rand(1, $count),
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
			'relationship' => 'member',
			'relationship_guid' => $entity->container_guid,
			'inverse_relationship' => true,
		));
	} else {
		$users = elgg_get_entities_from_relationship(array(
			'types' => 'user',
			'limit' => rand(1, $count),
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

	foreach ($users as $user) {

		if (elgg_annotation_exists($entity->guid, 'likes')) {
			continue;
		}

		if (!$entity->canAnnotate($user->guid, 'likes')) {
			continue;
		}

		$annotation_id = create_annotation($entity->guid,
										'likes',
										"likes",
										"",
										$user->guid,
										$entity->access_id);

		$annotation_id  ? $success++ : $error++;

	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_likes:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_likes:success', array($success)));
}

forward(REFERER);
