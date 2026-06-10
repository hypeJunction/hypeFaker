<?php

set_time_limit(0);
$rel_membership_request = 0;
$rel_invited = $rel_membership_request;
$rel_member = $rel_invited;
$member_count_max = (int) get_input('max');
$groups = new ElggBatch('elgg_get_entities', ['types' => 'group', 'metadata_names' => '__faker', 'limit' => 0]);
foreach ($groups as $group) {
	$group->removeAllRelationships('member', true);
	$group->removeAllRelationships('membership_request', true);
	$group->removeAllRelationships('invited');
	$acl = $group->getOwnedAccessCollection('group_acl');
	if ($acl) {
		$acl_members = $acl->getMembers(['limit' => 0]);
		if (is_array($acl_members)) {
			foreach ($acl_members as $acl_member) {
				$acl->removeMember((int) $acl_member->guid);
			}
		}

		$acl->addMember((int) $group->owner_guid);
	}

	$owner = $group->owner_guid ? get_entity((int) $group->owner_guid) : null;
	if ($owner instanceof ElggUser) {
		$group->join($owner);
	}
	$members_count = rand(1, $member_count_max);
	$members = elgg_get_entities(['types' => 'user', 'limit' => $members_count, 'order_by' => 'RAND()', 'metadata_names' => '__faker']);
	foreach ($members as $member) {
		if ($group->join($member)) {
			$rel_member++;
		}
	}

	if (!$group->isPublicMembership()) {
		$invites_count = rand(1, $member_count_max);
		$invitees = elgg_get_entities(['types' => 'user', 'limit' => $invites_count, 'order_by' => 'RAND()', 'metadata_names' => '__faker']);
		foreach ($invitees as $invitee) {
			if (!$invitee->hasRelationship($group->guid, 'member')) {
				if ($group->addRelationship($invitee->guid, 'invited')) {
					$rel_invited++;
				}
			}
		}

		$requests_count = rand(1, $member_count_max);
		$requestors = elgg_get_entities(['types' => 'user', 'limit' => $requests_count, 'order_by' => 'RAND()', 'metadata_names' => '__faker']);
		foreach ($requestors as $requestor) {
			if (!$group->hasRelationship($requestor->guid, 'invited') && !$requestor->hasRelationship($group->guid, 'member')) {
				if ($requestor->addRelationship($group->guid, 'membership_request')) {
					$rel_membership_request++;
				}
			}
		}
	}
}

elgg_register_success_message(elgg_echo('faker:gen_group_members:success', [$rel_member, $rel_invited, $rel_membership_request, count($groups)]));
return elgg_redirect_response(REFERRER);
