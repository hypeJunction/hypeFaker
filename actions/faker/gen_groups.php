<?php

namespace hypeJunction\Faker;

use ElggFile;
use ElggGroup;
use Faker as F;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');
$featured_count = (int) get_input('featured_count');

$faker = F\Factory::create(LOCALE);

foreach (array(ACCESS_PRIVATE, ACCESS_LOGGED_IN, ACCESS_PUBLIC) as $visibility) {
	foreach (array(get_group_content_access_mode('members_only'), get_group_content_access_mode('unrestricted')) as $content_access_mode) {
		foreach (array(ACCESS_PRIVATE, ACCESS_PUBLIC) as $membership) {

			for ($i = 0; $i < $count; $i++) {

				$users = elgg_get_entities_from_metadata(array(
					'types' => 'user',
					'limit' => 1,
					'order_by' => 'RAND()',
					'metadata_names' => '__faker',
				));

				$owner = $users[0];

				$group = new ElggGroup();
				$group->name = $faker->sentence(5);
				$group->owner_guid = $owner->guid;
				$group->container_guid = $owner->guid;
				$group->description = $faker->text(500);
				$group->briefdescription = $faker->bs;
				$group->interests = $faker->words(10);
				$group->access_id = ACCESS_PUBLIC;
				$group->membership = $membership;
				$group->content_access_mode = $content_access_mode;

				$guid = $group->save();

				if (!$guid) {
					$errors++;
					continue;
				}

				if ($visibility != ACCESS_PUBLIC && $visibility != ACCESS_LOGGED_IN) {
					$visibility = $group->group_acl;
				}

				if ($group->access_id != $visibility) {
					$group->access_id = $visibility;
				}

				$group->__faker = true; // store this flag so we can easily find fake entities

				$group->join($owner);

				$icon_sizes = elgg_get_config('icon_sizes');

				$files = array();
				$profile_icon_url = $faker->imageURL();

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $profile_icon_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				$file_contents = curl_exec($ch);
				$curl_info = curl_getinfo($ch);
				curl_close($ch);

				$mime_type = $curl_info['content_type'];

				if (substr_count($mime_type, 'image/')) {

					$prefix = "groups/{$group->guid}";

					$filehandler = new ElggFile();
					$filehandler->owner_guid = $group->owner_guid;
					$filehandler->setFilename("{$prefix}.jpg");
					$filehandler->open('write');
					$filehandler->write($file_contents);
					$filehandler->close();

					foreach ($icon_sizes as $name => $size_info) {
						$resized = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $size_info['w'], $size_info['h'], $size_info['square'], 0, 0, 0, 0, $size_info['upscale']);

						if ($resized) {
							$file = new ElggFile();
							$file->owner_guid = $group->owner_guid;
							$file->setFilename("{$prefix}{$name}.jpg");
							$file->open('write');
							$file->write($resized);
							$file->close();
							$files[] = $file;
						} else {
							$avatar_error = true;
						}
					}

					if (!empty($avatar_error)) {
						foreach ($files as $file) {
							$file->delete();
							$filehandler->delete();
						}
					} else {
						$group->icontime = time();
					}
				}

				elgg_create_river_item(array(
					'view' => 'river/group/create',
					'action_type' => 'create',
					'subject_guid' => $owner->guid,
					'object_guid' => $group->guid,
				));

				$tool_options = elgg_get_config('group_tool_options');
				if ($tool_options) {
					foreach ($tool_options as $group_option) {
						$option_toggle_name = $group_option->name . "_enable";
						$option_default = $group_option->default_on ? 'yes' : 'no';
						$group->$option_toggle_name = $option_default;
					}
				}

				if ($group->save()) {
					$success++;
					$groups[$group->guid] = $group;
				} else {
					$error++;
				}
			}
		}
	}
}

if (!empty($groups)) {

	$featured_group_keys = array_rand($groups, $featured_count);

	foreach ($groups as $key => $group) {
		if (in_array($key, $featured_group_keys)) {
			$group->featured_group = "yes";
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_groups:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_groups:success', array($success)));
}

forward(REFERER);
