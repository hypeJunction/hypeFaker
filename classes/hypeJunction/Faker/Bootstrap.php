<?php

namespace hypeJunction\Faker;

use Elgg\DefaultPluginBootstrap;
use Elgg\Event;

/**
 * Bootstrap class.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_register_event_handler('register', 'menu:page', [self::class, 'setupPageMenu']);
	}

	/**
	 * Add the Faker entry under the Developer admin page menu.
	 *
	 * @param Event $event "register", "menu:page"
	 * @return \ElggMenuItem[]
	 */
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
