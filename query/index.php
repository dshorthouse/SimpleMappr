<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.query.class.php');

$mappr_query = new MAPPRQUERY();
$mappr_query->setShapePath(MAPPR_DIRECTORY . "/maps");
$mappr_query->setSymbolsPath(MAPPR_DIRECTORY . "/config/symbols");
$mappr_query->setFontFile(MAPPR_DIRECTORY . "/config/fonts.list");
$mappr_query->setTmpPath(MAPPR_DIRECTORY . "/tmp/");
$mappr_query->setTmpUrl("/tmp");
$mappr_query->setDefaultProjection("epsg:4326");
$mappr_query->setMaxExtent("-180,-90,180,90");
$mappr_query->setImageSize("800,400");

$mappr_query->getRequest();
$mappr_query->execute();
$mappr_query->queryLayer();
$mappr_query->queryFreehand();
$mappr_query->produceOutput();
?>