<?php

/********************************************************************

kml.class.php released under MIT License
Produce a KML file from SimpleMappr data

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2010 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

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

  private $kml = '';
  private $metadata = array();
  private $placemark = array();

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
  public function create_output() {

    $clean_filename = Mappr::clean_filename($this->file_name);

    $this->set_metadata("name", "SimpleMappr: " . $clean_filename);

    $this->add_coordinates();

    $this->kml = new XMLWriter();

    Utilities::set_header("kml");
    header("Content-disposition: attachment; filename=" . $clean_filename . ".kml");
    $this->kml->openURI('php://output');
    
    $this->kml->startDocument('1.0', 'UTF-8');
    $this->kml->setIndent(4);
    $this->kml->startElement('kml');
    $this->kml->writeAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
    $this->kml->writeAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');
    
    $this->kml->startElement('Document');
    $this->kml->writeElement('name', $this->get_metadata('name'));
    
    //Style elements
    for($i=0; $i<=count($this->get_all_placemarks())-1; $i++) {
      $this->kml->startElement('Style');
      $this->kml->writeAttribute('id', 'pushpin'.$i);
      $this->kml->startElement('IconStyle');
      $this->kml->writeAttribute('id', 'simplemapprstyle'.$i);
      $this->kml->startElement('Icon');
      $this->kml->writeElement('href', self::$pushpins[$i]);
      $this->kml->writeElement('scale', '1.0');
      $this->kml->endElement(); //end Icon
      $this->kml->endElement(); //end IconStyle
      $this->kml->endElement(); //end Style
    }
    
    foreach($this->get_all_placemarks() as $key => $placemarks) {
      $this->kml->startElement('Folder');
      $this->kml->writeAttribute('id', 'simplemapprfolder'.$key);
      $this->kml->writeElement('name', $this->get_placemark($key, 0, 'name'));
      foreach($placemarks as $id => $placemark) {
        $this->kml->startElement('Placemark');
        $this->kml->writeAttribute('id', 'simplemapprpin'.$key.$id);
        $this->kml->writeElement('name', $this->get_placemark($key, $id, 'name'));
        $this->kml->writeElement('description', $this->get_placemark($key, $id, 'coordinate'));
        $this->kml->writeElement('styleUrl', '#pushpin'.$key);
        $this->kml->startElement('Point');
        $this->kml->writeElement('coordinates', $this->get_placemark($key, $id, 'coordinate') . ',0');
        $this->kml->endElement(); //end Point
        $this->kml->endElement(); //end Placemark
      }
      $this->kml->endElement();
    }

    $this->kml->endElement(); //end Document
    $this->kml->endElement(); //end kml
    $this->kml->endDocument();
    $this->kml->flush();
  }

  /**
  *  Set basic metadata for kml
  * @param string $name
  * @param string $value
  */
  public function set_metadata($name, $value) {
    $this->metadata[$name] = $value;
  }

  /**
  * Get value of a metadata element
  * @param string $name
  * @return string value
  */
  public function get_metadata($name) {
    return $this->metadata[$name];
  }

  /**
  * Set a placemark in kml
  * @param int $key 
  * @param int $mark
  * @param string $name
  * @param string $value
  */
  public function set_placemark($key=0, $mark=0, $name, $value) {
    $this->placemark[$key][$mark][$name] = $value;
  }

  /**
  * Get a placemark
  * @param int $key
  * @param int $mark
  * @param string $name
  * @return string $value
  */
  private function get_placemark($key=0, $mark=0, $name) {
    return $this->placemark[$key][$mark][$name];
  }

  /**
  * Helper function to get all placemarks
  */
  private function get_all_placemarks() {
    return $this->placemark;
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
    if(get_magic_quotes_gpc() != 1) $value = Mappr::add_slashes_extended($value);
    return $value;
  }

  /**
  * Helper function to add coordinates to placemarks
  */
  public function add_coordinates() {
    for($j=0; $j<=count($this->coords)-1; $j++) {
      $title = $this->coords[$j]['title'] ? $this->coords[$j]['title'] : '';

      if(trim($this->coords[$j]['data'])) {
        $whole = trim($this->coords[$j]['data']);  //grab the whole textarea
        $row = explode("\n",Mappr::remove_empty_lines($whole));  //split the lines that have data

        $point_key = 0;
        foreach ($row as $loc) {
          $coord_array = Mappr::make_coordinates($loc);
          $coord = new stdClass();
          $coord->x = ($coord_array[1]) ? Mappr::clean_coord($coord_array[1]) : null;
          $coord->y = ($coord_array[0]) ? Mappr::clean_coord($coord_array[0]) : null;
          if(Mappr::check_coord($coord) && $title != "") {  //only add point when data are good & a title
            $this->set_placemark($j, $point_key, "name", $title);
            $this->set_placemark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
            $point_key++;
          }
        }
      }
    }
  }

}
?>