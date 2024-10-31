<?php

$env = parse_ini_file(__DIR__ . "/env.ini");

define("DB_HOST", $env["DB_HOST"]); 
define("DB_USER", $env["DB_USER"]);
define("DB_PASS", $env["DB_PASS"]);
define("DB_NAME", $env["DB_NAME"]);

define("ENVIRONMENT", "development");

if (ENVIRONMENT === "production") {
    error_reporting(0);
    ini_set("display_errors", "Off");
}

if (ENVIRONMENT === "development") {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}