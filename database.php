<?php

require DATABASE_CONFIG_FILE;

// Get Sonerezh's conf
$conf = (object) ((new DATABASE_CONFIG) -> default);

// Instantiate pdo
$dsn = "mysql:dbname=$conf->database;host=$conf->host;charset=utf8";
$pdo = new PDO ($dsn, $conf->login, $conf->password);
