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

use \Symfony\Component\Yaml\Yaml;

/**
 * Main Mappr class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
abstract class Mappr
{
    /**
     * Create output, required for all extended classes
     *
     * @return void
     */
    abstract function createOutput();

    /* the base map object */
    protected $map_obj;

    /* path to the font file */
    protected $font_file;

    /* file system temp path to store files produced */ 
    protected $tmp_path = '/tmp';

    /* url temp path to retrieve files produced */
    protected $tmp_url = '/tmp';

    /* default extent when map first loaded */
    protected $max_extent = array(-180,-90,180,90);

    /* default projection when map first loaded */
    protected $default_projection = 'epsg:4326';

    protected $image;

    protected $scale;

    protected $legend;

    /* shapes and their mapfile configurations */
    protected $shapes = array();

    /* post-draw padding for longitude extent to be used as a correction factor on front-end */
    protected $ox_pad = 0;

    /* post-draw padding for latitude extent to be used as a correction factor on front-end */
    protected $oy_pad = 0;

    /* url for legend image if produced */
    protected $legend_url;

    /* url for scalebar image if produced */
    protected $scalebar_url;

    /* holding bin for any geographic coordinates that fall outside extent of Earth */
    protected $bad_points = array();

    /* Initial mapfile as string */
    protected $mapfile_string = "
        MAP

            OUTPUTFORMAT
                NAME png
                DRIVER AGG/PNG
                IMAGEMODE RGB
                MIMETYPE 'image/png'
                EXTENSION 'png'
                FORMATOPTION 'INTERLACE=OFF'
                FORMATOPTION 'COMPRESSION=9'
            END

            OUTPUTFORMAT
                NAME png_download
                DRIVER AGG/PNG
                IMAGEMODE RGB
                MIMETYPE 'image/png'
                EXTENSION 'png'
                FORMATOPTION 'INTERLACE=OFF'
                FORMATOPTION 'COMPRESSION=9'
            END

            OUTPUTFORMAT
                NAME pnga
                DRIVER AGG/PNG
                IMAGEMODE RGB
                MIMETYPE 'image/png'
                EXTENSION 'png'
                FORMATOPTION 'INTERLACE=OFF'
                FORMATOPTION 'COMPRESSION=9'
            END

            OUTPUTFORMAT
                NAME pnga_download
                DRIVER AGG/PNG
                IMAGEMODE RGB
                MIMETYPE 'image/png'
                EXTENSION 'png'
                FORMATOPTION 'INTERLACE=OFF'
                FORMATOPTION 'COMPRESSION=9'
            END

            OUTPUTFORMAT
                NAME pnga_transparent
                DRIVER AGG/PNG
                IMAGEMODE RGBA
                MIMETYPE 'image/png'
                EXTENSION 'png'
                TRANSPARENT ON
                FORMATOPTION 'INTERLACE=OFF'
                FORMATOPTION 'COMPRESSION=9'
            END

            OUTPUTFORMAT
                NAME jpg
                DRIVER AGG/JPEG
                IMAGEMODE RGB
                MIMETYPE 'image/jpeg'
                EXTENSION 'jpg'
                FORMATOPTION 'QUALITY=95'
            END

            OUTPUTFORMAT
                NAME jpga
                DRIVER AGG/JPEG
                IMAGEMODE RGB
                MIMETYPE 'image/jpeg'
                EXTENSION 'jpg'
                FORMATOPTION 'QUALITY=95'
            END

            OUTPUTFORMAT
                NAME tif
                DRIVER GDAL/GTiff
                IMAGEMODE RGB
                MIMETYPE 'image/tiff'
                EXTENSION 'tif'
                FORMATOPTION 'COMPRESS=JPEG'
                FORMATOPTION 'JPEG_QUALITY=100'
                FORMATOPTION 'PHOTOMETRIC=YCBCR'
            END

            OUTPUTFORMAT
                NAME svg
                DRIVER CAIRO/SVG
                MIMETYPE 'image/svg+xml'
                EXTENSION 'svg'
                FORMATOPTION 'COMPRESSED_OUTPUT=FALSE'
                FORMATOPTION 'FULL_RESOLUTION=TRUE'
            END

        END
  ";

    /* placeholder for presence of anything that might need a legend */
    private $_legend_required = false;

    /* base download factor to rescale the resultant image */
    private $_download_factor = 1;

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
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->map_obj = ms_newMapObjFromString($this->mapfile_string);
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
        // set a property
        $property_prefix = substr($name, 0, 4);
        $property = substr($name, 4);
        if ($property_prefix == 'set_') {
            $this->{$property} = $arguments[0];
        } else if ($property_prefix == 'add_') { // add to an array property
            array_push($this->{$property}, $arguments[0]);
        } else if ($property_prefix == 'get_') { //get a property
            return $this->{$property};
        }
        return $this;
    }

    /**
     * Set the extent of the map.
     *
     * @param array $extent The maximum extent.
     *
     * @return object $this
     */
    public function setMaxExtent($extent = array())
    {
        $extent = explode(',', $extent);
        $this->max_extent = $extent;
        return $this;
    }

    /**
     * Flexibly load up all the request parameters
     *
     * @return object $this
     */
    public function getRequest()
    {
        $this->coords           = Utilities::loadParam('coords', array());
        $this->regions          = Utilities::loadParam('regions', array());

        $this->output           = Utilities::loadParam('output', 'pnga');
        $this->width            = (float)Utilities::loadParam('width', 900);
        $this->height           = (float)Utilities::loadParam('height', $this->width/2);

        $this->image_size       = array($this->width, $this->height);

        $this->projection       = Utilities::loadParam('projection', 'epsg:4326');
        $this->projection_map   = Utilities::loadParam('projection_map', 'epsg:4326');
        $this->origin           = (int)Utilities::loadParam('origin', false);

        $this->bbox_map         = Utilities::loadParam('bbox_map', '-180,-90,180,90');

        $this->bbox_rubberband  = Utilities::loadParam('bbox_rubberband', array());

        $this->pan              = Utilities::loadParam('pan', false);

        $this->layers           = Utilities::loadParam('layers', array());

        $this->graticules       = (array_key_exists('grid', $this->layers)) ? true : false;

        $this->watermark        = Utilities::loadParam('watermark', false);

        $this->gridspace        = Utilities::loadParam('gridspace', false);

        $this->gridlabel        = (int)Utilities::loadParam('gridlabel', 1);

        $this->download         = Utilities::loadParam('download', false);

        $this->crop             = Utilities::loadParam('crop', false);

        $this->options          = Utilities::loadParam('options', array()); //scalebar, legend, border, linethickness

        $this->border_thickness = (float)Utilities::loadParam('border_thickness', 1.25);

        $this->rotation         = (int)Utilities::loadParam('rotation', 0);
        $this->zoom_in          = Utilities::loadParam('zoom_in', false);
        $this->zoom_out         = Utilities::loadParam('zoom_out', false);

        $this->_download_factor = (int)Utilities::loadParam('download_factor', 1);

        $this->file_name        = Utilities::loadParam('file_name', time());

        $this->download_token   = Utilities::loadParam('download_token', md5(time()));
        setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");

        return $this;
    }

    /**
     * Execute the process. This is the main method that calls other req'd and optional methods.
     *
     * @return object $this
     */
    public function execute()
    {
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
     * Set the projection
     *
     * @return void
     */
    private function _loadProjection()
    {
        $this->map_obj->setProjection(AcceptedProjections::$projections[$this->default_projection]['proj']);
    }

    /**
     * Load-up all the settings for potential shapes
     *
     * @return void
     */
    private function _loadShapes()
    {
        $config_file = file_get_contents(ROOT . "/config/shapefiles.yml");
        $this->shapes = $this->_tokenize_shapefile_config(Yaml::parse($config_file));
    }

    /**
     * Tokenize shapefile.yml config
     *
     * @return array of config
     */
    private function _tokenize_shapefile_config($config)
    {
        $config = array_merge($config['layers'], $config['labels']);
        $pattern = '/%%(.+)%%(.+)?/';
        foreach($config as $shape => $values) {
            foreach($values as $key => $value) {
                $config[$shape][$key] = preg_replace_callback($pattern, function($matches) {
                    if(isset($matches[2])) {
                        return constant($matches[1]) . $matches[2];
                    } else {
                        return constant($matches[1]);
                    }
                }, $value);
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
        foreach(AcceptedMarkerShapes::$shapes as $type => $style) {
            $fill = MS_FALSE;
            if ($type == 'closed') { $fill = MS_TRUE; }
            foreach($style as $name => $settings) {
                $type = (strpos($name, 'circle') !== FALSE) ? MS_SYMBOL_ELLIPSE : MS_SYMBOL_VECTOR;
                $vertices = AcceptedMarkerShapes::vertices($settings['style']);
                $this->_createSymbol($name, $type, $fill, $vertices);
            }
        }
    }

    /**
     * Create a symbol
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
        if ($this->output == 'tif') {
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
        $units = (isset($this->projection) && $this->projection == $this->default_projection) ? MS_DD : MS_METERS;
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
        if (isset($this->output) && $this->output) {
            $output = (($this->output == 'png' || $this->output == 'pnga') && $this->download) ? $this->output . "_download" : $this->output;
            if ($output == 'pptx' || $output == 'docx') {
                $output = 'pnga_transparent';
                if ($this->_layersContainRaster() == TRUE) {
                    $output = 'png_download';
                }
            }
            $this->map_obj->selectOutputFormat($output);
        }
    }

    /**
     * Test presence of raster in layers
     *
     * @return Boolean
     */
    private function _layersContainRaster()
    {
        $raster_active = false;
        foreach ($this->layers as $layer => $status) {
            if ($this->shapes[$layer]['type'] == MS_LAYER_RASTER) {
                $raster_active = true;
                break;
            }
        }
        return $raster_active;
    }

    /**
     * Add legend and scalebar
     *
     * @return void
     */
    private function _addLegendScalebar()
    {
        if ($this->download && array_key_exists('legend', $this->options) && $this->options['legend']) {
            $this->addLegend();
        } else if (!$this->download) {
            $this->addLegend();
        }
        if ($this->download && array_key_exists('scalebar', $this->options) && $this->options['scalebar']) {
            $this->addScalebar();
        } else if (!$this->download) {
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
        $ext = explode(',', $this->bbox_map);
        if (isset($this->projection) && $this->projection != $this->projection_map) {
            $origProjObj = ms_newProjectionObj(self::getProjection($this->projection_map));
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
        $this->map_obj->setSize($this->image_size[0], $this->image_size[1]);
        if ($this->_isResize()) {
            $this->map_obj->setSize($this->_download_factor*$this->image_size[0], $this->_download_factor*$this->image_size[1]);
        }
    }

    /**
     * Zoom In
     *
     * @return void
     */
    private function _setZoom()
    {
        //Zoom in
        if (isset($this->bbox_rubberband) && $this->bbox_rubberband && !$this->_isResize()) {
            $bbox_rubberband = explode(',', $this->bbox_rubberband);
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
        if (isset($this->zoom_in) && $this->zoom_in) {
            $zoom_point = ms_newPointObj();
            $zoom_point->setXY($this->map_obj->width/2, $this->map_obj->height/2);
            $this->map_obj->zoompoint(2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
        }

        //Zoom out
        if (isset($this->zoom_out) && $this->zoom_out) {
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
        if (isset($this->pan) && $this->pan) {
            switch ($this->pan) {
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
        if (isset($this->rotation) && $this->rotation != 0) {
            $this->map_obj->setRotation($this->rotation);
            if ($this->projection == $this->default_projection) {
                $this->_reproject($this->default_projection, $this->projection);
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
        if (isset($this->crop) && $this->crop && $this->bbox_rubberband && $this->_isResize()) {

            $bbox_rubberband = explode(',', $this->bbox_rubberband);

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
            if ($this->_isResize() && $this->_download_factor > 1) {
                $this->map_obj->setSize($this->_download_factor*$width, $this->_download_factor*$height);
            }

            //set the extent to match that of the crop
            $this->map_obj->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);
        }
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
        $this->bad_points = array();
        if (isset($this->coords) && $this->coords) {
            //do this in reverse order because the legend will otherwise be presented in reverse order
            for ($j=count($this->coords)-1; $j>=0; $j--) {

                //clear out previous loop's selection
                $size = "";
                $shape = "";
                $shadow = false;
                $offset = 2;
                $color = array();

                $title = ($this->coords[$j]['title']) ? stripslashes($this->coords[$j]['title']) : "";
                $size = ($this->coords[$j]['size']) ? $this->coords[$j]['size'] : 8;
                if ($this->_isResize() && $this->_download_factor > 1) {
                    $size = $this->_download_factor*$size;
                    $offset = $this->_download_factor;
                }
                $shape = (isset($this->coords[$j]['shape'])) ? $this->coords[$j]['shape'] : 'circle';
                $shadow = (array_key_exists('shadow', $this->coords[$j])) ? true : false;
                if ($this->coords[$j]['color']) {
                    $color = explode(" ", $this->coords[$j]['color']);
                    if (count($color) != 3) {
                        $color = array();
                    }
                }

                $data = trim($this->coords[$j]['data']);

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
                        $class->set("name", $title);
                    }

                    if ($shadow) {
                      $bstyle = ms_newStyleObj($class);
                      $bstyle->set("symbolname", $shape);
                      $bstyle->set("size", $size);
                      $bstyle->set("offsetx", $offset);
                      $bstyle->set("offsety", $offset);
                      $bstyle->color->setRGB(180,180,180);
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

                    $rows = explode("\n", Utilities::removeEmptyLines($data));  //split the lines that have data
                    $points = array(); //create an array to hold unique locations

                    foreach ($rows as $row) {
                        $coord_array = Utilities::makeCoordinates($row);
                        $coord = new \stdClass();
                        $coord->x = ($coord_array[1]) ? Utilities::cleanCoord($coord_array[1]) : null;
                        $coord->y = ($coord_array[0]) ? Utilities::cleanCoord($coord_array[0]) : null;
                        //only add point when data are good & have a title
                        if (Utilities::onEarth($coord) && !empty($title)) {
                            if (!array_key_exists($coord->x.$coord->y, $points)) { //unique locations
                                $new_point = ms_newPointObj();
                                $new_point->setXY($coord->x, $coord->y);
                                $new_line->add($new_point);
                                $points[$coord->x.$coord->y] = array();
                            }
                        } else {
                            $this->bad_points[] = stripslashes($this->coords[$j]['title'] . ' : ' . $row);
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
     * Add shaded regions to the map
     *
     * @return void
     */
    public function addRegions()
    {
        if (isset($this->regions) && $this->regions) {  
            for ($j=count($this->regions)-1; $j>=0; $j--) {
                //clear out previous loop's selection
                $color = array();
                $title = ($this->regions[$j]['title']) ? stripslashes($this->regions[$j]['title']) : "";
                if ($this->regions[$j]['color']) {
                    $color = explode(" ", $this->regions[$j]['color']);
                    if (count($color) != 3) {
                        $color = array();
                    }
                }

                $data = trim($this->regions[$j]['data']);

                if ($data) {
                    $this->_legend_required = true;
                    $baselayer = true;
                    //grab the textarea for regions & split
                    $rows = explode("\n", Utilities::removeEmptyLines($data));
                    $qry = array();
                    foreach ($rows as $row) {
                        $regions = preg_split("/[,;]+/", $row); //split by a comma, semicolon
                        foreach ($regions as $region) {
                            if ($region) {
                                $pos = strpos($region, '[');
                                if ($pos !== false) {
                                    $baselayer = false;
                                    $split = explode("[", str_replace("]", "", trim(strtoupper($region))));
                                    $states = preg_split("/[\s|]+/", $split[1]);
                                    $statekey = array();
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
                    $class->set("name", $title);

                    $style = ms_newStyleObj($class);
                    if (!empty($color)) {
                        $style->color->setRGB($color[0], $color[1], $color[2]);
                    }
                    $style->outlinecolor->setRGB(30, 30, 30);
                    $style->set("opacity", 75);
                    $style->set("width", $this->_determineWidth());
                    $layer->set("status", MS_ON);
                }

            }
        }
    }

    /**
     * Add all selected layers to the map
     *
     * @return void
     */
    private function _addLayers()
    {
        $sort = array();

        $this->layers['base'] = 'on';
        unset($this->layers['grid']);

        foreach ($this->layers as $key => $row) {
            if(isset($this->output) && $this->output == 'svg' && $this->shapes[$key]['type'] == MS_LAYER_RASTER) {
                unset($this->layers[$key]);
            } else {
                $sort[$key] = (isset($this->shapes[$key])) ? $this->shapes[$key]['sort'] : $row;
            }
        }
        array_multisort($sort, SORT_ASC, $this->layers);

        $srs_projections = implode(array_keys(AcceptedProjections::$projections), " ");

        foreach ($this->layers as $name => $status) {
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

                if(isset($this->shapes[$name]['opacity'])) {
                    $layer->set("opacity", (int)$this->shapes[$name]['opacity']);
                }

                if(isset($this->shapes[$name]['class'])) {
                    $classitem = $this->shapes[$name]['class']['item'];
                    $layer->set("classitem", $classitem);
                    $this->_setSLDClasses($layer, $this->shapes[$name]['class']['sld'], $classitem);
                    $this->_legend_required = true;
                }

                if(isset($this->shapes[$name]['tolerance'])) {
                    $layer->set("tolerance", (int)$this->shapes[$name]['tolerance']);
                }

                if(isset($this->shapes[$name]['tolerance_units'])) {
                    $layer->set("toleranceunits", $this->shapes[$name]['tolerance_units']);
                }

                if(isset($this->shapes[$name]['label'])) {
                    $layer->set("labelitem", $this->shapes[$name]['label']['item']);
                }

                $class = ms_newClassObj($layer);
                $style = ms_newStyleObj($class);

                if(isset($this->shapes[$name]['legend'])) {
                    $class->set("name", $this->shapes[$name]['legend']);
                    $this->_legend_required = true;
                }

                if(isset($this->shapes[$name]['outline_color'])) {
                    $color = explode(",",$this->shapes[$name]['outline_color']);
                    $style->outlinecolor->setRGB($color[0], $color[1], $color[2]);
                }

                if(isset($this->shapes[$name]['color'])) {
                    $color = explode(",",$this->shapes[$name]['color']);
                    $style->color->setRGB($color[0], $color[1], $color[2]);
                }

                if(isset($this->shapes[$name]['dynamic_width'])) {
                    $style->set("width", $this->_determineWidth());
                }

                if(isset($this->shapes[$name]['label'])) {
                    $class->addLabel($this->_createLabel((int)$this->shapes[$name]['label']['size'], $this->shapes[$name]['label']['position'], $this->shapes[$name]['encoding']));
                }

                if(isset($this->shapes[$name]['symbol'])) {
                    $style->set("symbolname", $this->shapes[$name]['symbol']['shape']);
                    $size = $this->shapes[$name]['symbol']['size'];
                    $style->set("size", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*($size+1) : $size);
                    $color = explode(",",$this->shapes[$name]['symbol']['color']);
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
        $label->set("size", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*7 : $size);
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
        if (isset($this->watermark) && $this->watermark) {
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
     * Add graticules (or grid lines) to map
     *
     * @return void
     */
    public function addGraticules()
    {
        if (isset($this->graticules) && $this->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(self::getProjection($this->default_projection));

            $class = ms_newClassObj($layer);

            if ($this->gridlabel != 0) {
                $label = new \labelObj();
                $label->set("font", "arial");
                $label->set("encoding", "UTF-8");
                $label->set("size", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*9 : 10);
                $label->set("position", MS_CC);
                $label->color->setRGB(30, 30, 30);
                $class->addLabel($label);
            }

            $style = ms_newStyleObj($class);
            $style->color->setRGB(200, 200, 200);

            $minx = $this->map_obj->extent->minx;
            $maxx = $this->map_obj->extent->maxx;

            //project the extent back to default such that we can work with proper tick marks
            if ($this->projection != $this->default_projection && $this->projection == $this->projection_map) {
                $origProjObj = ms_newProjectionObj(self::getProjection($this->projection));
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

            $labelformat = ($this->gridspace) ? "DD" : $labelformat;
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
     * Create the legend file
     *
     * @return void
     */
    public function addLegend()
    {
        if ($this->_legend_required) {
            $this->map_obj->legend->set("keysizex", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*15 : 20);
            $this->map_obj->legend->set("keysizey", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*15 : 20);
            $this->map_obj->legend->set("keyspacingx", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*3 : 5);
            $this->map_obj->legend->set("keyspacingy", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*3 : 5);
            $this->map_obj->legend->set("postlabelcache", 1);
            $this->map_obj->legend->label->set("font", "arial");
            $this->map_obj->legend->label->set("encoding", "UTF-8");
            $this->map_obj->legend->label->set("position", 1);
            $this->map_obj->legend->label->set("size", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*8 : 10);
            $this->map_obj->legend->label->color->setRGB(0, 0, 0);

            //svg format cannot do legends in MapServer
            if ($this->download && $this->options['legend'] && $this->output != 'svg') {
                $this->map_obj->legend->set("status", MS_EMBED);
                $this->map_obj->legend->set("position", MS_UR);
                $this->map_obj->drawLegend();
            }
            if (!$this->download) {
                $this->map_obj->legend->set("status", MS_DEFAULT);
                $this->legend = $this->map_obj->drawLegend();
                $this->legend_url = $this->legend->saveWebImage();
            }
        }
    }

    /**
     * Discover if the output ought to be resized
     *
     * @return bool
     */
    private function _isResize()
    {
        if ($this->download || $this->output == 'pptx' || $this->output == 'docx') {
            return true;
        }
        return false;
    }

    /**
     * Create a scalebar image
     *
     * @return void
     */
    public function addScalebar()
    {
        $this->map_obj->scalebar->set("style", 0);
        $this->map_obj->scalebar->set("intervals", 3);
        $this->map_obj->scalebar->set("height", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*4 : 8);
        $this->map_obj->scalebar->set("width", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*100 : 200);
        $this->map_obj->scalebar->color->setRGB(30, 30, 30);
        $this->map_obj->scalebar->outlinecolor->setRGB(0, 0, 0);
        $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->map_obj->scalebar->label->set("font", "arial");
        $this->map_obj->scalebar->label->set("encoding", "UTF-8");
        $this->map_obj->scalebar->label->set("size", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*5 : 8);
        $this->map_obj->scalebar->label->color->setRGB(0, 0, 0);

        //svg format cannot do scalebar in MapServer
        if ($this->download && $this->options['scalebar'] && $this->output != 'svg') {
            $this->map_obj->scalebar->set("status", MS_EMBED);
            $this->map_obj->scalebar->set("position", MS_LR);
            $this->map_obj->drawScalebar();
        }
        if (!$this->download) {
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
     * Add a border to a downloaded map image
     *
     * @return void
     */
    private function _addBorder()
    {
        if ($this->_isResize() && array_key_exists('border', $this->options) && ($this->options['border'] == 1 || $this->options['border'] == 'true')) {
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
        if (isset($this->projection)) {
            $this->_reproject($this->projection_map, $this->projection);
            $this->_addLegendScalebar();
            $this->_addBorder();
            $this->image = $this->map_obj->drawQuery();
        }
    }

    /**
     * Reproject a $map from one projection to another.
     *
     * @param string $input_projection  The input projection.
     * @param string $output_projection The output projection.
     *
     * @return void
     */
    private function _reproject($input_projection, $output_projection)
    {
        $this->_setOrigin($output_projection);

        $origProjObj = ms_newProjectionObj(self::getProjection($input_projection));
        $newProjObj = ms_newProjectionObj(self::getProjection($output_projection));

        $oRect = $this->map_obj->extent;
        $oRect->project($origProjObj, $newProjObj);
        //TODO: failing for http://www.simplemappr.net/map/2962
        $this->map_obj->setExtent($oRect->minx, $oRect->miny, $oRect->maxx, $oRect->maxy);
        $this->map_obj->setProjection(self::getProjection($output_projection));
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
        $lambert_projections = array('esri:102009', 'esri:102015', 'esri:102014', 'esri:102102', 'esri:102024', 'epsg:3112');
        if (in_array($this->projection, $lambert_projections) && $this->origin && ($this->origin >= -180) && ($this->origin <= 180)) {
            AcceptedProjections::$projections[$output_projection]['proj'] = preg_replace('/lon_0=(.*?),/', 'lon_0='.$this->origin.',', self::getProjection($output_projection));
        }
    }

    /**
     * Build ecoregion layer classes from SLD file
     *
     * @param obj $layer The MapScript layer object.
     * @param string $sld The full path to an SLD file
     * @param string $item The term filtered on in the SLD
     *
     * @return void
     */
    private function _setSLDClasses($layer, $sld, $item)
    {
        $xml = simplexml_load_file($sld);
        $xml->registerXPathNamespace('sld', 'http://www.opengis.net/sld');
        $xml->registerXPathNamespace('ogc', 'http://www.opengis.net/ogc');
        foreach ($xml->xpath('//sld:Rule') as $rule) {
            $class = ms_newClassObj($layer);
            $class->setExpression("([".$item."] = ".$rule->xpath('.//sld:Name')[0].")");
            $style = ms_newStyleObj($class);
            $color = Utilities::hex2Rgb($rule->xpath('.//sld:CssParameter')[0]);
            $style->color->setRGB($color[0], $color[1], $color[2]);
            $style->outlinecolor->setRGB(30, 30, 30);
        }
    }

    /**
     * Get the scaled width for a layer's line
     *
     * @return $width
     */
    private function _determineWidth()
    {
        $width = 1.25;
        if (isset($this->border_thickness)) {
            $width = $this->border_thickness;
            if ($this->_isResize() 
                && $this->_download_factor > 1
                && array_key_exists('scalelinethickness', $this->options)
                && $this->options['scalelinethickness']
            ) {
                $width = $this->border_thickness*$this->_download_factor/2;
            }
        }
        return $width;
    }

}
