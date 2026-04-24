<?php

if (!elgg_is_active_plugin('countries')) {
    return elgg_redirect_response(REFERRER);
}
set_time_limit(0);
$success = $error = 0;
// In Elgg 3.x, subtypes are stored as strings, no need for get_subtype_id()
$exclude_subtypes = array('messages', 'plugin', 'widget', 'site_notification');
$entities = new ElggBatch('elgg_get_entities', array(
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
));
$countries = elgg_get_country_info(array('name', 'capital'));
foreach ($entities as $entity) {
    $country = $countries[array_rand($countries, 1)];
    $location = "{$country['capital']}, {$country['name']}";
    $entity->location = $location;
    if ($entity->save()) {
        error_log("New location for {$entity->guid}: {$entity->location}");
        $success++;
    }
}
if ($error) {
    elgg_register_success_message(elgg_echo('faker:gen_location:error', array($success, $error)));
} else {
    elgg_register_success_message(elgg_echo('faker:gen_location:success', array($success)));
}
return elgg_redirect_response(REFERRER);
