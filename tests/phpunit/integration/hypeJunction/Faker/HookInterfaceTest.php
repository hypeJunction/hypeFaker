<?php

namespace hypeJunction\Faker;

use Elgg\Hook;
use Elgg\IntegrationTestCase;

/**
 * hypeFaker does not currently register any plugin hooks, but if migration
 * rewrites the init callback into a class-based hook handler it will need
 * to receive an \Elgg\Hook. This test locks the Hook interface mock recipe
 * (from SKILL.md) for use by future handlers.
 */
class HookInterfaceTest extends IntegrationTestCase
{
    public function up()
    {
    }

    public function down()
    {
    }

    public function getPluginID(): string
    {
        return '';
    }

    public function testHookInterfaceMockable(): void
    {
        $hook = $this->getMockBuilder(Hook::class)->getMock();
        $hook->method('getValue')->willReturn(['ok']);
        $hook->method('getName')->willReturn('register');
        $hook->method('getType')->willReturn('menu:page');
        $hook->method('getParams')->willReturn([]);

        $this->assertInstanceOf(Hook::class, $hook);
        $this->assertSame(['ok'], $hook->getValue());
        $this->assertSame('register', $hook->getName());
    }

    public function testHookHandlerClosureReceivesHookObject(): void
    {
        $received = null;
        $handler = function (Hook $hook) use (&$received) {
            $received = $hook->getName();
            return $hook->getValue();
        };

        elgg_register_plugin_hook_handler('faker:test', 'all', $handler);
        elgg_trigger_plugin_hook('faker:test', 'all', [], []);
        elgg_unregister_plugin_hook_handler('faker:test', 'all', $handler);

        $this->assertSame('faker:test', $received);
    }
}
