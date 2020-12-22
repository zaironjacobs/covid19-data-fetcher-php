<?php

/**
 * PHP version 7.4
 *
 * @author      Zairon Jacobs <zaironjacobs@gmail.com>
 */


require_once("../vendor/autoload.php");
require("constants.php");
require("app.php");
Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1))->load();

$app = new App();
$app->init();
