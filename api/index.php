<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.api.class.php');

$mappr_api = new MAPPRAPI();
$mappr_api->set_shape_path(MAPPR_DIRECTORY . "/maps")
          ->set_font_file(MAPPR_DIRECTORY . "/config/fonts.list")
          ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
          ->set_tmp_url("/tmp");

$mappr_api->get_request()
          ->execute()
          ->get_output();
?>