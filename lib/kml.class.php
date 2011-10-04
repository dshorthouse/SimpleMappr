<?php

/**************************************************************************

File: kml.class.php

Description: Produce a kml file from SimpleMappr. 

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

class Kml {

  private $_kml = '';
  private $_metadata = array();
  private $_placemark = array();

  public function __construct() {
    $this->pushpins = array(
      'http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/grn-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/red-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/blue-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/wht-pushpin.png',
      'http://maps.google.com/mapfiles/kml/paddle/red-stars.png',
      'http://maps.google.com/mapfiles/kml/paddle/wht-blank.png',
    );
  }

  /**
  *  Generate the kml file
  * @param string $url
  * @return xml
  */
  public function generate_kml($url = '') {
    $this->get_request();

    $this->set_metadata("name", "SimpleMappr: " . $this->get_filename());

    $this->add_coordinates();
    
    $this->_kml = new XMLWriter();

    header("Pragma: public");
    header("Expires: 0");       
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: application/vnd.google-earth.kml+xml kml; charset=utf8");
    header("Content-disposition: attachment; filename=" . $this->get_filename() . ".kml");
    $this->_kml->openURI('php://output');
    
    $this->_kml->startDocument('1.0', 'UTF-8');
    $this->_kml->setIndent(4);
    $this->_kml->startElement('kml');
    $this->_kml->writeAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
    $this->_kml->writeAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');
    
    $this->_kml->startElement('Document');
    $this->_kml->writeElement('name', $this->get_metadata('name'));
    
    //Style elements
    for($i=0; $i<=count($this->get_all_placemarks())-1; $i++) {
      $this->_kml->startElement('Style');
      $this->_kml->writeAttribute('id', 'pushpin'.$i);
      $this->_kml->startElement('IconStyle');
      $this->_kml->writeAttribute('id', 'simplemapprstyle'.$i);
      $this->_kml->startElement('Icon');
      $this->_kml->writeElement('href', $this->pushpins[$i]);
      $this->_kml->writeElement('scale', '1.0');
      $this->_kml->endElement(); //end Icon
      $this->_kml->endElement(); //end IconStyle
      $this->_kml->endElement(); //end Style
    }
    
    foreach($this->get_all_placemarks() as $key => $placemarks) {
      foreach($placemarks as $id => $placemark) {
        $this->_kml->startElement('Placemark');
        $this->_kml->writeAttribute('id', 'simplemapprpin'.$key.$id);
        $this->_kml->writeElement('name', $this->get_placemark($key, $id, 'name'));
        $this->_kml->writeElement('styleUrl', '#pushpin'.$key);
        $this->_kml->startElement('Point');
        $this->_kml->writeElement('coordinates', $this->get_placemark($key, $id, 'coordinate') . ',0');
        $this->_kml->endElement(); //end Point
        $this->_kml->endElement(); //end Placemark
      }
    }

    $this->_kml->endElement(); //end Document
    $this->_kml->endElement(); //end kml
    $this->_kml->endDocument();
    $this->_kml->flush();
  }

  /**
  *  Set basic metadata for kml
  * @param string $name
  * @param string $value
  */
  public function set_metadata($name, $value) {
    $this->_metadata[$name] = $value;
  }

  /**
  * Get value of a metadata element
  * @param string $name
  * @return string value
  */
  public function get_metadata($name) {
    return $this->_metadata[$name];
  }

  /**
  * Set a placemark in kml
  * @param int $key 
  * @param int $mark
  * @param string $name
  * @param string $value
  */
  public function set_placeMark($key=0, $mark=0, $name, $value) {
    $this->_placemark[$key][$mark][$name] = $value;
  }

  /**
  * Get a placemark
  * @param int $key
  * @param int $mark
  * @param string $name
  * @return string $value
  */
  private function get_placemark($key=0, $mark=0, $name) {
    return $this->_placemark[$key][$mark][$name];
  }

  /**
  * Helper function to get all placemarks
  */
  private function get_all_placemarks() {
    return $this->_placemark;
  }

  /**
  * Helper function to get the request parameter coords
  */
  private function get_request() {
    $this->coords         = $this->load_param('coords', array());
    $this->file_name      = $this->load_param('file_name', time());
    $this->download_token = $this->load_param('download_token', md5(time()));
    setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");
  }

  /**
  * Get a request parameter
  * @param string $name
  * @param string $default parameter optional
  * @return string the parameter value or empty string if null
  */
  private function load_param($name, $default = ''){
    if(!isset($_REQUEST[$name]) || !$_REQUEST[$name]) return $default;
    $value = $_REQUEST[$name];
    if(get_magic_quotes_gpc() != 1) $value = $this->add_slashes_extended($value);
    return $value;
  }

  /**
  * Add slashes to either a string or an array
  * @param string/array $arr_r
  * @return string/array
  */
  private function add_slashes_extended(&$arr_r) {
    if(is_array($arr_r)) {
      foreach ($arr_r as &$val) {
        is_array($val) ? $this->add_slashes_extended($val) : $val = addslashes($val);
      }
      unset($val);
    } else {
      $arr_r = addslashes($arr_r);
    }
    return $arr_r;
  }

  /**
  * Helper function to add coordinates to placemarks
  */
  public function add_coordinates() {
    for($j=0; $j<=count($this->coords)-1; $j++) {
      $title = $this->coords[$j]['title'] ? $this->coords[$j]['title'] : '';

      if(trim($this->coords[$j]['data'])) {
        $whole = trim($this->coords[$j]['data']);  //grab the whole textarea
        $row = explode("\n",$this->remove_empty_lines($whole));  //split the lines that have data

        $point_key = 0;
        foreach ($row as $loc) {
          $coord_array = preg_split("/[\s,;]+/",$loc); //split the coords by a space, comma, semicolon, or \t
          $coord = new stdClass();
          $coord->x = array_key_exists(1, $coord_array) ? trim($coord_array[1]) : "nil";
          $coord->y = array_key_exists(0, $coord_array) ? trim($coord_array[0]) : "nil";
          if($this->check_coord($coord) && $title != "") {  //only add point when data are good & a title
            $this->set_placemark($j, $point_key, "name", $title);
            $this->set_placemark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
            $point_key++;
          }
        }
      }
    }
  }

  /**
  * Remove empty lines from a string
  * @param $string
  * @return string cleansed string with empty lines removed
  */
  private function remove_empty_lines($string) {
    return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
  }

  /**
  * Check a DD coordinate object and return true if it fits on globe, false if not
  * @param obj $coord (x,y) coordinates
  * @return true,false
  */
  private function check_coord($coord) {
    $output = false;
    if((float)$coord->x && (float)$coord->y && $coord->y <= 90 && $coord->y >= -90 && $coord->x <= 180 && $coord->x >= -180) { $output = true; }
    return $output;
  }

  private function get_filename() {
    return preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $this->file_name);
  }

}
?>