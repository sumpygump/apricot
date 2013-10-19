<?php
/**
 * Apricot Setup script
 *
 * @package Apricot
 */
session_start();
date_default_timezone_set('America/Chicago');
ini_set('magic_quotes_gpc', 0);
ini_set('expose_php', 0);
ini_set('always_populate_raw_post_data', 0);
ini_set('session.gc_divisor', '1000');

// Load configuration
require_once realpath(dirname(dirname(__FILE__))) . '/config/config.php';
if (isset($cfg['app_root'])) {
    $app_root = $cfg['app_root'];
} else {
    $app_root = realpath(dirname(dirname(__FILE__)));
}

// Include path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            get_include_path(),
            $app_root,
            $app_root . "/lib",
        )
    )
);

require_once 'Apricot/Base.php';
require_once 'Apricot/Controller.php';
require_once 'Apricot/View.php';
require_once 'Apricot/Model.php';
require_once 'Apricot/ExceptionHandler.php';
Apricot_ExceptionHandler::init_handlers();
