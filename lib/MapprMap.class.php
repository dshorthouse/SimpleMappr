<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link      http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @package   SimpleMappr
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
 * Map handler for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class MapprMap extends Mappr
{
    private $_id;
    private $_extension;

    /**
     * Constructor
     *
     * @param int $id Identifier for the map
     * @param string $extension File extension
     * @return void
     */
    function __construct($id, $extension)
    {
        parent::__construct();
        $this->_id = (int)$id;
        $this->_extension = ($extension) ? $extension : "pnga";
    }

    /**
     * Override the method in the parent class
     */
    public function get_request()
    {
        if (!$this->_id) {
            $this->set_not_found();
            exit();
        }
        $db = new Database();
        $sql = "SELECT map FROM maps WHERE mid=:mid";
        $db->prepare($sql);
        $db->bind_param(":mid", $this->_id, 'integer');
        $record = $db->fetch_first_object($sql);
        if (!$record) {
            $this->set_not_found();
            exit();
        }

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

        (isset($this->layers['grid'])) ? $this->graticules = true : $this->graticules = false;
        if (!isset($this->projection_map) || empty($this->projection_map)) {
            $this->projection_map = 'epsg:4326';
        }
        if (!isset($this->bbox_map) || empty($this->bbox_map) || $this->bbox_map == "0,0,0,0") {
            $this->bbox_map = '-180,-90,180,90';
        }

        $this->download         = true;
        $this->watermark        = true;

        unset($this->options['border']);
        $this->width            = (float)$this->load_param('width', 800);
        $this->height           = (float)$this->load_param('height', (isset($_GET['width']) && !isset($_GET['height'])) ? $this->width/2 : 400);
        if ($this->width == 0 || $this->height == 0) {
            $this->width = 800; $this->height = 400;
        }
        if ($this->load_param('legend', false)) {
            $this->options['legend'] = true;
        }

        $this->image_size       = array($this->width, $this->height);
        $this->callback         = $this->load_param('callback', null);
        $this->output           = $this->_extension; //overwrite the output

        return $this;
    }

    private function set_not_found()
    {
        http_response_code(404);
        switch ($this->_extension) {
        case 'pnga':
            header("Content-Type: image/png");
            $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/public/images/logo.png");
            imagepng($im);
            imagedestroy($im);
            break;

        case 'json':
            header("Content-Type: application/json");
            echo json_encode(array("error" => "not found"));
            break;

        default:
            readfile($_SERVER["DOCUMENT_ROOT"].'/error/404.html');
        }
    }

    /**
     * Override the method in the parent class
     */
    public function execute()
    {
        if (in_array($this->_extension, array('png', 'pnga', 'svg'))) {
            parent::execute();
        }
        return $this;
    }

    /**
     * Override the method in the parent class
     */
    public function add_graticules()
    {
        if ($this->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(parent::get_projection($this->default_projection));

            $class = ms_newClassObj($layer);
            if (isset($this->gridlabel) && $this->gridlabel == 1) {
                $label = new \labelObj();
                $label->set("font", "arial");
                $label->set("type", MS_TRUETYPE);
                $label->set("size", 10);
                $label->set("position", MS_UC);
                $label->color->setRGB(30, 30, 30);
                $class->addLabel($label);
            }
            $style = ms_newStyleObj($class);
            $style->color->setRGB(200, 200, 200);

            ms_newGridObj($layer);
            $minx = $this->map_obj->extent->minx;
            $maxx = $this->map_obj->extent->maxx;

            $ticks = abs($maxx-$minx)/24;

            if ($ticks >= 5) {
                $labelformat = "DD";
            }
            if ($ticks < 5) {
                $labelformat = "DDMM";
            }
            if ($ticks <= 1) {
                $labelformat = "DDMMSS";
            }

            $layer->grid->set("labelformat", $labelformat);
            $layer->grid->set("maxarcs", $ticks);
            $layer->grid->set("maxinterval", isset($this->gridspace) ? $this->gridspace : $ticks);
            $layer->grid->set("maxsubdivide", 2);
        }
    }

    /**
     * Override the method in the parent class
     */
    public function add_scalebar()
    {
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
        $this->map_obj->scalebar->label->set("type", MS_TRUETYPE);
        $this->map_obj->scalebar->label->set("size", 10);
        $this->map_obj->scalebar->label->set("antialias", 50);
        $this->map_obj->scalebar->label->color->setRGB(0, 0, 0);

        //svg format cannot do scalebar in MapServer
        if ($this->_extension != 'svg') {
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
    }

    private function get_coordinates()
    {
        $output = array();
        for ($j=0; $j<=count($this->coords)-1; $j++) {
            $title = ($this->coords[$j]['title']) ? stripslashes($this->coords[$j]['title']) : '';

            if (trim($this->coords[$j]['data'])) {
                $whole = trim($this->coords[$j]['data']);
                $row = explode("\n", $this->remove_empty_lines($whole));

                $point_key = 0;
                foreach ($row as $loc) {
                    $coord_array = parent::make_coordinates($loc);
                    $coord = new \stdClass();
                    $coord->x = array_key_exists(1, $coord_array) ? (float)trim($coord_array[1]) : "nil";
                    $coord->y = array_key_exists(0, $coord_array) ? (float)trim($coord_array[0]) : "nil";
                    if (parent::check_coord($coord) && $title != "") {
                        $output[] = array(
                            'type' => 'Feature',
                            'geometry' => array('type' => 'Point', 'coordinates' => array($coord->x,$coord->y)),
                            'properties' => array('title' => $title)
                        );
                    }
                }
            }
        }
        return $output;
    }

    public function create_output()
    {
        switch($this->_extension) {
        case 'png':
        case 'pnga':
            header("Content-Type: image/png");
            $this->image->saveImage("");
            break;

        case 'json':
            Header::set_header('json');
            $output = new \stdClass;
            $output->type = 'FeatureCollection';
            $output->features = $this->get_coordinates();
            $output->crs = array(
                'type'       => 'name',
                'properties' => array('name' => 'urn:ogc:def:crs:OGC:1.3:CRS84')
            );
            $output = json_encode($output);
            if (isset($this->callback) && $this->callback) {
                $output = $this->callback . '(' . $output . ');';
            }
            echo $output;
            break;

        case 'kml':
            Header::set_header('kml');
            $kml = new Kml;
            $kml->get_request($this->_id, $this->coords)->create_output();
            break;

        case 'svg':
            Header::set_header('svg');
            $this->image->saveImage("");
            break;

        default:
            $this->set_not_found();
        }
    }

}