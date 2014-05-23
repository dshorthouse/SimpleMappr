<?php

// DB PDO connection string
defined("DB_DSN") || define("DB_DSN", "mysql:host=127.0.0.1;dbname=simplemappr;charset=utf8");

// DB login name
defined("DB_USER") || define("DB_USER", "root");

// DB password
defined("DB_PASS") || define("DB_PASS", "");

// Redis server
defined("REDIS_SERVER") || define("REDIS_SERVER", "127.0.0.1");

?>