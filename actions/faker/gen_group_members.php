<?php

namespace hypeJunction\Faker;

use ElggBatch;

set_time_limit(0);

$rel_member = $rel_invited = $rel_membership_request = 0;

$member_count_max = (int) get_input('max');

$groups = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'group',
	'metadata_names' => '__faker',
	'limit' => 0
		));


foreach ($groups as $group) {
	
	remove_entity_relationships($group->guid, 'member', true);
	remove_entity_relationships($group->guid, 'membership_request', true);
	remove_entity_relationships($group->guid, 'invited');

	update_access_collection($group->group_acl, array($group->owner_guid));
	$group->join(get_entity($group->owner_guid));
	
	$members_count = rand(1, $member_count_max);
	$members = elgg_get_entities_from_metadata(array(
		'types' => 'user',
		'limit' => $members_count,
		'order_by' => 'RAND()',
		'metadata_names' => '__faker',
	));

	foreach ($members as $member) {
		if ($group->join($member)) {
			$rel_member++;
		}
	}

	if (!$group->isPublicMembership()) {
		$invites_count = rand(1, $member_count_max);
		$invitees = elgg_get_entities_from_metadata(array(
			'types' => 'user',
			'limit' => $invites_count,
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
		));
		foreach ($invitees as $invitee) {
			if (!check_entity_relationship($invitee->guid, 'member', $group->guid)) {
				if (add_entity_relationship($group->guid, 'invited', $invitee->guid)) {
					$rel_invited++;
				}
			}
		}

		$requests_count = rand(1, $member_count_max);
		$requestors = elgg_get_entities_from_metadata(array(
			'types' => 'user',
			'limit' => $requests_count,
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
		));
		foreach ($requestors as $requestor) {
			if (!check_entity_relationship($group->guid, 'invited', $requestor->guid) && !check_entity_relationship($requestor->guid, 'member', $group->guid)) {
				if (add_entity_relationship($user->guid, 'membership_request', $user->guid)) {
					$rel_membership_request++;
				}
			}
		}
	}
}

system_message(elgg_echo('faker:gen_group_members:success', array($rel_member, $rel_invited, $rel_membership_request, sizeof($groups))));

forward(REFERER);
