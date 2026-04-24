<?php

namespace hypeJunction\Faker;

use Elgg\Event;
use Elgg\IntegrationTestCase;

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

    public function testEventInterfaceMockable(): void
    {
        $event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        $event->method('getValue')->willReturn(['ok']);
        $event->method('getName')->willReturn('register');
        $event->method('getType')->willReturn('menu:page');
        $event->method('getParams')->willReturn([]);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame(['ok'], $event->getValue());
        $this->assertSame('register', $event->getName());
    }

    public function testEventHandlerClosureReceivesEventObject(): void
    {
        $received = null;
        $handler = function (Event $event) use (&$received) {
            $received = $event->getName();
            return $event->getValue();
        };

        elgg_register_event_handler('faker:test', 'all', $handler);
        elgg_trigger_event_results('faker:test', 'all', []);
        elgg_unregister_event_handler('faker:test', 'all', $handler);

        $this->assertSame('faker:test', $received);
    }
}
