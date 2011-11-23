<?php
require_once('../../config/conf.php');
require_once('../../lib/mapprservice.docx.class.php');

$mappr_docx = new MAPPRDOCX();
$mappr_docx->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
           ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
           ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
           ->set_tmp_url("/tmp");

$mappr_docx->get_request()
           ->execute()
           ->get_output();
?>