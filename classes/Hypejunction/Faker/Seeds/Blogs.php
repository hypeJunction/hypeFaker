<?php

namespace Hypejunction\Faker\Seeds;

use Elgg\Database\Seeds\Seed;

class Blogs extends Seed {

	public function seed() {
		$count = function () {
			return elgg_count_entities([
				'types' => 'object',
				'subtypes' => 'blog',
				'metadata_names' => '__faker',
			]);
		};

		$this->advance($count());

		$statuses = ['unsaved_draft', 'draft', 'published'];

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
				$status = $statuses[array_rand($statuses)];

				$blog = $this->createObject([
					'subtype' => 'blog',
					'owner_guid' => $owner->guid,
					'container_guid' => $container->guid,
					'access_id' => $status === 'unsaved_draft' || $status === 'draft' ? ACCESS_PRIVATE : ACCESS_PUBLIC,
					'status' => $status,
					'title' => $this->faker()->sentence(6),
					'description' => $this->faker()->text(500),
					'excerpt' => $this->faker()->sentence(12),
					'tags' => $this->faker()->words(5),
				]);

				if ($blog && $status === 'published') {
					elgg_create_river_item([
						'view' => 'river/object/blog/create',
						'action_type' => 'create',
						'subject_guid' => $blog->owner_guid,
						'object_guid' => $blog->guid,
					]);
					elgg_trigger_event('publish', 'object', $blog);
				}

				$this->advance();

				if ($count() >= $this->limit) {
					break;
				}
			}
		}
	}

	public function unseed() {
		$blogs = elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'blog',
			'metadata_names' => '__faker',
			'limit' => 0,
			'batch' => true,
		]);

		/* @var $blogs \ElggBatch */
		$blogs->setIncrementOffset(false);

		foreach ($blogs as $blog) {
			if ($blog->delete()) {
				$this->log("Deleted blog $blog->guid");
			}
			$this->advance();
		}
	}
}
