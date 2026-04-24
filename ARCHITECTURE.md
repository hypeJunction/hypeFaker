# hypefaker — Architecture (Elgg 5.x)

## Summary

hypefaker generates demo data (users, friendships, groups, blogs, bookmarks,
files, pages, wire posts, messages, discussions, comments, likes, locations)
for an Elgg site via an admin page at `admin/developers/faker`. Each
generator is a dedicated action under `actions/faker/`. All content is
tagged with a `__faker` metadata marker so the delete action can sweep only
faker-created entities.

## Directory Structure

```
hypefaker/
├── actions/faker/            — one PHP action per generator
├── classes/hypeJunction/Faker/
│   └── Bootstrap.php         — registers admin menu item on init
├── languages/en.php          — returns translations array
├── views/default/
│   ├── admin/developers/faker.php  — admin page
│   └── forms/faker/          — one form per generator
├── tests/
│   ├── phpunit/              — integration + unit PHPUnit suite
│   ├── playwright/           — admin UI + gen_users E2E
│   └── phpunit.xml
├── docker/                   — per-plugin Elgg 5.x test stack
├── composer.json
└── elgg-plugin.php           — action registrations (admin gate) + bootstrap ref
```

## Events (5.x)

| Event       | Type         | Handler                                    |
|-------------|--------------|--------------------------------------------|
| `register`  | `menu:page`  | `hypeJunction\Faker\Bootstrap::setupPageMenu` — adds "faker" item in admin develop section |

## Actions

All actions are admin-gated (`'access' => 'admin'`):

| Action                      | Purpose                                |
|-----------------------------|----------------------------------------|
| `faker/delete`              | Sweep-delete all `__faker`-marked entities |
| `faker/gen_users`           | Generate user accounts                 |
| `faker/gen_friends`         | Create friendships + access collections |
| `faker/gen_groups`          | Generate groups (membership × visibility combos) |
| `faker/gen_group_members`   | Populate group membership              |
| `faker/gen_blogs`           | Generate blog posts                    |
| `faker/gen_bookmarks`       | Generate bookmarks                     |
| `faker/gen_files`           | Generate files                         |
| `faker/gen_pages`           | Generate pages + subpages              |
| `faker/gen_wire`            | Generate wire posts + replies          |
| `faker/gen_messages`        | Generate private messages              |
| `faker/gen_discussions`     | Generate discussions + replies         |
| `faker/gen_comments`        | Generate comments                      |
| `faker/gen_likes`           | Generate likes                         |

## Dependencies

Runtime:
- `fakerphp/faker` (^1.23) — replaces the abandoned `fzaninotto/faker`

Plugin deps (activated at test time but not enforced by composer):
- `blog`, `bookmarks`, `file`, `groups`, `messages`, `pages`, `thewire` (Elgg core plugins)

## Migration Notes (4.x → 5.x)

- `\Elgg\Hook` → `\Elgg\Event`; `elgg_register_plugin_hook_handler()` →
  `elgg_register_event_handler()`; `elgg_trigger_plugin_hook()` →
  `elgg_trigger_event_results()`.
- `add_translation('en', $english)` removed — `languages/en.php` now returns
  the translations array directly.
- `composer.json`: `elgg/elgg` pinned to `~5.1.0` (5.0.x pulls in the blocked
  CKEditor security advisory), PHP `>=8.2`, asset-packagist repo added,
  `minimum-stability: dev`, `roave/security-advisories` replaced (mirrors
  site-level composer so `composer install` in the plugin dir succeeds).
- PSR-4 autoload block added so `hypeJunction\\Faker\\` is loadable from
  the plugin's own `vendor/autoload.php` (tests require this).
- PHPUnit tests:
  - `elgg_get_session()->setLoggedInUser()` → `_elgg_services()->session_manager->setLoggedInUser()`
  - `assertFalse(get_entity($guid))` → `assertNull(get_entity($guid))` — 5.x
    returns `null` for non-existent entities.
  - `HookInterfaceTest` renamed conceptually to exercise `\Elgg\Event`.
- Docker test stack switched to the 5.x template (PHP 8.2-apache, MySQL 8.0,
  `ELGG_SITE_URL` uses internal service DNS). Install script activates
  `activity, blog, bookmarks, file, thewire, messages, groups, pages` core
  plugins so faker's generators have their target types available.
