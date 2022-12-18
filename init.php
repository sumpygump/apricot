<?php

/**
 * Initialize environment
 *
 * @package Apricot
 */

define('APP_ROOT', realpath(dirname(__FILE__)));

date_default_timezone_set('America/Chicago');

ini_set('magic_quotes_gpc', 0);
ini_set('expose_php', 0);
ini_set('always_populate_raw_post_data', 0);
ini_set('session.gc_divisor', '1000');

session_start();

// Include path
$paths = [
    get_include_path(),
    APP_ROOT,
    APP_ROOT . "/lib",
];
set_include_path(implode(PATH_SEPARATOR, $paths));

// Register autoloader
require_once 'Apricot/Loader.php';
Apricot\Loader::register();
