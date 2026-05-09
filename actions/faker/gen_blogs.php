<?php

use Faker\Factory;

set_time_limit(0);
$error = 0;
$success = $error;
$count = (int) get_input('count');
$locale = elgg_get_plugin_setting('locale', 'hypefaker', 'en_US');
$faker = Factory::create($locale);
$statuses = ['unsaved_draft', 'draft', 'published'];
for ($i = 0; $i < $count; $i++) {
	$users = elgg_get_entities(['types' => 'user', 'limit' => 1, 'order_by' => 'RAND()', 'metadata_names' => '__faker']);
	$owner = $users[0];
	$containers = [$owner];
	$groups = $owner->getGroups([], 100);
	if ($groups) {
		$containers = array_merge($containers, $groups);
	}

	foreach ($containers as $container) {
		elgg_set_page_owner_guid($container->guid);
		$access_array = get_write_access_array($owner->guid);
		$blog = new \ElggBlog();
		$blog->owner_guid = $owner->guid;
		$blog->container_guid = $container->guid;
		$blog->status = $statuses[array_rand($statuses, 1)];
		$access = array_rand($access_array, 1);
		if ($blog->status == 'unsaved_draft' || $blog->status == 'draft') {
			$blog->access_id = ACCESS_PRIVATE;
			$blog->future_access = $access;
		} else {
			$blog->access_id = $access;
		}

		$blog->title = $faker->sentence(6);
		$blog->description = $faker->text(500);
		$blog->excerpt = $faker->sentence(12);
		$blog->tags = $faker->words(5);
		$blog->__faker = true;
		if ($blog->save()) {
			$success++;
			if ($blog->status == 'published') {
				elgg_create_river_item(['view' => 'river/object/blog/create', 'action_type' => 'create', 'subject_guid' => $blog->owner_guid, 'object_guid' => $blog->getGUID()]);
				elgg_trigger_event('publish', 'object', $blog);
			}

			if (rand(0, 1)) {
				$blog->annotate('blog_auto_save', $faker->text(500), ACCESS_PRIVATE, $blog->owner_guid);
			}

			if (rand(0, 1) && $blog->status != 'unsaved_draft') {
				$blog->annotate('blog_revision', $blog->description, ACCESS_PRIVATE, $blog->owner_guid);
				$blog->description = $faker->text(500);
			}
		} else {
			$error++;
		}
	}
}

if ($error) {
	elgg_register_success_message(elgg_echo('faker:gen_blogs:error', [$success, $error]));
} else {
	elgg_register_success_message(elgg_echo('faker:gen_blogs:success', [$success]));
}

return elgg_redirect_response(REFERRER);
