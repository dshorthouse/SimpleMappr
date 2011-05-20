<?php

/**************************************************************************

File: kml.class.php

Description: Produce a kml file from SimpleMappr. 

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

class Kml {
	
	private $_kml = '';
	private $_MetaData = array();
	private $_PlaceMark = array();

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
	public function generateKml($url = '') {

		$this->setMetaData("name", "SimpleMappr Data");
		$this->getRequest();
		$this->addCoordinates();
		
		$this->_kml = new XMLWriter();

		header("Pragma: public");
		header("Expires: 0");       
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/vnd.google-earth.kml+xml kml; charset=utf8");
		header("Content-disposition: attachment; filename=simplemappr.kml");
		$this->_kml->openURI('php://output');
		
		$this->_kml->startDocument('1.0', 'UTF-8');
		$this->_kml->setIndent(4);
		$this->_kml->startElement('kml');
			$this->_kml->writeAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
			$this->_kml->writeAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');
			
			$this->_kml->startElement('Document');
			$this->_kml->writeElement('name', $this->getMetaData('name'));
			
			//Style elements
			for($i=0; $i<=count($this->getAllPlaceMarks())-1; $i++) {
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
			
			foreach($this->getAllPlaceMarks() as $key => $placemarks) {
				foreach($placemarks as $id => $placemark) {
					$this->_kml->startElement('Placemark');
					$this->_kml->writeAttribute('id', 'simplemapprpin'.$key.$id);
					$this->_kml->writeElement('name', $this->getPlaceMark($key, $id, 'name'));
					$this->_kml->writeElement('styleUrl', '#pushpin'.$key);
					$this->_kml->startElement('Point');
					$this->_kml->writeElement('coordinates', $this->getPlaceMark($key, $id, 'coordinate') . ',0');
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
	public function setMetaData($name, $value) {
		$this->_MetaData[$name] = $value;
	}

	/**
	* Get value of a metadata element
	* @param string $name
	* @return string value
	*/
	public function getMetaData($name) {
		return $this->_MetaData[$name];
	}
	
	/**
	* Set a placemark in kml
	* @param int $key 
	* @param int $mark
	* @param string $name
	* @param string $value
	*/
	public function setPlaceMark($key=0, $mark=0, $name, $value) {
		$this->_PlaceMark[$key][$mark][$name] = $value;
	}
	
	/**
	* Get a placemark
	* @param int $key
	* @param int $mark
	* @param string $name
	* @return string $value
	*/
	private function getPlaceMark($key=0, $mark=0, $name) {
		return $this->_PlaceMark[$key][$mark][$name];
	}
	
	/**
	* Helper function to get all placemarks
	*/
	private function getAllPlaceMarks() {
		return $this->_PlaceMark;
	}
	
	/**
	* Helper function to get the request parameter coords
	*/
	private function getRequest() {
        $this->coords = $this->loadParam('coords', array());
    }

	/**
	* Get a request parameter
	* @param string $name
	* @param string $default parameter optional
	* @return string the parameter value or empty string if null
	*/
	private function loadParam($name, $default = ''){
    	if(!isset($_REQUEST[$name]) || !$_REQUEST[$name]) return $default;
    	$value = $_REQUEST[$name];
    	if(get_magic_quotes_gpc() != 1) $value = addslashes($value);
    	return $value;
	}
	
	/**
	* Helper function to add coordinates to placemarks
	*/
	public function addCoordinates() {

	  for($j=0; $j<=count($this->coords)-1; $j++) {

		$title = $this->coords[$j]['title'] ? $this->coords[$j]['title'] : '';

	    if(trim($this->coords[$j]['data'])) {

		    $whole = trim($this->coords[$j]['data']);  //grab the whole textarea
		    $row = explode("\n",$this->removeEmptyLines($whole));  //split the lines that have data

			$point_key = 0;
		    foreach ($row as $loc) {
			  $coord_array = preg_split("/[\s,;]+/",$loc); //split the coords by a space, comma, semicolon, or \t
			  $coord = new stdClass();
			  $coord->x = trim($coord_array[1]);
			  $coord->y = trim($coord_array[0]);
		      if($this->checkCoord($coord) && $title != "") {  //only add point when data are good & a title
				  $this->setPlaceMark($j, $point_key, "name", $title);
				  $this->setPlaceMark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
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
	private function removeEmptyLines($string) {
	  return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
	}
	
	/**
	 * Check a DD coordinate object and return true if it fits on globe, false if not
	 * @param obj $coord (x,y) coordinates
	 * @return true,false
	 */
	private function checkCoord($coord) {
		$output = false;
		if((float)$coord->x && (float)$coord->y && $coord->y <= 90 && $coord->y >= -90 && $coord->x <= 180 && $coord->x >= -180) $output = true;
		return $output;
	}

}
?>