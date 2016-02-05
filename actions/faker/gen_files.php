<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

for ($i = 0; $i < $count; $i++) {

	$users = elgg_get_entities_from_metadata(array(
		'types' => 'user',
		'limit' => 1,
		'order_by' => 'RAND()',
		'metadata_names' => '__faker',
	));
	$owner = $users[0];

	$containers = array($owner);

	$groups = $owner->getGroups(array(), 100);
	if ($groups) {
		$containers = array_merge($containers, $groups);
	}

	foreach ($containers as $container) {

		elgg_set_page_owner_guid($container->guid);

		$access_array = get_write_access_array($owner->guid);
		$access_id = array_rand($access_array, 1);

		$file = new ElggFile();
		$file->originalfilename = implode('_', $faker->words(3)) . '.jpg';
		$file->setFilename("files/{$file->originalfilename}");
		$file->owner_guid = $owner->guid;
		$file->container_guid = $container->guid;
		$file->title = $faker->sentence(6);
		$file->description = $faker->text(500);
		$file->tags = $faker->words(5);
		$file->access_id = $access_id;
		$file->__faker = true;
		$file_url = $faker->imageURL();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $file_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$file_contents = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		curl_close($ch);

		$mime_type = $curl_info['content_type'];
		$file->setMimeType($mime_type);
		$file->simpletype = file_get_simple_type($mime_type);

		$file->open('write');
		$file->write($file_contents);
		$file->close();

		if ($file->save()) {
			$success++;
			if (substr_count($mime_type, 'image/')) {
				$file->icontime = time();
				$prefix = "files/";
				$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
				if ($thumbnail) {
					$thumb = new ElggFile();
					$thumb->owner_guid = $file->owner_guid;
					$thumb->setFilename($prefix . "thumb" . $file->originalfilename);
					$thumb->open("write");
					$thumb->write($thumbnail);
					$thumb->close();

					$file->thumbnail = $prefix . "thumb" . $file->originalfilename;
					unset($thumbnail);
				}

				$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
				if ($thumbsmall) {
					$thumb = new ElggFile();
					$thumb->owner_guid = $file->owner_guid;
					$thumb->setFilename($prefix . "smallthumb" . $file->originalfilename);
					$thumb->open("write");
					$thumb->write($thumbsmall);
					$thumb->close();
					$file->smallthumb = $prefix . "smallthumb" . $file->originalfilename;
					unset($thumbsmall);
				}

				$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
				if ($thumblarge) {
					$thumb = new ElggFile();
					$thumb->owner_guid = $file->owner_guid;
					$thumb->setFilename($prefix . "largethumb" . $file->originalfilename);
					$thumb->open("write");
					$thumb->write($thumblarge);
					$thumb->close();
					$file->largethumb = $prefix . "largethumb" . $file->originalfilename;
					unset($thumblarge);
				}
			}


			elgg_create_river_item(array(
				'view' => 'river/object/file/create',
				'action_type' => 'create',
				'subject_guid' => $file->owner_guid,
				'object_guid' => $file->guid,
			));
		} else {
			$error++;
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_files:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_files:success', array($success)));
}

forward(REFERER);
