<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
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
 *
 */
namespace SimpleMappr;

use XMLWriter;

/**
 * KML handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Kml
{
    /**
     * @var array $pushpins Array of default pushpins
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
     * @var object $_kml XMLWriter object
     */
    private $_kml;

    /**
     * @var array $_metadata Metadata array
     */
    private $_metadata = [];

    /**
     * @var array $_placemark Placemarkers for KML
     */
    private $_placemark = [];

    /**
     * @var array $coords Coordinates to build a placemark
     */
    public $coords;

    /**
     * @var string $file_name User-defined filename
     */
    public $file_name;

    /**
     * @var string $download_token Download token used in session cookie
     */
    public $download_token;

    /**
     * The constructor
     */
    public function __construct()
    {
        session_start();
    }

    /**
     * The destructor
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * Get the request parameter coords.
     *
     * @param string $file_name A name for a file to be downloaded.
     * @param array  $coords    An array of geographic coordinates.
     *
     * @return object $this
     */
    public function getRequest($file_name = "", $coords = [])
    {
        $this->coords         = ($coords) ? $coords : Utility::loadParam('coords', []);
        $this->file_name      = ($file_name) ? $file_name : Utility::loadParam('file_name', time());
        $this->download_token = Utility::loadParam('download_token', md5(time()));
        setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");
        return $this;
    }

    /**
     * Generate the kml file.
     *
     * @return void
     */
    public function createOutput()
    {
        $clean_filename = Utility::cleanFilename($this->file_name);

        $this->setMetadata("name", "SimpleMappr: " . $clean_filename);

        $this->addCoordinates();

        $this->_kml = new XMLWriter();

        Header::setHeader("kml", $clean_filename . ".kml");
        $this->_kml->openURI('php://output');

        $this->_kml->startDocument('1.0', 'UTF-8');
        $this->_kml->setIndent(4);
        $this->_kml->startElement('kml');
        $this->_kml->writeAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
        $this->_kml->writeAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');

        $this->_kml->startElement('Document');
        $this->_kml->writeElement('name', $this->getMetadata('name'));

        //Style elements
        $count = count($this->_getAllPlacemarks())-1;
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

        foreach ($this->_getAllPlacemarks() as $key => $placemarks) {
            $this->_kml->startElement('Folder');
            $this->_kml->writeAttribute('id', 'simplemapprfolder'.$key);
            $this->_kml->writeElement('name', $this->_getPlacemark($key, 0, 'name'));
            foreach ($placemarks as $id => $placemark) {
                $this->_kml->startElement('Placemark');
                $this->_kml->writeAttribute('id', 'simplemapprpin'.$key.$id);
                $this->_kml->writeElement('name', $this->_getPlacemark($key, $id, 'name'));
                $this->_kml->writeElement('description', $this->_getPlacemark($key, $id, 'coordinate'));
                $this->_kml->writeElement('styleUrl', '#pushpin'.$key);
                $this->_kml->startElement('Point');
                $this->_kml->writeElement('coordinates', $this->_getPlacemark($key, $id, 'coordinate') . ',0');
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
     * Set basic metadata for kml.
     *
     * @param string $name  The metadata key.
     * @param string $value The metadata value.
     *
     * @return void
     */
    public function setMetadata($name, $value)
    {
        $this->_metadata[$name] = $value;
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
     * Set a placemark in kml.
     *
     * @param int    $key   An index for a group placemarks.
     * @param int    $mark  An index for the placemark.
     * @param string $name  The name of the placemark.
     * @param string $value The coordinates for the placemark.
     *
     * @return void
     */
    public function setPlacemark($key, $mark, $name, $value)
    {
        $this->_placemark[$key][$mark][$name] = $value;
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
    private function _getPlacemark($key, $mark, $name)
    {
        return $this->_placemark[$key][$mark][$name];
    }

    /**
     * Helper function to get all placemarks
     *
     * @return Array
     */
    private function _getAllPlacemarks()
    {
        return $this->_placemark;
    }

    /**
     * Helper function to add coordinates to placemarks
     *
     * @return void
     */
    public function addCoordinates()
    {
        $count = count($this->coords)-1;
        for ($j=0; $j<=$count; $j++) {
            $title = $this->coords[$j]['title'] ? $this->coords[$j]['title'] : "";

            if (trim($this->coords[$j]['data'])) {
                $whole = trim($this->coords[$j]['data']);  //grab the whole textarea
                $row = explode("\n", Utility::removeEmptyLines($whole));  //split the lines that have data

                $point_key = 0;
                foreach ($row as $loc) {
                    $coord_array = Utility::makeCoordinates($loc);
                    $coord = new \stdClass();
                    $coord->x = ($coord_array[1]) ? Utility::cleanCoord($coord_array[1]) : null;
                    $coord->y = ($coord_array[0]) ? Utility::cleanCoord($coord_array[0]) : null;
                    if (Utility::onEarth($coord) && $title != "") {  //only add point when data are good & a title
                        $this->setPlacemark($j, $point_key, "name", $title);
                        $this->setPlacemark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
                        $point_key++;
                    }
                }
            }
        }
    }

}