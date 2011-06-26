<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.api.class.php');

$mappr_api = new MAPPRAPI();
$mappr_api->set_shape_path(MAPPR_DIRECTORY . "/maps")
          ->set_symbols_path(MAPPR_DIRECTORY . "/config/symbols")
          ->set_font_file(MAPPR_DIRECTORY . "/config/fonts.list")
          ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
          ->set_tmp_url("/tmp")
          ->set_default_projection("epsg:4326")
          ->set_max_extent("-180,-90,180,90")
          ->set_image_size("800,400");

$mappr_api->get_request()
          ->execute()
          ->get_output();
?>