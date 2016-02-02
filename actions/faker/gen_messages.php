<?php

use Faker\Factory;
use const hypeJunction\Faker\LOCALE;

set_time_limit(0);

$success = $error = 0;

$count = (int) get_input('count');

$faker = Factory::create(LOCALE);

global $messagesendflag;
$messagesendflag = 1;

global $messages_pm;
$messages_pm = 1;

$senders = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'user',
	'metadata_names' => '__faker',
	'limit' => 0
		));

foreach ($senders as $sender) {

	for ($i = 0; $i < $count; $i++) {
		$users = elgg_get_entities_from_metadata(array(
			'types' => 'user',
			'limit' => 1,
			'order_by' => 'RAND()',
			'metadata_names' => '__faker',
		));
		$recipient = $users[0];

		if (!$sender || !$recipient) {
			continue;
		}

		$sender_guid = $sender->guid;
		$recipient_guid = $recipient->guid;
		$subject = $faker->sentence();
		$body = $faker->text(1000);

		$message_to = new ElggObject();
		$message_sent = new ElggObject();

		$message_to->subtype = "messages";
		$message_sent->subtype = "messages";

		$message_to->owner_guid = $recipient_guid;
		$message_to->container_guid = $recipient_guid;
		$message_sent->owner_guid = $sender_guid;
		$message_sent->container_guid = $sender_guid;

		$message_to->access_id = ACCESS_PUBLIC;
		$message_sent->access_id = ACCESS_PUBLIC;

		$message_to->title = $subject;
		$message_to->description = $body;

		$message_sent->title = $subject;
		$message_sent->description = $body;

		$message_to->toId = $recipient_guid; // the user receiving the message
		$message_to->fromId = $sender_guid; // the user receiving the message
		$message_to->readYet = rand(0, 1); // this is a toggle between 0 / 1 (1 = read)
		$message_to->hiddenFrom = 0; // this is used when a user deletes a message in their sentbox, it is a flag
		$message_to->hiddenTo = 0; // this is used when a user deletes a message in their inbox

		$message_sent->toId = $recipient_guid; // the user receiving the message
		$message_sent->fromId = $sender_guid; // the user receiving the message
		$message_sent->readYet = rand(0, 1); // this is a toggle between 0 / 1 (1 = read)
		$message_sent->hiddenFrom = 0; // this is used when a user deletes a message in their sentbox, it is a flag
		$message_sent->hiddenTo = 0; // this is used when a user deletes a message in their inbox

		$message_to->msg = 1;
		$message_sent->msg = 1;

		$message_to->__faker = true;
		$message_from->__faker = true;
		
		$success = $message_to->save();
		$sucess_sent = $message_sent->save();

		if ($success && $seccess_sent) {
			$success++;
			$message_to->access_id = ACCESS_PRIVATE;
			$message_to->save();

			$message_sent->access_id = ACCESS_PRIVATE;
			$message_sent->save();

			$message_contents = strip_tags($body);
			$subject = elgg_echo('messages:email:subject');
			$body = elgg_echo('messages:email:body', array(
				$sender->name,
				$message_contents,
				elgg_get_site_url() . "messages/inbox/" . $recipient->username,
				$sender->name,
				elgg_get_site_url() . "messages/compose?send_to=" . $sender_guid
			));

			notify_user($recipient_guid, $sender_guid, $subject, $body);
		} else {
			$error++;
		}
	}
}

if ($error) {
	system_message(elgg_echo('faker:gen_messages:error', array($success, $error)));
} else {
	system_message(elgg_echo('faker:gen_messages:success', array($success)));
}

forward(REFERER);
