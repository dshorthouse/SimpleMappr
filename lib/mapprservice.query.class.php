<?php

/**************************************************************************

File: mapprservice.query.class.php

Description: Extends the Base map class for SimpleMappr to shade regions

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

require_once('mapprservice.class.php');

class MAPPRQUERY extends MAPPR {

  private $_data = "";

  /**
  * Override the method in the MAPPR class
  */
  public function get_request() {
    $this->download         = false;
    $this->options          = array();
    $this->border_thickness = 1.25;
    $this->width            = (float)$this->load_param('width', 800);
    $this->height           = (float)$this->load_param('height', $this->width/2);
    $this->image_size       = array($this->width, $this->height);
    $this->output           = $this->load_param('output','pnga');
    $this->projection       = $this->load_param('projection', 'epsg:4326');
    $this->projection_map   = $this->load_param('projection_map', 'epsg:4326');
    $this->origin           = (int)$this->load_param('origin', false);
    $this->bbox_map         = $this->load_param('bbox', '-180,-90,180,90');
    $this->layers           = $this->load_param('layers',array());
    $this->graticules       = $this->load_param('graticules', false);

    $this->bbox_query       = $this->load_param('bbox_query', '0,0,0,0');
    $this->queryLayer       = $this->load_param('qlayer', 'base');
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
            $hasc = explode(".",$shape->values['code_hasc']);
            if(isset($shape->values['sr_adm0_a3']) && isset($hasc[1])) { $items[$shape->values['sr_adm0_a3']][$hasc[1]] = array(); }
          }
          else {
            $this->_data[] = $shape->values['admin'];
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
    
  public function get_output() {
    header("Content-Type: application/json");
    echo json_encode($this->_data);
  }

}
?>