<?php

set_time_limit(0);

elgg_call(ELGG_SHOW_DISABLED_ENTITIES, function () {
    $data = new ElggBatch('elgg_get_entities', array(
        'types' => 'user',
        'limit' => 0,
        'metadata_names' => '__faker',
    ));
    $data->setIncrementOffset(false);
    foreach ($data as $d) {
        $d->delete(true);
    }
});

$fake_count = elgg_get_entities(array('metadata_names' => '__faker', 'count' => true));
if (!$fake_count) {
    elgg_register_success_message(elgg_echo('faker:delete:success'));
} else {
    elgg_register_error_message(elgg_echo('faker:delete:error', $fake_count));
}

return elgg_redirect_response(REFERRER);
