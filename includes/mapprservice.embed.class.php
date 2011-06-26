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
      $this->width            = $this->load_param('width', 800);
      $this->height           = $this->load_param('height', 400);
      $this->image_size       = array($this->width, $this->height);
      $this->output           = 'pnga';
      $this->bbox_map         = '-180,-90,180,90';

      $this->download         = true;
      $this->options          = array();

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

      return $this;
    }

    private function set_not_found() {
      header("HTTP/1.0 404 Not Found");
      header("Content-Type: image/png");
      $im = imagecreatefrompng(MAPPR_DIRECTORY . "/images/not-found.png");
      imagepng($im);
      imagedestroy($im);
      exit();
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