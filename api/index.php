<?php
require_once('../config/conf.php');
require_once('../lib/mapprservice.api.class.php');
require_once('../lib/mapprservice.logger.class.php');

$mappr_api = new MAPPRAPI();
$mappr_api->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
          ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
          ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
          ->set_tmp_url(MAPPR_MAPS_URL);

$mappr_api->get_request()
          ->execute()
          ->get_output();

$logger = new LOGGER(MAPPR_DIRECTORY . "/log/logger.log");
$message = date('Y-m-d H:i:s') . " - $_SERVER[REMOTE_ADDR]";
$logger->log($message);
?>