<?php

use Faker\Factory;

set_time_limit(0);
$error = 0;
$success = $error;
$locale = elgg_get_plugin_setting('locale', 'hypefaker', 'en_US');
$faker = Factory::create($locale);
$exclude_subtypes = ['messages', 'plugin', 'widget', 'site_notification'];
$entities = new ElggBatch('elgg_get_entities', [
	'limit' => 0,
	'wheres' => [
		function(\Elgg\Database\QueryBuilder $qb, $alias) use ($exclude_subtypes) {
			$wheres = [];
			$wheres[] = $qb->compare("{$alias}.subtype", 'NOT IN', $exclude_subtypes, ELGG_VALUE_STRING);
			// Exclude entities that already have a location
			$md = $qb->joinMetadataTable($alias, 'guid', 'location', 'left');
			$wheres[] = $qb->compare("{$md}.value", 'IS NULL');
			return $qb->merge($wheres);
		}
	],
]);
foreach ($entities as $entity) {
	$location = "{$faker->city()}, {$faker->country()}";
	$entity->location = $location;
	if ($entity->save()) {
		error_log("New location for {$entity->guid}: {$entity->location}");
		$success++;
	}
}

if ($error) {
	elgg_register_success_message(elgg_echo('faker:gen_location:error', [$success, $error]));
} else {
	elgg_register_success_message(elgg_echo('faker:gen_location:success', [$success]));
}

return elgg_redirect_response(REFERRER);
