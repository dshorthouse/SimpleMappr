<?php

//config
$config_dir = dirname(__DIR__).'/config/';
require($config_dir.'conf.php');
require($config_dir.'conf.db.php');

//utilities
require('utilities.class.php');

//logger
require('logger.class.php');

//header
require('header.class.php');

//database
require('db.class.php');

//base abstract class
require('mappr.class.php');

//extended classes
require('mappr.api.class.php');
require('mappr.application.class.php');
require('mappr.docx.class.php');
require('mappr.map.class.php');
require('mappr.pptx.class.php');
require('mappr.query.class.php');
require('mappr.wfs.class.php');
require('mappr.wms.class.php');

//kml
require('kml.class.php');

//session
require('session.class.php');

//users
require('user.class.php');
require('usermap.class.php');

//places
require('places.class.php');

//citations
require('citation.class.php');