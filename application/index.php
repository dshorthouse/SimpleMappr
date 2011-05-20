<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.class.php');

session_start();

$mappr = new MAPPR();
$mappr->setImagemagickPath(IMAGEMAGICK_CONVERT);
$mappr->setShapePath(MAPPR_DIRECTORY . "/maps");
$mappr->setSymbolsPath(MAPPR_DIRECTORY . "/config/symbols");
$mappr->setFontFile(MAPPR_DIRECTORY . "/config/fonts.list");
$mappr->setTmpPath(MAPPR_DIRECTORY . "/tmp/");
$mappr->setTmpUrl("/tmp");
$mappr->setDefaultProjection("epsg:4326");
$mappr->setMaxExtent("-180,-90,180,90");
$mappr->setImageSize("800,400");

$mappr->getRequest();
$mappr->execute();
$mappr->produceOutput();

?>
