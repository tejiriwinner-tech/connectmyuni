<?php

return [
    "host"     => "127.0.0.1",
    "port"     => 3306,
    "dbname"   => "connect_myuni",
    "username" => "root",
    "password" => "",
    "charset"  => "utf8",
    "options"  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];

