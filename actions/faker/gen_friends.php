<?php

namespace hypeJunction\Faker;

use ElggBatch;

set_time_limit(0);

$rels = $collections = 0;
$max = (int) get_input('max', 20);
$friends_count = rand(1, $max);

$users = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'user',
	'metadata_names' => '__faker',
	'limit' => 0
		));

$dbprefix = elgg_get_config('dbprefix');

foreach ($users as $user) {
	
	remove_entity_relationships($user->guid, 'friend');

	$query = "SELECT ag.id FROM {$dbprefix}access_collections ag WHERE ag.owner_guid = $user->guid";
	$acls = get_data($query);
	foreach ($acls as $col) {
		delete_access_collection($col->id);
	}

	$friends = elgg_get_entities_from_metadata(array(
		'types' => 'user',
		'limit' => $friends_count,
		'order_by' => 'RAND()',
		'wheres' => array("e.guid != $user->guid"),
		'metadata_names' => '__faker',
	));
	$rand_friends = false;

	$collection_id = create_access_collection('Best Fake Friends Collection', $user->guid);
	if ($collection_id) {
		$rand_friends = array_rand($friends, rand(2, $friends_count));
		$collections++;
	}

	foreach ($friends as $friends_key => $friend) {
		if ($user->addFriend($friend->guid)) {
			$rels++;
			elgg_create_river_item(array(
				'view' => 'river/relationship/friend/create',
				'action_type' => 'friend',
				'subject_guid' => $user->guid,
				'object_guid' => $friend->guid,
			));
			if ($rand_friends && array_key_exists($friends_key, $rand_friends)) {
				add_user_to_access_collection($friend->guid, $collection_id);
			}
		}
	}

	$random_acl_members = elgg_get_entities_from_metadata(array(
		'types' => 'user',
		'limit' => 10,
		'order_by' => 'RAND()',
		'wheres' => array("e.guid != $user->guid"),
		'metadata_names' => '__faker',
	));

	if ($random_acl_members) {
		$collection_id = create_access_collection('Fake Arbitrary Collection', $user->guid);
		if ($collection_id) {
			$collections++;
			foreach ($random_acl_members as $random_acl_member) {
				add_user_to_access_collection($random_acl_member->guid, $collection_id);
			}
		}
	}
}

system_message(elgg_echo('faker:gen_friends:success', array($rels, $collections)));
forward(REFERER);
