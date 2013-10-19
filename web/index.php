<?php
/**
 * Apricot Bootstrap script
 *
 * @package Apricot
 */

require_once '../init.php';

$config = new Apricot\Config(
    '../app/config/config.ini',
    array(),
    array('{app_root}' => APP_ROOT)
);

$kernel = new Apricot\Kernel($config);
Apricot\Exception\ExceptionHandlerHttp::initHandlers($kernel);

//$logger = new Apricot\Extension\Logger($kernel);
//$kernel->loadExtension($logger);

//$request = new Apricot\Http\Request(null, $config);
$kernel->dispatch();
