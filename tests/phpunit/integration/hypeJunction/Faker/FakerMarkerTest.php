<?php

namespace hypeJunction\Faker;

use Elgg\IntegrationTestCase;

/**
 * The `__faker` metadata marker is the single load-bearing API contract
 * for hypeFaker. Every generator action must set `$entity->__faker = true`
 * so that the delete action and admin listing can find fake content by
 * `metadata_names => '__faker'`. These tests lock that contract.
 */
class FakerMarkerTest extends IntegrationTestCase
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

    public function testFakerMarkerPersistsOnObject(): void
    {
        $user = $this->createUser();
        $obj = new \ElggBlog();
        $obj->owner_guid = $user->guid;
        $obj->container_guid = $user->guid;
        $obj->access_id = ACCESS_PUBLIC;
        $obj->title = 'Fake blog';
        $obj->description = 'lorem ipsum';
        $obj->__faker = true;

        _elgg_services()->session_manager->setLoggedInUser($user);
        $this->assertNotFalse($obj->save());
        _elgg_services()->session_manager->removeLoggedInUser();

        _elgg_services()->entityCache->delete($obj->guid);
        $loaded = get_entity($obj->guid);
        $this->assertEquals(1, (int) $loaded->__faker);
        $obj->delete();
    }

    public function testFakerEntitiesFindableByMetadataName(): void
    {
        $user = $this->createUser();
        $obj = new \ElggBlog();
        $obj->owner_guid = $user->guid;
        $obj->container_guid = $user->guid;
        $obj->access_id = ACCESS_PUBLIC;
        $obj->title = 'Fake blog for search';
        $obj->__faker = true;

        _elgg_services()->session_manager->setLoggedInUser($user);
        $obj->save();
        _elgg_services()->session_manager->removeLoggedInUser();

        $found = elgg_get_entities([
            'types' => 'object',
            'subtypes' => ['blog'],
            'metadata_names' => '__faker',
            'limit' => 0,
        ]);

        $guids = array_map(static fn($e) => $e->guid, $found);
        $this->assertContains($obj->guid, $guids);

        $obj->delete();
    }

    public function testFakerEntityCountQuery(): void
    {
        $user = $this->createUser();
        $before = elgg_get_entities([
            'metadata_names' => '__faker',
            'count' => true,
        ]);

        $obj = new \ElggBlog();
        $obj->owner_guid = $user->guid;
        $obj->container_guid = $user->guid;
        $obj->access_id = ACCESS_PUBLIC;
        $obj->title = 'counted';
        $obj->__faker = true;

        _elgg_services()->session_manager->setLoggedInUser($user);
        $obj->save();
        _elgg_services()->session_manager->removeLoggedInUser();

        $after = elgg_get_entities([
            'metadata_names' => '__faker',
            'count' => true,
        ]);

        $this->assertSame((int) $before + 1, (int) $after);
        $obj->delete();
    }

    public function testFakerMarkerOnUser(): void
    {
        // gen_users sets __faker on user entities so they can be swept.
        $user = $this->createUser();
        $user->__faker = true;

        _elgg_services()->session_manager->setLoggedInUser($user);
        $this->assertNotFalse($user->save());
        _elgg_services()->session_manager->removeLoggedInUser();

        $found = elgg_get_entities([
            'types' => 'user',
            'metadata_names' => '__faker',
            'limit' => 0,
        ]);
        $guids = array_map(static fn($e) => $e->guid, $found);
        $this->assertContains($user->guid, $guids);
    }
}
