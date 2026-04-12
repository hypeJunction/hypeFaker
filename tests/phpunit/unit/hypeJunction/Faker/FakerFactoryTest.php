<?php

namespace hypeJunction\Faker;

use Elgg\UnitTestCase;
use Faker\Factory;
use Faker\Generator;

/**
 * Unit tests covering the third-party Faker library API surface that
 * hypeFaker relies on. These tests lock the contract so a migration away
 * from fzaninotto/faker (abandoned) to fakerphp/faker does not silently
 * break the plugin's generator actions.
 */
class FakerFactoryTest extends UnitTestCase
{
    public function up()
    {
    }

    public function down()
    {
    }

    public function testFactoryCreatesGeneratorForDefaultLocale(): void
    {
        $faker = Factory::create('en_US');
        $this->assertInstanceOf(Generator::class, $faker);
    }

    public function testFactoryCreatesGeneratorForAlternateLocale(): void
    {
        $faker = Factory::create('de_DE');
        $this->assertInstanceOf(Generator::class, $faker);
    }

    /**
     * gen_users relies on these properties/methods.
     */
    public function testUserProvidersExist(): void
    {
        $faker = Factory::create('en_US');
        $this->assertIsString($faker->name);
        $this->assertIsString($faker->safeEmailDomain);
        $this->assertIsString($faker->catchPhrase);
        $this->assertIsString($faker->city);
        $this->assertIsString($faker->country);
        $this->assertIsFloat((float) $faker->latitude);
        $this->assertIsFloat((float) $faker->longitude);
        $this->assertIsArray($faker->words(5));
        $this->assertIsString($faker->companyEmail);
        $this->assertIsString($faker->phoneNumber);
        $this->assertIsString($faker->url);
    }

    /**
     * gen_blogs / gen_comments rely on these.
     */
    public function testContentProvidersExist(): void
    {
        $faker = Factory::create('en_US');
        $this->assertIsString($faker->sentence(6));
        $this->assertIsString($faker->text(200));
    }

    /**
     * gen_groups relies on bs() and imageURL().
     */
    public function testGroupProvidersExist(): void
    {
        $faker = Factory::create('en_US');
        $this->assertIsString($faker->bs);
        // imageURL may require network; assert method exists on generator.
        $this->assertTrue(method_exists($faker, 'imageURL') || is_callable([$faker, 'imageURL']));
    }

    /**
     * Lock that text(N) produces something substantive.
     */
    public function testTextLengthIsBounded(): void
    {
        $faker = Factory::create('en_US');
        $text = $faker->text(500);
        $this->assertNotEmpty($text);
        $this->assertLessThanOrEqual(500, strlen($text));
    }

    /**
     * Username sanitisation logic mirrored from actions/faker/gen_users.php.
     * Lock the transformation so post-migration actions produce the same
     * shape of usernames for fake users.
     */
    public function testUsernameSanitisation(): void
    {
        $raw = "O'Brien Jr. — 北京";
        $blacklist = '/[\x{0080}-\x{009f}\x{00a0}\x{2000}-\x{200f}\x{2028}-\x{202f}\x{3000}\x{e000}-\x{f8ff}]/u';
        $blacklist2 = [' ', '\'', '/', '\\', '"', '*', '&', '?', '#', '%', '^', '(', ')', '{', '}', '[', ']', '~', '?', '<', '>', ';', '|', '¬', '`', '@', '-', '+', '='];

        $username = strtolower(str_replace(' ', '', $raw));
        $username = @iconv('UTF-8', 'ASCII//TRANSLIT', $username);
        $username = preg_replace($blacklist, '', $username);
        $username = str_replace($blacklist2, '.', $username);

        $this->assertIsString($username);
        $this->assertStringNotContainsString("'", $username);
        $this->assertStringNotContainsString(' ', $username);
    }
}
