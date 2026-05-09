<?php

namespace Hypejunction\Faker\Seeds;

use Elgg\Database\Seeds\Seed;
use ElggComment;
use ElggGroup;

class Discussions extends Seed {

	public function seed() {
		$count = function () {
			return elgg_count_entities([
				'types' => 'object',
				'subtypes' => 'discussion',
				'metadata_names' => '__faker',
			]);
		};

		$this->advance($count());

		$statuses = ['open', 'closed'];

		while ($count() < $this->limit) {
			$owner = $this->getRandomUser();
			if (!$owner) {
				$owner = $this->createUser();
			}

			$containers = [$owner];
			$groups = $owner->getGroups([], 5);
			if ($groups) {
				$containers = array_merge($containers, $groups);
			}

			foreach ($containers as $container) {
				$discussion = $this->createObject([
					'subtype' => 'discussion',
					'owner_guid' => $owner->guid,
					'container_guid' => $container->guid,
					'access_id' => ACCESS_PUBLIC,
					'status' => $statuses[array_rand($statuses)],
					'title' => $this->faker()->sentence(6),
					'description' => $this->faker()->text(500),
					'tags' => $this->faker()->words(5),
				]);

				if (!$discussion) {
					continue;
				}

				elgg_create_river_item([
					'view' => 'river/object/discussion/create',
					'action_type' => 'create',
					'subject_guid' => $owner->guid,
					'object_guid' => $discussion->guid,
				]);

				$reply_count = $this->faker()->numberBetween(1, 5);
				$members = ($container instanceof ElggGroup)
					? $container->getMembers(['limit' => 10])
					: $owner->getFriends(['limit' => 10]);

				if ($members) {
					for ($k = 0; $k < $reply_count; $k++) {
						$replier = $members[array_rand($members)];
						$reply = new ElggComment();
						$reply->description = $this->faker()->text();
						$reply->owner_guid = $replier->guid;
						$reply->container_guid = $discussion->guid;
						$reply->access_id = ACCESS_PUBLIC;
						$reply->__faker = true;
						if ($reply->save()) {
							elgg_create_river_item([
								'view' => 'river/object/comment/create',
								'action_type' => 'comment',
								'subject_guid' => $replier->guid,
								'object_guid' => $reply->guid,
								'target_guid' => $discussion->guid,
							]);
						}
					}
				}

				$this->advance();

				if ($count() >= $this->limit) {
					break;
				}
			}
		}
	}

	public function unseed() {
		$discussions = elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'discussion',
			'metadata_names' => '__faker',
			'limit' => 0,
			'batch' => true,
		]);

		/* @var $discussions \ElggBatch */
		$discussions->setIncrementOffset(false);

		foreach ($discussions as $discussion) {
			if ($discussion->delete()) {
				$this->log("Deleted discussion $discussion->guid");
			}
			$this->advance();
		}
	}
}
