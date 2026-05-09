<?php

namespace Hypejunction\Faker\Seeds;

use Elgg\Database\Seeds\Seed;

class Wire extends Seed {

	public function seed() {
		$count = function () {
			return elgg_count_entities([
				'types' => 'object',
				'subtypes' => 'thewire',
				'metadata_names' => '__faker',
			]);
		};

		$this->advance($count());

		while ($count() < $this->limit) {
			$owner = $this->getRandomUser();
			if (!$owner) {
				$owner = $this->createUser();
			}

			$tags = $this->faker()->words(3);
			$text = $this->faker()->text(80);
			foreach ($tags as $tag) {
				$text .= " #{$tag}";
			}

			$limit = elgg_get_plugin_setting('limit', 'thewire');
			if ($limit > 0) {
				$text = elgg_substr($text, 0, (int) $limit);
			}

			$wire = $this->createObject([
				'subtype' => 'thewire',
				'owner_guid' => $owner->guid,
				'container_guid' => $owner->guid,
				'access_id' => ACCESS_PUBLIC,
				'description' => htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8'),
				'tags' => $tags,
				'method' => 'faker',
				'wire_thread' => 0,
			]);

			if (!$wire) {
				continue;
			}

			$wire->wire_thread = $wire->guid;
			$wire->save();

			elgg_create_river_item([
				'view' => 'river/object/thewire/create',
				'action_type' => 'create',
				'subject_guid' => $wire->owner_guid,
				'object_guid' => $wire->guid,
			]);

			$reply_count = $this->faker()->numberBetween(0, 3);
			$exclude = [$owner->guid];

			for ($k = 0; $k < $reply_count; $k++) {
				$responder = $this->getRandomUser($exclude);
				if (!$responder) {
					break;
				}
				$exclude[] = $responder->guid;

				$reply_text = '@' . $owner->username . ' ' . $this->faker()->text(60);
				if ($limit > 0) {
					$reply_text = elgg_substr($reply_text, 0, (int) $limit);
				}

				$reply = $this->createObject([
					'subtype' => 'thewire',
					'owner_guid' => $responder->guid,
					'container_guid' => $responder->guid,
					'access_id' => ACCESS_PUBLIC,
					'description' => htmlspecialchars($reply_text, ENT_NOQUOTES, 'UTF-8'),
					'method' => 'faker',
					'reply' => true,
					'wire_thread' => $wire->guid,
				]);

				if ($reply) {
					$reply->addRelationship($wire->guid, 'parent');
					elgg_create_river_item([
						'view' => 'river/object/thewire/create',
						'action_type' => 'create',
						'subject_guid' => $reply->owner_guid,
						'object_guid' => $reply->guid,
					]);
				}
			}

			$this->advance();
		}
	}

	public function unseed() {
		$wires = elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'thewire',
			'metadata_names' => '__faker',
			'limit' => 0,
			'batch' => true,
		]);

		/* @var $wires \ElggBatch */
		$wires->setIncrementOffset(false);

		foreach ($wires as $wire) {
			if ($wire->delete()) {
				$this->log("Deleted wire post $wire->guid");
			}
			$this->advance();
		}
	}
}
