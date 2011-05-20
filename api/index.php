<?php
/*
$_GET PARAMETERS:

output (png,jpg,tif,svg,pnga,jpga)
bbox
width
height
projection
size[0]
shape[0] (circle, square, triangle, star, opencircle, opensquare, opentriangle, openstar)
color[0] (0,0,0)
outlinecolor (255,255,255)
layers (lakes, rivers, placenames, stateprovince)
file
graticules
border
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

define('DEFAULT_PROJECTION', "epsg:4326");

// the extent
list($ex0,$ex1,$ex2,$ex3) = array(-180,-90,180,90);

// the size of the image produced
define('WIDTH', 800);
define('HEIGHT', 400);

function reproject_map( &$map, $shape_file_projection, $output_projection ) {
    $origProjObj = ms_newProjectionObj( $shape_file_projection );
    $newProjObj = ms_newProjectionObj( $output_projection );

    $oRect = $map->extent;
    $oRect->project( $origProjObj, $newProjObj );
    $map->setExtent( $oRect->minx, $oRect->miny, $oRect->maxx, $oRect->maxy );
    $map->setProjection( $output_projection  );
}

function removeEmptyLines($string) {
  return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
}

/**
 * Check a DD coordinate as an array of lat,long and return true if it fits on globe
 */
function check_coord( $coord ) {
	$output = false;
	if((float)$coord[0] && (float)$coord[1] && $coord[0] <= 90 && $coord[0] >= -90 && $coord[1] <= 180 && $coord[1] >= -180) $output = true;
	return $output;
}

//output format
$output_options = array('png','jpg','tif','svg','pnga','jpga');
$output = ($_GET['output'] && in_array($_GET['output'],$output_options)) ? $_GET['output'] : 'pnga';  //options are png, jpg, tif, svg

//extent
$bbox = ($_GET['bbox']) ? explode(",",$_GET['bbox']) : explode(",","-180,-90,180,90");
$minx = (float)$bbox[0];
$miny = (float)$bbox[1];
$maxx = (float)$bbox[2];
$maxy = (float)$bbox[3];

if($miny < -90) $miny = -90;
if($maxy > 90) $maxy = 90;
if($minx < -180) $minx = -180;
if($maxx > 180) $maxx = 180;

if($minx > $maxx || $minx == $maxx) return;
if($miny > $maxy || $miny == $maxy) return;

//size of image
$width = (float)$_GET['width'] ? $_GET['width'] : WIDTH; //default conf'g in map-settings.inc
$height = (float)$_GET['height'] ? $_GET['height'] : HEIGHT;  //default conf'g in map-settings.inc

//projection
$output_projection = $_GET['projection'] ? 'init='.strtolower($_GET['projection']) : DEFAULT_PROJECTION;

//size of markers
$size = array();
if($_GET['size']) {
  $sizes = (is_array($_GET['size'])) ? $_GET['size'] : array("8");
  foreach($sizes as $_size) {
    $size[] = (int)$_size ? $_size : 8;	
  }
}

//shape options
$shape_options = array('circle','square','triangle','star','opencircle','opensquare','opentriangle','openstar');

$shape = array();
if($_GET['shape']) {
  $shapes = (is_array($_GET['shape'])) ? $_GET['shape'] : array("circle");
  foreach($shapes as $_shape) {
    $shape[] = (in_array($_shape, $shape_options)) ? $_shape : 'circle';	
  }
}

$color = array();
if($_GET['color']) {
  $colors = (is_array($_GET['color'])) ? $_GET['color'] : array("0,0,0");
  foreach($colors as $_color) {
	$color[] = explode(",",$_color);
  }
}

$outlinecolor = $_GET['outlinecolor'] ? explode(",",$_GET['outlinecolor']) : explode(",","255,255,255");

//layers
$layer_options = array('stateprovinces','stateprovinces_polygon','lakes','rivers','placenames');
$layers = ($_GET['layers']) ? explode(",",$_GET['layers']) : array();
foreach ($layers as $layer) {
  if(in_array($layer,$layer_options)) {
    $layers[] = $layer; 
  }
}

//shaded places
$shaded_places = ($_GET['shade']['places']) ? $_GET['shade']['places'] : '';
$shaded_color = ($_GET['shade']['color']) ? explode(",", $_GET['shade']['color']) : explode(",",array("0,0,0"));
$shaded_title = ($_GET['shade']['title']) ? $_GET['shade']['title'] : '';

//additional options
$graticules = ($_GET['graticules']) ? true : false;
$border = ($_GET['border']) ? true : false;
$legend_req = ($_GET['legend']) ? true : false;
$scalebar = ($_GET['scalebar']) ? true : false;

//file for points
$file = ($_GET['file']) ? $_GET['file'] : '';

//Start the map creation
$map = ms_newMapObj(BASE_MAP);
$map->set("name","newworld");

// Set the extent
$map->setExtent($minx,$miny,$maxx,$maxy);
$map->set("units",MS_DD);
$map->imagecolor->setRGB(255,255,255);

// Set the directories, see map-settings.inc.php
$shape_file = SHAPE_PATH . "/10m_cultural/10m_admin_0_countries.shp";

$map->set("shapepath",$shape_path);
$map->setSymbolSet(SYMBOL_FILE);
$map->setFontSet(FONT_FILE);
$map->web->set("imagepath",TMP_PATH);
$map->web->set("imageurl",TMP_URL);

// Set the output format and size
$map->selectOutputFormat($output);
$map->setSize($width, $height);

//add layers if requested
foreach($layers as $layer) {
  switch($layer) {
    case 'lakes':
	  $lakes = $map->getLayerByName("lakes");
	  $lakes->set("data",SHAPE_PATH."/10m_physical/10m_lakes.shp");
	  $lakes->set("status",MS_ON);
	  $lakes->setProjection(DEFAULT_PROJECTION);
	break;
	
	case 'rivers':
	  $rivers = $map->getLayerByName("rivers");
	  $rivers->set("data",SHAPE_PATH."/10m_physical/10m_rivers_lake_centerlines.shp");
	  $rivers->set("status",MS_ON);
	  $rivers->setProjection(DEFAULT_PROJECTION);
    break;

	case 'stateprovinces':
      $stateprov = $map->getLayerByName("stateprovinces");
      $stateprov->set("data",SHAPE_PATH."/10m_cultural/10m_admin_1_states_provinces_lines_shp.shp");
      $stateprov->set("status",MS_ON);
      $stateprov->setProjection(DEFAULT_PROJECTION);
	break;
	
	case 'stateprovinces_polygon':
	  $stateprovpoly = $map->getLayerByName("stateprovinces_polygon");
      $stateprovpoly->set("data",SHAPE_PATH."/10m_cultural/10m-admin-1-states-provinces-shp.shp");
      $stateprovpoly->set("template", "template.html");
      $stateprovpoly->set("status",MS_ON);
      $stateprovpoly->setProjection(DEFAULT_PROJECTION);
	break;

	case 'placenames':
	  $placenames = $map->getLayerByName("placenames");
	  $placenames->set("data",SHAPE_PATH."/10m_cultural/10m_populated_places.shp");
	  $placenames->set("status",MS_ON);
	  $placenames->setProjection(DEFAULT_PROJECTION);
	break;
  }
}

$layer = ms_newLayerObj($map);
$layer->set("name","newworld");
$layer->set("status",MS_ON);
$layer->set("data",$shape_file );
$layer->set("type",MS_SHAPE_LINE);
$layer->setProjection(DEFAULT_PROJECTION);

// Add new class to new layer
$class = ms_newClassObj($layer);

// Add new style to new class
$style = ms_newStyleObj($class);
$style->color->setRGB(30,30,30);

//add the shaded places
if($shaded_places) {
	$stateprov_poly = $map->getLayerByName("stateprovinces_polygon");
    $stateprov_poly->set("data",SHAPE_PATH."/10m_cultural/10m-admin-1-states-provinces-shp.shp");
    $stateprov_poly->set("template", "template.html");
    $stateprov_poly->setProjection(DEFAULT_PROJECTION);
    $shaded_places = trim($shaded_places);
	$qry = array();
	$regions = preg_split("/[,;]+/", $shaded_places); //split by a comma, semicolon
	foreach($regions as $region) {
		$pos = strpos($region, '[');
		if($pos !== false) {
			$split = explode("[", str_replace("]", "", trim(strtoupper($region))));
			$states = explode("|", $split[1]);
			$statekey = array();
			foreach($states as $state) {
				$statekey[] = "'[HASC_1]' =~ /".$state."$/";
			}
			$qry[] = "'[ISO]' = '".trim($split[0])."' AND (".implode(" OR ", $statekey).")";
		}
		else {
			$region = addslashes(ucwords(strtolower(trim($region))));
			$qry[] = "'[NAME_0]' = '".$region."' OR '[NAME_1]' = '".$region."'";
		}
	}
	
	$stateprov_poly->setFilter("(".implode(" OR ", $qry).")");
	$class = ms_newClassObj($stateprov_poly);
	if($shaded_title) $class->set("name", $shaded_title);

	$style = ms_newStyleObj($class);
	$style->color->setRGB($shaded_color[0], $shaded_color[1], $shaded_color[2]);
	$style->outlinecolor->setRGB(30,30,30);
	
	$stateprov_poly->set("status",MS_ON);
}

//add the points from the remote file
if($file) {
	$coord_cols = array();
	$legend = array();
	if (@$fp = fopen($file, 'r')) {
	  while ($line = fread($fp, 1024)) {
		$rows = preg_split("/[\r\n]+/", $line, -1, PREG_SPLIT_NO_EMPTY);
		$cols = explode("\t", $rows[0]);
		$num_cols = count($cols);
		$legend = explode("\t", $rows[0]);
		unset($rows[0]);
		foreach($rows as $row) {
			$cols = explode("\t", $row);
			for($i=0;$i<$num_cols;$i++) {
			  $coord_cols[$i][] = preg_split("/[\s,;]+/",$cols[$i]);
			}
		}
	  }
	}

	$col = 0;
	
	foreach($coord_cols as $coords) {
		$mlayer = ms_newLayerObj($map);
		$mlayer->set("name",$legend[$col]);
		$mlayer->set("status",MS_ON);
		$mlayer->set("type",MS_POINT);
		$mlayer->set("tolerance",5);
		$mlayer->set("toleranceunits",6);
		$mlayer->setProjection(DEFAULT_PROJECTION);

		$class = ms_newClassObj($mlayer);
		$class->set("name",$legend[$col]);

		$style = ms_newStyleObj($class);
		$style->set("symbolname",($shape[$col]) ? $shape[$col] : 'circle');
		$style->set("size",($size[$col]) ? $size[$col] : 8);

		$style->color->setRGB($color[$col][0],$color[$col][1],$color[$col][2]);

		if(!substr($shape[$col], 0, 4) == 'open') {
			$style->outlinecolor->setRGB($outlinecolor[0],$outlinecolor[1],$outlinecolor[2]);
		}

	    $mcoord_shape = ms_newShapeObj(MS_SHAPE_POINT);
	    $mcoord_line = ms_newLineObj();

		//add all the points
		foreach ($coords as $coord) {
			if( check_coord(array($coord[0],$coord[1])) ) {
				$mlayer->set("status", MS_ON);
				$mcoord_point = ms_newPointObj();
				$mcoord_point->setXY(trim($coord[1]), trim($coord[0]));
				$mcoord_line->add($mcoord_point);
	 		}
		}

		$mcoord_shape->add($mcoord_line);
		$mlayer->addFeature($mcoord_shape);
		
		$col++;
	}

}

//extra features
if($graticules) {
	$graticules = $map->getLayerByName("grid");
	$graticules->set("data",SHAPE_PATH."/10m_physical/10m_graticules_all/10m_graticules_10.shp");
	$graticules->set("status",MS_ON);
	$graticules->setProjection(DEFAULT_PROJECTION);

	$minx = $map->extent->minx;
	$maxx = $map->extent->maxx;
	
	$ticks = abs($maxx-$minx)/24;
	
	if($ticks >= 5) $labelformat = "DD";
	if($ticks < 5) $labelformat = "DDMM";
	if($ticks <= 1) $labelformat = "DDMMSS";
	
	ms_newGridObj($graticules);
	$graticules->grid->set("labelformat", $labelformat);
	$graticules->grid->set("maxarcs", $ticks);
	$graticules->grid->set("maxinterval", $ticks);
	$graticules->grid->set("maxsubdivide", 2);
}

//legend
if($legend_req) {
	$map->legend->set("status", MS_EMBED);
	$map->legend->set("keysizex", 20);
	$map->legend->set("keysizey", 17);
	$map->legend->set("keyspacingx", 5);
	$map->legend->set("keyspacingy", 5);
	$map->legend->set("postlabelcache", 1); // true
	$map->legend->set("transparent", 0); // 0 false, 1 true
	$map->legend->set("position", MS_UR);
	$map->legend->outlinecolor->setRGB(255,255,255);
	$map->legend->label->set("font", "arial");
	$map->legend->label->set("type", MS_TRUETYPE);
	$map->legend->label->set("size", 10);
	$map->legend->label->set("antialias", MS_FALSE);
	$map->legend->label->color->setRGB(0,0,0);
    $map->drawLegend();
}

if($scalebar) {
	$map->scalebar->set("status", MS_EMBED);
	$map->scalebar->set("style", 0);
	$map->scalebar->set("intervals", 3);
	$map->scalebar->set("height", 8);
	$map->scalebar->set("width", 200);
	$map->scalebar->color->setRGB(30,30,30);
	$map->scalebar->backgroundcolor->setRGB(255,255,255);
	$map->scalebar->outlinecolor->setRGB(0,0,0);
	$map->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
	$map->scalebar->set("position", MS_LR);
	$map->scalebar->set("transparent", 1); // 1 true, 0 false
	$map->scalebar->label->set("font", "arial");
	$map->scalebar->label->set("type", MS_TRUETYPE);
	$map->scalebar->label->set("size", 10);
	$map->scalebar->label->set("antialias", 50);
	$map->scalebar->label->color->setRGB(0,0,0);
	$map->drawScalebar();
}

if($border) {
	  $outline_layer = ms_newLayerObj($map);
	  $outline_layer->set("name","outline");
	  $outline_layer->set("type",MS_SHAPE_POLYGON);
	  $outline_layer->set("status",MS_ON);

	  // Add new class to new layer
	  $outline_class = ms_newClassObj($outline_layer);

	  // Add new style to new class
	  $outline_style = ms_newStyleObj($outline_class);
	  $outline_style->outlinecolor->setRGB(0,0,0);
	  $outline_style->set("width",3);

	  $polygon = ms_newShapeObj(MS_SHAPE_POLYGON);

	  $polyLine = ms_newLineObj();

	  $polyLine->addXY($map->extent->minx,$map->extent->miny);
	  $polyLine->addXY($map->extent->maxx,$map->extent->miny);
	  $polyLine->addXY($map->extent->maxx,$map->extent->maxy);
	  $polyLine->addXY($map->extent->minx,$map->extent->maxy);
	  $polyLine->addXY($map->extent->minx,$map->extent->miny);
	  $polygon->add($polyLine);
	  $outline_layer->addFeature($polygon);
}

if($output_projection != DEFAULT_PROJECTION) {
    @reproject_map($map,DEFAULT_PROJECTION,$output_projection);
    $image = $map->drawQuery();
} 
else {
    $image = $map->draw();
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

switch($output) {
	case 'tif': 
	  header("Content-Type: image/tiff");
	  header("Content-Transfer-Encoding: binary");
	break;
	
	case 'svg': 
	  header("Content-Type: image/svg+xml");
	break;

	case 'jpg':
	case 'jpga':
	  header("Content-Type: image/jpeg");
	break;
	
	case 'png':
	case 'pnga':
	  header("Content-Type: image/png");
	break;
		
	default:
	  header("Content-Type: image/png");
}

$image_url = $image->saveImage("");

?>