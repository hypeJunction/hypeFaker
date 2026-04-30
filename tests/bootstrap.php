<?php
/**
 * PHPUnit bootstrap for hypeFaker plugin tests.
 * Plugin must be installed at {elgg_root}/mod/hypeFaker/
 */

// tests/ -> mod/hypeFaker/ -> mod/ -> elgg_root/
$elggRoot = dirname(dirname(dirname(__DIR__)));

// Load plugin's vendor FIRST so the main Elgg autoloader can prepend itself on top.
// Composer registers with prepend=true, so last-loaded wins for conflicting namespaces.
// Loading plugin's vendor first ensures main Elgg's Elgg\ classes take precedence.
$pluginRoot = dirname(__DIR__);
if (file_exists($pluginRoot . '/vendor/autoload.php')) {
    require_once $pluginRoot . '/vendor/autoload.php';
} elseif (file_exists($pluginRoot . '/autoloader.php')) {
    require_once $pluginRoot . '/autoloader.php';
}

require_once $elggRoot . '/vendor/autoload.php';

// Load Elgg test classes (UnitTestCase, IntegrationTestCase, etc.)
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\Elgg\Application::loadCore();
