<?php

//config
$config_dir = dirname(dirname(__FILE__)).'/config/';
require_once($config_dir.'conf.php');
require_once($config_dir.'conf.db.php');

//utilities
require_once('utilities.class.php');

//logger
require_once('logger.class.php');

//header, cssmin
require_once('cssmin.php');
require_once('header.class.php');

//database
require_once('db.class.php');

//base abstract class
require_once('mappr.class.php');

//extended classes
require_once('mappr.api.class.php');
require_once('mappr.application.class.php');
require_once('mappr.docx.class.php');
require_once('mappr.map.class.php');
require_once('mappr.pptx.class.php');
require_once('mappr.query.class.php');
require_once('mappr.wfs.class.php');
require_once('mappr.wms.class.php');

//kml && georss
require_once('kml.class.php');
require_once('georss/rss_fetch.inc');

//session
require_once('session.class.php');

//users
require_once('user.class.php');
require_once('usermap.class.php');

//places
require_once('places.class.php');

//citations
require_once('citation.class.php');