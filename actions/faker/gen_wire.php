<?php

namespace hypeJunction\Faker;

use ElggObject;
use Faker as F;

set_time_limit(0);

function add_wire($owner, $parent = null) {

	$faker = F\Factory::create(LOCALE);

	$wire = new ElggObject();
	$wire->subtype = 'thewire';
	$wire->owner_guid = $owner->guid;

	$tags = $faker->words(5);
	$text = $faker->text(80);

	foreach ($tags as $tag) {
		$text .= " #{$tag}";
	}

	if ($parent) {
		$wire->reply = true;
		$username = $parent->getOwnerEntity()->username;
		$text = "@$username $text";
	}

	$limit = elgg_get_plugin_setting('limit', 'thewire');
	if ($limit > 0) {
		$text = elgg_substr($text, 0, $limit);
	}

	$wire->description = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
	$wire->tags = $tags;
	$wire->method = 'faker';
	$wire->access_id = ACCESS_PUBLIC;

	$wire->__faker = true;

	if ($wire->save()) {

		if ($parent) {
			$wire->addRelationship($parent->guid, 'parent');
			$wire->wire_thread = $parent->wire_thread;
		} else {
			$wire->wire_thread = $wire->guid;
		}

		elgg_create_river_item(array(
			'view' => 'river/object/thewire/create',
			'action_type' => 'create',
			'subject_guid' => $wire->owner_guid,
			'object_guid' => $wire->guid,
		));
		$params = array(
			'entity' => $wire,
			'user' => $owner,
			'message' => $wire->description,
			'url' => $wire->getURL(),
			'origin' => 'thewire',
		);
		elgg_trigger_plugin_hook('status', 'user', $params);

		return $wire;
	}

	return false;
}

$success = $error = 0;

$count = (int) get_input('count');
$max_replies = (int) get_input('max_replies');

$users = elgg_get_entities_from_metadata(array(
	'types' => 'user',
	'limit' => $count,
	'order_by' => 'RAND()',
	'metadata_names' => '__faker',
		));

foreach ($users as $user) {
	$wire = add_wire($user);
	if ($wire) {
		$success++;
		$replies = rand(1, $max_replies);
		if ($replies) {
			$responders = elgg_get_entities_from_metadata(array(
				'types' => 'user',
				'limit' => $replies,
				'order_by' => 'RAND()',
				'metadata_names' => '__faker',
				'wheres' => array("e.guid != $user->guid")
			));
			foreach ($responders as $responder) {
				if (add_wire($responder, $wire)) {
					$success++;
				}
			}
		}
	}
}


if ($error) {
	system_message(elgg_echo('faker:gen_wire:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_wire:success', array($success)));
}

forward(REFERER);
