<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.api.class.php');

$mappr_api = new MAPPRAPI();
$mappr_api->setShapePath(MAPPR_DIRECTORY . "/maps");
$mappr_api->setSymbolsPath(MAPPR_DIRECTORY . "/config/symbols");
$mappr_api->setFontFile(MAPPR_DIRECTORY . "/config/fonts.list");
$mappr_api->setTmpPath(MAPPR_DIRECTORY . "/tmp/");
$mappr_api->setTmpUrl("/tmp");
$mappr_api->setDefaultProjection("epsg:4326");
$mappr_api->setMaxExtent("-180,-90,180,90");
$mappr_api->setImageSize("800,400");

$mappr_api->getRequest();
$mappr_api->execute();
$mappr_api->produceOutput();
?>