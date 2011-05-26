<?php

/**************************************************************************

File: mapprservice.query.class.php

Description: Extends the Base map class for SimpleMappr to shade regions 

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

require_once('../includes/mapprservice.class.php');

class MAPPRQUERY extends MAPPR {
    
    private $_data = "";
    
    function __construct() {
    }
    
    function __destruct() {
    }
    
    /**
    * Override the getRequest() method in the MAPPR class
    */
    public function getRequest() {
        $this->download         = false;
        $this->options          = array();
        $this->output           = $this->loadParam('output','pnga');
        $this->projection       = $this->loadParam('projection', 'epsg:4326');
        $this->projection_map   = $this->loadParam('projection_map', 'epsg:4326');
        $this->bbox_map         = $this->loadParam('bbox', '-180,-90,180,90');
        $this->layers           = $this->loadParam('layers',array());
        $this->graticules       = $this->loadParam('graticules', false);

        $this->bbox_query       = $this->loadParam('bbox_query', '0,0,0,0');
        $this->queryLayer       = $this->loadParam('qlayer', 'base');

        $this->freehandCoords   = $this->loadParam('freehand', array());
        
        //blank out some variables
        $this->download_legend      = false;
        $this->zoom_out             = false;
        $this->pan                  = false;
        $this->crop                 = false;
        $this->bbox_rubberband      = array();
        $this->rotation             = 0;
        $this->coords               = array();
        $this->wkt                  = array();
    }
    
    /**
    * Override the addCoordinates() method in the MAPPR class (i.e. blank it out)
    */
    public function addCoordinates() {
    }
    
    /**
    * Override the addRegions() method in the MAPPR class (i.e. blank it out)
    */
    public function addRegions() {
    }
    
    /**
    * Query a layer
    */
    public function queryLayer() {
        
        $bbox_query = explode(',',$this->bbox_query);

        if(!array_key_exists($this->queryLayer, $this->_shapes)) return;

        //lower-left coordinate
        $ll_point = new stdClass();
        $ll_point->x = $bbox_query[0];
        $ll_point->y = $bbox_query[3];
        $ll_coord = $this->pix2Geo($ll_point);
        
        //upper-right coordinate
        $ur_point = new stdClass();
        $ur_point->x = $bbox_query[2];
        $ur_point->y = $bbox_query[1];
        $ur_coord = $this->pix2Geo($ur_point);
        
        $layer = ms_newLayerObj($this->_map_obj);
        $layer->set("name","stateprovinces_polygon_query");
        $layer->set("data",$this->_shapes[$this->queryLayer]['shape']);
        $layer->set("type",$this->_shapes[$this->queryLayer]['type']);
        $layer->set("template", "template.html");
        $layer->setProjection('init=' . $this->_default_projection);

        $rect = ms_newRectObj();
        $rect->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);

        $return = @$layer->queryByRect($rect);
        
        if($return == MS_SUCCESS) {
            if($layer->getNumResults() > 0) {
                $layer->open();
                $items = array();
                for($i = 0; $i < $layer->getNumResults(); $i++) {
                    $res = $layer->getResult($i);
                    $shape = $layer->getShape($res->tileindex, $res->shapeindex);
                    
                    if($this->queryLayer == 'stateprovinces_polygon') {
                        $hasc = explode(".",$shape->values['HASC_1']);
                        if(isset($shape->values['ISO']) && isset($hasc[1])) $items[$shape->values['ISO']][$hasc[1]] = array();
                    }
                    else {
                        $this->_data[] = $shape->values['ADMIN'];
                    }
                    
                }
                if($this->queryLayer == 'stateprovinces_polygon') {
                    foreach($items as $key => $value) {
                        $this->_data[] = $key . "[" . implode(" ", array_keys($value)) . "]";
                    }
                }
                
                $layer->close();
            }
        }
        
    }

    public function queryFreehand() {
	  if(!$this->freehandCoords) return;
	
	  foreach($this->freehandCoords as $pixelcoord) {
	    $coord = explode(" ", $pixelcoord[0]);
	
	    $point = new stdClass();
        $point->x = $coord[0];
        $point->y = $coord[1];
        $proj_point = $this->pix2Geo($point);

        $this->_data[] = $proj_point->x . " " . $proj_point->y;
	  }
    }
    
    public function produceOutput() {
        header("Content-Type: application/json");
        echo json_encode($this->_data);
    }
}

?>