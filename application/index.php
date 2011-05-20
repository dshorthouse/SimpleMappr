<?php
require_once('../includes/mapprservice.class.php');

$mappr = new mapprService();
$mappr->setMapPath("/var/www/mapper/maps/mapfiles");
$mappr->setMapFile("/var/www/mapper/maps/mapfiles/world.map");
$mappr->setShapePath("/var/www/mapper/maps");
$mappr->setSymbolFile("/var/www/mapper/config/symbols.sym");
$mappr->setFontFile("/var/www/mapper/config/fonts.list");
$mappr->setTmpPath("/var/www/mapper/tmp/");
$mappr->setTmpUrl("/tmp");
$mappr->setDefaultProjection("epsg:4326");
$mappr->setMaxExtent("-180,-90,180,90");
$mappr->setImageSize("800,400");
$mappr->run();
?>
