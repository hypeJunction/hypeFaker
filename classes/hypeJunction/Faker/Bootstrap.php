<?php

namespace hypeJunction\Faker;

use Elgg\DefaultPluginBootstrap;
use Elgg\Hook;

class Bootstrap extends DefaultPluginBootstrap {

	public function init() {
		// Register the faker page menu item via a declarative hook.
		elgg_register_plugin_hook_handler('register', 'menu:page', [self::class, 'setupPageMenu']);
	}

	public static function setupPageMenu(Hook $hook) {
		$return = $hook->getValue();
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
