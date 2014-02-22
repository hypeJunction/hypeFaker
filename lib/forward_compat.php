<?php

namespace hypeJunction\Faker;

function elgg_create_river_item($options = array()) {
	return add_to_river($options['view'], $options['action_type'], $options['subject_guid'], $options['object_guid'], $options['access_id'], $options['posted'], $options['annotation_id']);
}