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

        $remaining = 0;
        elgg_call(ELGG_SHOW_DISABLED_ENTITIES | ELGG_IGNORE_ACCESS, function () use (&$remaining) {
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
        });

        $this->assertSame(0, (int) $remaining);
    }

    public function testSweepLeavesNonFakerEntitiesIntact(): void
    {
        // createUser() (Seeding trait) always marks users with __faker=true,
        // so they would be swept. Use the installed admin user as the keeper —
        // it was created by the Elgg installer without __faker metadata.
        $admins = elgg_get_entities(['type' => 'user', 'limit' => 1]);
        $keeper = $admins[0];
        $this->assertInstanceOf(\ElggUser::class, $keeper, 'admin user not found');

        elgg_get_session()->setLoggedInUser($keeper);
        $victim = new ElggObject();
        $victim->setSubtype('blog');
        $victim->owner_guid = $keeper->guid;
        $victim->container_guid = $keeper->guid;
        $victim->access_id = ACCESS_PUBLIC;
        $victim->title = 'fake';
        $victim->__faker = true;
        $victim->save();
        elgg_get_session()->removeLoggedInUser();

        elgg_call(ELGG_SHOW_DISABLED_ENTITIES | ELGG_IGNORE_ACCESS, function () {
            $batch = new \ElggBatch('elgg_get_entities', [
                'limit' => 0,
                'metadata_names' => '__faker',
            ]);
            $batch->setIncrementOffset(false);
            foreach ($batch as $d) {
                $d->delete(true);
            }
        });

        elgg_call(ELGG_IGNORE_ACCESS, function () use ($keeper, $victim) {
            // Keeper (no __faker) must survive.
            $this->assertInstanceOf(\ElggUser::class, get_entity($keeper->guid));
            // Victim gone.
            $this->assertFalse(get_entity($victim->guid));
        });
    }
}
