<?php
require_once('../../config/conf.php');
require_once('../../lib/mapprservice.pptx.class.php');
require_once('../../lib/mapprservice.usersession.class.php');
USERSESSION::select_language();

$mappr_pptx = new MAPPRPPTX();
$mappr_pptx->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
           ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
           ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
           ->set_tmp_url(MAPPR_MAPS_URL);

$mappr_pptx->get_request()
           ->execute()
           ->get_output();
?>