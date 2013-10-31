#!/usr/local/bin/php

<?php

try {
  $redis = new Redis();
  $redis->connect('127.0.0.1');
  $redis->delete('simplemappr_hash');
} catch (Exception $e) {
  echo 'Exception received : ',  $e->getMessage() . "\n";
}

?>