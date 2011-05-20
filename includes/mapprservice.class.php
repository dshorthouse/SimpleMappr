<?php

//include php_mapscript if not already included
if (!extension_loaded("MapScript"))  dl('php_mapscript.'.PHP_SHLIB_SUFFIX);

class mapprService {
	
	function __construct() {

	  $this->map						= '';
	  $this->map_path					= '';
	  $this->map_obj					= '';
	
	  $this->shape_path					= '';
	
	  $this->shapes						= array();
	
	  $this->symbol_file				= '';
	  $this->font_file					= '';
	  $this->tmp_path					= '/tmp';
	  $this->tmp_url					= '/tmp';
	
	  $this->default_projection			= 'epsg:4326';
	  $this->max_extent					= array(-180,-90,180,90);
	  $this->image_size					= array(800,400);
	  $this->download_factor			= 1;
	
	  $this->legend_url					= '';
	  $this->scalbar_url				= '';

	  $this->errors						= array();

	  $this->bad_points					= array();
	
	  // get $_POSTed elements
	  $this->getRequest();

	}

    /**
    * Test if has errors
    * @return boolean
    */
    private function has_error(){
        return count($this->errors) > 0;
    }

    /**
    * Set error message
    * @param string $message
    * @param string $layer name
    */
    private function set_error($message, $layer = 'Error'){
        $this->errors[$layer][] = $message;
    }
	
	private function show_errors() {
		print_r($this->errors);
	}
	
    public function setMapPath($map_path) {
	    $this->map_path = $map_path;
    }

    public function setMapFile($map_file) {
	    $this->map = $map_file;
    }

    public function setShapePath($shape_path) {
	    $this->shape_path = $shape_path;
    }

    public function setSymbolFile($symbol_file) {
	    $this->symbol_file = $symbol_file;
    }

    public function setFontFile($font_file) {
	    $this->font_file = $font_file;
    }

    public function setTmpPath($tmp_path) {
	    $this->tmp_path = $tmp_path;
    }

    public function setTmpUrl($tmp_url) {
	    $this->tmp_url = $tmp_url;
    }

    public function setDefaultProjection($projection) {
	    $this->default_projection = $projection;
    }

    public function setMaxExtent($extent) {
	    $extent = explode(',', $extent);
	    $this->max_extent = $extent;
    }

    public function setImageSize($image_size) {
	    $image_size = explode(',', $image_size);
	    $this->image_size = $image_size;
    }

    private function getRequest() {
        $this->coords			= $this->loadParam('coords', array());
        $this->regions			= $this->loadParam('regions', array());
        $this->output			= $this->loadParam('output','pnga');
        $this->projection		= $this->loadParam('projection', 'epsg:4326');
        $this->bbox_map			= $this->loadParam('bbox_map', '-180,-90,180,90');

        $this->bbox_rubberband	= $this->loadParam('bbox_rubberband', array());

        $this->pan				= $this->loadParam('pan', false);

        $this->layers			= $this->loadParam('layers', array());

        $this->download			= $this->loadParam('download', false);

        $this->crop				= $this->loadParam('crop', false);
        $this->legend			= $this->loadParam('legend', false);

        $this->options			= $this->loadParam('options', array()); //scalebar, legend

        $this->rotation			= $this->loadParam('rotation', 0);
        $this->zoom_out			= $this->loadParam('zoom_out', false);

        $this->download_factor	= $this->loadParam('download_factor', 1);
    }

	/**
	* Get a request parameter
	* @param string $name
	* @param string $default parameter optional
	* @return string the parameter value or empty string if null
	*/
	private function loadParam($name, $default = ''){
    	if(!isset($_REQUEST[$name]) || !$_REQUEST[$name]) return $default;
    	$value = $_REQUEST[$name];
    	if(get_magic_quotes_gpc() != 1) $value = addslashes($value);
    	return $value;
	}
	
    /**
    * Load the map and create the map instance
    */
    private function loadMap(){
        if(!file_exists($this->map) && is_readable($this->map)){
            $this->set_error('Cannot read mapfile '. $this->map);
        } else {
            $this->map_object = ms_newMapObj($this->map);
            if(!$this->map_object){
                $this->set_error('Cannot load mapfile '. $this->map);
            }
        }
    }

    private function loadShapes() {
	  $this->shapes['base']				= $this->shape_path . "/10m_cultural/10m_admin_0_countries.shp";
	  $this->shapes['graticules']		= $this->shape_path . "/10m_physical/10m_graticules_all/10m_graticules_10.shp";
	  $this->shapes['stateprovinces']	= $this->shape_path . "/10m_cultural/10m_admin_1_states_provinces_lines_shp.shp";
	  $this->shapes['stateprovinces_polygon'] = $this->shape_path . "/10m_cultural/10m-admin-1-states-provinces-shp.shp";
	  $this->shapes['lakes']			= $this->shape_path . "/10m_physical/10m_lakes.shp";
	  $this->shapes['rivers']			= $this->shape_path . "/10m_physical/10m_rivers_lake_centerlines.shp";
	  $this->shapes['placenames']		= $this->shape_path . "/10m_cultural/10m_populated_places_simple.shp";
    }

    /**
    *
    */
    public function run() {

        if(!$this->map){
            $this->set_error('No mapfile specified');
        }
        else {
			// Load map
			$this->loadShapes();
			$this->loadMap();
        }
		
		if($this->has_error()) {
           exit;
        }
	
		$this->map_object->set("name","newworld");
		$this->map_object->set("shapepath",$this->shapes['base']);
		$this->map_object->setSymbolSet($this->symbol_file);
		$this->map_object->setFontSet($this->font_file);
		$this->map_object->web->set("template","template.html");
		$this->map_object->web->set("imagepath",$this->tmp_path);
		$this->map_object->web->set("imageurl",$this->tmp_url);

		// Set the output format and size
		$this->map_object->selectOutputFormat($this->output);
		
		// Set the extent
		$ext = explode(',',$this->bbox_map);
		$this->map_object->setExtent($ext[0],$ext[1],$ext[2],$ext[3]);
		$this->map_object->set("units",MS_DD);
		$this->map_object->imagecolor->setRGB(255,255,255);
		
		//adjust size depending on user input
		$this->setMapSize();
		
		// Add new base layer to map
		$layer = ms_newLayerObj($this->map_object);
		$layer->set("name","newworld");
		$layer->set("status",MS_ON);
		$layer->set("data",$this->shapes['base']);
		$layer->set("type",MS_SHAPE_LINE);
		$layer->setProjection('init=' . $this->default_projection);

		// Add new class to new layer
		$class = ms_newClassObj($layer);

		// Add new style to new class
		$style = ms_newStyleObj($class);
		$style->color->setRGB(30,30,30);

		//zoom in
		if($this->bbox_rubberband && !$this->download) $this->zoomIn();

		//zoom out
		if($this->zoom_out) $this->zoomOut();
		
		//pan
		if($this->pan) $this->setPan();
		
		//rotation
		if($this->rotation != 0) $this->map_object->setRotation($this->rotation);
		
		//crop
		if($this->crop && $this->bbox_rubberband && $this->download) $this->setCrop();
		
		// Add border if requested
		if($this->options['border'] && $this->download) $this->addBorder();
		
		//add other layers as requested
		$this->addLayers();
		
		//add north arrow
//		$this->addNorthArrow();
		
		//add shaded political regions
		$this->addRegions();

		//add the coordinates
		$this->addCoordinates();

		if($this->projection != $this->default_projection) {
		    $this->reprojectMap($this->default_projection, $this->projection);
			if($this->download) {
				if($this->options['legend']) $this->addLegend();
				if($this->options['scalebar']) $this->addScalebar();
				$this->image = $this->map_object->drawQuery();
			}
			else {
				$this->image = $this->map_object->drawQuery();
				if($this->legend) $this->addLegend();
				if($this->options['scalebar']) $this->addScalebar();
			}
		} 
		else {
			//swap the order of legend and scalebar addition depending on if download or not
			if($this->download) {
				if($this->options['legend']) $this->addLegend();
				if($this->options['scalebar']) $this->addScalebar();
				$this->image = $this->map_object->draw();
			}
			else {
				$this->image = $this->map_object->draw();
				if($this->legend) $this->addLegend();
				if($this->options['scalebar']) $this->addScalebar();
			}
		}
		
		$this->produceOutput();
    }

    private function setMapSize() {
		$this->map_object->setSize($this->image_size[0], $this->image_size[1]);
		if($this->download) {
			$this->map_object->setSize($this->download_factor*$this->image_size[0], $this->download_factor*$this->image_size[1]);	
		}
    }

    private function zoomIn() {
		$bbox_rubberband = explode(',',$this->bbox_rubberband);
		
		if($bbox_rubberband[0] == $bbox_rubberband[2] || $bbox_rubberband[1] == $bbox_rubberband[3]) {
			$zoom_point = ms_newPointObj();
			$zoom_point->setXY($bbox_rubberband[0],$bbox_rubberband[1]);
			$max_extent = ms_newRectObj();
			$max_extent->setExtent($this->max_extent[0], $this->max_extent[1], $this->max_extent[2], $this->max_extent[3]);
			if($this->projection != $this->default_projection) {
			  $origProjObj = ms_newProjectionObj('init=' . $this->default_projection);
			  $newProjObj = ms_newProjectionObj('init=' . $this->projection);
			  $max_extent->project($origProjObj,$newProjObj);	
			}
			$this->map_object->zoompoint(2, $zoom_point, $this->map_object->width, $this->map_object->height, $this->map_object->extent, $max_extent);
		}
		else {
			$zoom_rect = ms_newRectObj();
			$zoom_rect->setExtent($bbox_rubberband[0], $bbox_rubberband[3], $bbox_rubberband[2], $bbox_rubberband[1]);
			$this->map_object->zoomrectangle($zoom_rect, $this->map_object->width, $this->map_object->height, $this->map_object->extent);	
		}
    }

	private function zoomOut() {
		$zoom_point = ms_newPointObj();
		$zoom_point->setXY($this->map_object->width/2,$this->map_object->height/2);
		$max_extent = ms_newRectObj();
		$max_extent->setExtent($this->max_extent[0], $this->max_extent[1], $this->max_extent[2], $this->max_extent[3]);
		if($this->projection != $this->default_projection) {
		  $origProjObj = ms_newProjectionObj('init=' . $this->default_projection);
		  $newProjObj = ms_newProjectionObj('init=' . $this->projection);
		  $max_extent->project($origProjObj,$newProjObj);	
		}
		$this->map_object->zoompoint(-2, $zoom_point, $this->map_object->width, $this->map_object->height, $this->map_object->extent, $max_extent);
	}
	
	private function setPan() {
		switch ($this->pan) {
		    case 'up':
		      $x_offset = 1;
		      $y_offset = 0.9;
		    break;

		    case 'right':
		      $x_offset = 1.1;
		      $y_offset = 1;
		    break;

		    case 'down':
		      $x_offset = 1;
		      $y_offset = 1.1;
		    break;

		    case 'left':
		      $x_offset = 0.9;
		      $y_offset = 1;
		    break;
		  }
		  $new_point = ms_newPointObj();
		  $new_point->setXY($this->map_object->width/2*$x_offset,$this->map_object->height/2*$y_offset);
		  $max_extent = ms_newRectObj();
		  $max_extent->setExtent($this->max_extent[0], $this->max_extent[1], $this->max_extent[2], $this->max_extent[3]);
		  if($this->projection != $this->default_projection) {
		    $origProjObj = ms_newProjectionObj('init=' . $this->default_projection);
		    $newProjObj = ms_newProjectionObj('init=' . $this->projection);
		    $max_extent->project($origProjObj,$newProjObj);	
		  }
		  $this->map_object->zoompoint(1, $new_point, $this->map_object->width, $this->map_object->height, $this->map_object->extent, $max_extent);
	}

    private function setCrop() {

        $bbox_rubberband = explode(',',$this->bbox_rubberband);

        //lower-left coordinate
        $ll_point = new stdClass();
        $ll_point->x = $bbox_rubberband[0];
        $ll_point->y = $bbox_rubberband[3];
		$ll_coord = $this->pix2Geo($ll_point);
		
		//upper-right coordinate
		$ur_point = new stdClass();
		$ur_point->x = $bbox_rubberband[2];
		$ur_point->y = $bbox_rubberband[1];
		$ur_coord = $this->pix2Geo($ur_point);
		
		//set the size as selected
		$width = abs($bbox_rubberband[2]-$bbox_rubberband[0]);
		$height = abs($bbox_rubberband[3]-$bbox_rubberband[1]);
		$this->map_object->setSize($this->download_factor*$width,$this->download_factor*$height);
		
        //set the extent to match that of the crop
		$this->map_object->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);
    }

	private function addCoordinates() {

	  //do this in reverse order because the legend will otherwise be presented in reverse order
	  for($j=count($this->coords)-1; $j>=0; $j--) {

		//clear out previous loop's selection
	    $size = '';
	    $shape = '';
	    $color = '';

		$title = $this->coords[$j]['title'] ? $this->coords[$j]['title'] : '';
		$size = $this->coords[$j]['size'] ? $this->coords[$j]['size'] : 8;
		$shape = $this->coords[$j]['shape'] ? $this->coords[$j]['shape'] : 'circle';
		$color = $this->coords[$j]['color'] ? explode(" ",$this->coords[$j]['color']) : explode(" ","0 0 0");

	    if(trim($this->coords[$j]['data'])) {
		
		    $this->legend = true;
		
		    $layer = ms_newLayerObj($this->map_object);
		    $layer->set("name","layer_".$j);
		    $layer->set("status",MS_ON);
		    $layer->set("type",MS_LAYER_POINT);
		    $layer->set("tolerance",5);
		    $layer->set("toleranceunits",6);
		    $layer->setProjection('init=' . $this->default_projection);

		    $class = ms_newClassObj($layer);
		    if($title != "") $class->set("name",$title);

		    $style = ms_newStyleObj($class);
		    $style->set("symbolname",$shape);
		    $style->set("size",$size);

		    if(substr($shape, 0, 4) == 'open') {
			    $style->color->setRGB($color[0],$color[1],$color[2]);
		    }
		    else {
			    $style->color->setRGB($color[0],$color[1],$color[2]);
			    $style->outlinecolor->setRGB(30,30,30);
		    }

            $new_shape = ms_newShapeObj(MS_SHAPE_POINT);
            $new_line = ms_newLineObj();

		    $whole = trim($this->coords[$j]['data']);  //grab the whole textarea
		    $row = explode("\n",$this->removeEmptyLines($whole));  //split the lines that have data
		
			$points = array(); //create an array to hold unique locations
		
		    foreach ($row as $loc) {
			  $coord_array = preg_split("/[\s,;]+/",$loc); //split the coords by a space, comma, semicolon, or \t
			  $coord = new stdClass();
			  $coord->x = trim($coord_array[1]);
			  $coord->y = trim($coord_array[0]);
		      if($this->checkCoord($coord) && $title != "") {  //only add point when data are good & a title
				  $points[$coord->x.$coord->y] = array($coord->x, $coord->y); //unique locations
		      }
		      else {
			    $this->bad_points[] = $this->coords[$j]['title'].': '.$coord->x.','.$coord->y;
		      }
		    }
		    foreach($points as $point) {
				$new_point = ms_newPointObj();
				$new_point->setXY($point[0], $point[1]);
				$new_line->add($new_point);
		    }
		    $new_shape->add($new_line);
		    $layer->addFeature($new_shape);
	    }
	  }
	}

	private function addRegions() {
		if($this->regions['data']) {
			
			$this->legend = true;
			
			$layer = $this->map_object->getLayerByName('stateprovinces_polygon');
			$layer->set("data",$this->shapes['stateprovinces_polygon']);
			$layer->set("template", "template.html");
			$layer->setProjection('init=' . $this->default_projection);
			
			//grab the textarea for regions & split
			$whole = trim($this->regions['data']);
			$rows = explode("\n",$this->removeEmptyLines($whole));
			$qry = array();
			foreach($rows as $row) {
				$regions = preg_split("/[,;]+/", $row); //split by a comma, semicolon
				foreach($regions as $region) {
					$pos = strpos($region, '[');
					if($pos !== false) {
						$split = explode("[", str_replace("]", "", trim(strtoupper($region))));
						$states = preg_split("/[\s|]+/", $split[1]);
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
			}
			
			$layer->setFilter("(".implode(" OR ", $qry).")");
			$class = ms_newClassObj($layer);
			$class->set("name", $this->regions['title']);

			$style = ms_newStyleObj($class);
			$color = ($this->regions['color']) ? explode(' ', $this->regions['color']) : explode(" ", "0 0 0");
			$style->color->setRGB($color[0],$color[1],$color[2]);
			$style->outlinecolor->setRGB(30,30,30);
			
			$layer->set("status",MS_ON);

		}
	}

	private function addLayers() {
		foreach($this->layers as $layer => $status) {
			$shape = $this->map_object->getLayerByName($layer);
			$shape->set("data",$this->shapes[$layer]);
			$shape->set("status",MS_ON);
			$shape->setProjection('init=' . $this->default_projection);
			switch($layer) {
				case 'grid':
					ms_newGridObj($shape);
					$minx = $this->map_object->extent->minx;
					$maxx = $this->map_object->extent->maxx;
					
					//project the extent back to default such that we can work with proper tick marks
					if($this->projection != $this->default_projection) {
						$origProjObj = ms_newProjectionObj('init=' . $this->projection);
						$newProjObj = ms_newProjectionObj('init=' . $this->default_projection);
						
						$poPoint1 = ms_newPointObj();
						$poPoint1->setXY($this->map_object->extent->minx, $this->map_object->extent->miny);
						
						$poPoint2 = ms_newPointObj();
						$poPoint2->setXY($this->map_object->extent->maxx, $this->map_object->extent->maxy);
						
						@$poPoint1->project($origProjObj,$newProjObj);
						@$poPoint2->project($origProjObj,$newProjObj);
						
						$minx = $poPoint1->x;
						$maxx = $poPoint2->x;
					}
					
					$ticks = abs($maxx-$minx)/24;
					
					if($ticks >= 5) $labelformat = "DD";
					if($ticks < 5) $labelformat = "DDMM";
					if($ticks <= 1) $labelformat = "DDMMSS";
					
					$shape->grid->set("labelformat", $labelformat);
					$shape->grid->set("maxarcs", $ticks);
					$shape->grid->set("maxinterval", $ticks);
					$shape->grid->set("maxsubdivide", 2);
				break;

				default:
			}
		}
	}
	
	private function addLegend() {
		$this->map_object->legend->set("keysizex", 20);
		$this->map_object->legend->set("keysizey", 17);
		$this->map_object->legend->set("keyspacingx", 5);
		$this->map_object->legend->set("keyspacingy", 5);
		$this->map_object->legend->set("postlabelcache", 1); // true
		$this->map_object->legend->set("transparent", 0); // 0 false, 1 true
		$this->map_object->legend->outlinecolor->setRGB(255,255,255);  //white border
		$this->map_object->legend->label->set("font", "arial");
		$this->map_object->legend->label->set("type", MS_TRUETYPE);
		$this->map_object->legend->label->set("position", 1);
		$this->map_object->legend->label->set("size", ($this->download) ? $this->download_factor*9 : 10);
		$this->map_object->legend->label->set("antialias", 50);
		$this->map_object->legend->label->set("offsetx", -10);
		$this->map_object->legend->label->set("offsety", -13);
		$this->map_object->legend->label->color->setRGB(0,0,0);
		
		//svg format cannot do legends in MapServer
		if($this->download && $this->legend && $this->output != 'svg') {
			$this->map_object->legend->set("status", MS_EMBED);
			$this->map_object->legend->set("position", MS_UR);
			$this->map_object->drawLegend();
		}
		if(!$this->download) {
			$this->map_object->legend->set("status", MS_DEFAULT);
			$legend = $this->map_object->drawLegend();
			$this->legend_url = $legend->saveWebImage();
		}
	}
	
	private function addScalebar() {
		$this->map_object->scalebar->set("style", 0);
		$this->map_object->scalebar->set("intervals", 3);
		$this->map_object->scalebar->set("height", ($this->download) ? $this->download_factor*4 : 8);
		$this->map_object->scalebar->set("width", ($this->download) ? $this->download_factor*100 : 200);
		$this->map_object->scalebar->color->setRGB(30,30,30);
		$this->map_object->scalebar->backgroundcolor->setRGB(255,255,255);
		$this->map_object->scalebar->outlinecolor->setRGB(0,0,0);
		$this->map_object->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
		$this->map_object->scalebar->set("transparent", 1); // 1 true, 0 false
		$this->map_object->scalebar->label->set("font", "arial");
		$this->map_object->scalebar->label->set("type", MS_TRUETYPE);
		$this->map_object->scalebar->label->set("size", ($this->download) ? $this->download_factor*5 : 8);
		$this->map_object->scalebar->label->set("antialias", 50);
		$this->map_object->scalebar->label->color->setRGB(0,0,0);
		
		//svg format cannot do scalebar in MapServer
		if($this->download && $this->output != 'svg') {
			$this->map_object->scalebar->set("status", MS_EMBED);
			$this->map_object->scalebar->set("position", MS_LR);
			$this->map_object->drawScalebar();
		}
		if(!$this->download) {
			$this->map_object->scalebar->set("status", MS_DEFAULT);
			$scale = $this->map_object->drawScalebar();
			$this->scalebar_url = $scale->saveWebImage();
		}
	}
	
	private function addNorthArrow() {
		$shape = $this->map_object->getLayerByName("northarrow");
		$shape->set("status",MS_ON);
		$shape->setProjection('init=' . $this->default_projection);
		
		$class = ms_newClassObj($shape);
	    $class->set("name","northarrow");

	    $style = ms_newStyleObj($class);
	    $style->set("symbolname","northarrow");
	    $style->set("angle","[".$this->rotation."]");
		
		$new_shape = ms_newShapeObj(MS_SHAPE_POINT);
		$new_line = ms_newLineObj();

		$new_point = ms_newPointObj();
		$loc = new stdClass();
		$loc->x = $this->image_size[0]-95;
		$loc->y = 75;
		
		$point = $this->pix2Geo($loc);
		$new_point->setXY($point->x, $point->y);
		$new_line->add($new_point);

	    $new_shape->add($new_line);
	    $shape->addFeature($new_shape);
	}

	private function addBorder() {
		  $outline_layer = ms_newLayerObj($this->map_object);
		  $outline_layer->set("name","outline");
		  $outline_layer->set("type",MS_LAYER_POLYGON);
		  $outline_layer->set("status",MS_ON);

		  // Add new class to new layer
		  $outline_class = ms_newClassObj($outline_layer);

		  // Add new style to new class
		  $outline_style = ms_newStyleObj($outline_class);
		  $outline_style->outlinecolor->setRGB(0,0,0);
		  $outline_style->set("width",3);

		  $polygon = ms_newShapeObj(MS_SHAPE_POLYGON);

		  $polyLine = ms_newLineObj();
		  $polyLine->addXY($this->map_object->extent->minx,$this->map_object->extent->miny);
		  $polyLine->addXY($this->map_object->extent->maxx,$this->map_object->extent->miny);
		  $polyLine->addXY($this->map_object->extent->maxx,$this->map_object->extent->maxy);
		  $polyLine->addXY($this->map_object->extent->minx,$this->map_object->extent->maxy);
		  $polyLine->addXY($this->map_object->extent->minx,$this->map_object->extent->miny);
		  $polygon->add($polyLine);

		  $outline_layer->addFeature($polygon);	
	}
	
	private function getBadPoints() {
		return implode('<br />', $this->bad_points);
	}
	
	private function produceOutput() {
		switch($this->output) {
		case 'tif':
		  error_reporting(0);
		  $this->image_url = $this->image->saveWebImage();
		  $image_filename = basename($this->image_url);
		  header("Pragma: public");
		  header("Expires: 0");
		  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		  header("Cache-Control: private",false); 
		  header("Content-Type: image/tiff");
		  header("Content-Disposition: attachment; filename=\"map-" . $image_filename ."\";" );
		  header("Content-Transfer-Encoding: binary");
		  header("Content-Length: ".filesize($this->tmp_path.$image_filename));
		  ob_clean();
		  flush();
		  readfile($this->tmp_path.$image_filename);
		  exit();
		break;

		case 'png':
		  error_reporting(0);
		  $this->image_url = $this->image->saveWebImage();
		  $image_filename = basename($this->image_url);
		  header("Pragma: public");
		  header("Expires: 0");
		  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		  header("Cache-Control: private",false); 
		  header("Content-Type: image/png");
		  header("Content-Disposition: attachment; filename=\"map-" . $image_filename ."\";" );
		  header("Content-Transfer-Encoding: binary");
		  header("Content-Length: ".filesize($this->tmp_path.$image_filename));
		  ob_clean();
		  flush();
		  readfile($this->tmp_path.$image_filename);
		  exit();
		break;

		case 'svg':
		  header("Pragma: public");
		  header("Expires: 0");
		  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		  header("Cache-Control: private",false); 
		  header("Content-Type: image/svg+xml");
		  header("Content-Disposition: attachment; filename=\"map-" . time() . ".svg\";" );
		  $this->image->saveImage("");
		  exit();
		break;

		default:
		  $this->image_url = $this->image->saveWebImage();
		
		  echo '<img id="mapOutputImage" src="'.$this->image_url.'" />' . "\n";
		  echo '<input type="hidden" id="rendered_bbox" value="'.$this->map_object->extent->minx.', '.$this->map_object->extent->miny.', '.$this->map_object->extent->maxx.', '.$this->map_object->extent->maxy.'"></input>';
		  echo '<input type="hidden" id="rendered_rotation" value="'.$this->rotation.'"></input>';
		  echo '<input type="hidden" id="legend_url" value="' . $this->legend_url . '"></input>';
		  echo '<input type="hidden" id="scalebar_url" value="' . $this->scalebar_url . '"></input>';
		  echo '<input type="hidden" id="bad_points" value="' . $this->getBadPoints() . '"></input>';
		}
	}
	
	/**
	 * Reproject a $map from one projection to another
	 * @param obj $map
	 * @param string $input_projection
	 * @param string $output_projection
	 */
	private function reprojectMap($input_projection,$output_projection) {
		
	    $origProjObj = ms_newProjectionObj('init=' . $input_projection);
	    $newProjObj = ms_newProjectionObj('init=' . $output_projection);

	    $oRect = $this->map_object->extent;
	    @$oRect->project($origProjObj,$newProjObj);
	    $this->map_object->setExtent($oRect->minx,$oRect->miny,$oRect->maxx,$oRect->maxy);
	    $this->map_object->setProjection('init=' . $output_projection);
	}

	/**
	 * Convert image coordinates to map coordinates
	 * @param obj $point, (x,y) coordinates in pixels
	 * @return obj $newPoint reprojected point in map coordinates
	 */
	 private function pix2Geo($point) {
	   $newPoint = new stdClass();
	   $deltaX = abs($this->map_object->extent->maxx - $this->map_object->extent->minx);
	   $deltaY = abs($this->map_object->extent->maxy - $this->map_object->extent->miny);
	
	   $newPoint->x = $this->map_object->extent->minx + ($point->x*$deltaX)/(int)$this->image_size[0];
	   $newPoint->y = $this->map_object->extent->miny + (((int)$this->image_size[1] - $point->y)*$deltaY)/(int)$this->image_size[1];
	   return $newPoint;
	 }

    /**
    * Remove empty lines from a string
    * @param $string
    * @return string cleansed string with empty lines removed
    */
	private function removeEmptyLines($string) {
	  return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
	}

	/**
	 * Check a DD coordinate object and return true if it fits on globe, false if not
	 * @param obj $coord (x,y) coordinates
	 * @return true,false
	 */
	private function checkCoord($coord) {
		$output = false;
		if((float)$coord->x && (float)$coord->y && $coord->y <= 90 && $coord->y >= -90 && $coord->x <= 180 && $coord->x >= -180) $output = true;
		return $output;
	}

}
?>
