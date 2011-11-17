<?php
require_once('../../lib/kml.class.php');

$kml = new Kml();
$kml->get_request()
    ->generate_kml();
?>