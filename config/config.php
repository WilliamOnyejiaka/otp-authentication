<?php

require_once __DIR__ . "./../vendor/autoload.php";

ini_set("display_errors", 1);
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));

$dotenv->load();


function config($key){
    return ([
        'host' => $_ENV['DB_HOST'],
        'db_name' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'allow_cors' => false,
        'secret_key' => $_ENV['SECRET_KEY'],
        'hash' => $_ENV['HASH'],
        'default_password' => $_ENV['DEFAULT_PASS'],
        'super_password' => $_ENV['SUPER_ADMIN_KEY'] ?? null,
    ])[$key];
}