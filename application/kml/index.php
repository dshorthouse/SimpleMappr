<?php
require_once('../../lib/kml.class.php');

session_start();

$kml = new Kml();
$kml->generate_kml();
?>