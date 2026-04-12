<?php

namespace hypeJunction\Faker;

use Elgg\IntegrationTestCase;
use ElggObject;

/**
 * The delete action sweeps every entity (users + objects) carrying the
 * `__faker` metadata. Lock that contract: after the sweep, no entity
 * tagged with __faker should remain.
 */
class DeleteSweepTest extends IntegrationTestCase
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

    public function testSweepRemovesFakerTaggedUsers(): void
    {
        $u1 = $this->createUser();
        $u1->__faker = true;
        $u1->save();

        $u2 = $this->createUser();
        $u2->__faker = true;
        $u2->save();

        $hidden = access_get_show_hidden_status();
        access_show_hidden_entities(true);

        $batch = new \ElggBatch('elgg_get_entities', [
            'types' => 'user',
            'limit' => 0,
            'metadata_names' => '__faker',
        ]);
        $batch->setIncrementOffset(false);
        foreach ($batch as $d) {
            $d->delete(true);
        }

        $remaining = elgg_get_entities([
            'types' => 'user',
            'metadata_names' => '__faker',
            'count' => true,
        ]);

        access_show_hidden_entities($hidden);
        $this->assertSame(0, (int) $remaining);
    }

    public function testSweepLeavesNonFakerEntitiesIntact(): void
    {
        $keeper = $this->createUser();
        $keeper->save();

        $victim = new ElggObject();
        $victim->setSubtype('blog');
        $victim->owner_guid = $keeper->guid;
        $victim->container_guid = $keeper->guid;
        $victim->access_id = ACCESS_PUBLIC;
        $victim->title = 'fake';
        $victim->__faker = true;
        $victim->save();

        $hidden = access_get_show_hidden_status();
        access_show_hidden_entities(true);
        $batch = new \ElggBatch('elgg_get_entities', [
            'limit' => 0,
            'metadata_names' => '__faker',
        ]);
        $batch->setIncrementOffset(false);
        foreach ($batch as $d) {
            $d->delete(true);
        }
        access_show_hidden_entities($hidden);

        // Keeper (no __faker) must survive.
        $this->assertInstanceOf(\ElggUser::class, get_entity($keeper->guid));
        // Victim gone.
        $this->assertFalse(get_entity($victim->guid));
    }
}
