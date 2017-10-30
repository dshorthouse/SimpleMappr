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
namespace SimpleMappr\Mappr;

use \Symfony\Component\Yaml\Yaml;
use SimpleMappr\Constants\AcceptedMarkers;
use SimpleMappr\Constants\AcceptedOutputs;
use SimpleMappr\Constants\AcceptedProjections;
use SimpleMappr\Utility;

/**
 * Main Mappr class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
abstract class Mappr
{
    /**
     * Receive request, required for all extended classes
     *
     * @return void
     */
    abstract public function getRequest();

    /**
     * Create output, required for all extended classes
     *
     * @return void
     */
    abstract public function createOutput();

    /**
     * Path to the shapefile config
     *
     * @var string $shapefile_config
     */
    public static $shapefile_config = ROOT.'/config/shapefiles.yml';

    /**
     * Path to the font file
     *
     * @var string $font_file
     */
    protected $font_file = ROOT.'/mapserver/fonts/fonts.list';

    /**
     * File system temp path to store files produced
     *
     * @var string $tmp_path
     */
    protected $tmp_path = ROOT.'/public/tmp/';

    /**
     * URL temp path to retrieve files produced
     *
     * @var string $tmp_url
     */
    protected $tmp_url = MAPPR_MAPS_URL;

    /**
     * The base map object
     *
     * @var object $map_obj
     */
    protected $map_obj;

    /**
     * Default extent when map first loaded
     *
     * @var array $max_extent
     */
    protected $max_extent = [-180,-90,180,90];

    /**
     * Default projection when map first loaded
     *
     * @var string $default_projection
     */
    protected $default_projection = 'epsg:4326';

    /**
     * Image object produced from MapScript
     *
     * @var object $image
     */
    protected $image;

    /**
     * Scale object for scalebar produced from MapScript
     *
     * @var object $scale
     */
    protected $scale;

    /**
     * Legend object produced from MapScript
     *
     * @var object $legend
     */
    protected $legend;

    /**
     * Shapes and their mapfile configurations
     *
     * @var array $shapes
     */
    protected $shapes = [];

    /**
     * Post-draw padding for longitude extent used as a correction factor on front-end
     *
     * @var int $ox_pad
     */
    protected $ox_pad = 0;

    /**
     * Post-draw padding for latitude extent used as a correction factor on front-end
     *
     * @var int $oy_pad
     */
    protected $oy_pad = 0;

    /**
     * URL for legend image if produced
     *
     * @var string $legend_url
     */
    protected $legend_url;

    /**
     * URL for scalebar image if produced
     *
     * @var string $scalebar_url
     */
    protected $scalebar_url;

    /**
     * Holding bin for coordinates outside extent of Earth
     *
     * @var array $bad_points
     */
    protected $bad_points = [];

    /**
     * Holding bin for WKT that fail to render
     *
     * @var array $bad_drawings
     */
    protected $bad_drawings = [];

    /**
     * Placeholder for presence of anything that might need a legend
     *
     * @var bool $_legend_required
     */
    private $_legend_required = false;

    /**
     * Get projection
     *
     * @param string $projection Projection expressed as epsg code
     *
     * @return string PROJ representation of projection
     */
    public static function getProjection($projection)
    {
        if (!array_key_exists($projection, AcceptedProjections::$projections)) {
            $projection = 'epsg:4326';
        }
        return AcceptedProjections::$projections[$projection]['proj'];
    }

    /**
     * Get shapefile config
     *
     * @return array shapefile config
     */
    public static function getShapefileConfig()
    {
        $config_file = file_get_contents(self::$shapefile_config);
        return self::_tokenizeShapefileConfig(Yaml::parse($config_file));
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        try {
            if (!extension_loaded('MapScript')) {
              throw new \Exception("PHP MapScript extension is not loaded"); 
            }
            $this->map_obj = ms_newMapObjFromString("MAP END");
            $this->request = (object)array_merge((array)$this->_defaultAttributes(), (array)$this->getRequest());
            $this->image_size = [$this->request->width, $this->request->height];
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Global call method to coordinate setters, getters.
     *
     * @param string $name      Name of the object.
     * @param array  $arguments Value for the object.
     *
     * @return object $this
     */
    public function __call($name, $arguments)
    {
        $property_prefix = substr($name, 0, 4);
        $property = substr($name, 4);
        if ($property_prefix == 'set_') {
            $this->{$property} = $arguments[0];
        } elseif ($property_prefix == 'add_') { // add to an array property
            array_push($this->{$property}, $arguments[0]);
        } elseif ($property_prefix == 'get_') { //get a property
            return $this->{$property};
        }
        return $this;
    }

    /**
     * Execute the process. This is the main method that calls other req'd and optional methods.
     *
     * @return object $this
     */
    public function execute()
    {
        $this->_loadOutputFormats();
        $this->_loadProjection();
        $this->_loadShapes();
        $this->_loadSymbols();
        $this->_setWebConfig();
        $this->_setResolution();
        $this->_setUnits();
        $this->_setMapColor();
        $this->_setOutputFormat();
        $this->_setMapExtent();
        $this->_setMapSize();
        $this->_setZoom();
        $this->_setPan();
        $this->_setRotation();
        $this->_setCrop();
        $this->_addLayers();
        $this->addRegions();
        $this->addWKT();
        $this->addGraticules();
        $this->addCoordinates();
        $this->_addWatermark();
        $this->_prepareOutput();

        return $this;
    }

    /**
     * Convert image coordinates to map coordinates
     *
     * @param obj $point (x,y) coordinates in pixels
     *
     * @return object $newPoint reprojected point in map coordinates
     */
    public function pix2Geo($point)
    {
        $newPoint = new \stdClass();
        $deltaX = abs($this->map_obj->extent->maxx - $this->map_obj->extent->minx);
        $deltaY = abs($this->map_obj->extent->maxy - $this->map_obj->extent->miny);

        $newPoint->x = $this->map_obj->extent->minx + ($point->x*$deltaX)/(float)$this->image_size[0];
        $newPoint->y = $this->map_obj->extent->miny + (((float)$this->image_size[1] - $point->y)*$deltaY)/(float)$this->image_size[1];
        return $newPoint;
    }

    /**
     * Get all the shapes
     *
     * @return object
     */
    public function getShapes()
    {
        return $this->shapes;
    }

    /**
     * Add all coordinates to the map
     *
     * @return void
     */
    public function addCoordinates()
    {
        $this->bad_points = [];
        if (isset($this->request->coords) && $this->request->coords) {
            //do this in reverse order because the legend will otherwise be presented in reverse order
            $count = count($this->request->coords)-1;
            for ($j=$count; $j>=0; $j--) {
                $title = "";
                $size = 8;
                $shape = "circle";
                $shadow = false;
                $color = [];
                $offset = 2;

                if ($this->request->coords[$j]['title']) {
                    $title = $this->request->coords[$j]['title'];
                }
                if ($this->request->coords[$j]['size']) {
                    $size = $this->request->coords[$j]['size'];
                }
                if ($this->_isResize() && $this->request->download_factor > 1) {
                    $size = $this->request->download_factor*$size;
                    $offset = $this->request->download_factor;
                }
                if (isset($this->request->coords[$j]['shape'])) {
                    $shape = $this->request->coords[$j]['shape'];
                }
                if (array_key_exists('shadow', $this->request->coords[$j])) {
                    $shadow = true;
                }
                if ($this->request->coords[$j]['color']) {
                    $color = explode(" ", $this->request->coords[$j]['color']);
                    if (count($color) != 3) {
                        $color = [];
                    }
                }

                $data = trim($this->request->coords[$j]['data']);

                if ($data) {
                    $this->_legend_required = true;
                    $layer = ms_newLayerObj($this->map_obj);
                    $layer->set("name", "layer_".$j);
                    $layer->set("status", MS_ON);
                    $layer->set("type", MS_LAYER_POINT);
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", 6);
                    $layer->setProjection(self::getProjection($this->default_projection));

                    $class = ms_newClassObj($layer);
                    if ($title != "") {
                        $class->set("name", trim(stripslashes($title)));
                    }

                    if ($shadow) {
                        $bstyle = ms_newStyleObj($class);
                        $bstyle->set("symbolname", $shape);
                        $bstyle->set("size", $size);
                        $bstyle->set("offsetx", $offset);
                        $bstyle->set("offsety", $offset);
                        $bstyle->color->setRGB(180, 180, 180);
                    }

                    $style = ms_newStyleObj($class);
                    $style->set("symbolname", $shape);
                    $style->set("size", $size);

                    if (!empty($color)) {
                        if (substr($shape, 0, 4) == 'open') {
                            $style->color->setRGB($color[0], $color[1], $color[2]);
                        } else {
                            $style->color->setRGB($color[0], $color[1], $color[2]);
                            $style->outlinecolor->setRGB(85, 85, 85);
                        }
                    } else {
                        $style->outlinecolor->setRGB(0, 0, 0);
                    }

                    $style->set("width", $this->_determineWidth());

                    $new_shape = ms_newShapeObj(MS_SHAPE_POINT);
                    $new_line = ms_newLineObj();

                    $rows = explode("\n", Utility::removeEmptyLines($data));
                    $points = [];

                    foreach ($rows as $row) {
                        $coord_array = Utility::makeCoordinates($row);
                        $coord = new \stdClass();
                        $coord->x = ($coord_array[1]) ? Utility::cleanCoord($coord_array[1]) : null;
                        $coord->y = ($coord_array[0]) ? Utility::cleanCoord($coord_array[0]) : null;
                        //only add point when data are good & have a title
                        if (Utility::onEarth($coord) && !empty($title)) {
                            if (!array_key_exists($coord->x.$coord->y, $points)) { //unique locations
                                $new_point = ms_newPointObj();
                                $new_point->setXY($coord->x, $coord->y);
                                $new_line->add($new_point);
                                $points[$coord->x.$coord->y] = [];
                            }
                        } else {
                            $this->bad_points[] = stripslashes($this->request->coords[$j]['title'] . ' : ' . $row);
                        }
                        unset($coord);
                    }

                    unset($points);
                    $new_shape->add($new_line);
                    $layer->addFeature($new_shape);
                }
            }
        }
    }

    /**
     * Add WKT layers to the map
     *
     * @return void
     */
    public function addWKT()
    {
        $this->bad_drawings = [];
        if (isset($this->request->wkt) && $this->request->wkt) {
            $count = count($this->request->wkt)-1;
            for ($j=$count; $j>=0; $j--) {
                $color = [];
                $border = false;
                $hatched = false;
                $title = "";
                if ($this->request->wkt[$j]['title']) {
                    $title = $this->request->wkt[$j]['title'];
                }
                if (array_key_exists('border', $this->request->wkt[$j])) {
                    $border = true;
                }
                if (array_key_exists('hatch', $this->request->wkt[$j])) {
                    $hatched = true;
                }
                if ($this->request->wkt[$j]['color']) {
                    $color = explode(" ", $this->request->wkt[$j]['color']);
                    if (count($color) != 3) {
                        $color = [];
                    }
                }

                $data = trim($this->request->wkt[$j]['data']);

                if ($data) {
                    $this->_legend_required = true;
                    $rows = explode("\n", Utility::removeEmptyLines($data));
                    foreach ($rows as $key => $row) {
                        if (strpos($row, "POINT") !== false) {
                            $type = MS_LAYER_POINT;
                        } elseif (strpos($row, "LINE") !== false) {
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
                        $class->set("name", trim(stripslashes($title)));
                        $style = ms_newStyleObj($class);
                        if ($type == MS_LAYER_POINT) {
                            $style->set("symbolname", 'circle');
                            $style->set("size", 8);
                        }
                        if ($type == MS_LAYER_POLYGON && $border) {
                            $style->outlinecolor->setRGB(0, 0, 0);
                            $style->set("width", $this->_determineWidth());
                        }
                        if (!empty($color)) {
                            $style->color->setRGB($color[0], $color[1], $color[2]);
                        }
                        $style->set("opacity", 75);

                        if ($hatched && !empty($color)) {
                            $style = ms_newStyleObj($class);
                            $style->set("symbolname", "hatch");
                            $style->set("angle", 45);
                            $style->set("size", 5);
                            $style->color->setRGB($color[0]-100, $color[1]-100, $color[2]-100);
                        }

                        try {
                            $shape = ms_shapeObjFromWkt($row);
                            $layer->addFeature($shape);
                        } catch (\Exception $e) {
                            $this->bad_drawings[] = $title;
                        }
                    }
                }
            }
        }
    }

    /**
     * Add shaded regions to the map
     *
     * @return void
     */
    public function addRegions()
    {
        if (isset($this->request->regions) && $this->request->regions) {
            $count = count($this->request->regions)-1;
            for ($j=$count; $j>=0; $j--) {
                //clear out previous loop's selection
                $color = [];
                $title = "";
                $hatched = false;
                if ($this->request->regions[$j]['title']) {
                    $title = $this->request->regions[$j]['title'];
                }
                if ($this->request->regions[$j]['color']) {
                    $color = explode(" ", $this->request->regions[$j]['color']);
                    if (count($color) != 3) {
                        $color = [];
                    }
                }
                if (array_key_exists('hatch', $this->request->regions[$j])) {
                    $hatched = true;
                }

                $data = trim($this->request->regions[$j]['data']);

                if ($data) {
                    $this->_legend_required = true;
                    $baselayer = true;
                    //grab the textarea for regions & split
                    $rows = explode("\n", Utility::removeEmptyLines($data));
                    $qry = [];
                    foreach ($rows as $row) {
                        $regions = preg_split("/[,;]+/", $row); //split by a comma, semicolon
                        foreach ($regions as $region) {
                            if ($region) {
                                $pos = strpos($region, '[');
                                if ($pos !== false) {
                                    $baselayer = false;
                                    $split = explode("[", str_replace("]", "", trim(strtoupper($region))));
                                    $states = preg_split("/[\s|]+/", $split[1]);
                                    $statekey = [];
                                    foreach ($states as $state) {
                                        $statekey[] = "'[code_hasc]' ~* '\.".$state."$'";
                                    }
                                    $qry['stateprovince'][] = "'[adm0_a3]' = '".trim($split[0])."' && (".implode(" || ", $statekey).")";
                                    $qry['country'][] = "'[iso_a3]' = '".trim($split[0])."'";
                                } else {
                                    $region = addslashes(trim($region));
                                    $qry['stateprovince'][] = "'[name]' ~* '".$region."$'";
                                    $qry['country'][] = "'[NAME]' ~* '".$region."$' || '[NAME_LONG]' ~* '".$region."$' || '[GEOUNIT]' ~* '".$region."$' || '[FORMAL_EN]' ~* '".$region."$'";
                                }
                            }
                        }
                    }

                    $layer = ms_newLayerObj($this->map_obj);
                    $layer->set("name", "query_layer_".$j);

                    if ($baselayer) {
                        $layer->set("data", $this->shapes['countries']['path']);
                        $layer->set("type", MS_LAYER_POLYGON);
                    } else {
                        $layer->set("data", $this->shapes['stateprovinces_polygon']['path']);
                        $layer->set("type", $this->shapes['stateprovinces_polygon']['type']);
                    }

                    $layer->set("template", "template.html");
                    $layer->setProjection(self::getProjection($this->default_projection));

                    $query = ($baselayer) ? $qry['country'] : $qry['stateprovince'];

                    $layer->setFilter("(".implode(" || ", $query).")");

                    $class = ms_newClassObj($layer);
                    $class->set("name", trim(stripslashes($title)));

                    $style = ms_newStyleObj($class);
                    if (!empty($color)) {
                        $style->color->setRGB($color[0], $color[1], $color[2]);
                    }
                    $style->outlinecolor->setRGB(30, 30, 30);
                    $style->set("opacity", 40);
                    $style->set("width", $this->_determineWidth());

                    if ($hatched && !empty($color)) {
                        $style = ms_newStyleObj($class);
                        $style->set("symbolname", "hatch");
                        $style->set("angle", 45);
                        $style->set("size", 5);
                        $style->color->setRGB($color[0]-100, $color[1]-100, $color[2]-100);
                    }

                    $layer->set("status", MS_ON);
                }
            }
        }
    }

    /**
     * Add graticules (or grid lines) to map
     *
     * @return void
     */
    public function addGraticules()
    {
        if (isset($this->request->graticules) && $this->request->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(self::getProjection($this->default_projection));

            $class = ms_newClassObj($layer);

            $label = new \labelObj();
            if (isset($this->request->hide_gridlabel) && $this->request->hide_gridlabel) {
                $label->color->setRGB(255, 255, 255, 0);
            } else {
                $label->set("font", "arial");
                $label->set("encoding", "UTF-8");
                $size = 10;
                if ($this->_isResize() && $this->request->download_factor > 1) {
                    $size = $this->request->download_factor*9;
                }
                $label->set("size", $size);
                $label->set("position", MS_CC);
                $label->color->setRGB(30, 30, 30);
            }
            $class->addLabel($label);

            $style = ms_newStyleObj($class);
            $style->color->setRGB(200, 200, 200);

            $minx = $this->map_obj->extent->minx;
            $maxx = $this->map_obj->extent->maxx;

            //project the extent back to default such that we can work with proper tick marks
            if ($this->request->projection != $this->default_projection
                && $this->request->projection == $this->request->projection_map
            ) {
                $origProjObj = ms_newProjectionObj(self::getProjection($this->request->projection));
                $newProjObj = ms_newProjectionObj(self::getProjection($this->default_projection));

                $poPoint1 = ms_newPointObj();
                $poPoint1->setXY($this->map_obj->extent->minx, $this->map_obj->extent->miny);

                $poPoint2 = ms_newPointObj();
                $poPoint2->setXY($this->map_obj->extent->maxx, $this->map_obj->extent->maxy);

                $poPoint1->project($origProjObj, $newProjObj);
                $poPoint2->project($origProjObj, $newProjObj);

                $minx = $poPoint1->x;
                $maxx = $poPoint2->x;
            }

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

            $labelformat = ($this->request->gridspace) ? "DD" : $labelformat;
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
     * Create the legend file
     *
     * @return void
     */
    public function addLegend()
    {
        if ($this->_legend_required) {
            $keysize = 20;
            $keyspacing = 5;
            $size = 10;
            if ($this->_isResize() 
                && $this->request->download_factor > 1
            ) {
                $keysize = $this->request->download_factor*15;
                $keyspacing = $this->request->download_factor*3;
                $size = $this->request->download_factor*8;
            }
            $this->map_obj->legend->set("keysizex", $keysize);
            $this->map_obj->legend->set("keysizey", $keysize);
            $this->map_obj->legend->set("keyspacingx", $keyspacing);
            $this->map_obj->legend->set("keyspacingy", $keyspacing);
            $this->map_obj->legend->set("postlabelcache", 1);
            $this->map_obj->legend->label->set("font", "arial");
            $this->map_obj->legend->label->set("encoding", "UTF-8");
            $this->map_obj->legend->label->set("position", 1);
            $this->map_obj->legend->label->set("size", $size);
            $this->map_obj->legend->label->color->setRGB(0, 0, 0);

            //svg format cannot do legends in MapServer
            if ($this->request->download 
                && $this->request->options['legend'] 
                && $this->request->output != 'svg'
            ) {
                $this->map_obj->legend->set("status", MS_EMBED);
                $this->map_obj->legend->set("position", MS_UR);
                $this->map_obj->drawLegend();
            }
            if (!$this->request->download) {
                $this->map_obj->legend->set("status", MS_DEFAULT);
                $this->legend = $this->map_obj->drawLegend();
                $this->legend_url = $this->legend->saveWebImage();
            }
        }
    }

    /**
     * Create a scalebar image
     *
     * @return void
     */
    public function addScalebar()
    {
        $height = 8;
        $width = 200;
        $size = 8;
        if ($this->_isResize() && $this->request->download_factor > 1) {
            $height = $this->request->download_factor*4;
            $width = $this->request->download_factor*100;
            $size = $this->request->download_factor*5;
        }
        $this->map_obj->scalebar->set("style", 0);
        $this->map_obj->scalebar->set("intervals", 3);
        $this->map_obj->scalebar->set("height", $height);
        $this->map_obj->scalebar->set("width", $width);
        $this->map_obj->scalebar->color->setRGB(30, 30, 30);
        $this->map_obj->scalebar->outlinecolor->setRGB(0, 0, 0);
        $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->map_obj->scalebar->label->set("font", "arial");
        $this->map_obj->scalebar->label->set("encoding", "UTF-8");
        $this->map_obj->scalebar->label->set("size", $size);
        $this->map_obj->scalebar->label->color->setRGB(0, 0, 0);

        //svg format cannot do scalebar in MapServer
        if ($this->request->download 
            && $this->request->options['scalebar'] 
            && $this->request->output != 'svg'
        ) {
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
        if (!$this->request->download) {
            $this->map_obj->scalebar->set("status", MS_DEFAULT);
            $this->scale = $this->map_obj->drawScalebar();
            $this->scalebar_url = $this->scale->saveWebImage();
        }
    }

    /**
     * Get all the coordinates that fall outside Earth's geographic extent in dd
     *
     * @return string
     */
    public function getBadPoints()
    {
        return implode('<br />', $this->bad_points);
    }

    /**
     * Get all the coordinates that fall outside Earth's geographic extent in dd
     *
     * @return string
     */
    public function getBadDrawings()
    {
        return implode('<br />', $this->bad_drawings);
    }

    /**
     * Default attributes for the request object
     *
     * @return obj
     */
    private function _defaultAttributes()
    {
        $attr = new \stdClass();
        $attr->coords           = [];
        $attr->regions          = [];
        $attr->wkt              = [];
        $attr->output           = 'png';
        $attr->width            = 900;
        $attr->height           = $attr->width/2;
        $attr->projection       = 'epsg:4326';
        $attr->projection_map   = 'epsg:4326';
        $attr->origin           = false;
        $attr->bbox_map         = '-180,-90,180,90';
        $attr->bbox_rubberband  = [];
        $attr->pan              = false;
        $attr->layers           = [];
        $attr->graticules       = false;
        $attr->watermark        = false;
        $attr->gridspace        = false;
        $attr->gridlabel        = true;
        $attr->download         = false;
        $attr->crop             = false;
        $attr->options          = []; //scalebar, legend, border, linethickness
        $attr->border_thickness = 1.25;
        $attr->rotation         = 0;
        $attr->zoom_in          = false;
        $attr->zoom_out         = false;
        $attr->download_factor  = 1;
        $attr->file_name        = time();

        return $attr;
    }

    /**
     * Load the accepted output formats from the AcceptedOutputs trait
     *
     * @return void
     */
    private function _loadOutputFormats()
    {
        foreach (AcceptedOutputs::$outputs as $output) {
            $format = new \outputFormatObj($output["driver"], $output["name"]);
            foreach ($output as $key => $value) {
                if ($key != "formatoptions") {
                    $format->set($key, $value);
                } else {
                    foreach ($value as $options) {
                        $option = explode("=", $options);
                        $format->setOption($option[0], $option[1]);
                    }
                }
            }
            $this->map_obj->appendOutputFormat($format);
        }
    }

    /**
     * Set the projection
     *
     * @return void
     */
    private function _loadProjection()
    {
        $projection = AcceptedProjections::$projections[$this->default_projection]['proj'];
        $this->map_obj->setProjection($projection, true);
    }

    /**
     * Load-up all the settings for potential shapes
     *
     * @return void
     */
    private function _loadShapes()
    {
        $this->shapes = self::getShapefileConfig();
    }

    /**
     * Tokenize shapefile.yml config
     *
     * @param array $config Array of configuration elements
     *
     * @return array of config
     */
    private static function _tokenizeShapefileConfig($config)
    {
        $config = $config['environments'][ENVIRONMENT];
        $config = array_merge($config['layers'], $config['labels']);
        $pattern = '/%%(.+)%%(.+)?/';
        foreach ($config as $shape => $values) {
            foreach ($values as $key => $value) {
                $config[$shape][$key] = preg_replace_callback(
                    $pattern, function ($matches) {
                        if (isset($matches[2])) {
                            return constant($matches[1]) . $matches[2];
                        } else {
                            return constant($matches[1]);
                        }
                    }, $value
                );
            }
        }
        return $config;
    }

    /**
     * Load-up all the symbols
     *
     * @return void
     */
    private function _loadSymbols()
    {
        // Add hatch symbol
        $nId = ms_newSymbolObj($this->map_obj, "hatch");
        $symbol = $this->map_obj->getSymbolObjectById($nId);
        $symbol->set("type", MS_SYMBOL_HATCH);
        $symbol->set("filled", FALSE);
        $symbol->set("inmapfile", MS_TRUE);

        foreach (AcceptedMarkers::$shapes as $type => $style) {
            $fill = MS_FALSE;
            if ($type == 'closed') {
                $fill = MS_TRUE;
            }
            foreach ($style as $name => $settings) {
                $type = MS_SYMBOL_VECTOR;
                if (strpos($name, 'circle') !== false) {
                    $type = MS_SYMBOL_ELLIPSE;
                }
                $vertices = AcceptedMarkers::vertices($settings['style']);
                $this->_createSymbol($name, $type, $fill, $vertices);
            }
        }
    }

    /**
     * Create a symbol
     *
     * @param string $name     The name of the symbol
     * @param string $type     The type of the symbol
     * @param string $fill     MS_TRUE or MS_FALSE
     * @param array  $vertices The vertices
     *
     * @return void
     */
    private function _createSymbol($name, $type, $fill, $vertices)
    {
        $nId = ms_newSymbolObj($this->map_obj, $name);
        $symbol = $this->map_obj->getSymbolObjectById($nId);
        $symbol->set("type", $type);
        $symbol->set("filled", $fill);
        $symbol->set("inmapfile", MS_TRUE);
        $symbol->setpoints($vertices);
    }

    /**
     * Set config for web object, storage of tmp files
     *
     * @return void
     */
    private function _setWebConfig()
    {
        $this->map_obj->set("name", "simplemappr");
        $this->map_obj->setFontSet($this->font_file);
        $this->map_obj->web->set("template", "template.html");
        $this->map_obj->web->set("imagepath", $this->tmp_path);
        $this->map_obj->web->set("imageurl", $this->tmp_url);
    }

    /**
     * Set resolution
     *
     * @return void
     */
    private function _setResolution()
    {
        if ($this->request->output == 'tif') {
            $this->map_obj->set("defresolution", 300);
            $this->map_obj->set("resolution", 300);
        }
    }

    /**
     * Set units
     *
     * @return void
     */
    private function _setUnits()
    {
        $units = MS_METERS;
        if (isset($this->request->projection)
            && $this->request->projection == $this->default_projection
        ) {
            $units = MS_DD;
        }
        $this->map_obj->set("units", $units);
    }

    /**
     * Set map color
     *
     * @return void
     */
    private function _setMapColor()
    {
        $this->map_obj->imagecolor->setRGB(255, 255, 255);
    }

    /**
     * Set output format
     *
     * @return void
     */
    private function _setOutputFormat()
    {
        $this->map_obj->selectOutputFormat('png');
        if (isset($this->request->output) && $this->request->output) {
            $output = $this->request->output;
            if ($this->request->output == 'pptx' 
                || $this->request->output == 'docx'
                || !in_array($output, AcceptedOutputs::outputList())
            ) {
                $output = 'png';
            }
            $this->map_obj->selectOutputFormat($output);
        }
    }

    /**
     * Add legend and scalebar
     *
     * @return void
     */
    private function _addLegendScalebar()
    {
        if ($this->request->download
            && array_key_exists('legend', $this->request->options)
            && $this->request->options['legend']
        ) {
            $this->addLegend();
        } elseif (!$this->request->download) {
            $this->addLegend();
        }
        if ($this->request->download
            && array_key_exists('scalebar', $this->request->options)
            && $this->request->options['scalebar']
        ) {
            $this->addScalebar();
        } elseif (!$this->request->download) {
            $this->addScalebar();
        }
    }

    /**
     * Set the map extent
     *
     * @return void
     */
    private function _setMapExtent()
    {
        $ext = explode(',', $this->request->bbox_map);
        if (isset($this->request->projection) 
            && $this->request->projection != $this->request->projection_map
        ) {
            $origProjObj = ms_newProjectionObj(self::getProjection($this->request->projection_map));
            $newProjObj = ms_newProjectionObj(self::getProjection($this->default_projection));

            $poPoint1 = ms_newPointObj();
            $poPoint1->setXY($ext[0], $ext[1]);

            $poPoint2 = ms_newPointObj();
            $poPoint2->setXY($ext[2], $ext[3]);

            $poPoint1->project($origProjObj, $newProjObj);
            $poPoint2->project($origProjObj, $newProjObj);

            $ext[0] = $poPoint1->x;
            $ext[1] = $poPoint1->y;
            $ext[2] = $poPoint2->x;
            $ext[3] = $poPoint2->y;

            if ($poPoint1->x < $this->max_extent[0]
                || $poPoint1->y < $this->max_extent[1]
                || $poPoint2->x > $this->max_extent[2]
                || $poPoint2->y > $this->max_extent[3]
                || $poPoint1->x > $poPoint2->x
                || $poPoint1->y > $poPoint2->y
            ) {
                $ext[0] = $this->max_extent[0];
                $ext[1] = $this->max_extent[1];
                $ext[2] = $this->max_extent[2];
                $ext[3] = $this->max_extent[3];
            }
        }

        $ext0 = (min($ext[0], $ext[2]) == $ext[0]) ? $ext[0] : $ext[2];
        $ext2 = (max($ext[0], $ext[2]) == $ext[2]) ? $ext[2] : $ext[0];
        $ext1 = (min($ext[1], $ext[3]) == $ext[1]) ? $ext[1] : $ext[3];
        $ext3 = (max($ext[1], $ext[3]) == $ext[3]) ? $ext[3] : $ext[1];

        // Set the padding correction factors because final extent produced after draw() is off from setExtent
        $cellsize = max(($ext2 - $ext0)/($this->image_size[0]-1), ($ext3 - $ext1)/($this->image_size[1]-1));

        if ($cellsize > 0) {
            $ox = max((($this->image_size[0]-1) - ($ext2 - $ext0)/$cellsize)/2, 0);
            $oy = max((($this->image_size[1]-1) - ($ext3 - $ext1)/$cellsize)/2, 0);
            $this->ox_pad = $ox*$cellsize;
            $this->oy_pad = $oy*$cellsize;
        }

        $this->map_obj->setExtent($ext0, $ext1, $ext2, $ext3);
    }

    /**
     * Set the map size
     *
     * @return void
     */
    private function _setMapSize()
    {
        $width = $this->image_size[0];
        $height = $this->image_size[1];
        if ($this->_isResize()) {
            $width = $this->request->download_factor*$this->image_size[0];
            $height = $this->request->download_factor*$this->image_size[1];
        }
        $this->map_obj->setSize($width, $height);
    }

    /**
     * Zoom In
     *
     * @return void
     */
    private function _setZoom()
    {
        //Zoom in
        if (isset($this->request->bbox_rubberband) && $this->request->bbox_rubberband && !$this->_isResize()) {
            $bbox_rubberband = explode(',', $this->request->bbox_rubberband);
            if ($bbox_rubberband[0] == $bbox_rubberband[2] || $bbox_rubberband[1] == $bbox_rubberband[3]) {
                $zoom_point = ms_newPointObj();
                $zoom_point->setXY($bbox_rubberband[0], $bbox_rubberband[1]);
                $this->map_obj->zoompoint(2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
            } else {
                $zoom_rect = ms_newRectObj();
                $zoom_rect->setExtent($bbox_rubberband[0], $bbox_rubberband[3], $bbox_rubberband[2], $bbox_rubberband[1]);
                $this->map_obj->zoomrectangle($zoom_rect, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
            }
        }

        //Auto Zoom in
        if (isset($this->request->zoom_in) && $this->request->zoom_in) {
            $zoom_point = ms_newPointObj();
            $zoom_point->setXY($this->map_obj->width/2, $this->map_obj->height/2);
            $this->map_obj->zoompoint(2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
        }

        //Zoom out
        if (isset($this->request->zoom_out) && $this->request->zoom_out) {
            $zoom_point = ms_newPointObj();
            $zoom_point->setXY($this->map_obj->width/2, $this->map_obj->height/2);
            $this->map_obj->zoompoint(-2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
        }
    }

    /**
     * Set the pan direction
     *
     * @return void
     */
    private function _setPan()
    {
        if (isset($this->request->pan) && $this->request->pan) {
            switch ($this->request->pan) {
            case 'up':
                $x_offset = 1;
                $y_offset = 0.9;
                break;

            case 'right':
                $x_offset = 1.1;
                $y_offset = 1;
                break;

            case 'down':
                $x_offset = 1;
                $y_offset = 1.1;
                break;

            case 'left':
                $x_offset = 0.9;
                $y_offset = 1;
                break;
            }

            $new_point = ms_newPointObj();
            $new_point->setXY($this->map_obj->width/2*$x_offset, $this->map_obj->height/2*$y_offset);
            $this->map_obj->zoompoint(1, $new_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
        }
    }

    /**
     * Set the rotation
     *
     * @return void
     */
    private function _setRotation()
    {
        if (isset($this->request->rotation) && $this->request->rotation != 0) {
            $this->map_obj->setRotation($this->request->rotation);
            if ($this->request->projection == $this->default_projection) {
                $this->_reproject($this->request->projection);
            }
        }
    }

    /**
     * Set a new extent in the event of a crop action
     *
     * @return void
     */
    private function _setCrop()
    {
        if (isset($this->request->crop) 
            && $this->request->crop 
            && $this->request->bbox_rubberband 
            && $this->_isResize()
        ) {
            $bbox_rubberband = explode(',', $this->request->bbox_rubberband);

            //lower-left coordinate
            $ll_point = new \stdClass();
            $ll_point->x = $bbox_rubberband[0];
            $ll_point->y = $bbox_rubberband[3];
            $ll_coord = $this->pix2Geo($ll_point);

            //upper-right coordinate
            $ur_point = new \stdClass();
            $ur_point->x = $bbox_rubberband[2];
            $ur_point->y = $bbox_rubberband[1];
            $ur_coord = $this->pix2Geo($ur_point);

            //set the size as selected
            $width = abs($bbox_rubberband[2]-$bbox_rubberband[0]);
            $height = abs($bbox_rubberband[3]-$bbox_rubberband[1]);

            $this->map_obj->setSize($width, $height);
            if ($this->_isResize() && $this->request->download_factor > 1) {
                $width = $this->request->download_factor*$width;
                $height = $this->request->download_factor*$height;
                $this->map_obj->setSize($width, $height);
            }

            //set the extent to match that of the crop
            $this->map_obj->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);
        }
    }

    /**
     * Add all selected layers to the map
     *
     * @return void
     */
    private function _addLayers()
    {
        $sort = [];

        $this->request->layers['base'] = 'on';
        unset($this->request->layers['grid']);

        foreach ($this->request->layers as $key => $row) {
            if (isset($this->request->output)
                && $this->request->output == 'svg'
                && $this->shapes[$key]['type'] == MS_LAYER_RASTER
            ) {
                unset($this->request->layers[$key]);
            } else {
                $sort[$key] = (isset($this->shapes[$key])) ? $this->shapes[$key]['sort'] : $row;
            }
        }
        array_multisort($sort, SORT_ASC, $this->request->layers);

        $srs_projections = implode(array_keys(AcceptedProjections::$projections), " ");

        foreach ($this->request->layers as $name => $status) {
            if (array_key_exists($name, $this->shapes)) {
                $layer = ms_newLayerObj($this->map_obj);
                $layer->set("name", $name);
                $layer->setMetaData("wfs_title", $name);
                $layer->setMetaData("wfs_typename", $name);
                $layer->setMetaData("wfs_srs", $srs_projections);
                $layer->setMetaData("wfs_extent", "-180 -90 180 90");
                $layer->setMetaData("wfs_encoding", $this->shapes[$name]['encoding']);
                $layer->setMetaData("wms_title", $name);
                $layer->setMetaData("wms_srs", $srs_projections);
                $layer->setMetaData("gml_include_items", "all");
                $layer->setMetaData("gml_featureid", "OBJECTID");
                $layer->set("type", $this->shapes[$name]['type']);
                $layer->set("status", MS_ON);
                $layer->setConnectionType(MS_SHAPEFILE);
                $layer->set("data", $this->shapes[$name]['path']);
                $layer->setProjection(self::getProjection($this->default_projection));
                $layer->set("template", "template.html");
                $layer->set("dump", true);

                if (isset($this->shapes[$name]['opacity'])) {
                    $layer->set("opacity", (int)$this->shapes[$name]['opacity']);
                }

                if (isset($this->shapes[$name]['class'])) {
                    $classitem = $this->shapes[$name]['class']['item'];
                    $layer->set("classitem", $classitem);
                    $this->_setSLDClasses($layer, $this->shapes[$name]['class']['sld'], $classitem);
                    $this->_legend_required = true;
                }

                if (isset($this->shapes[$name]['tolerance'])) {
                    $layer->set("tolerance", (int)$this->shapes[$name]['tolerance']);
                }

                if (isset($this->shapes[$name]['tolerance_units'])) {
                    $layer->set("toleranceunits", $this->shapes[$name]['tolerance_units']);
                }

                if (isset($this->shapes[$name]['label'])) {
                    $layer->set("labelitem", $this->shapes[$name]['label']['item']);
                }

                $class = ms_newClassObj($layer);
                $style = ms_newStyleObj($class);

                if (isset($this->shapes[$name]['legend'])) {
                    $class->set("name", $this->shapes[$name]['legend']);
                    $this->_legend_required = true;
                }

                if (isset($this->shapes[$name]['outline_color'])) {
                    $color = explode(",", $this->shapes[$name]['outline_color']);
                    $style->outlinecolor->setRGB($color[0], $color[1], $color[2]);
                }

                if (isset($this->shapes[$name]['color'])) {
                    $color = explode(",", $this->shapes[$name]['color']);
                    $style->color->setRGB($color[0], $color[1], $color[2]);
                }

                if (isset($this->shapes[$name]['dynamic_width'])) {
                    $style->set("width", $this->_determineWidth());
                }

                if (isset($this->shapes[$name]['label'])) {
                    $class->addLabel($this->_createLabel((int)$this->shapes[$name]['label']['size'], $this->shapes[$name]['label']['position'], $this->shapes[$name]['encoding']));
                }

                if (isset($this->shapes[$name]['symbol'])) {
                    $style->set("symbolname", $this->shapes[$name]['symbol']['shape']);
                    $size = $this->shapes[$name]['symbol']['size'];
                    $style->set("size", ($this->_isResize() && $this->request->download_factor > 1) ? $this->request->download_factor*($size+1) : $size);
                    $color = explode(",", $this->shapes[$name]['symbol']['color']);
                    $style->color->setRGB($color[0], $color[1], $color[2]);
                }
            }
        }
    }

    /**
     * Make label for layers
     *
     * @param int    $size     The pixel size for the label.
     * @param int    $position The position of the label using MapScript constant
     * @param string $encoding The encoding
     *
     * @return object $label
     */
    private function _createLabel($size = 8, $position = MS_UR, $encoding = "CP1252")
    {
        $label = new \labelObj();
        $label->set("font", "arial");
        $label->set("encoding", $encoding);
        if ($this->_isResize() && $this->request->download_factor > 1) {
            $size = $this->request->download_factor*7;
        }
        $label->set("size", $size);
        $label->set("position", $position);
        $label->set("offsetx", 3);
        $label->set("offsety", 3);
        $label->set("partials", MS_FALSE);
        $label->color->setRGB(10, 10, 10);
        return $label;
    }

    /**
     * Add a watermark
     *
     * @return void
     */
    private function _addWatermark()
    {
        if (isset($this->request->watermark) && $this->request->watermark) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'watermark');
            $layer->set("type", MS_LAYER_POINT);
            $layer->set("status", MS_ON);
            $layer->set("transform", MS_FALSE);
            $layer->set("sizeunits", MS_PIXELS);

            $class = ms_newClassObj($layer);
            $class->settext(MAPPR_URL);

            $label = new \labelObj();
            $label->set("font", "arial");
            $label->set("encoding", "UTF-8");
            $label->set("size", 8);
            $label->set("position", MS_XY);
            $label->color->setRGB(10, 10, 10);
            $class->addLabel($label);

            $shape = ms_newShapeObj(MS_SHAPE_POINT);
            $line = ms_newLineObj();

            $point = ms_newPointObj();
            $point->setXY(2, $this->map_obj->height-2);

            $line->add($point);
            $shape->add($line);
            $layer->addFeature($shape);
        }
    }

    /**
     * Discover if the output ought to be resized
     *
     * @return bool
     */
    private function _isResize()
    {
        if ($this->request->download
            || $this->request->output == 'pptx'
            || $this->request->output == 'docx'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Add a border to a downloaded map image
     *
     * @return void
     */
    private function _addBorder()
    {
        if ($this->_isResize()
            && array_key_exists('border', $this->request->options)
            && ($this->request->options['border'] == 1 || $this->request->options['border'] == 'true')
        ) {
            $outline_layer = ms_newLayerObj($this->map_obj);
            $outline_layer->set("name", "outline");
            $outline_layer->set("type", MS_LAYER_POLYGON);
            $outline_layer->set("status", MS_ON);
            $outline_layer->set("transform", MS_FALSE);
            $outline_layer->set("sizeunits", MS_PIXELS);

            // Add new class to new layer
            $outline_class = ms_newClassObj($outline_layer);

            // Add new style to new class
            $outline_style = ms_newStyleObj($outline_class);
            $outline_style->outlinecolor->setRGB(0, 0, 0);
            $outline_style->set("width", 3);

            $polygon = ms_newShapeObj(MS_SHAPE_POLYGON);

            $polyLine = ms_newLineObj();
            $polyLine->addXY(0, 0);
            $polyLine->addXY($this->map_obj->width, 0);
            $polyLine->addXY($this->map_obj->width, $this->map_obj->height);
            $polyLine->addXY(0, $this->map_obj->height);
            $polyLine->addXY(0, 0);

            $polygon->add($polyLine);
            $outline_layer->addFeature($polygon);
        }
    }

    /**
     * Prepare the output.
     *
     * @return void
     */
    private function _prepareOutput()
    {
        if (isset($this->request->projection)) {
            $this->_reproject($this->request->projection);
            $this->_addLegendScalebar();
            $this->_addBorder();
            $this->image = $this->map_obj->drawQuery();
        }
    }

    /**
     * Reproject a $map from one projection to another.
     *
     * @param string $output_projection The output projection.
     *
     * @return void
     */
    private function _reproject($output_projection)
    {
        $this->_setOrigin($output_projection);
        $this->map_obj->setProjection(self::getProjection($output_projection), true);
    }

    /**
     * Change the longitude of the natural origin for Lambert projections.
     *
     * @param string $output_projection The output projection.
     *
     * @return void
     */
    private function _setOrigin($output_projection)
    {
        $lambert_projections = [
            'esri:102009',
            'esri:102015',
            'esri:102014',
            'esri:102102',
            'esri:102024',
            'epsg:3112'
        ];
        if (in_array($this->request->projection, $lambert_projections)
            && $this->request->origin
            && $this->request->origin >= -180
            && $this->request->origin <= 180
        ) {
            $proj = preg_replace('/lon_0=(.*?),/', 'lon_0='.$this->request->origin.',', self::getProjection($output_projection));
            AcceptedProjections::$projections[$output_projection]['proj'] = $proj;
        }
    }

    /**
     * Build ecoregion layer classes from SLD file
     *
     * @param obj    $layer The MapScript layer object.
     * @param string $sld   The full path to an SLD file
     * @param string $item  The term filtered on in the SLD
     *
     * @return void
     */
    private function _setSLDClasses($layer, $sld, $item)
    {
        $xml = simplexml_load_file($sld);
        $xml->registerXPathNamespace('sld', 'http://www.opengis.net/sld');
        $xml->registerXPathNamespace('se', 'http://www.opengis.net/se');
        $xml->registerXPathNamespace('ogc', 'http://www.opengis.net/ogc');

        //version 1.0.0 of SLD
        foreach ($xml->xpath('//sld:Rule') as $rule) {
            $class = ms_newClassObj($layer);
            $class->setExpression("([".$item."] = ".(string)$rule->xpath('.//ogc:Literal')[0].")");
            $style = ms_newStyleObj($class);
            $color = Utility::hex2Rgb((string)$rule->xpath('.//sld:CssParameter[@name="fill"]')[0]);
            $style->color->setRGB($color[0], $color[1], $color[2]);
            $style->outlinecolor->setRGB(30, 30, 30);
        }

        //version 1.1.0 of SLD
        foreach ($xml->xpath('//se:Rule') as $rule) {
            $class = ms_newClassObj($layer);
            $class->setExpression("([".$item."] = ".(string)$rule->xpath('.//se:Name')[0].")");
            $style = ms_newStyleObj($class);
            $color = Utility::hex2Rgb((string)$rule->xpath('.//se:SvgParameter')[0]);
            $style->color->setRGB($color[0], $color[1], $color[2]);
            $style->outlinecolor->setRGB(30, 30, 30);
        }
    }

    /**
     * Get the scaled width for a layer's line
     *
     * @return number $width
     */
    private function _determineWidth()
    {
        $width = 1.25;
        if (isset($this->request->border_thickness)) {
            $width = $this->request->border_thickness;
            if ($this->_isResize()
                && $this->request->download_factor > 1
                && array_key_exists('scalelinethickness', $this->request->options)
                && $this->request->options['scalelinethickness']
            ) {
                $width = $this->request->border_thickness*$this->request->download_factor/2;
            }
        }
        return $width;
    }
}
