<?php
require_once('../config/conf.php');
require_once('../lib/mapprservice.class.php');

session_start();

$mappr = new MAPPR();
$mappr->set_imagemagic_path(IMAGEMAGICK_CONVERT)
      ->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
      ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
      ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
      ->set_tmp_url("/tmp")
      ->set_default_projection("epsg:4326")
      ->set_max_extent("-180,-90,180,90")
      ->set_image_size("800,400");

$mappr->get_request()
      ->execute()
      ->get_output();

?>
