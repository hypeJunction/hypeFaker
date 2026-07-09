<?php

namespace hypeJunction\Faker;

use Elgg\Event;
use Elgg\IntegrationTestCase;

/**
 * Regression guard for the 4.x/6.x Hook->Event port of the page-menu handler
 * (refs 072691f, 947b982). Exercises Bootstrap::setupPageMenu directly:
 *   - it type-hints \Elgg\Event and reads $event->getValue()
 *   - it RETURNS the menu items as an array (7.x value-return idiom), never
 *     mutating via ->add()
 *   - it appends exactly one 'faker' ElggMenuItem, and ONLY in admin context
 *   - it preserves items already present in the incoming value
 */
class BootstrapMenuTest extends IntegrationTestCase
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

    private function eventReturning(array $value): Event
    {
        $event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        $event->method('getValue')->willReturn($value);

        return $event;
    }

    private function existingItem(): \ElggMenuItem
    {
        return \ElggMenuItem::factory([
            'name' => 'preexisting',
            'text' => 'Pre-existing',
            'href' => '#',
        ]);
    }

    public function testAppendsSingleFakerItemInAdminContext(): void
    {
        elgg_push_context('admin');
        try {
            $existing = $this->existingItem();
            $result = Bootstrap::setupPageMenu($this->eventReturning([$existing]));
        } finally {
            elgg_pop_context();
        }

        $this->assertIsArray($result, 'setupPageMenu must return the menu items by value (array)');
        $this->assertCount(2, $result, 'exactly one item appended to the one incoming item');

        $names = array_map(static fn($i) => $i->getName(), $result);
        $this->assertContains('preexisting', $names, 'pre-existing items must be preserved');
        $this->assertContains('faker', $names, 'faker item must be appended in admin context');
    }

    public function testAppendedFakerItemPointsAtDeveloperPage(): void
    {
        elgg_push_context('admin');
        try {
            $result = Bootstrap::setupPageMenu($this->eventReturning([]));
        } finally {
            elgg_pop_context();
        }

        $faker = null;
        foreach ($result as $item) {
            if ($item->getName() === 'faker') {
                $faker = $item;
            }
        }

        $this->assertInstanceOf(\ElggMenuItem::class, $faker);
        $this->assertStringContainsString('admin/developers/faker', (string) $faker->getHref());
        $this->assertSame('develop', $faker->getSection());
    }

    public function testReturnsValueUnchangedOutsideAdminContext(): void
    {
        elgg_push_context('default');
        try {
            $value = [$this->existingItem()];
            $result = Bootstrap::setupPageMenu($this->eventReturning($value));
        } finally {
            elgg_pop_context();
        }

        $this->assertSame($value, $result, 'non-admin context must return the incoming value unchanged (no faker item)');
    }
}
