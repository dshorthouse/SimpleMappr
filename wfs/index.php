<?php
require_once('../config/conf.php');
require_once('../lib/mapprservice.wfs.class.php');

$mappr_wfs = new MAPPRWFS();
$mappr_wfs->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
          ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
          ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
          ->set_tmp_url(MAPPR_MAPS_URL)
          ->set_default_projection("epsg:4326")
          ->set_max_extent("-180,-90,180,90");

$mappr_wfs->get_request()
          ->make_service()
          ->execute()
          ->prepapre_output()
          ->get_output();
?>