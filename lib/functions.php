<?php

namespace hypeJunction\Faker;

function get_group_content_access_mode($mode) {
	switch ($mode) {
		case 'members_only' :
			if (is_callable('ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY')) {
				return ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY;
			} else {
				return 'members_only';
			}
			break;

		case 'unrestricted' :
			if (is_callable('ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED')) {
				return ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED;
			} else {
				return 'unrestricted';
			}
			break;
	}
}