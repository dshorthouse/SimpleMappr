<?php

/**************************************************************************

File: kml.class.php

Description: Produce a kml file from SimpleMappr. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

class Kml {

  public static $pushpins = array(
      'http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/grn-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/red-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/blue-pushpin.png',
      'http://maps.google.com/mapfiles/kml/pushpin/wht-pushpin.png',
      'http://maps.google.com/mapfiles/kml/paddle/red-stars.png',
      'http://maps.google.com/mapfiles/kml/paddle/wht-blank.png',
      'http://maps.google.com/mapfiles/ms/micons/pink-pushpin.png',
      'http://maps.google.com/mapfiles/ms/micons/purple-pushpin.png',
      'http://maps.google.com/mapfiles/ms/micons/ltblu-pushpin.png'
  );

  private $_kml = '';
  private $_metadata = array();
  private $_placemark = array();

  public function __construct() {
    session_start();
  }

  /**
  * Get the request parameter coords
  * @param $file_name string
  * @param $coords array
  */
  public function get_request($file_name = '', $coords = array()) {
    $this->coords         = ($coords) ? $coords : $this->load_param('coords', array());
    $this->file_name      = ($file_name) ? $file_name : $this->load_param('file_name', time());
    $this->download_token = $this->load_param('download_token', md5(time()));
    setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");
    return $this;
  }

  /**
  *  Generate the kml file
  * @return xml
  */
  public function generate_kml() {

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
      $this->_kml->writeElement('href', self::$pushpins[$i]);
      $this->_kml->writeElement('scale', '1.0');
      $this->_kml->endElement(); //end Icon
      $this->_kml->endElement(); //end IconStyle
      $this->_kml->endElement(); //end Style
    }
    
    foreach($this->get_all_placemarks() as $key => $placemarks) {
      $this->_kml->startElement('Folder');
      $this->_kml->writeAttribute('id', 'simplemapprfolder'.$key);
      $this->_kml->writeElement('name', $this->get_placemark($key, 0, 'name'));
      foreach($placemarks as $id => $placemark) {
        $this->_kml->startElement('Placemark');
        $this->_kml->writeAttribute('id', 'simplemapprpin'.$key.$id);
        $this->_kml->writeElement('name', $this->get_placemark($key, $id, 'name'));
        $this->_kml->writeElement('description', $this->get_placemark($key, $id, 'coordinate'));
        $this->_kml->writeElement('styleUrl', '#pushpin'.$key);
        $this->_kml->startElement('Point');
        $this->_kml->writeElement('coordinates', $this->get_placemark($key, $id, 'coordinate') . ',0');
        $this->_kml->endElement(); //end Point
        $this->_kml->endElement(); //end Placemark
      }
      $this->_kml->endElement();
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
          $loc = preg_replace('/[\p{Z}\s]/u', ' ', $loc);
          $loc = trim(preg_replace('/[^\d\s,;.\-NSEW°dm\'"]/i', '', $loc));
          if(preg_match('/[NSEW]/', $loc) != 0) {
            $coord = preg_split("/[,;]/", $loc);
            $coord = (preg_match('/[EW]/i', $coord[1]) != 0) ? $coord : array_reverse($coord);
            $coord_array = array($this->dms_to_deg(trim($coord[0])),$this->dms_to_deg(trim($coord[1])));
          } else {
            $coord_array = preg_split("/[\s,;]+/",$loc);
          }
          $coord = new stdClass();
          $coord->x = array_key_exists(1, $coord_array) ? $this->clean_coord($coord_array[1]) : "nil";
          $coord->y = array_key_exists(0, $coord_array) ? $this->clean_coord($coord_array[0]) : "nil";
          if($this->check_coord($coord) && $title != "") {  //only add point when data are good & a title
            $this->set_placemark($j, $point_key, "name", $title);
            $this->set_placemark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
            $point_key++;
          }
        }
      }
    }
  }

  private function clean_coord($coord) {
    return preg_replace('/[^\d.-]/i', '', $coord);
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

  /**
   * Convert a coordinate in dms to deg
   * @param string $dms coordinate
   * @return float
   */
  private function dms_to_deg($dms) {
    $dms = stripslashes($dms);
    $neg = (preg_match('/[SW]/', $dms) == 0) ? 1 : -1;
    $dms = preg_replace('/(^\s?-)|(\s?[NSEW]\s?)/i','', $dms);
    $parts = preg_split('/(\d{1,3})[,°d ]?(\d{0,2})(?:[,°d ])[.,\'m ]?(\d{0,2})(?:[.,\'m ])[,"s ]?(\d{0,})(?:[,"s ])?/i', $dms, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    if (!$parts) { return; }
    // parts: 0 = degree, 1 = minutes, 2 = seconds
    $d = isset($parts[0]) ? (float)$parts[0] : 0;
    $m = isset($parts[1]) ? (float)$parts[1] : 0;
    if(strpos($dms, ".") > 1 && isset($parts[2])) {
      $m = (float)($parts[1] . '.' . $parts[2]);
      unset($parts[2]);
    }
    $s = isset($parts[2]) ? (float)$parts[2] : 0;
    $dec = ($d + ($m/60) + ($s/3600))*$neg; 
    return $dec;
  }

  private function get_filename() {
    return preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $this->file_name);
  }

}
?>