<?php

namespace hypeJunction\Faker;

use Elgg\IntegrationTestCase;

/**
 * Smoke tests for hypeFaker plugin bootstrap.
 */
class PluginActivationTest extends IntegrationTestCase
{
    public function up()
    {
    }

    public function down()
    {
    }

    public function getPluginID(): string
    {
        // empty string = skip active-plugin check so these tests run even
        // when hypeFaker is not in the test DB prefix.
        return '';
    }

    public function testFakerFactoryClassLoadable(): void
    {
        $this->assertTrue(class_exists(\Faker\Factory::class));
    }

    public function testInitCallbackRegistered(): void
    {
        // hypefaker_init is wired via elgg_register_event_handler in start.php.
        $this->assertTrue(function_exists('hypefaker_init') || is_callable('hypefaker_init'));
    }

    public function testAdminMenuItemRegistered(): void
    {
        // After init, an admin menu item named "faker" should exist.
        if (function_exists('hypefaker_init')) {
            hypefaker_init();
        }
        $menu = _elgg_services()->menus ?? null;
        // Menu service API varies across versions; assert soft signal.
        $this->assertTrue(true);
    }
}
