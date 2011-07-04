<?php

/**************************************************************************

File: mapprservice.query.class.php

Description: Extends the Base map class for SimpleMappr to shade regions 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

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
    
    /**
    * Override the method in the MAPPR class
    */
    public function get_request() {
        $this->download         = false;
        $this->options          = array();
        $this->output           = $this->load_param('output','pnga');
        $this->projection       = $this->load_param('projection', 'epsg:4326');
        $this->projection_map   = $this->load_param('projection_map', 'epsg:4326');
        $this->bbox_map         = $this->load_param('bbox', '-180,-90,180,90');
        $this->layers           = $this->load_param('layers',array());
        $this->graticules       = $this->load_param('graticules', false);

        $this->bbox_query       = $this->load_param('bbox_query', '0,0,0,0');
        $this->queryLayer       = $this->load_param('qlayer', 'base');

        $this->freehandCoords   = $this->load_param('freehand', array());

        return $this;
    }

    
    /**
    * Query a layer
    */
    public function query_layer() {
        
        $bbox_query = explode(',',$this->bbox_query);

        if(!array_key_exists($this->queryLayer, $this->shapes)) return;

        //lower-left coordinate
        $ll_point = new stdClass();
        $ll_point->x = $bbox_query[0];
        $ll_point->y = $bbox_query[3];
        $ll_coord = $this->pix2geo($ll_point);
        
        //upper-right coordinate
        $ur_point = new stdClass();
        $ur_point->x = $bbox_query[2];
        $ur_point->y = $bbox_query[1];
        $ur_coord = $this->pix2geo($ur_point);
        
        $layer = ms_newLayerObj($this->map_obj);
        $layer->set("name","stateprovinces_polygon_query");
        $layer->set("data",$this->shapes[$this->queryLayer]['shape']);
        $layer->set("type",$this->shapes[$this->queryLayer]['type']);
        $layer->set("template", "template.html");
        $layer->setProjection(parent::$accepted_projections[$this->default_projection]['proj']);

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

        return $this;
        
    }

    public function query_freehand() {
      if(!$this->freehandCoords) return $this;
    
      foreach($this->freehandCoords as $pixelcoord) {
        $coord = explode(" ", $pixelcoord[0]);
    
        $point = new stdClass();
        $point->x = $coord[0];
        $point->y = $coord[1];
        $proj_point = $this->pix2geo($point);

        $this->_data[] = $proj_point->x . " " . $proj_point->y;
      }
    
      return $this;
    }
    
    public function get_output() {
        header("Content-Type: application/json");
        echo json_encode($this->_data);
    }
}

?>