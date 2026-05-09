<?php

use Faker\Factory;

set_time_limit(0);

/**
 * Create a fake wire post on behalf of $owner, optionally as a reply to $parent.
 *
 * @param \ElggUser        $owner  User authoring the wire post
 * @param \ElggObject|null $parent Optional parent wire post being replied to
 * @return \ElggObject|false
 */
function hypefaker_add_wire($owner, $parent = null) {
	$locale = elgg_get_plugin_setting('locale', 'hypefaker', 'en_US');
	$faker = Factory::create($locale);
	$wire = new \ElggWire();
	$wire->owner_guid = $owner->guid;
	$tags = $faker->words(5);
	$text = $faker->text(80);
	foreach ($tags as $tag) {
		$text .= " #{$tag}";
	}

	if ($parent) {
		$wire->reply = true;
		$username = $parent->getOwnerEntity()->username;
		$text = "@{$username} {$text}";
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

		elgg_create_river_item(['view' => 'river/object/thewire/create', 'action_type' => 'create', 'subject_guid' => $wire->owner_guid, 'object_guid' => $wire->guid]);
		$params = ['entity' => $wire, 'user' => $owner, 'message' => $wire->description, 'url' => $wire->getURL(), 'origin' => 'thewire'];
		elgg_trigger_event_results('status', 'user', $params);
		return $wire;
	}

	return false;
}

$error = 0;
$success = $error;
$count = (int) get_input('count');
$max_replies = (int) get_input('max_replies');
$users = elgg_get_entities(['types' => 'user', 'limit' => $count, 'order_by' => 'RAND()', 'metadata_names' => '__faker']);
foreach ($users as $user) {
	$wire = hypefaker_add_wire($user);
	if ($wire) {
		$success++;
		$replies = rand(1, $max_replies);
		if ($replies) {
			$responders = elgg_get_entities([
				'types' => 'user',
				'limit' => $replies,
				'order_by' => 'RAND()',
				'metadata_names' => '__faker',
				'wheres' => [
					function(\Elgg\Database\QueryBuilder $qb, $alias) use ($user) {
						return $qb->compare("{$alias}.guid", '!=', $user->guid, ELGG_VALUE_INTEGER);
					}
				],
			]);
			foreach ($responders as $responder) {
				if (hypefaker_add_wire($responder, $wire)) {
					$success++;
				}
			}
		}
	}
}

if ($error) {
	elgg_register_success_message(elgg_echo('faker:gen_wire:error', [$success, $error]));
} else {
	elgg_register_success_message(elgg_echo('faker:gen_wire:success', [$success]));
}

return elgg_redirect_response(REFERRER);
