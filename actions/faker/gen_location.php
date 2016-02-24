<?php

if (!elgg_is_active_plugin('countries')) {
	forward(REFERRER);
}

set_time_limit(0);

$success = $error = 0;


$exclude = array(
	'messages',
	'plugin',
	'widget',
	'site_notification',
);

foreach ($exclude as $k => $e) {
	$exclude[$k] = get_subtype_id('object', $e);
}
$exclude_ids = implode(',', array_filter($exclude));

$location_md = elgg_get_metastring_id('location');
$lat_md = elgg_get_metastring_id('geo:lat');
$long_md = elgg_get_metastring_id('geo:long');

$dbprefix = elgg_get_config('dbprefix');
$entities = new ElggBatch('elgg_get_entities', array(
	'limit' => 0,
	'wheres' => array(
		($exclude_ids) ? "e.subtype NOT IN ($exclude_ids)" : null,
		"NOT EXISTS (SELECT 1 FROM {$dbprefix}metadata WHERE entity_guid = e.guid AND name_id = $location_md)",
	)
		));

$countries = elgg_get_country_info(array('name', 'capital'));

foreach ($entities as $entity) {
	$country = $countries[array_rand($countries, 1)];
	$location = "{$country['capital']}, {$country['name']}";
	$entity->setLocation($location);
	if ($entity->save()) {
		error_log("New location for $entity->guid: {$entity->getLocation()}");
		$success++;
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_location:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_location:success', array($success)));
}

forward(REFERER);
