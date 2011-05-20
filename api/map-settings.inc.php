<?php

/**
 * Directories & files: must all be readable by Apache & $tmp_* must be writable
 */
define('BASE_URL', "http://www.simplemappr.net"); //url of site, specify port if necessary
define('MAP_PATH', "/var/www/mapper/maps/mapfiles"); // *.map mapfile directory
define('SHAPE_PATH', "/var/www/mapper/maps"); // ArcView shapefile directory
define('SYMBOL_FILE', "/var/www/mapper/config/symbols.sym"); //symbols file
define('FONT_FILE', "/var/www/mapper/config/fonts.list"); //contains directory pointers to TrueType fonts

define('TMP_PATH', "/var/www/mapper/tmp/"); //must be writable by Apache, location of tmp files created by app
define('TMP_URL', BASE_URL . "/tmp"); //relative to base url of application, location where tmp files are retrieved for client

// the map file
define('BASE_MAP', MAP_PATH . "/world.map");

/********* end directories and files ****************/

/**
 * General settings for output
 */

$shape_file_projection = "epsg:4326";

// the extent
list($ex0,$ex1,$ex2,$ex3) = array(-180,-90,180,90);

// the size of the image produced
list($output_width, $output_height) = array(800,400);

// some projections for the drop-down tools
$projection_arr = array(
	'epsg:4326' => 'Geographic',
	'esri:102009' => 'NA Lambert',
	'esri:102014' => 'Europe Lambert',
	'epsg:3107' => 'South America Lambert',
	'esri:102024' => 'Africa Lambert',
	'epsg:3033' => 'Australia Lambert',
);

// Load MapScript extension
if (!extension_loaded("MapScript"))  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

?>
