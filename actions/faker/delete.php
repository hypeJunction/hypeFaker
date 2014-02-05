<?php

namespace hypeJunction\Faker;

use ElggBatch;

set_time_limit(0);

$hidden = access_get_show_hidden_status();
access_show_hidden_entities(true);

$data = new ElggBatch('elgg_get_entities_from_metadata', array(
	'types' => array('user', 'group'),
	'limit' => 0,
	'metadata_names' => '__faker',
));
$data->setIncrementOffset(false);

foreach ($data as $d) {
	$d->delete(true);
}

access_show_hidden_entities($hidden);