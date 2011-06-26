<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.query.class.php');

$mappr_query = new MAPPRQUERY();
$mappr_query->set_shape_path(MAPPR_DIRECTORY . "/maps")
            ->set_symbols_path(MAPPR_DIRECTORY . "/config/symbols")
            ->set_font_file(MAPPR_DIRECTORY . "/config/fonts.list")
            ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
            ->set_tmp_url("/tmp")
            ->set_default_projection("epsg:4326")
            ->set_max_extent("-180,-90,180,90")
            ->set_image_size("800,400");

$mappr_query->get_request()
            ->execute()
            ->query_layer()
            ->query_freehand()
            ->get_output();
?>