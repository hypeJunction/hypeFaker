<?php

namespace hypeJunction\Faker;

use ElggBatch;

set_time_limit(0);

$hidden = access_get_show_hidden_status();
access_show_hidden_entities(true);

$data = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => 'user',
	'limit' => 0,
	'metadata_names' => '__faker',
));
$data->setIncrementOffset(false);

foreach ($data as $d) {
	$d->delete(true);
}

$fake_count = elgg_get_entities_from_metadata(array(
	'metadata_names' => '__faker',
	'count' => true
));


if (!$fake_count) {
	system_message(elgg_echo('faker:delete:success'));
} else {
	register_error(elgg_echo('faker:delete:error', $fake_count));
}

access_show_hidden_entities($hidden);