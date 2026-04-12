<?php

namespace hypeJunction\Faker;

use Elgg\IntegrationTestCase;

/**
 * Every generator action file must be syntactically loadable and present
 * on disk. This catches regressions where a migration accidentally drops
 * or renames an action script.
 */
class ActionRegistrationTest extends IntegrationTestCase
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

    private function pluginRoot(): string
    {
        return dirname(__DIR__, 5);
    }

    public function expectedActions(): array
    {
        return [
            ['delete'],
            ['gen_users'],
            ['gen_friends'],
            ['gen_groups'],
            ['gen_group_members'],
            ['gen_blogs'],
            ['gen_bookmarks'],
            ['gen_files'],
            ['gen_pages'],
            ['gen_wire'],
            ['gen_messages'],
            ['gen_discussions'],
            ['gen_comments'],
            ['gen_likes'],
            ['gen_location'],
        ];
    }

    /**
     * @dataProvider expectedActions
     */
    public function testActionFileExists(string $action): void
    {
        $path = $this->pluginRoot() . '/actions/faker/' . $action . '.php';
        $this->assertFileExists($path, "Missing action file: $action");
    }

    /**
     * @dataProvider expectedActions
     */
    public function testActionFileIsSyntacticallyValid(string $action): void
    {
        $path = $this->pluginRoot() . '/actions/faker/' . $action . '.php';
        $output = [];
        $status = 0;
        exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
        $this->assertSame(0, $status, implode("\n", $output));
    }

    public function testFormViewsExistForAllGenerators(): void
    {
        $forms = [
            'gen_users', 'gen_friends', 'gen_groups', 'gen_group_members',
            'gen_blogs', 'gen_bookmarks', 'gen_files', 'gen_pages', 'gen_wire',
            'gen_messages', 'gen_discussions', 'gen_comments', 'gen_likes',
            'gen_location',
        ];
        foreach ($forms as $form) {
            $path = $this->pluginRoot() . '/views/default/forms/faker/' . $form . '.php';
            $this->assertFileExists($path, "Missing form view: $form");
        }
    }

    public function testAdminPageViewExists(): void
    {
        $this->assertFileExists(
            $this->pluginRoot() . '/views/default/admin/developers/faker.php'
        );
    }
}
