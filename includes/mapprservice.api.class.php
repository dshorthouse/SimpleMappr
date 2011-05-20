<?php

/**************************************************************************

File: mapprservice.api.class.php

Description: Extends the base map class for SimpleMappr. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  Marine Biological Laboratory

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/

require_once ('../includes/mapprservice.class.php');
require_once ('../includes/georss/rss_fetch.inc');

class MAPPRAPI extends MAPPR {
    
    private $_file;
    private $_data;
    
    function __construct() {
    }
    
    function __destruct() {
    }
    
    /**
    * Override the getRequest() method in the MAPPR class
    */
    public function getRequest() {
    
        //ping API to return JSON
        $this->ping             = $this->loadParam('ping', false);
        if($this->ping) {
          header("Content-Type: application/json");
          $output = array("status" => "ok");
          echo json_encode($output);
          exit;
        }
        
        $this->download         = true;
        $this->options          = array();

        //load the file
        $this->file             = urldecode($this->loadParam('file', '')); 
        $this->georss           = urldecode($this->loadParam('georss', ''));
        $this->shape            = (is_array($this->loadParam('shape', array()))) ? $this->loadParam('shape', array()) : array($this->loadParam('shape', array()));
        $this->size             = (is_array($this->loadParam('size', array()))) ? $this->loadParam('size', array()) : array($this->loadParam('size', array()));
        $this->color            = (is_array($this->loadParam('color', array()))) ? $this->loadParam('color', array()) : array($this->loadParam('color', array()));
        
        $this->outlinecolor     = $this->loadParam('outlinecolor', '255,255,255');
        
        $shaded = $this->loadParam('shade', array());
        $this->regions = array(
            'data' => (array_key_exists('places', $shaded)) ? $shaded['places'] : "",
            'title' => (array_key_exists('title', $shaded)) ? $shaded['title'] : "",
            'color' => (array_key_exists('color', $shaded)) ? str_replace(",", " ",$shaded['color']) : "120 120 120"
        );

        $wkt = $this->loadParam('wkt', array());
        $this->wkt = array(
          'data' => (array_key_exists('data', $wkt)) ? $wkt['data'] : "",
          'title' => (array_key_exists('title', $wkt)) ? $wkt['title'] : "",
          'color' => (array_key_exists('color', $wkt)) ? $wkt['color'] : ""
        );
        
        $this->output           = $this->loadParam('output','pnga');
        $this->projection       = $this->loadParam('projection', 'epsg:4326');
        $this->projection_map   = $this->projection;
        
        $this->bbox_map         = $this->loadParam('bbox', '-180,-90,180,90');

        //convert layers as comma-separated values to an array
        $_layers                = explode(',', $this->loadParam('layers', ''));
        $layers = array();
        foreach($_layers as $_layer) {
            if($_layer) $layers[trim($_layer)] = trim($_layer);
        }
        $this->layers           = $layers;
        $this->graticules       = $this->loadParam('graticules', false);

        if($this->loadParam('border', false)) $this->options['border'] = true;
        if($this->loadParam('legend', false)) $this->options['legend'] = true;
        if($this->loadParam('scalebar', false)) $this->options['scalebar'] = true;

        //set the image size from width & height to array(width, height)
        $this->width            = $this->loadParam('width', 800);
        $this->height           = $this->loadParam('height', 400);
        $this->image_size       = array($this->width, $this->height);
        
        //blank out some variables
        $this->download_legend      = false;
        $this->zoom_out             = false;
        $this->pan                  = false;
        $this->crop                 = false;
        $this->bbox_rubberband      = array();
        $this->rotation             = 0;
        $this->coords               = array();
    }
    
    /**
    * Override the addCoordinates() method in the MAPPR class
    */ 
    public function addCoordinates() {  
	
        $coord_cols = array();
        $legend = array();
        $col = 0;
        
        if($this->file || $this->georss) {
            if($this->file) {
                if (@$fp = fopen($this->file, 'r')) {
                  while ($line = fread($fp, 1024)) {
                    $rows = preg_split("/[\r\n]+/", $line, -1, PREG_SPLIT_NO_EMPTY);
                    $cols = explode("\t", $rows[0]);
                    $num_cols = count($cols);
                    $legend = explode("\t", $rows[0]);
                    unset($rows[0]);
                    foreach($rows as $row) {
                        $cols = explode("\t", $row);
                        for($i=0;$i<$num_cols;$i++) {
                          $coord_cols[$i][] = array_key_exists($i, $cols) ? preg_split("/[\s,;]+/",$cols[$i]) : array();
                        }
                    }
                  }
                }
            }
            if($this->georss) {
                $rss = fetch_rss($this->georss);
                if(isset($rss->items)) {
                    $num_cols = (isset($num_cols)) ? $num_cols++ : 0;
                    $legend[$num_cols] = $rss->channel['title'];
                    foreach ($rss->items as $item) {
                        if(isset($item['georss']) && isset($item['georss']['point'])) {
                            $coord_cols[$num_cols][] = preg_split("/[\s,;]+/", $item['georss']['point']);
                        }
                        elseif(isset($item['geo']) && isset($item['geo']['lat']) && isset($item['geo']['lat'])) {
                            $coord_cols[$num_cols][] = array($item['geo']['lat'], $item['geo']['long']);
                        }
                        elseif(isset($item['geo']) && isset($item['geo']['lat_long'])) {
                            $coord_cols[$num_cols][] = preg_split("/[\s,;]+/", $item['geo']['lat_long']);
                        }
                    }
                }
            }

            foreach($coord_cols as $col => $coords) {
                $mlayer = ms_newLayerObj($this->_map_obj);
                $mlayer->set("name",$legend[$col]);
                $mlayer->set("status",MS_ON);
                $mlayer->set("type",MS_LAYER_POINT);
                $mlayer->set("tolerance",5);
                $mlayer->set("toleranceunits",6);
                $mlayer->setProjection($this->_default_projection);

                $class = ms_newClassObj($mlayer);
                $class->set("name",$legend[$col]);

                $style = ms_newStyleObj($class);
                $style->set("symbolname",(array_key_exists($col, $this->shape) && in_array($this->shape[$col], $this->accepted_shapes)) ? $this->shape[$col] : 'circle');
                $style->set("size",(array_key_exists($col, $this->size)) ? $this->size[$col] : 8);
                
                if(array_key_exists($col, $this->color)) {
                    $color = explode(",",$this->color[$col]);
                    $style->color->setRGB(
                        (array_key_exists(0, $color)) ? $color[0] : 0,
                        (array_key_exists(1, $color)) ? $color[1] : 0,
                        (array_key_exists(2, $color)) ? $color[2] : 0
                    );
                }
                else {
                    $style->color->setRGB(0,0,0);
                }
                
                if(array_key_exists($col, $this->shape) && !substr($this->shape[$col], 0, 4) == 'open') {
                    $outlinecolor = explode(",", $this->outlinecolor);  
                    $style->outlinecolor->setRGB(
                        (array_key_exists(0, $outlinecolor)) ? $outlinecolor[0] : 255,
                        (array_key_exists(1, $outlinecolor)) ? $outlinecolor[1] : 255,
                        (array_key_exists(2, $outlinecolor)) ? $outlinecolor[2] : 255
                    );
                }

                $mcoord_shape = ms_newShapeObj(MS_SHAPE_POINT);
                $mcoord_line = ms_newLineObj();
            
                //add all the points
                foreach ($coords as $coord) {
                    if(is_array($coord) && array_key_exists(0, $coord) && array_key_exists(1, $coord)) {
                        $_coord = new stdClass;
                        $_coord->y = trim($coord[0]);
                        $_coord->x = trim($coord[1]);
                        if($this->checkCoord($_coord)) {
                            $mcoord_point = ms_newPointObj();
                            $mcoord_point->setXY($_coord->x, $_coord->y);
                            $mcoord_line->add($mcoord_point);
                        }
                    }
                }
                 
                $mcoord_shape->add($mcoord_line);
                $mlayer->addFeature($mcoord_shape);

                $col++;
            }
        }
    }
    
    /**
    * Override the addRegions() method in the MAPPR class
    */
    public function addRegions() {
        if($this->regions['data']) {            
            $layer = ms_newLayerObj($this->_map_obj);
            $layer->set("name","stateprovinces_polygon");
            $layer->set("data",$this->_shapes['stateprovinces_polygon']['shape']);
            $layer->set("type",$this->_shapes['stateprovinces_polygon']['type']);
            $layer->set("template", "template.html");
            $layer->setProjection('init=' . $this->_default_projection);
            
            //grab the data for regions & split
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
                        $qry[] = "'[NAME_0]' =~ /".$region."$/ OR '[NAME_1]' =~ /".$region."$/";
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

    /**
     * Override the addFreehand method in the MAPPR class
     */
     public function addFreehand() {
       if($this->wkt['data']) {
           $layer = ms_newLayerObj($this->_map_obj);
           $layer->set("name", $this->wkt['title']);

           $feature = ms_shapeObjFromWkt($this->wkt['data']);
           $layer->addFeature($feature);

           $type = (strstr($this->wkt['data'], "LINE")) ? MS_LAYER_LINE : MS_LAYER_POLYGON;

           $layer->set("type",$type);
           $layer->set("template", "template.html");
           $layer->setProjection('init=' . $this->_default_projection);

           $class = ms_newClassObj($layer);
           $class->set("name", $this->wkt['title']);

           $style = ms_newStyleObj($class);
           $style->set("opacity",80);
           $color = ($this->wkt['color']) ? explode(' ', $this->wkt['color']) : explode(" ", "0 0 0");
           $style->color->setRGB($color[0],$color[1],$color[2]);
           $style->outlinecolor->setRGB(30,30,30);

           $layer->set("status",MS_ON);
      }
    }
    
    /**
    * Override the addGraticules() method in the MAPPR class
    */
    public function addGraticules() {
        $layer = ms_newLayerObj($this->_map_obj);
        $layer->set("name", 'grid');
        $layer->set("data", $this->_shapes['grid']['shape']);
        $layer->set("type", $this->_shapes['grid']['type']);
        $layer->set("status",MS_ON);
        $layer->setProjection('init=' . $this->_default_projection);
        
        $class = ms_newClassObj($layer);
        $class->label->set("font", "arial");
        $class->label->set("type", MS_TRUETYPE);
        $class->label->set("size", 10);
        $class->label->set("position", MS_UC);
        $class->label->color->setRGB(30, 30, 30);
        $style = ms_newStyleObj($class);
        $style->color->setRGB(200,200,200);

        ms_newGridObj($layer);
        $minx = $this->_map_obj->extent->minx;
        $maxx = $this->_map_obj->extent->maxx;

        $ticks = abs($maxx-$minx)/24;

        if($ticks >= 5) $labelformat = "DD";
        if($ticks < 5) $labelformat = "DDMM";
        if($ticks <= 1) $labelformat = "DDMMSS";

        $layer->grid->set("labelformat", $labelformat);
        $layer->grid->set("maxarcs", $ticks);
        $layer->grid->set("maxinterval", $ticks);
        $layer->grid->set("maxsubdivide", 2);
    }
    
    /**
    * Override the addScalebar() method in the MAPPR class
    */
    public function addScalebar() {
        $this->_map_obj->scalebar->set("style", 0);
        $this->_map_obj->scalebar->set("intervals", 3);
        $this->_map_obj->scalebar->set("height", 8);
        $this->_map_obj->scalebar->set("width", 200);
        $this->_map_obj->scalebar->color->setRGB(30,30,30);
        $this->_map_obj->scalebar->backgroundcolor->setRGB(255,255,255);
        $this->_map_obj->scalebar->outlinecolor->setRGB(0,0,0);
        $this->_map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->_map_obj->scalebar->set("transparent", 1); // 1 true, 0 false
        $this->_map_obj->scalebar->label->set("font", "arial");
        $this->_map_obj->scalebar->label->set("type", MS_TRUETYPE);
        $this->_map_obj->scalebar->label->set("size", 10);
        $this->_map_obj->scalebar->label->set("antialias", 50);
        $this->_map_obj->scalebar->label->color->setRGB(0,0,0);
        
        //svg format cannot do scalebar in MapServer
        if($this->output != 'svg') {
            $this->_map_obj->scalebar->set("status", MS_EMBED);
            $this->_map_obj->scalebar->set("position", MS_LR);
            $this->_map_obj->drawScalebar();
        }
    }
    
    public function produceOutput() {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);

        switch($this->output) {
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

        $this->image->saveImage("");
    }

}
?>