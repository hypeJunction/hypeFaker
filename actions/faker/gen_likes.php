<?php

use Faker\Factory;
set_time_limit(0);
$success = $error = 0;
$count = (int) get_input('count');
$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);
$entities = new \ElggBatch('elgg_get_entities', ['types' => 'object', 'limit' => 0, 'metadata_names' => '__faker']);
foreach ($entities as $entity) {
    if ($entity->getContainerEntity() instanceof \ElggGroup) {
        $users = elgg_get_entities(array('types' => 'user', 'limit' => rand(1, $count), 'order_by' => 'RAND()', 'metadata_names' => '__faker', 'relationship' => 'member', 'relationship_guid' => $entity->container_guid, 'inverse_relationship' => true));
    } else {
        $users = elgg_get_entities(array('types' => 'user', 'limit' => rand(1, $count), 'order_by' => 'RAND()', 'metadata_names' => '__faker', 'relationship' => 'friend', 'relationship_guid' => $entity->owner_guid));
    }
    if (!$users) {
        $users = [];
    }
    $users[] = $entity->getOwnerEntity();
    foreach ($users as $user) {
        // Check if this user already liked this entity
        $existing = elgg_get_annotations([
            'guid' => $entity->guid,
            'annotation_name' => 'likes',
            'annotation_owner_guid' => $user->guid,
            'count' => true,
        ]);
        if ($existing) {
            continue;
        }
        if (!$entity->canAnnotate($user->guid, 'likes')) {
            continue;
        }
        // In Elgg 3.x, use $entity->annotate() instead of procedural create_annotation()
        $annotation_id = $entity->annotate('likes', "likes", $entity->access_id, $user->guid);
        $annotation_id ? $success++ : $error++;
    }
}
if ($error) {
    elgg_register_success_message(elgg_echo('faker:gen_likes:error', array($success, $error)));
} else {
    elgg_register_success_message(elgg_echo('faker:gen_likes:success', array($success)));
}
forward(REFERER);
