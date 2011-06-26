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
    
    /**
    * Override the method in the MAPPR class
    */
    public function get_request() {
    
        //ping API to return JSON
        $this->ping             = $this->load_param('ping', false);
        if($this->ping) {
          header("Content-Type: application/json");
          $output = array("status" => "ok");
          echo json_encode($output);
          exit;
        }
        
        $this->download         = true;
        $this->options          = array();

        //load the file
        $this->file             = urldecode($this->load_param('file', '')); 
        $this->georss           = urldecode($this->load_param('georss', ''));
        $this->shape            = (is_array($this->load_param('shape', array()))) ? $this->load_param('shape', array()) : array($this->load_param('shape', array()));
        $this->size             = (is_array($this->load_param('size', array()))) ? $this->load_param('size', array()) : array($this->load_param('size', array()));
        $this->color            = (is_array($this->load_param('color', array()))) ? $this->load_param('color', array()) : array($this->load_param('color', array()));
        
        $this->outlinecolor     = $this->load_param('outlinecolor', '255,255,255');
        
        $shaded = $this->load_param('shade', array());
        $this->regions = array(
            'data' => (array_key_exists('places', $shaded)) ? $shaded['places'] : "",
            'title' => (array_key_exists('title', $shaded)) ? $shaded['title'] : "",
            'color' => (array_key_exists('color', $shaded)) ? str_replace(",", " ",$shaded['color']) : "120 120 120"
        );

        $wkt = $this->load_param('wkt', array());
        $this->wkt = array(
          'data' => (array_key_exists('data', $wkt)) ? $wkt['data'] : "",
          'title' => (array_key_exists('title', $wkt)) ? $wkt['title'] : "",
          'color' => (array_key_exists('color', $wkt)) ? $wkt['color'] : ""
        );
        
        $this->output           = $this->load_param('output','pnga');
        $this->projection       = $this->load_param('projection', 'epsg:4326');
        $this->projection_map   = $this->projection;
        
        $this->bbox_map         = $this->load_param('bbox', '-180,-90,180,90');

        //convert layers as comma-separated values to an array
        $_layers                = explode(',', $this->load_param('layers', ''));
        $layers = array();
        foreach($_layers as $_layer) {
            if($_layer) $layers[trim($_layer)] = trim($_layer);
        }
        $this->layers           = $layers;
        $this->graticules       = $this->load_param('graticules', false);

        if($this->load_param('border', false)) $this->options['border'] = true;
        if($this->load_param('legend', false)) $this->options['legend'] = true;
        if($this->load_param('scalebar', false)) $this->options['scalebar'] = true;

        //set the image size from width & height to array(width, height)
        $this->width            = $this->load_param('width', 800);
        $this->height           = $this->load_param('height', 400);
        $this->image_size       = array($this->width, $this->height);

        return $this;

    }
    
    /**
    * Override the method in the MAPPR class
    */ 
    public function add_coordinates() {  
	
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
                $mlayer = ms_newLayerObj($this->map_obj);
                $mlayer->set("name",$legend[$col]);
                $mlayer->set("status",MS_ON);
                $mlayer->set("type",MS_LAYER_POINT);
                $mlayer->set("tolerance",5);
                $mlayer->set("toleranceunits",6);
                $mlayer->setProjection($this->default_projection);

                $class = ms_newClassObj($mlayer);
                $class->set("name",$legend[$col]);

                $style = ms_newStyleObj($class);
                $style->set("symbolname",(array_key_exists($col, $this->shape) && in_array($this->shape[$col], parent::$accepted_shapes)) ? $this->shape[$col] : 'circle');
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
                        if($this->check_coord($_coord)) {
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
    * Override the method in the MAPPR class
    */
    public function add_regions() {
        if($this->regions['data']) {            
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name","stateprovinces_polygon");
            $layer->set("data",$this->shapes['stateprovinces_polygon']['shape']);
            $layer->set("type",$this->shapes['stateprovinces_polygon']['type']);
            $layer->set("template", "template.html");
            $layer->setProjection('init=' . $this->default_projection);
            
            //grab the data for regions & split
            $whole = trim($this->regions['data']);
            $rows = explode("\n",$this->remove_empty_lines($whole));
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
     * Override the method in the MAPPR class
     */
     public function add_freehand() {
       if($this->wkt['data']) {
           $layer = ms_newLayerObj($this->map_obj);
           $layer->set("name", $this->wkt['title']);

           $feature = ms_shapeObjFromWkt($this->wkt['data']);
           $layer->addFeature($feature);

           $type = (strstr($this->wkt['data'], "LINE")) ? MS_LAYER_LINE : MS_LAYER_POLYGON;

           $layer->set("type",$type);
           $layer->set("template", "template.html");
           $layer->setProjection('init=' . $this->default_projection);

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
    * Override the method in the MAPPR class
    */
    public function add_graticules() {
        $layer = ms_newLayerObj($this->map_obj);
        $layer->set("name", 'grid');
        $layer->set("data", $this->shapes['grid']['shape']);
        $layer->set("type", $this->shapes['grid']['type']);
        $layer->set("status",MS_ON);
        $layer->setProjection('init=' . $this->default_projection);
        
        $class = ms_newClassObj($layer);
        $class->label->set("font", "arial");
        $class->label->set("type", MS_TRUETYPE);
        $class->label->set("size", 10);
        $class->label->set("position", MS_UC);
        $class->label->color->setRGB(30, 30, 30);
        $style = ms_newStyleObj($class);
        $style->color->setRGB(200,200,200);

        ms_newGridObj($layer);
        $minx = $this->map_obj->extent->minx;
        $maxx = $this->map_obj->extent->maxx;

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
    * Override the method in the MAPPR class
    */
    public function add_scalebar() {
        $this->map_obj->scalebar->set("style", 0);
        $this->map_obj->scalebar->set("intervals", 3);
        $this->map_obj->scalebar->set("height", 8);
        $this->map_obj->scalebar->set("width", 200);
        $this->map_obj->scalebar->color->setRGB(30,30,30);
        $this->map_obj->scalebar->backgroundcolor->setRGB(255,255,255);
        $this->map_obj->scalebar->outlinecolor->setRGB(0,0,0);
        $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->map_obj->scalebar->set("transparent", 1); // 1 true, 0 false
        $this->map_obj->scalebar->label->set("font", "arial");
        $this->map_obj->scalebar->label->set("type", MS_TRUETYPE);
        $this->map_obj->scalebar->label->set("size", 10);
        $this->map_obj->scalebar->label->set("antialias", 50);
        $this->map_obj->scalebar->label->color->setRGB(0,0,0);
        
        //svg format cannot do scalebar in MapServer
        if($this->output != 'svg') {
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
    }
    
    public function get_output() {
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