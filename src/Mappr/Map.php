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
namespace SimpleMappr\Mappr;

use geoPHP;
use GeoJSON;

use SimpleMappr\Database;
use SimpleMappr\Utility;
use SimpleMappr\Constants\AcceptedOutputs;
use SimpleMappr\Controller\Kml;

/**
 * Map handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Map extends Mappr
{
    /**
     * @var int $_id Database identifer for a map
     */
    private $_id;

    /**
     * @var string $_extension File extension for the output
     */
    private $_extension;

    /**
     * Constructor
     *
     * @param int    $id        Identifier for the map
     * @param string $extension File extension
     */
    public function __construct($id, $extension)
    {
        $this->_id = (int)$id;
        $this->_extension = ($extension) ? $extension : "png";
        parent::__construct();
    }

    /**
     * Implement getRequest method
     *
     * @return object $this
     */
    public function getRequest()
    {
        if (!$this->_id) {
            $this->_setNotFound();
            exit();
        }
        $db = Database::getInstance();
        $sql = "SELECT title, map FROM maps WHERE mid=:mid";
        $db->prepare($sql);
        $db->bindParam(":mid", $this->_id, 'integer');
        $record = $db->fetchFirstObject($sql);
        if (!$record) {
            $this->_setNotFound();
            exit();
        }

        $this->title = $record->title;

        $result = json_decode($record->map, true);

        foreach ($result as $key => $data) {
            $this->{$key} = $data;
        }

        if (isset($this->border_thickness) && !$this->border_thickness) {
            $this->border_thickness = 1.25;
        }
        if (isset($this->bbox_rubberband) && $this->bbox_rubberband) {
            $this->crop = true;
        }

        $this->graticules = false;
        if (isset($this->layers['grid'])) {
            $this->graticules = true;
        }
        if (!isset($this->projection_map) || empty($this->projection_map)) {
            $this->projection_map = 'epsg:4326';
        }
        if (!isset($this->bbox_map) || empty($this->bbox_map) || $this->bbox_map == "0,0,0,0") {
            $this->bbox_map = '-180,-90,180,90';
        }
        if (!isset($this->origin)) {
            $this->origin = (int)Utility::loadParam('origin', false);
        }

        $this->watermark        = true;

        unset($this->options['border']);
        $this->width            = (float)Utility::loadParam('width', 900);
        $this->height           = (float)Utility::loadParam('height', (isset($_GET['width']) && !isset($_GET['height'])) ? $this->width/2 : 450);
        if ($this->width == 0 || $this->height == 0) {
            $this->width = 900; $this->height = 450;
        }

        $this->options['legend'] = true;
        if (Utility::loadParam('legend', false) == "false") {
            $this->options['legend'] = false;
        }

        $this->callback         = Utility::loadParam('callback', null);
        $this->output           = $this->_extension; //overwrite the output
        
        return $this;
    }

    /**
     * Override the method in the parent class
     *
     * @return object $this
     */
    public function execute()
    {
        if (in_array($this->_extension, AcceptedOutputs::outputList())) {
            parent::execute();
        }
        return $this;
    }

    /**
     * Override the method in the parent class
     *
     * @return void
     */
    public function addGraticules()
    {
        if ($this->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(parent::getProjection($this->default_projection));

            $class = ms_newClassObj($layer);
            $label = new \labelObj();
            $label->set("encoding", "UTF-8");
            $label->set("font", "arial");
            $label->set("size", 10);
            $label->set("position", MS_UC);
            $label->color->setRGB(30, 30, 30);
            $class->addLabel($label);
            $style = ms_newStyleObj($class);
            $style->color->setRGB(200, 200, 200);

            $minx = $this->map_obj->extent->minx;
            $maxx = $this->map_obj->extent->maxx;

            $maxarcs = abs($maxx-$minx)/24;

            if ($maxarcs >= 5) {
                $labelformat = "DD";
            }
            if ($maxarcs < 5) {
                $labelformat = "DDMM";
            }
            if ($maxarcs <= 1) {
                $labelformat = "DDMMSS";
            }

            $maxinterval = ($this->gridspace) ? $this->gridspace : $maxarcs;
            $maxsubdivide = 2;

            ms_newGridObj($layer);
            $layer->grid->set("labelformat", $labelformat);
            $layer->grid->set("maxarcs", $maxarcs);
            $layer->grid->set("maxinterval", $maxinterval);
            $layer->grid->set("maxsubdivide", $maxsubdivide);
        }
    }

    /**
     * Override method in the parent class
     *
     * @return void
     */
    public function addScalebar()
    {
        if ($this->_extension == 'png' || $this->_extension == 'jpg') {
            $this->map_obj->scalebar->set("style", 0);
            $this->map_obj->scalebar->set("intervals", 3);
            $this->map_obj->scalebar->set("height", 8);
            $this->map_obj->scalebar->set("width", 200);
            $this->map_obj->scalebar->color->setRGB(30, 30, 30);
            $this->map_obj->scalebar->backgroundcolor->setRGB(255, 255, 255);
            $this->map_obj->scalebar->outlinecolor->setRGB(0, 0, 0);
            $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
            $this->map_obj->scalebar->label->set("encoding", "UTF-8");
            $this->map_obj->scalebar->label->set("font", "arial");
            $this->map_obj->scalebar->label->set("size", 8);
            $this->map_obj->scalebar->label->color->setRGB(0, 0, 0);
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
    }

    /**
     * Override method in the parent class
     *
     * @return void
     */
    public function addLegend()
    {
        if ($this->request->options['legend']) {
            if ($this->request->output == 'png' || $this->request->output == 'jpg') {
                $this->map_obj->legend->set("keysizex", 20);
                $this->map_obj->legend->set("keysizey", 20);
                $this->map_obj->legend->set("keyspacingx", 5);
                $this->map_obj->legend->set("keyspacingy", 5);
                $this->map_obj->legend->set("postlabelcache", 1);
                $this->map_obj->legend->label->set("font", "arial");
                $this->map_obj->legend->label->set("encoding", "UTF-8");
                $this->map_obj->legend->label->set("position", 1);
                $this->map_obj->legend->label->set("size", 10);
                $this->map_obj->legend->label->color->setRGB(0, 0, 0);
                $this->map_obj->legend->set("status", MS_EMBED);
                $this->map_obj->legend->set("position", MS_UR);
                $this->map_obj->drawLegend();
            }
        }
    }

    /**
     * Implement createOutput method
     *
     * @return void
     */
    public function createOutput()
    {
        switch($this->_extension) {
        case 'jpg':
        case 'png':
        case 'svg':
            $this->image->saveImage("");
            break;

        case 'json':
            $output = new \stdClass;
            $output->type = 'FeatureCollection';
            $output->features = array_merge($this->_getCoordinates(), $this->_getWKT());
            $output->crs = [
                'type'       => 'name',
                'properties' => ['name' => 'urn:ogc:def:crs:OGC:1.3:CRS84']
            ];
            $output = json_encode($output);
            if (isset($this->callback) && $this->callback) {
                $output = $this->callback . '(' . $output . ');';
            }
            return $output;
            break;

        case 'kml':
            return (new Kml)->create(["file_name" => $this->title, "coords" => $this->coords]);
            break;

        default:
            $this->_setNotFound();
        }
    }

    /**
     * Set default not found image and 404
     *
     * @return void
     */
    private function _setNotFound()
    {
        http_response_code(404);
        switch ($this->_extension) {
        case 'jpg':
        case 'png':
            header("Content-Type: image/png");
            $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/public/images/404.png");
            imagepng($im);
            imagedestroy($im);
            break;

        case 'json':
            header("Content-Type: application/json");
            echo json_encode(["error" => "not found"]);
            break;

        default:
            readfile($_SERVER["DOCUMENT_ROOT"].'/error/404.html');
        }
    }

    /**
     * Get all coordinates in GeoJSON format
     *
     * @return array $output
     */
    private function _getCoordinates()
    {
        $output = [];
        if (property_exists($this, 'coords')) {
            $count = count($this->coords)-1;
            for ($j=0; $j<=$count; $j++) {
                $title = ($this->coords[$j]['title']) ? stripslashes($this->coords[$j]['title']) : "";

                if (trim($this->coords[$j]['data'])) {
                    $whole = trim($this->coords[$j]['data']);
                    $row = explode("\n", Utility::removeEmptyLines($whole));

                    foreach ($row as $loc) {
                        $coord_array = Utility::makeCoordinates($loc);
                        $coord = new \stdClass();
                        $coord->x = array_key_exists(1, $coord_array) ? (float)trim($coord_array[1]) : "nil";
                        $coord->y = array_key_exists(0, $coord_array) ? (float)trim($coord_array[0]) : "nil";
                        if (Utility::onEarth($coord) && $title != "") {
                            $output[] = [
                                'type' => 'Feature',
                                'geometry' => ['type' => 'Point', 'coordinates' => [$coord->x,$coord->y]],
                                'properties' => ['title' => $title]
                            ];
                        }
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Get all drawings in GeoJSON format
     *
     * @return array $output
     */
    private function _getWKT()
    {
        $output = [];
        if (property_exists($this, 'wkt')) {
            $count = count($this->wkt)-1;
            for ($j=0; $j<=$count; $j++) {
                $title = ($this->wkt[$j]['title']) ? stripslashes($this->wkt[$j]['title']) : "";

                if (trim($this->wkt[$j]['data'])) {
                    $whole = trim($this->wkt[$j]['data']);
                    $rows = explode("\n", Utility::removeEmptyLines($whole));

                    foreach ($rows as $row) {
                        $shape = geoPHP::load($row,'wkt');
                        $geojson = new GeoJSON();
                        $geometry = $geojson->write($shape, TRUE);
                        $output[] = [
                            'type' => 'Feature',
                            'geometry' => $geometry,
                            'properties' => ['title' => $title]
                        ];
                    }
                }
            }
        }
        return $output;
    }

}