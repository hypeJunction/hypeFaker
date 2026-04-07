<?php

use Faker\Factory;

set_time_limit(0);

$success = $error = 0;
$exceptions = array();

$count = (int) get_input('count');
$friends_count = (int) rand(1, $count);
$password = get_input('password');
$domain = get_input('email_domain');

$locale = elgg_get_plugin_setting('locale', 'hypeFaker', 'en_US');
$faker = Factory::create($locale);

for ($i = 0; $i < $count; $i++) {

	if (!$password) {
		$password = generate_random_cleartext_password();
	}

	$name = $faker->name;
	$username = strtolower(str_replace(' ', '', $name));
	$username = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
	$blacklist = '/[\x{0080}-\x{009f}\x{00a0}\x{2000}-\x{200f}\x{2028}-\x{202f}\x{3000}\x{e000}-\x{f8ff}]/u';
	$blacklist2 = array(' ', '\'', '/', '\\', '"', '*', '&', '?', '#', '%', '^', '(', ')', '{', '}', '[', ']', '~', '?', '<', '>', ';', '|', '¬', '`', '@', '-', '+', '=');
	$username = preg_replace($blacklist, '', $username);
	$username = str_replace($blacklist2, '.', $username);
	if ($domain) {
		$email = "{$username}@{$domain}";
	} else {
		$email = "{$username}@{$faker->safeEmailDomain}";
	}

	try {
		$guid = register_user($username, $password, $name, $email);
	} catch (Exception $e) {
		$exceptions[] = $e;
	}

	if (!$guid) {
		$error++;
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
		$user->twitter = "@" . $username;

		// In Elgg 3.x, save only master icon and let other sizes generate on demand
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
			$tmp = new ElggFile();
			$tmp->owner_guid = $user->guid;
			$tmp->setFilename("profile/{$user->guid}__src.jpg");
			$tmp->open('write');
			$tmp->write($file_contents);
			$tmp->close();

			if ($user->saveIconFromElggFile($tmp, 'icon')) {
				elgg_create_river_item(array(
					'view' => 'river/user/default/profileiconupdate',
					'action_type' => 'update',
					'subject_guid' => $user->guid,
					'object_guid' => $user->guid,
				));
			}

			$tmp->delete();
		}

		$user->setValidationStatus(true, 'FAKER');

		if ($user->save()) {
			$success++;
		} else {
			$error++;
		}
	}
}

if ($error) {
	elgg_register_success_message(elgg_echo('faker:gen_users:error', array($success, $error, implode('<br />', $exceptions))));
} else {
	elgg_register_success_message(elgg_echo('faker:gen_users:success', array($success)));
}

forward(REFERER);
