<?php
//ini_set("display_errors", true);
ini_set("max_execution_time", 300);
ini_set("memory_limit", "500M");

if (PHP_MAJOR_VERSION >= 7) {
    set_error_handler(function ($errno, $errstr) {
        return strpos($errstr, 'Declaration of') === 0;
    }, E_WARNING);
}

include "../application/bootstrap.php";
include "../vendor/autoload.php";

$configSection = getenv('TESTE_CONFIG') ? getenv('TESTE_CONFIG') : "general";
$bootstrap = new Bootstrap($configSection);
$bootstrap->runApp();
