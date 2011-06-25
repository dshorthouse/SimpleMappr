<?php
require_once('../conf/conf.php');
require_once('../includes/mapprservice.wfs.class.php');

$mappr_wfs = new MAPPRWFS();
$mappr_wfs->setShapePath(MAPPR_DIRECTORY . "/maps");
$mappr_wfs->setSymbolsPath(MAPPR_DIRECTORY . "/config/symbols");
$mappr_wfs->setFontFile(MAPPR_DIRECTORY . "/config/fonts.list");
$mappr_wfs->setTmpPath(MAPPR_DIRECTORY . "/tmp/");
$mappr_wfs->setTmpUrl("/tmp");
$mappr_wfs->setDefaultProjection("epsg:4326");
$mappr_wfs->setMaxExtent("-180,-90,180,90");
$mappr_wfs->setImageSize("800,400");

$mappr_wfs->getRequest();
$mappr_wfs->makeService();
$mappr_wfs->execute();
$mappr_wfs->produceOutput();
?>