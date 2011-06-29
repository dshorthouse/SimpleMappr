<?php

/**************************************************************************

File: mapprservice.embed.class.php

Description: Extends the base map class for SimpleMappr to support WFS. 

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

require_once ('conf/conf.db.php');
require_once ('db.class.php');
require_once ('mapprservice.class.php');

class MAPPREMBED extends MAPPR {

  /**
    * Override the method in the MAPPR class
    */
    public function get_request() {
      $this->map              = (int)$this->load_param('map', 0);

      $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      $sql = "SELECT map FROM maps WHERE mid=" . $db->escape($this->map);
      $record = $db->query_first($sql);

      if(!$record) {
        $this->set_not_found();
      }

      $result = unserialize($record['map']);
      
      foreach($result as $key => $data) {
        $this->{$key} = $data;
      }

      (isset($this->layers['grid'])) ? $this->graticules = true : $this->graticules = false;

      $this->download         = true;
      $this->width            = $this->load_param('width', 800);
      $this->height           = $this->load_param('height', 400);
      $this->image_size       = array($this->width, $this->height);
      $this->output           = 'pnga';

      $this->set_map_extent();

      return $this;
    }

    private function set_map_extent() {
      $ext = explode(',',$this->bbox_map);
      $origProjObj = ms_newProjectionObj(parent::$accepted_projections_proj[$this->projection_map]);
      $newProjObj = ms_newProjectionObj(parent::$accepted_projections_proj[$this->default_projection]);

      $poPoint1 = ms_newPointObj();
      $poPoint1->setXY($ext[0], $ext[1]);

      $poPoint2 = ms_newPointObj();
      $poPoint2->setXY($ext[2], $ext[3]);
            
      @$poPoint1->project($origProjObj,$newProjObj);
      @$poPoint2->project($origProjObj,$newProjObj);

      $ext[0] = $poPoint1->x;
      $ext[1] = $poPoint1->y;
      $ext[2] = $poPoint2->x;
      $ext[3] = $poPoint2->y;

      $this->bbox_map = implode(",", $ext);
    }

    private function set_not_found() {
      header("HTTP/1.0 404 Not Found");
      header("Content-Type: image/png");
      $im = imagecreatefrompng(MAPPR_DIRECTORY . "/images/not-found.png");
      imagepng($im);
      imagedestroy($im);
      exit();
    }

    /**
    * Override the method in the MAPPR class
    */
    public function add_graticules() {
      if($this->graticules) {
        $layer = ms_newLayerObj($this->map_obj);
        $layer->set("name", 'grid');
        $layer->set("data", $this->shapes['grid']['shape']);
        $layer->set("type", $this->shapes['grid']['type']);
        $layer->set("status",MS_ON);
        $layer->setProjection(parent::$accepted_projections_proj[$this->default_projection]);
        
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
        header("Content-Type: image/png");

        $this->image->saveImage("");
    }
}