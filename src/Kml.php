<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
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

    private $_kml = "";
    private $_metadata = array();
    private $_placemark = array();

    /**
     * The constructor
     *
     * @return void
     */
    public function __construct()
    {
        session_start();
    }

    /**
     * Get the request parameter coords.
     *
     * @param string $file_name A name for a file to be downloaded.
     * @param array  $coords    An array of geographic coordinates.
     *
     * @return object $this
     */
    public function get_request($file_name = "", $coords = array())
    {
        $this->coords         = ($coords) ? $coords : Utilities::loadParam('coords', array());
        $this->file_name      = ($file_name) ? $file_name : Utilities::loadParam('file_name', time());
        $this->download_token = Utilities::loadParam('download_token', md5(time()));
        setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");
        return $this;
    }

    /**
     * Generate the kml file.
     *
     * @return xml The xml file.
     */
    public function createOutput()
    {
        $clean_filename = Mappr::clean_filename($this->file_name);

        $this->set_metadata("name", "SimpleMappr: " . $clean_filename);

        $this->add_coordinates();

        $this->_kml = new \XMLWriter();

        Header::setHeader("kml", $clean_filename . ".kml");
        $this->_kml->openURI('php://output');

        $this->_kml->startDocument('1.0', 'UTF-8');
        $this->_kml->setIndent(4);
        $this->_kml->startElement('kml');
        $this->_kml->writeAttribute('xmlns', 'http://www.opengis.net/kml/2.2');
        $this->_kml->writeAttribute('xmlns:gx', 'http://www.google.com/kml/ext/2.2');

        $this->_kml->startElement('Document');
        $this->_kml->writeElement('name', $this->get_metadata('name'));

        //Style elements
        for ($i=0; $i<=count($this->get_all_placemarks())-1; $i++) {
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

        foreach ($this->get_all_placemarks() as $key => $placemarks) {
            $this->_kml->startElement('Folder');
            $this->_kml->writeAttribute('id', 'simplemapprfolder'.$key);
            $this->_kml->writeElement('name', $this->get_placemark($key, 0, 'name'));
            foreach ($placemarks as $id => $placemark) {
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
     * Set basic metadata for kml.
     *
     * @param string $name  The metadata key.
     * @param string $value The metadata value.
     *
     * @return void
     */
    public function set_metadata($name, $value)
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
    public function get_metadata($name)
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
    public function set_placemark($key, $mark, $name, $value)
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
    private function get_placemark($key, $mark, $name)
    {
        return $this->_placemark[$key][$mark][$name];
    }

    /**
     * Helper function to get all placemarks
     *
     * @return void
     */
    private function get_all_placemarks()
    {
        return $this->_placemark;
    }

    /**
     * Helper function to add coordinates to placemarks
     *
     * @return void
     */
    public function add_coordinates()
    {
        for ($j=0; $j<=count($this->coords)-1; $j++) {
            $title = $this->coords[$j]['title'] ? $this->coords[$j]['title'] : "";

            if (trim($this->coords[$j]['data'])) {
                $whole = trim($this->coords[$j]['data']);  //grab the whole textarea
                $row = explode("\n", Mappr::remove_empty_lines($whole));  //split the lines that have data

                $point_key = 0;
                foreach ($row as $loc) {
                    $coord_array = Mappr::make_coordinates($loc);
                    $coord = new \stdClass();
                    $coord->x = ($coord_array[1]) ? Mappr::clean_coord($coord_array[1]) : null;
                    $coord->y = ($coord_array[0]) ? Mappr::clean_coord($coord_array[0]) : null;
                    if (Mappr::check_on_earth($coord) && $title != "") {  //only add point when data are good & a title
                        $this->set_placemark($j, $point_key, "name", $title);
                        $this->set_placemark($j, $point_key, "coordinate", $coord->x . "," . $coord->y);
                        $point_key++;
                    }
                }
            }
        }
    }

}