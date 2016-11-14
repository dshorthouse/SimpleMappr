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

/**
 * API handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class MapprApi extends Mappr
{
    /**
     * @var array $_coord_cols Coordinates for each column of data
     */
    private $_coord_cols = [];

    /**
     * @var array $_bad_points Coordinates that are not on Earth
     */
    private $_bad_points = [];

    /**
     * @var array $_bad_drawings WKT that do not render
     */
    private $_bad_drawings = [];

    /**
     * Implement getRequest method
     *
     * @return object
     */
    public function getRequest()
    {
        $attr = new \stdClass();

        //ping API to return JSON
        $attr->ping             = Utility::loadParam('ping', false);
        $attr->parameters       = Utility::loadParam('parameters', false);
        $attr->method           = $_SERVER['REQUEST_METHOD'];
        $attr->download         = true;
        $attr->watermark        = true;
        $attr->options          = [];
        $attr->url              = false;
        $attr->url_content      = "";
        $url                    = urldecode(Utility::loadParam('url', false));

        if ($attr->method == "POST" && $_FILES) {
            $file = $this->_moveFile();
        } else {
            $file = urldecode(Utility::loadParam('file', false));
        }

        if ($url) {
            $attr->url = $url;
        }
        if ($file) {
            $attr->url = $file;
        }

        $points = Utility::loadParam('points', []);
        $attr->points = ((array)$points === $points) ? $points : [$points];

        $wkt = Utility::loadParam('wkt', []);
        $attr->wkt = ((array)$wkt === $wkt) ? $wkt : [$wkt];

        $attr->legend           = Utility::loadParam('legend', []);

        $shape = Utility::loadParam('shape', []);
        $attr->shape = ((array)$shape === $shape) ? $shape : [$shape];

        $size = Utility::loadParam('size', []);
        $attr->size = ((array)$size === $size) ? $size : [$size];

        $color = Utility::loadParam('color', []);
        $attr->color = ((array)$color === $color) ? $color : [$color];

        $attr->outlinecolor     = Utility::loadParam('outlinecolor', null);
        $attr->border_thickness = (float)Utility::loadParam('thickness', 1.25);

        $shaded = Utility::loadParam('shade', []);
        $attr->regions = [
            'data' => (array_key_exists('places', $shaded)) ? $shaded['places'] : "",
            'title' => (array_key_exists('title', $shaded)) ? $shaded['title'] : "",
            'color' => (array_key_exists('color', $shaded)) ? str_replace(",", " ", $shaded['color']) : "120 120 120"
        ];

        $attr->output           = Utility::loadParam('output', 'png');
        $attr->projection       = Utility::loadParam('projection', 'epsg:4326');
        $attr->projection_map   = 'epsg:4326';
        $attr->origin           = (int)Utility::loadParam('origin', false);
        $attr->bbox_map         = Utility::loadParam('bbox', '-180,-90,180,90');
        $attr->zoom             = (int)Utility::loadParam('zoom', false);

        //convert layers as comma-separated values to an array
        $_layers                = explode(',', Utility::loadParam('layers', ""));
        $layers = [];
        $layers['countries']    = true;
        foreach ($_layers as $_layer) {
            if ($_layer) {
                $layers[trim($_layer)] = trim($_layer);
            }
        }
        $attr->layers           = $layers;
        $attr->graticules       = Utility::loadParam('graticules', false);
        $attr->gridspace        = Utility::loadParam('spacing', false);
        $attr->gridlabel        = Utility::loadParam('gridlabel', "true");

        if (Utility::loadParam('border', false)) {
            $attr->options['border'] = true;
        }
        if (Utility::loadParam('legend', false)) {
            $attr->options['legend'] = true;
        }
        if (Utility::loadParam('scalebar', false)) {
            $attr->options['scalebar'] = true;
        }

        //set the image size from width & height to array(width, height)
        $attr->width            = (float)Utility::loadParam('width', 900);
        $height = 450;
        if (isset($_REQUEST['width']) && !isset($_REQUEST['height'])) {
            $height = $attr->width/2;
        }
        $attr->height           = (float)Utility::loadParam('height', $height);
        if ($attr->width == 0 || $attr->height == 0) {
            $attr->width = 900; $attr->height = 450;
        }

        if (!in_array($attr->output, AcceptedOutputs::outputList())) {
            $attr->output = 'png';
        }

        return $attr;
    }

    /**
     * Override addCoordinates method
     *
     * @return void
     */ 
    public function addCoordinates()
    {
        if ($this->request->url || $this->request->points) {
            if ($this->request->url) {
                $this->_parseUrl();
            }
            if ($this->request->points) {
                $this->_parsePoints();
            }
            if ($this->request->zoom) {
                $this->_setZoom();
            }
        }

        foreach ($this->_coord_cols as $col => $coords) {
            $mlayer = ms_newLayerObj($this->map_obj);
            $title = (is_array($this->request->legend) && isset($this->request->legend[$col])) ? $this->request->legend[$col] : "";
            $mlayer->set("name", $title);
            $mlayer->set("status", MS_ON);
            $mlayer->set("type", MS_LAYER_POINT);
            $mlayer->set("tolerance", 5);
            $mlayer->set("toleranceunits", 6);
            $mlayer->setProjection(parent::getProjection($this->default_projection));

            $class = ms_newClassObj($mlayer);
            $class->set("name", $title);

            $style = ms_newStyleObj($class);
            $symbol = 'circle';
            if (array_key_exists($col, $this->request->shape) && in_array($this->request->shape[$col], AcceptedMarkerShapes::shapes())) {
                $symbol = $this->request->shape[$col];
            }
            $style->set("symbolname", $symbol);
            $style->set("size", (array_key_exists($col, $this->request->size)) ? $this->request->size[$col] : 8);

            if (array_key_exists($col, $this->request->color)) {
                $color = explode(",", $this->request->color[$col]);
                $style->color->setRGB(
                    (array_key_exists(0, $color)) ? $color[0] : 0,
                    (array_key_exists(1, $color)) ? $color[1] : 0,
                    (array_key_exists(2, $color)) ? $color[2] : 0
                );
            } else {
                $style->color->setRGB(0, 0, 0);
            }

            if ($this->request->outlinecolor && substr($class->getStyle(0)->symbolname, 0, 4) != 'open') {
                $outlinecolor = explode(",", $this->request->outlinecolor);
                $style->outlinecolor->setRGB(
                    (array_key_exists(0, $outlinecolor)) ? $outlinecolor[0] : 255,
                    (array_key_exists(1, $outlinecolor)) ? $outlinecolor[1] : 255,
                    (array_key_exists(2, $outlinecolor)) ? $outlinecolor[2] : 255
                );
            }

            $mcoord_shape = ms_newShapeObj(MS_SHAPE_POINT);
            $mcoord_line = ms_newLineObj();

            //add all the points
            foreach ($coords as $coord) {
                $_coord = new \stdClass();
                $_coord->x = array_key_exists(1, $coord) ? Utility::cleanCoord($coord[1]) : null;
                $_coord->y = array_key_exists(0, $coord) ? Utility::cleanCoord($coord[0]) : null;
                //only add point when data are good
                if (Utility::onEarth($_coord)) {
                    $mcoord_point = ms_newPointObj();
                    $mcoord_point->setXY($_coord->x, $_coord->y);
                    $mcoord_line->add($mcoord_point);
                } else {
                    $this->_bad_points[] = ($title) ? join(":",[$title,join(",",$coord)]) : join(",",$coord);
                }
            }
            $mcoord_shape->add($mcoord_line);
            $mlayer->addFeature($mcoord_shape);
        }
    }

    /**
     * Override addWKT method
     *
     * @return void
     */
    public function addWKT()
    {
        if ($this->request->wkt) {
            $count = count($this->request->wkt)-1;
            for ($j=$count; $j>=0; $j--) {
                $color = [120,120,120];
                $title = "";
                if (array_key_exists('color',$this->request->wkt[$j])) {
                    $color = explode(",", $this->request->wkt[$j]['color']);
                    if (count($color) != 3) {
                        $color = [120,120,120];
                    }
                }
                if (array_key_exists('title', $this->request->wkt[$j])) {
                    $title = stripslashes($this->request->wkt[$j]['title']);
                }

                if (array_key_exists('data', $this->request->wkt[$j])) {
                    $data = trim($this->request->wkt[$j]['data']);

                    if ($data) {
                        $this->_legend_required = true;
                        $rows = explode("\n", Utility::removeEmptyLines($data));
                        foreach ($rows as $key => $row) {
                            if (strpos($row, "POINT") !== false) {
                                $type = MS_LAYER_POINT;
                            } else if (strpos($row, "LINE") !== false) {
                                $type = MS_LAYER_LINE;
                            } else {
                                $type = MS_LAYER_POLYGON;
                            }
                            $layer = ms_newLayerObj($this->map_obj);
                            $layer->set("name", "wkt_layer_".$j.$key);
                            $layer->set("status", MS_ON);
                            $layer->set("type", $type);
                            $layer->set("template", "template.html");
                            $layer->setProjection(self::getProjection($this->default_projection));

                            $class = ms_newClassObj($layer);
                            $class->set("name", $title);
                            $style = ms_newStyleObj($class);
                            if ($type == MS_LAYER_POINT) {
                                $style->set("symbolname", 'circle');
                                $style->set("size", 8);
                            }
                            $style->color->setRGB($color[0], $color[1], $color[2]);
                            $style->set("opacity", 75);

                            try {
                                $shape = ms_shapeObjFromWkt($row);
                                $layer->addFeature($shape);
                            } catch(\Exception $e) {
                                $this->_bad_drawings[] = ($title) ? join(":",[$title, $row]) : $row;
                            }

                        }
                    }
                }

            }
        }
    }

    /**
     * Override addRegions method
     *
     * @return void
     */
    public function addRegions()
    {
        if ($this->request->regions['data']) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", "stateprovinces_polygon");
            $layer->set("data", $this->shapes['stateprovinces_polygon']['path']);
            $layer->set("type", $this->shapes['stateprovinces_polygon']['type']);
            $layer->set("template", "template.html");
            $layer->setProjection(parent::getProjection($this->default_projection));

            //grab the data for regions & split
            $whole = trim($this->request->regions['data']);
            $rows = explode("\n", Utility::removeEmptyLines($whole));
            $qry = [];
            foreach ($rows as $row) {
                $regions = preg_split("/[,;]+/", $row); //split by a comma, semicolon
                foreach ($regions as $region) {
                    $pos = strpos($region, '[');
                    if ($pos !== false) {
                        $split = explode("[", str_replace("]", "", trim(strtoupper($region))));
                        $states = preg_split("/[\s|]+/", $split[1]);
                        $statekey = [];
                        foreach ($states as $state) {
                            $statekey[] = "'[code_hasc]' ~* '\.".$state."$'";
                        }
                        $qry[] = "'[adm0_a3]' = '".trim($split[0])."' && (".implode(" || ", $statekey).")";
                    } else {
                        $region = addslashes(ucwords(strtolower(trim($region))));
                        $qry[] = "'[name]' ~* '".$region."$' || '[admin]' ~* '".$region."$'";
                    }
                }
            }

            $layer->setFilter("(".implode(" || ", $qry).")");
            $class = ms_newClassObj($layer);
            $class->set("name", stripslashes($this->request->regions['title']));

            $style = ms_newStyleObj($class);
            $color = explode(" ", "0 0 0");
            if ($this->request->regions['color']) {
                $color = explode(" ", $this->request->regions['color']);
            }
            $style->color->setRGB($color[0], $color[1], $color[2]);
            $style->outlinecolor->setRGB(30, 30, 30);

            $layer->set("status", MS_ON);
        }
    }

    /**
     * Override addGraticules method
     *
     * @return void
     */
    public function addGraticules()
    {
        if ($this->request->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(parent::getProjection($this->default_projection));

            $class = ms_newClassObj($layer);

            if ($this->request->gridlabel == "true") {
                $label = new \labelObj();
                $label->set("encoding", "UTF-8");
                $label->set("font", "arial");
                $label->set("size", 10);
                $label->set("position", MS_UC);
                $label->color->setRGB(30, 30, 30);
                $class->addLabel($label);
            }

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

            $maxinterval = ($this->request->gridspace) ? $this->request->gridspace : $maxarcs;
            $maxsubdivide = 2;

            ms_newGridObj($layer);
            $layer->grid->set("labelformat", $labelformat);
            $layer->grid->set("maxarcs", $maxarcs);
            $layer->grid->set("maxinterval", $maxinterval);
            $layer->grid->set("maxsubdivide", $maxsubdivide);
        }
    }

    /**
     * Override addScalebar method
     *
     * @return void
     */
    public function addScalebar()
    {
        $this->map_obj->scalebar->set("style", 0);
        $this->map_obj->scalebar->set("intervals", ($this->request->width <= 500) ? 2 : 3);
        $this->map_obj->scalebar->set("height", 8);
        $this->map_obj->scalebar->set("width", ($this->request->width <= 500) ? 100 : 200);
        $this->map_obj->scalebar->color->setRGB(30, 30, 30);
        $this->map_obj->scalebar->backgroundcolor->setRGB(255, 255, 255);
        $this->map_obj->scalebar->outlinecolor->setRGB(0, 0, 0);
        $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->map_obj->scalebar->label->set("encoding", "UTF-8");
        $this->map_obj->scalebar->label->set("font", "arial");
        $this->map_obj->scalebar->label->set("size", ($this->request->width <= 500) ? 8 : 10);
        $this->map_obj->scalebar->label->color->setRGB(0, 0, 0);

        //svg format cannot do scalebar in MapServer
        if ($this->request->output != 'svg') {
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
    }

    /**
     * Override addLegend method
     *
     * @return void
     */
    public function addLegend()
    {
        $this->map_obj->legend->set("postlabelcache", 1);
        $this->map_obj->legend->label->set("font", "arial");
        $this->map_obj->legend->label->set("position", 1);
        $this->map_obj->legend->label->set("size", ($this->request->width <= 500) ? 8 : 10);
        $this->map_obj->legend->label->color->setRGB(0, 0, 0);

        //svg format cannot do legends in MapServer
        if ($this->request->options['legend'] && $this->request->output != 'svg') {
            $this->map_obj->legend->set("status", MS_EMBED);
            $this->map_obj->legend->set("position", MS_UR);
            $this->map_obj->drawLegend();
        }
    }

    /**
     * Implement createOutput method
     *
     * @return json_encoded $output
     */
    public function createOutput()
    {
        if ($this->request->ping) {
            Header::setHeader("json");
            return json_encode(["status" => "ok"]);
        }
        else if ($this->request->parameters) {
            $url_parts = Utility::parsedURL();
            Header::setHeader("json");
            $swagger = [
              'swagger' => '2.0',
              'info' => [
                'title' => 'SimpleMappr API',
                'description' => 'Create free point maps for publications and presentations',
                'version' => '1.0.0',
                'contact' => [
                  'name' => 'David P. Shorthouse',
                  'email' => 'davidpshorthouse@gmail.com'
                ]
              ],
              'host' => $url_parts["host"],
              'schemes' => [$url_parts["scheme"]],
              'paths' => [
                '/api' => [
                  'get' => [
                    'summary' => 'GET to /api',
                    'description' => 'GET to /api to produce an image',
                    'produces' => [
                      'image/png',
                      'image/jpeg',
                      'image/tiff',
                      'image/svg+xml',
                      'application/json'
                    ],
                    'parameters' => $this->_apiParameters("GET"),
                    'responses' => [
                      200 => [
                        'description' => 'success'
                      ]
                    ]
                  ],
                  'post' => [
                    'summary' => 'POST to /api',
                    'description' => 'POST to /api to produce a JSON response containing URL to image',
                    'consumes' => [
                      'multipart/form-data'
                    ],
                    'produces' => [
                      'application/json',
                    ],
                    'parameters' => $this->_apiParameters("POST"),
                    'responses' => [
                      200 => [
                        'description' => 'success',
                        'examples' => [
                          'application/json' => "{'imageURL': 'http://img.simplemappr.net/50778960_464f_0.png','expiry': '2016-11-14T11:42:46-05:00','bad_points': [],'bad_drawings': []}"
                        ]
                      ]
                    ]
                  ]
                ]
              ]
            ];
            return json_encode($swagger);
        } else {
            if ($this->request->method == 'GET') {
                Header::setHeader($this->request->output);
                if ($this->request->output == 'tif') {
                  error_reporting(0);
                  $this->image_url = $this->image->saveWebImage();
                  $image_filename = basename($this->image_url);
                  readfile($this->tmp_path.$image_filename);
                } else {
                  $this->image->saveImage("");
                }
            } else if ($this->request->method == 'OPTIONS') { //For CORS requests
                http_response_code(204);
            } else {
                Header::setHeader("json");
                $output = [
                    'imageURL' => $this->image->saveWebImage(),
                    'expiry'   => date('c', time() + (6 * 60 * 60)),
                    'bad_points' => $this->_bad_points,
                    'bad_drawings' => $this->_bad_drawings
                ];
                return json_encode($output);
            }
        }
    }

    /**
     * Get the API parameters and their definitions
     *
     * @return array of API parameters
     */
    private function _apiParameters($request_method = "GET")
    {
      array_walk(AcceptedProjections::$projections, function ($val, $key) use (&$projections) {
          $projections[] = $key . " (" . $val['name'] . ")";
      });
      $params = [
        [
          'name' => 'ping',
          'in' => 'query',
          'description' => 'if ping=true is included, a JSON response will be produced in place of an image.',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'parameters',
          'in' => 'query',
          'description' => 'if parameters=true is included, a JSON response will be produced containing all accepted parameters and their descriptions.',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'url',
          'in' => 'query',
          'description' => 'a URL-encoded, remote tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated points.',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'file',
          'in' => 'formData',
          'description' => 'tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated. The initial response will be JSON with an imageURL element and an expiry element, which indicates when the file will likely be deleted from the server.',
          'required' => false,
          'type' => 'file'
        ],
        [
          'name' => 'points[x]',
          'in' => 'query',
          'description' => 'single or multiple markers written as latitude,longitude in decimal degrees, DDMMSS, or DD mm.mm. Multiple markers are separated by line-breaks, \n and these are best used in a POST request. If a POST request is used, the initial response will be JSON as above.',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'wkt[x][data]',
          'in' => 'query',
          'description' => 'data for array of well-known text shape x expressed as POINT, LINESTRING, POLYGON, MULTIPOINT, MULTILINESTRING, or MULTIPOLYGON',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'wkt[x][title]',
          'in' => 'query',
          'description' => 'title for well-known text shape x',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'wkt[x][color]',
          'in' => 'query',
          'description' => 'color for well-known text shape x',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'shape[x]',
          'in' => 'query',
          'description' => 'shape of marker for column x',
          'required' => false,
          'type' => 'string',
          'enum' => AcceptedMarkerShapes::shapes()
        ],
        [
          'name' => 'size[x]',
          'in' => 'query',
          'description' => 'integer-based point size of marker in column x',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'default' => '',
          'minimum' => 1,
          'maximum' => 14
        ],
        [
          'name' => 'color[x]',
          'in' => 'query',
          'description' => 'comma-separated RGB colors for marker in column x',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'outlinecolor',
          'in' => 'query',
          'description' => 'comma-separated RGB colors for halo around all solid markers',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'zoom',
          'in' => 'query',
          'description' => 'integer from 1 to 10, centered on the geographic midpoint of all coordinates',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'default' => '',
          'minimum' => 1,
          'maximum' => 10
        ],
        [
          'name' => 'bbox',
          'in' => 'query',
          'description' => 'comma-separated bounding box in decimal degrees',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'shade[places]',
          'in' => 'query',
          'description' => 'comma-separated State, Province or Country names or the three-letter ISO country code with pipe-separated States or Provinces flanked by brackets',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'shade[title]',
          'in' => 'query',
          'description' => 'the title for the shaded places',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'shade[color]',
          'in' => 'query',
          'description' => 'comma-separated RGB fill colors for shaded places',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'layers',
          'in' => 'query',
          'description' => 'comma-separated cultural or physical layers; options are relief, stateprovinces, lakes, rivers, oceans, placenames, ecoregions, conservation, blueMarble',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'projection',
          'in' => 'query',
          'description' => 'the output projection in either EPSG or ESRI references',
          'required' => false,
          'type' => 'string',
          'enum' => $projections
        ],
        [
          'name' => 'origin',
          'in' => 'query',
          'description' => 'longitude of natural origin used in Lambert projections',
          'required' => false,
          'type' => 'number',
          'format' => 'float',
          'default' => '',
          'minimum' => -180,
          'maximum' => 180
        ],
        [
          'name' => 'graticules',
          'in' => 'query',
          'description' => 'display the graticules',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'spacing',
          'in' => 'query',
          'description' => 'display the graticules with defined spacing in degrees',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'default' => '',
          'minimum' => 1,
          'maximum' => 50
        ],
        [
          'name' => 'width',
          'in' => 'query',
          'description' => 'integer-based output width in pixels',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'default' => '',
          'minimum' => 200,
          'maximum' => 4500
        ],
        [
          'name' => 'height',
          'in' => 'query',
          'description' => 'integer-based output height in pixels; if height is not provided, it will be half the width',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'default' => '',
          'minimum' => 200,
          'maximum' => 4500
        ],
        [
          'name' => 'output',
          'in' => 'query',
          'description' => 'file format of the image or vector produced',
          'required' => false,
          'type' => 'string',
          'enum' => AcceptedOutputs::outputList()
        ],
        [
          'name' => 'scalebar',
          'in' => 'query',
          'description' => 'embed a scalebar in the lower right of the image',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'legend[x]',
          'in' => 'query',
          'description' => 'URL-encode a title for an item in a legend, embedded in the upper right of the image. If you have a url or file parameter, use legend=true instead',
          'required' => false,
          'type' => 'string'
        ]
      ];
      if ($request_method == "GET") {
        foreach($params as $param => $value) {
          if($value['name'] == 'file') {
            unset($params[$param]);
            break;
          }
        }
      }
      return array_values($params);
    }

    /**
     * Move an uploaded file
     *
     * @return string The path of the uploaded file
     */
    private function _moveFile()
    {
        $uploadfile = MAPPR_UPLOAD_DIRECTORY . "/" . md5(time()) . '.txt';
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            if (mime_content_type($uploadfile) == "text/plain") {
                return $uploadfile;
            } else {
                unlink($uploadfile);
            }
        }
        return false;
    }

    /**
     * Set a zoom level
     *
     * @return void
     */
    private function _setZoom()
    {
        if ($this->request->zoom == 0 || $this->request->zoom > 10) {
            return;
        }
        $midpoint = $this->_getMidpoint($this->_coord_cols);
        $x = $this->map_obj->width*(($midpoint[0] + 180)/360);
        $y = $this->map_obj->height*((90 - $midpoint[1])/180);
        $zoom_point = ms_newPointObj();
        $zoom_point->setXY($x, $y);
        $this->map_obj->zoompoint($this->request->zoom*2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
    }

    /**
     * Find the geographic midpoint of a nested array of exploded dd coords
     *
     * @param array $array Array of coordinates
     *
     * @return array [long,lat]
     */
    private function _getMidpoint($array)
    {
        $x = $y = $z = [];
        foreach ($array as $coords) {
            foreach ($coords as $coord) {
                if (isset($coord[0]) && isset($coord[1])) {
                    $rx = deg2rad($coord[1]);
                    $ry = deg2rad($coord[0]);
                    $x[] = cos($ry)*cos($rx);
                    $y[] = cos($ry)*sin($rx);
                    $z[] = sin($ry);
                }
            }
        }
        $X = array_sum($x)/count($x);
        $Y = array_sum($y)/count($y);
        $Z = array_sum($z)/count($z);
        return [rad2deg(atan2($Y, $X)), rad2deg(atan2($Z, sqrt(pow($X, 2) + pow($Y, 2))))];
    }

    /**
     * Parse all POSTed data into cleaned array of points
     *
     * @return void
     */
    private function _parsePoints()
    {
        $num_cols = (isset($num_cols)) ? $num_cols++ : 0;
        foreach ($this->request->points as $rows) {
            $row = preg_split("/[\r\n]|(\\\[rn])/", urldecode(Utility::removeEmptyLines($rows)));
            foreach (str_replace("\\", "", $row) as $point) {
                $this->_coord_cols[$num_cols][] = Utility::makeCoordinates($point);
            }
            $num_cols++;
        }
    }

    /**
     * Discover format of URL and parse it
     *
     * @return void
     */
    private function _parseUrl()
    {
        if (strstr($this->request->url, MAPPR_UPLOAD_DIRECTORY)) {
            $this->_parseFile();
            unlink($this->request->url);
        } else {
            $headers = get_headers($this->request->url, 1);
            if (array_key_exists('Location', $headers)) {
                $this->request->url = array_pop($headers['Location']);
            }
            $this->request->url_content = @file_get_contents($this->request->url);
            preg_match_all('/[<>{}\[\]]/', $this->request->url_content, $match);
            if (count($match[0]) >= 4) {
                $this->_parseGeo();
            } else {
                $this->_parseFile();
            }
        }
    }

    /**
     * Parse text file into cleaned array of points
     *
     * @return void
     */
    private function _parseFile()
    {
        if (@$fp = fopen($this->request->url, 'r')) {
            while ($line = fread($fp, 1024)) {
                $rows = preg_split("/[\r\n]+/", $line, -1, PREG_SPLIT_NO_EMPTY);
                $cols = explode("\t", $rows[0]);
                $num_cols = count($cols);
                $this->legend = explode("\t", $rows[0]);
                unset($rows[0]);
                foreach ($rows as $row) {
                    $cols = explode("\t", $row);
                    for ($i=0;$i<$num_cols;$i++) {
                        if (array_key_exists($i, $cols)) {
                            $cols[$i] = preg_replace('/[\p{Z}\s]/u', ' ', $cols[$i]);
                            $cols[$i] = trim(preg_replace('/[^\d\s,;.\-NSEWO°dms\'"]/i', "", $cols[$i]));
                            if (preg_match('/[NSEWO]/', $cols[$i]) != 0) {
                                $coord = preg_split("/[,;]/", $cols[$i]);
                                $coord = (preg_match('/[EWO]/', $coord[1]) != 0) ? $coord : array_reverse($coord);
                                $this->_coord_cols[$i][] = [
                                    Utility::dmsToDeg(trim($coord[0])),
                                    Utility::dmsToDeg(trim($coord[1]))
                                ];
                            } else {
                                $this->_coord_cols[$i][] = preg_split("/[\s,;]+/", trim(preg_replace("/[^0-9-\s,;.]/", "", $cols[$i])));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Parse GeoRSS, GeoJSON, WKT, KML into cleaned array of points
     *
     * @return void
     */
    private function _parseGeo()
    {
        $geometries = \geoPHP::load($this->request->url_content);
        if ($geometries) {
            $num_cols = (isset($num_cols)) ? $num_cols++ : 0;
            $this->legend[$num_cols] = $this->request->url;
            foreach ($geometries as $geometry) {
                foreach ($geometry as $item) {
                    if ($item->geometryType() == 'Point') {
                        $this->_coord_cols[$num_cols][] = array_reverse($item->coords);
                    }
                }
            }
        }
    }

}