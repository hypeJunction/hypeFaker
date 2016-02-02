<?php

$plugin_root = __DIR__;
if (file_exists("{$plugin_root}/vendor/autoload.php")) {
	// check if composer dependencies are distributed with the plugin
	require_once "{$plugin_root}/vendor/autoload.php";
}

require_once "{$plugin_root}/lib/functions.php";

if (\hypeJunction\Integration::isElggVersionBelow('1.9.0')) {
	require_once "{$plugin_root}/lib/forward_compat.php";
}