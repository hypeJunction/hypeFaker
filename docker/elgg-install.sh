#!/bin/bash
set -e

# Per-plugin Elgg 5.x install + activation script.
# PLUGIN_ID must be set in the container environment (passed by docker-compose
# from <plugin>/docker/.env). Only that one plugin is activated — no fleet
# activation, no plugin-order.txt, no cross-plugin side effects.

if [ -z "${PLUGIN_ID:-}" ]; then
    echo "ERROR: PLUGIN_ID environment variable is required." >&2
    echo "Set it in docker/.env before starting the stack." >&2
    exit 1
fi

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${ELGG_DB_HOST:-db}', '${ELGG_DB_USER:-elgg}', '${ELGG_DB_PASS:-elgg}');" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

cd /var/www/html

if [ ! -f /var/www/html/.elgg-installed ]; then
    echo "Installing Elgg 5.x..."

    mkdir -p elgg-config
    cat > elgg-config/settings.php <<'SETTINGS_TEMPLATE'
<?php
global $CONFIG;
if (!isset($CONFIG)) {
    $CONFIG = new \stdClass;
}
SETTINGS_TEMPLATE

    cat >> elgg-config/settings.php <<SETTINGS_VALUES
\$CONFIG->dbuser = '${ELGG_DB_USER:-elgg}';
\$CONFIG->dbpass = '${ELGG_DB_PASS:-elgg}';
\$CONFIG->dbname = '${ELGG_DB_NAME:-elgg}';
\$CONFIG->dbhost = '${ELGG_DB_HOST:-db}';
\$CONFIG->dbport = '3306';
\$CONFIG->dbprefix = 'elgg_';
\$CONFIG->dbencoding = 'utf8mb4';
\$CONFIG->dataroot = '${ELGG_DATA_ROOT:-/var/www/data/}';
\$CONFIG->wwwroot = '${ELGG_SITE_URL:-http://localhost:8480/}';
\$CONFIG->cacheroot = '${ELGG_DATA_ROOT:-/var/www/data/}cache/';
\$CONFIG->assetroot = '${ELGG_DATA_ROOT:-/var/www/data/}assets/';
SETTINGS_VALUES

    php -r "
        require_once 'vendor/autoload.php';

        \$params = [
            'dbuser' => '${ELGG_DB_USER:-elgg}',
            'dbpassword' => '${ELGG_DB_PASS:-elgg}',
            'dbname' => '${ELGG_DB_NAME:-elgg}',
            'dbhost' => '${ELGG_DB_HOST:-db}',
            'dbport' => '3306',
            'dbprefix' => 'elgg_',
            'sitename' => 'Elgg 5.x Plugin Test',
            'siteemail' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'wwwroot' => '${ELGG_SITE_URL:-http://localhost:8480/}',
            'dataroot' => '${ELGG_DATA_ROOT:-/var/www/data/}',
            'displayname' => 'Admin',
            'email' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'username' => 'admin',
            'password' => '${ELGG_ADMIN_PASSWORD:-admin12345}',
        ];

        \$installer = new \ElggInstaller();
        \$installer->batchInstall(\$params);
        echo 'Elgg 5.x installed successfully.' . PHP_EOL;
    " 2>&1 || echo "Install completed (check for errors above)."

    # Install plugin-local composer deps (e.g. fakerphp/faker)
    if [ -f "/var/www/html/mod/${PLUGIN_ID}/composer.json" ] && [ ! -d "/var/www/html/mod/${PLUGIN_ID}/vendor" ]; then
        echo "Installing plugin composer deps for ${PLUGIN_ID}..."
        (cd "/var/www/html/mod/${PLUGIN_ID}" && composer install --no-interaction --prefer-dist --no-dev 2>&1 | tail -10) || echo "Plugin composer install completed (check for errors above)."
    fi

    echo "Activating plugins..."

    # Symlink core plugins needed for tests into mod/ so Elgg can discover them.
    for CORE_PLUGIN in activity blog bookmarks file thewire messages groups pages; do
        if [ ! -e "/var/www/html/mod/${CORE_PLUGIN}" ]; then
            ln -sf "/var/www/html/vendor/elgg/elgg/mod/${CORE_PLUGIN}" "/var/www/html/mod/${CORE_PLUGIN}"
        fi
    done

    php -r "
        require_once 'vendor/autoload.php';
        \$app = \Elgg\Application::getInstance();
        \$app->bootCore();
        _elgg_services()->plugins->generateEntities();

        // Resolve dep plugin IDs from the plugin's own metadata.
        // Priority: elgg-plugin.php 'plugin.dependencies' (Elgg 4.x) then manifest.xml <requires type='plugin'>.
        // IDs are lowercased to match mod/ directory names.
        // Deps not present in mod/ are skipped with a warning — this naturally excludes
        // deps that are unsafe to activate (e.g. unmigrated plugins not volume-mounted).
        \$dep_ids = [];
        \$plugin_file = '/var/www/html/mod/${PLUGIN_ID}/elgg-plugin.php';
        if (file_exists(\$plugin_file)) {
            \$manifest = include \$plugin_file;
            foreach (array_keys(\$manifest['plugin']['dependencies'] ?? []) as \$id) {
                \$dep_ids[] = strtolower(\$id);
            }
        }
        if (empty(\$dep_ids)) {
            \$xml_file = '/var/www/html/mod/${PLUGIN_ID}/manifest.xml';
            if (file_exists(\$xml_file)) {
                \$xml = simplexml_load_file(\$xml_file);
                foreach (\$xml->requires ?? [] as \$req) {
                    if ((string)\$req->type === 'plugin') {
                        \$dep_ids[] = strtolower((string)\$req->name);
                    }
                }
            }
        }

        foreach (\$dep_ids as \$dep_id) {
            \$dep = elgg_get_plugin_from_id(\$dep_id);
            if (!\$dep) {
                echo 'WARNING: dep plugin ' . \$dep_id . ' not in mod/ — skipping (not mounted).' . PHP_EOL;
                continue;
            }
            if (\$dep->isActive()) {
                echo 'Dep plugin ' . \$dep_id . ' already active.' . PHP_EOL;
                continue;
            }
            try {
                \$dep->activate();
                echo 'Dep plugin ' . \$dep_id . ' activated.' . PHP_EOL;
            } catch (\Throwable \$e) {
                echo 'FAILED to activate dep ' . \$dep_id . ': ' . \$e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        // Activate the main plugin.
        \$plugin = elgg_get_plugin_from_id('${PLUGIN_ID}');
        if (!\$plugin) {
            echo 'ERROR: plugin ${PLUGIN_ID} not found at /var/www/html/mod/${PLUGIN_ID}' . PHP_EOL;
            exit(1);
        }
        if (\$plugin->isActive()) {
            echo 'Plugin ${PLUGIN_ID} already active.' . PHP_EOL;
        } else {
            try {
                \$plugin->activate();
                echo 'Plugin ${PLUGIN_ID} activated.' . PHP_EOL;
            } catch (\Throwable \$e) {
                echo 'FAILED to activate ${PLUGIN_ID}: ' . \$e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        // Activate core plugins needed for E2E tests (not dep of ${PLUGIN_ID}).
        foreach (['activity', 'blog', 'bookmarks', 'file', 'thewire', 'messages', 'groups', 'pages'] as \$core_id) {
            \$core = elgg_get_plugin_from_id(\$core_id);
            if (!\$core) {
                echo 'WARNING: core plugin ' . \$core_id . ' not found — skipping.' . PHP_EOL;
                continue;
            }
            if (\$core->isActive()) {
                echo 'Core plugin ' . \$core_id . ' already active.' . PHP_EOL;
            } else {
                try {
                    \$core->activate();
                    echo 'Core plugin ' . \$core_id . ' activated.' . PHP_EOL;
                } catch (\Throwable \$e) {
                    echo 'WARNING: failed to activate core plugin ' . \$core_id . ': ' . \$e->getMessage() . PHP_EOL;
                }
            }
        }

        // elgg_invalidate_caches() (called inside activate()) is a no-op for the
        // localFileCache-backed serverCache where view_locations is stored.
        // elgg_clear_caches() properly wipes localFileCache so the next web request
        // rescans all active plugin views and rebuilds view_locations including them.
        elgg_clear_caches();
        echo 'Caches cleared after plugin activation.' . PHP_EOL;
    " 2>&1 || echo "Plugin activation completed (check for errors above)."

    chown -R www-data:www-data /var/www/data
    touch /var/www/html/.elgg-installed
    echo "Elgg 5.x setup complete."
fi

echo "Starting Apache..."
exec apache2-foreground
