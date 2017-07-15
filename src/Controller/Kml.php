<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
namespace SimpleMappr\Controller;

use XMLWriter;

use SimpleMappr\Header;
use SimpleMappr\Utility;

/**
 * KML handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Kml implements RestMethods
{
    /**
     * Array of default pushpins
     *
     * @var array $pushpins
     */
    public static $pushpins = [
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
    ];

    /**
     * XMLWriter object
     *
     * @var object $_kml
     */
    private $_kml;

    /**
     * Metadata array
     *
     * @var array $_metadata
     */
    private $_metadata = [];

    /**
     * Placemarkers for KML
     *
     * @var array $_placemark
     */
    private $_placemark = [];

    /**
     * Coordinates to build a placemark
     *
     * @var array $coords
     */
    public $coords;

    /**
     * User-defined filename
     *
     * @var string $file_name
     */
    public $file_name;

    /**
     * Implemented index method
     *
     * @param object $params null
     *
     * @return array
     */
    public function index($params = null)
    {
    }

    /**
     * Implemented create method
     *
     * @param array $content The content to create
     *
     * @return void
     */
    public function create($content)
    {
        $this->file_name = time();
        if (array_key_exists("file_name", $content)) {
            $this->file_name = Utility::cleanFilename($content["file_name"]);
        }
        $this->coords = $content["coords"];
        return $this->_createContent();
    }

    /**
     * Implemented show method.
     *
     * @param int $id identifier for the place.
     *
     * @return void
     */
    public function show($id)
    {
    }

    /**
     * Implemented update method
     *
     * @param string $content The array of content
     * @param string $where   The where clause
     *
     * @return void
     */
    public function update($content, $where)
    {
    }

    /**
     * Implemented destroy method.
     *
     * @param int $id identifier for the place.
     *
     * @return void
     */
    public function destroy($id)
    {
    }

    /**
     * Generate the kml file.
     *
     * @return void
     */
    private function _createContent()
    {
        $this->_setMetadata("name", "SimpleMappr: " . $this->file_name);

        $this->_addCoordinates();

        $this->_kml = new XMLWriter();
        $this->_kml->openMemory();

        $this->_kml->startDocument('1.0', 'UTF-8');
        $this->_kml->setIndent(4);
        $this->_kml->startElement('kml');
        $this->_kml->writeAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
        $this->_kml->writeAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');

        $this->_kml->startElement('Document');
        $this->_kml->writeElement('name', $this->getMetadata('name'));

        //Style elements
        $count = count($this->getAllPlacemarks())-1;
        for ($i=0; $i<=$count; $i++) {
            $this->_kml->startElement('Style');
            $this->_kml->writeAttribute('id', 'pushpin'.$i);
            $this->_kml->startElement('IconStyle');
            $this->_kml->writeAttribute('id', 'simplemapprstyle'.$i);
            $this->_kml->writeElement('scale', '1.0');
            $this->_kml->startElement('Icon');
            $this->_kml->writeElement('href', self::$pushpins[$i]);
            $this->_kml->endElement(); //end Icon
            $this->_kml->endElement(); //end IconStyle
            $this->_kml->endElement(); //end Style
        }

        foreach ($this->getAllPlacemarks() as $key => $placemarks) {
            $this->_kml->startElement('Folder');
            $this->_kml->writeAttribute('id', 'simplemapprfolder'.$key);
            $this->_kml->writeElement('name', $this->getPlacemark($key, 0, 'name'));
            foreach ($placemarks as $id => $placemark) {
                $name = $this->getPlacemark($key, $id, 'name');
                $description = $this->getPlacemark($key, $id, 'coordinate');
                $coordinates = $this->getPlacemark($key, $id, 'coordinate') . ',0';
                $this->_kml->startElement('Placemark');
                $this->_kml->writeAttribute('id', 'simplemapprpin'.$key.$id);
                $this->_kml->writeElement('name', $name);
                $this->_kml->writeElement('description', $description);
                $this->_kml->writeElement('styleUrl', '#pushpin'.$key);
                $this->_kml->startElement('Point');
                $this->_kml->writeElement('coordinates', $coordinates);
                $this->_kml->endElement(); //end Point
                $this->_kml->endElement(); //end Placemark
            }
            $this->_kml->endElement();
        }

        $this->_kml->endElement(); //end Document
        $this->_kml->endElement(); //end kml
        $this->_kml->endDocument();

        return $this->_kml->outputMemory();
    }

    /**
     * Get value of a metadata element.
     *
     * @param string $name The metadata key.
     *
     * @return string The metadata value.
     */
    public function getMetadata($name)
    {
        return $this->_metadata[$name];
    }

    /**
     * Get a placemark.
     *
     * @param int    $key  An index for a group of placemarks.
     * @param int    $mark An index for the placemark.
     * @param string $name The name of the placemark.
     *
     * @return string The placemark.
     */
    public function getPlacemark($key, $mark, $name)
    {
        return $this->_placemark[$key][$mark][$name];
    }

    /**
     * Helper function to get all placemarks
     *
     * @return Array
     */
    public function getAllPlacemarks()
    {
        return $this->_placemark;
    }

    /**
     * Set basic metadata for kml.
     *
     * @param string $name  The metadata key.
     * @param string $value The metadata value.
     *
     * @return void
     */
    private function _setMetadata($name, $value)
    {
        $this->_metadata[$name] = $value;
    }

    /**
     * Set a placemark in kml.
     *
     * @param int    $key   An index for a group placemarks.
     * @param int    $mark  An index for the placemark.
     * @param string $name  The name of the placemark.
     * @param string $value The coordinates for the placemark.
     *
     * @return void
     */
    private function _setPlacemark($key, $mark, $name, $value)
    {
        $this->_placemark[$key][$mark][$name] = $value;
    }

    /**
     * Helper function to add coordinates to placemarks
     *
     * @return void
     */
    private function _addCoordinates()
    {
        $count = count($this->coords)-1;
        for ($j=0; $j<=$count; $j++) {
            $title = "";
            if ($this->coords[$j]['title']) {
                $title = $this->coords[$j]['title'];
            }

            if (trim($this->coords[$j]['data'])) {
                $whole = trim($this->coords[$j]['data']);
                $row = explode("\n", Utility::removeEmptyLines($whole));

                $point_key = 0;
                foreach ($row as $loc) {
                    $coord_array = Utility::makeCoordinates($loc);
                    $coord = new \stdClass();
                    $coord->x = null;
                    $coord->y = null;
                    if ($coord_array[1]) {
                        $coord->x = Utility::cleanCoord($coord_array[1]);
                    }
                    if ($coord_array[0]) {
                        $coord->y = Utility::cleanCoord($coord_array[0]);
                    }
                    if (Utility::onEarth($coord) && $title != "") {
                        $this->_setPlacemark($j, $point_key, "name", $title);
                        $this->_setPlacemark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
                        $point_key++;
                    }
                }
            }
        }
    }
}
