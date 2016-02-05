<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;
$exceptions = array();

$count = (int) get_input('count');
$friends_count = (int) rand(1, $count);
$password = get_input('password');

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

for ($i = 0; $i < $count; $i++) {

	if (!$password) {
		$password = generate_random_cleartext_password();
	}

	try {
		$guid = register_user($faker->userName, $password, $faker->name, $faker->safeEmail);
	} catch (Exception $e) {
		$exceptions[] = $e;
	}

	if (!$guid) {
		$errors++;
		continue;
	}

	$user = get_entity($guid);
	$user->__faker = true; // store this flag so we can easily find fake users

	$users[$guid] = $user;
}

if (!empty($users)) {

	foreach ($users as $guid => $user) {

		$user->description = $faker->text(200);
		$user->briefdescription = $faker->catchPhrase;
		$user->setLocation(implode(', ', array($faker->city, $faker->country)));
		$user->setLatLong($faker->latitude, $faker->longitude);
		$user->interests = $faker->words(rand(1, 10));
		$user->skills = $faker->words(rand(1, 10));
		$user->contactemail = $faker->companyEmail;
		$user->phone = $faker->phoneNumber;
		$user->mobile = $faker->phoneNumber;
		$user->website = $faker->url;
		$user->twitter = "@" . $faker->word;

		$icon_sizes = elgg_get_config('icon_sizes');

		$files = array();
		$avatar_error = false;
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
			$filehandler = new ElggFile();
			$filehandler->owner_guid = $user->guid;
			$filehandler->setFilename("profile/{$user->guid}__src.jpg");
			$filehandler->open('write');
			$filehandler->write($file_contents);
			$filehandler->close();

			foreach ($icon_sizes as $name => $size_info) {
				$resized = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $size_info['w'], $size_info['h'], $size_info['square'], 0, 0, 0, 0, $size_info['upscale']);

				if ($resized) {
					$file = new ElggFile();
					$file->owner_guid = $user->guid;
					$file->setFilename("profile/{$user->guid}{$name}.jpg");
					$file->open('write');
					$file->write($resized);
					$file->close();
					$files[] = $file;
				} else {
					$avatar_error = true;
				}
			}

			if ($avatar_error) {
				foreach ($files as $file) {
					$file->delete();
				}
			} else {
				$user->x1 = 0;
				$user->x2 = 0;
				$user->y1 = 0;
				$user->y2 = 0;

				$user->icontime = time();

				elgg_create_river_item(array(
					'view' => 'river/user/default/profileiconupdate',
					'action_type' => 'update',
					'subject_guid' => $user->guid,
					'object_guid' => $user->guid,
				));
			}

			$filehandler->delete();
		}

		elgg_set_user_validation_status($user->guid, true, 'FAKER');

		if ($user->save()) {
			$success++;
			set_user_notification_setting($user->guid, 'email', false);
			set_user_notification_setting($user->guid, 'site', true);
		} else {
			$error++;
		}
	}
}

if ($errors) {
	system_message(elgg_echo('faker:gen_users:error', array($success, $error, implode('<br />', $exceptions))));
} else {
	system_message(elgg_echo('faker:gen_users:success', array($success)));
}

forward(REFERER);
