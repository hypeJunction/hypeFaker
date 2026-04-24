<?php

namespace hypeJunction\Faker;

use Elgg\DefaultPluginBootstrap;
use Elgg\Event;

class Bootstrap extends DefaultPluginBootstrap {

	public function init() {
		elgg_register_event_handler('register', 'menu:page', [self::class, 'setupPageMenu']);
	}

	public static function setupPageMenu(Event $event) {
		$return = $event->getValue();
		if (elgg_get_context() !== 'admin') {
			return $return;
		}

		$return[] = \ElggMenuItem::factory([
			'name' => 'faker',
			'href' => 'admin/developers/faker',
			'text' => elgg_echo('admin:developers:faker'),
			'context' => 'admin',
			'section' => 'develop',
		]);

		return $return;
	}
}
