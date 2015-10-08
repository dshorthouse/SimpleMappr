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

use \ForceUTF8\Encoding;

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

    /* path to shapefiles */
    protected $shape_path;

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
     * Acceptable projections in PROJ format
     * Included here for performance reasons AND each has 'over' switch to prevent line wraps
     */
    public static $accepted_projections = array(
        'epsg:4326'   => array(
            'name' => 'Geographic',
            'proj' => 'proj=longlat,ellps=WGS84,datum=WGS84,no_defs'),
        'esri:102009' => array(
            'name' => 'North America Lambert',
            'proj' => 'proj=lcc,lat_1=20,lat_2=60,lat_0=40,lon_0=-96,x_0=0,y_0=0,ellps=GRS80,datum=NAD83,units=m,over,no_defs'),
        'esri:102015' => array(
            'name' => 'South America Lambert',
            'proj' => 'proj=lcc,lat_1=-5,lat_2=-42,lat_0=-32,lon_0=-60,x_0=0,y_0=0,ellps=aust_SA,units=m,over,no_defs'),
        'esri:102014' => array(
            'name' => 'Europe Lambert',
            'proj' => 'proj=lcc,lat_1=43,lat_2=62,lat_0=30,lon_0=10,x_0=0,y_0=0,ellps=intl,units=m,over,no_defs'),
        'esri:102012' => array(
            'name' => 'Asia Lambert',
            'proj' => 'proj=lcc,lat_1=30,lat_2=62,lat_0=0,lon_0=105,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
        'esri:102024' => array(
            'name' => 'Africa Lambert',
            'proj' => 'proj=lcc,lat_1=20,lat_2=-23,lat_0=0,lon_0=25,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
        'epsg:3112'   => array(
            'name' => 'Australia Lambert',
            'proj' => 'proj=lcc,lat_1=-18,lat_2=-36,lat_0=0,lon_0=134,x_0=0,y_0=0,ellps=GRS80,towgs84=0,0,0,0,0,0,0,units=m,over,no_defs'),
        'epsg:102017' => array(
            'name' => 'North Pole Azimuthal',
            'proj' => 'proj=laea,lat_0=90,lon_0=0,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'),
        'epsg:102019' => array(
            'name' => 'South Pole Azimuthal',
            'proj' => 'proj=laea,lat_0=-90,lon_0=0,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs')
      );

    /* acceptable shapes */ 
    public static $accepted_shapes = array(
        'plus',
        'cross',
        'asterisk',
        'opencircle',
        'openstar',
        'opensquare',
        'opentriangle',
        'inverseopentriangle',
        'openhexagon',
        'circle',
        'star',
        'square',
        'triangle',
        'inversetriangle',
        'hexagon'
    );

    /**
     * Remove empty lines from a string.
     *
     * @param string $text String of characters
     *
     * @return string cleansed string with empty lines removed
     */
    public static function removeEmptyLines($text)
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
    }

    /**
     * Add slashes to either a string or an array.
     *
     * @param array $arr_r Array that needs addslashes.
     *
     * @return string/array
     */
    public static function addSlashesExtended(&$arr_r)
    {
        if (is_array($arr_r)) {
            foreach ($arr_r as &$val) {
                is_array($val) ? self::addSlashesExtended($val) : $val = addslashes($val);
            }
            unset($val);
        } else {
            $arr_r = addslashes($arr_r);
        }
        return $arr_r;
    }

    /**
     * Get a user-defined file name, cleaned of illegal characters.
     *
     * @param string $file_name String that should be a file name.
     * @param string $extension File extension.
     *
     * @return string Cleaned string that can be a file name.
     */
    public static function cleanFilename($file_name, $extension = "")
    {
        $clean_filename = preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $file_name);
        if ($extension) {
            return $clean_filename . "." . $extension;
        }
        return $clean_filename;
    }

    /**
     * Clean extraneous materials in coordinate that should (in theory) be DD.
     *
     * @param string $coord Dirty string that should be an real number
     *
     * @return real Cleaned coordinate
     */
    public static function cleanCoord($coord)
    {
        return preg_replace('/[^\d.-]/i', "", $coord);
    }

    /**
     * Check a DD coordinate object and return true if it fits on globe, false if not
     *
     * @param obj $coord (x,y) coordinates
     *
     * @return bool
     */
    public static function checkOnEarth($coord)
    {
        if ($coord->x 
            && $coord->y 
            && is_numeric($coord->x) 
            && is_numeric($coord->y) 
            && $coord->y <= 90 
            && $coord->y >= -90 
            && $coord->x <= 180 
            && $coord->x >= -180
        ) {
            return true;
        }
        return false;
    }

    /**
     * Split DDMMSS or DD coordinate pair string into an array
     *
     * @param string $point A string purported to be a coordinate
     *
     * @return array(latitude, longitude) in DD
     */
    public static function makeCoordinates($point)
    {
        $loc = preg_replace(array('/[\p{Z}\s]/u', '/[^\d\s,;.\-NSEWO°ºdms\'"]/i'), array(' ', ""), $point);
        if (preg_match('/[NSEWO]/', $loc) != 0) {
            $coord = preg_split("/[,;]/", $loc); //split by comma or semicolon
            if (count($coord) != 2 || empty($coord[1])) {
                return array(null, null);
            }
            $coord = (preg_match('/[EWO]/', $coord[1]) != 0) ? $coord : array_reverse($coord);
            return array(self::dmsToDeg(trim($coord[0])),self::dmsToDeg(trim($coord[1])));
        } else {
            $coord = preg_split("/[\s,;]+/", trim(preg_replace("/[^0-9-\s,;.]/", "", $loc))); //split by space, comma, or semicolon
            if (count($coord) != 2 || empty($coord[1])) {
                return array(null, null);
            }
            return $coord;
        }
    }

    /**
     * Convert a coordinate in dms to deg
     *
     * @param string $dms coordinate
     *
     * @return float
     */
    public static function dmsToDeg($dms)
    {
        $dec = null;
        $dms = stripslashes($dms);
        $neg = (preg_match('/[SWO]/i', $dms) == 0) ? 1 : -1;
        $dms = preg_replace('/(^\s?-)|(\s?[NSEWO]\s?)/i', "", $dms);
        $pattern = "/(\\d*\\.?\\d+)(?:[°ºd: ]+)(\\d*\\.?\\d+)*(?:['m′: ])*(\\d*\\.?\\d+)*[\"s″ ]?/i";
        $parts = preg_split($pattern, $dms, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (!$parts) {
            return;
        }
        // parts: 0 = degree, 1 = minutes, 2 = seconds
        $deg = isset($parts[0]) ? (float)$parts[0] : 0;
        $min = isset($parts[1]) ? (float)$parts[1] : 0;
        if (strpos($dms, ".") > 1 && isset($parts[2])) {
            $min = (float)($parts[1] . '.' . $parts[2]);
            unset($parts[2]);
        }
        $sec = isset($parts[2]) ? (float)$parts[2] : 0;
        if ($min >= 0 && $min < 60 && $sec >= 0 && $sec < 60) {
            $dec = ($deg + ($min/60) + ($sec/3600))*$neg;
        }
        return $dec;
    }

    /**
     * Get projection
     *
     * @param string $projection Projection expressed as epsg code
     *
     * @return string PROJ representation of projection
     */
    public static function getProjection($projection)
    {
        if (!array_key_exists($projection, self::$accepted_projections)) {
            $projection = 'epsg:4326';
        }
        return self::$accepted_projections[$projection]['proj'];
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
        $this->coords           = $this->loadParam('coords', array());
        $this->regions          = $this->loadParam('regions', array());

        $this->output           = $this->loadParam('output', 'pnga');
        $this->width            = (float)$this->loadParam('width', 900);
        $this->height           = (float)$this->loadParam('height', $this->width/2);

        $this->image_size       = array($this->width, $this->height);

        $this->projection       = $this->loadParam('projection', 'epsg:4326');
        $this->projection_map   = $this->loadParam('projection_map', 'epsg:4326');
        $this->origin           = (int)$this->loadParam('origin', false);

        $this->bbox_map         = $this->loadParam('bbox_map', '-180,-90,180,90');

        $this->bbox_rubberband  = $this->loadParam('bbox_rubberband', array());

        $this->pan              = $this->loadParam('pan', false);

        $this->layers           = $this->loadParam('layers', array());

        $this->graticules       = (array_key_exists('grid', $this->layers)) ? true : false;

        $this->watermark        = $this->loadParam('watermark', false);

        $this->gridspace        = $this->loadParam('gridspace', false);

        $this->gridlabel        = (int)$this->loadParam('gridlabel', 1);

        $this->download         = $this->loadParam('download', false);

        $this->crop             = $this->loadParam('crop', false);

        $this->options          = $this->loadParam('options', array()); //scalebar, legend, border, linethickness

        $this->border_thickness = (float)$this->loadParam('border_thickness', 1.25);

        $this->rotation         = (int)$this->loadParam('rotation', 0);
        $this->zoom_in          = $this->loadParam('zoom_in', false);
        $this->zoom_out         = $this->loadParam('zoom_out', false);

        $this->_download_factor = (int)$this->loadParam('download_factor', 1);

        $this->file_name        = $this->loadParam('file_name', time());

        $this->download_token   = $this->loadParam('download_token', md5(time()));
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
     * Get a case insensitive request parameter.
     *
     * @param string $name    Name of the parameter.
     * @param string $default Default value for the parameter.
     *
     * @return string The parameter value or empty string if null.
     */
    public function loadParam($name, $default = "")
    {
        $grep_key = $this->_pregGrepKeys("/\b(?<!-)$name(?!-)\b/i", $_REQUEST);
        if (!$grep_key || !array_values($grep_key)[0]) {
            return $default;
        }
        $value = array_values($grep_key)[0];
        $value = Encoding::fixUTF8($value);
        if (get_magic_quotes_gpc() != 1) {
            $value = self::addSlashesExtended($value);
        }
        return $value;
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
     * Grep on array keys.
     *
     * @param string $pattern A regex.
     * @param array  $input   An associative array.
     * @param int    $flags   Preg grep flags.
     *
     * @return array of matched keys.
     */
    private function _pregGrepKeys($pattern, $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Set the projection
     *
     * @return void
     */
    private function _loadProjection()
    {
        $this->map_obj->setProjection(self::$accepted_projections[$this->default_projection]['proj']);
    }

    /**
     * Load-up all the settings for potential shapes
     *
     * @return void
     */
    private function _loadShapes()
    {
        //shaded relief
        $this->shapes['relief'] = array(
            'shape'    => $this->shape_path . "/HYP_HR_SR_OB_DR/HYP_HR_SR_OB_DR.tif",
            'type'     => MS_LAYER_RASTER,
            'encoding' => "UTF-8",
            'sort'     => 1
        );

        // Geotiff created by David P. Shorthouse using above file.
        $this->shapes['reliefgrey'] = array(
            'shape'    => $this->shape_path . "/GRAY_HR_SR_OB_DR/GRAY_HR_SR_OB_DR.tif",
            'type'     => MS_LAYER_RASTER,
            'encoding' => "UTF-8",
            'sort'     => 1
        );

        //lakes outline
        $this->shapes['lakesOutline'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_lakes",
            'type'     => MS_LAYER_LINE,
            'encoding' => "CP1252",
            'sort'     => 2
        );

        //lakes
        $this->shapes['lakes'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_lakes",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 3
        );

        //oceans
        $this->shapes['oceans'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_ocean",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 3
        );

        //conservation
        $this->shapes['conservation'] = array(
            'shape'    => $this->shape_path . "/conservation_international/hotspots_2011_polygons",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 3
        );

        //ecoregions
        $this->shapes['ecoregions'] = array(
            'shape'    => $this->shape_path . "/wwf_terr_ecos/wwf_terr_ecos",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 3
        );

        //base map
        $this->shapes['base'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_land",
            'type'     => MS_LAYER_LINE,
            'encoding' => "CP1252",
            'sort'     => 4
        );

        //base map
        $this->shapes['countries'] = array(
            'shape'    => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_0_map_units",
            'type'     => MS_LAYER_LINE,
            'encoding' => "CP1252",
            'sort'     => 4
        );

        //stateprovinces_polygon
        $this->shapes['stateprovinces_polygon'] = array(
            'shape'    => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_1_states_provinces",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 5
        );

        //stateprovinces
        $this->shapes['stateprovinces'] = array(
            'shape'    => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_1_states_provinces_lines_shp",
            'type'     => MS_LAYER_LINE,
            'encoding' => "CP1252",
            'sort'     => 6
        );

        //lake names
        $this->shapes['lakenames'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_lakes",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 7
        );

        //rivers
        $this->shapes['rivers'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_rivers_lake_centerlines",
            'type'     => MS_LAYER_LINE,
            'encoding' => "CP1252",
            'sort'     => 8
        );

        //rivers
        $this->shapes['rivernames'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_rivers_lake_centerlines",
            'type'     => MS_LAYER_LINE,
            'encoding' => "CP1252",
            'sort'     => 9
        );

        //placename
        $this->shapes['placenames'] = array(
            'shape'    => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_populated_places_simple",
            'type'     => MS_LAYER_POINT,
            'encoding' => "CP1252",
            'sort'     => 10
        );

        //State/Provincial labels
        $this->shapes['stateprovnames'] = array(
            'shape'    => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_1_states_provinces",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "UTF-8",
            'sort'     => 11
        );

        //Country labels
        $this->shapes['countrynames'] = array(
            'shape'    => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_0_map_units",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 12
        );

        //physicalLabels
        $this->shapes['physicalLabels'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_geography_regions_polys",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 13
        );

        //marineLabels
        $this->shapes['marineLabels'] = array(
            'shape'    => $this->shape_path . "/10m_physical/ne_10m_geography_marine_polys",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 14
        );

        //hotspotLabels
        $this->shapes['hotspotLabels'] = array(
            'shape'    => $this->shape_path . "/conservation_international/hotspots_2011_polygons",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "UTF-8",
            'sort'     => 14
        );

        //ecoregions
        $this->shapes['ecoregionLabels'] = array(
            'shape'    => $this->shape_path . "/wwf_terr_ecos/wwf_terr_ecos",
            'type'     => MS_LAYER_POLYGON,
            'encoding' => "CP1252",
            'sort'     => 14
        );

    }

    /**
     * Create a symbol
     *
     * @return void
     */
    private function _createSymbol($name, $fill, $vertices)
    {
        $nId = ms_newSymbolObj($this->map_obj, $name);
        $symbol = $this->map_obj->getSymbolObjectById($nId);
        $type = (strpos($name, 'circle') !== FALSE) ? MS_SYMBOL_ELLIPSE : MS_SYMBOL_VECTOR;
        $symbol->set("type", $type);
        $symbol->set("filled", $fill);
        $symbol->set("inmapfile", MS_TRUE);
        $symbol->setpoints($vertices);
    }

    /**
     * Load-up all the symbols
     *
     * @return void
     */
    private function _loadSymbols()
    {
        $plus = array(
            0.5, 0,
            0.5, 1,
            -99, -99,
            0, 0.5,
            1, 0.5
        );

        $cross = array(
            0, 0,
            1, 1,
            -99, -99,
            0, 1,
            1, 0
        );

        $asterisk = array(
            0, 0,
            1, 1,
            -99, -99,
            0, 1,
            1, 0,
            -99, -99,
            0.5, 0,
            0.5, 1,
            -99, -99,
            0, 0.5,
            1, 0.5
        );

        $circle = array(
            1, 1
        );

        $star = array(
            0, 0.375,
            0.35, 0.365,
            0.5, 0,
            0.65, 0.375,
            1, 0.375,
            0.75, 0.625,
            0.875, 1,
            0.5, 0.75,
            0.125, 1,
            0.25, 0.625,
            0, 0.375
        );

        $square = array(
            0, 1,
            0, 0,
            1, 0,
            1, 1,
            0, 1
        );

        $triangle = array(
            0, 1,
            0.5, 0,
            1, 1,
            0, 1
        );

        $inversetriangle = array(
            0, 0,
            1, 0,
            0.5, 1,
            0, 0
        );

        $hexagon = array(
            0.23, 0,
            0, 0.5,
            0.23, 1,
            0.77, 1,
            1, 0.5,
            0.77, 0,
            0.23, 0
        );

        $this->_createSymbol(self::$accepted_shapes[0], MS_FALSE, $plus);
        $this->_createSymbol(self::$accepted_shapes[1], MS_FALSE, $cross);
        $this->_createSymbol(self::$accepted_shapes[2], MS_FALSE, $asterisk);
        $this->_createSymbol(self::$accepted_shapes[3], MS_FALSE, $circle);
        $this->_createSymbol(self::$accepted_shapes[9], MS_TRUE, $circle);
        $this->_createSymbol(self::$accepted_shapes[4], MS_FALSE, $star);
        $this->_createSymbol(self::$accepted_shapes[10], MS_TRUE, $star);
        $this->_createSymbol(self::$accepted_shapes[5], MS_FALSE, $square);
        $this->_createSymbol(self::$accepted_shapes[11], MS_TRUE, $square);
        $this->_createSymbol(self::$accepted_shapes[6], MS_FALSE, $triangle);
        $this->_createSymbol(self::$accepted_shapes[12], MS_TRUE, $triangle);
        $this->_createSymbol(self::$accepted_shapes[7], MS_FALSE, $inversetriangle);
        $this->_createSymbol(self::$accepted_shapes[13], MS_TRUE, $inversetriangle);
        $this->_createSymbol(self::$accepted_shapes[8], MS_FALSE, $hexagon);
        $this->_createSymbol(self::$accepted_shapes[14], MS_TRUE, $hexagon);
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
                if (isset($this->layers['relief']) || isset($this->layers['reliefgrey'])) {
                    $output = 'png_download';
                }
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
                $color = array();

                $title = ($this->coords[$j]['title']) ? stripslashes($this->coords[$j]['title']) : "";
                $size = ($this->coords[$j]['size']) ? $this->coords[$j]['size'] : 8;
                if ($this->_isResize() && $this->_download_factor > 1) {
                    $size = $this->_download_factor*$size;
                }
                $shape = ($this->coords[$j]['shape']) ? $this->coords[$j]['shape'] : 'circle';
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

                    $rows = explode("\n", self::removeEmptyLines($data));  //split the lines that have data
                    $points = array(); //create an array to hold unique locations

                    foreach ($rows as $row) {
                        $coord_array = self::makeCoordinates($row);
                        $coord = new \stdClass();
                        $coord->x = ($coord_array[1]) ? self::cleanCoord($coord_array[1]) : null;
                        $coord->y = ($coord_array[0]) ? self::cleanCoord($coord_array[0]) : null;
                        //only add point when data are good & a title
                        if (self::checkOnEarth($coord) && !empty($title)) {
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
                    $rows = explode("\n", self::removeEmptyLines($data));
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
                        $layer->set("data", $this->shapes['countries']['shape']);
                        $layer->set("type", MS_LAYER_POLYGON);
                    } else {
                        $layer->set("data", $this->shapes['stateprovinces_polygon']['shape']);
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

        if (isset($this->output) && $this->output == 'svg') {
            unset($this->layers['relief'], $this->layers['reliefgrey']);
        }

        foreach ($this->layers as $key => $row) {
            $sort[$key] = (isset($this->shapes[$key])) ? $this->shapes[$key]['sort'] : $row;
        }
        array_multisort($sort, SORT_ASC, $this->layers);

        $srs_projections = implode(array_keys(self::$accepted_projections), " ");

        foreach ($this->layers as $name => $status) {
            //make the layer
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
                $layer->set("data", $this->shapes[$name]['shape']);
                $layer->setProjection(self::getProjection($this->default_projection));
                $layer->set("template", "template.html");
                $layer->set("dump", true);

                switch ($name) {
                case 'lakesOutline':
                    $class = ms_newClassObj($layer);
                    $style = ms_newStyleObj($class);
                    $style->color->setRGB(80, 80, 80);
                    break;

                case 'rivers':
                case 'lakes':
                    $class = ms_newClassObj($layer);
                    $style = ms_newStyleObj($class);
                    $style->color->setRGB(120, 120, 120);
                    break;

                case 'oceans':
                    $class = ms_newClassObj($layer);
                    $style = ms_newStyleObj($class);
                    $style->color->setRGB(220, 220, 220);
                    break;

                case 'conservation':
                    $layer->set("opacity", 75);
                    $class = ms_newClassObj($layer);
                    $class->set("name", "Conservation International 2011 Hotspots");
                    $style = ms_newStyleObj($class);
                    $style->color->setRGB(200, 200, 200);
                    $style->outlinecolor->setRGB(30, 30, 30);
                    $this->_legend_required = true;
                    break;

                case 'ecoregions':
                    $layer->set("opacity", 75);
                    $layer->set("classitem", "ECO_SYM");
                    $this->_setEcoregionClasses($layer);
                    $this->_legend_required = true;
                    break;

                case 'rivernames':
                case 'lakenames':
                    $layer->set("tolerance", 1);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(8, MS_UR, $this->shapes[$name]['encoding']));
                    break;

                case 'base':
                case 'countries':
                case 'stateprovinces':
                    $class = ms_newClassObj($layer);
                    $style = ms_newStyleObj($class);
                    $style->set("width", $this->_determineWidth());
                    $style->color->setRGB(10, 10, 10);
                    break;

                case 'countrynames':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(12, MS_CC, $this->shapes[$name]['encoding']));
                    break;

                case 'stateprovnames':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(10, MS_CC, $this->shapes[$name]['encoding']));
                    break;

                case 'placenames':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(8, MS_UR, $this->shapes[$name]['encoding']));

                    $style = ms_newStyleObj($class);
                    $style->set("symbolname", "circle");
                    $style->set("size", ($this->_isResize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 6);
                    $style->color->setRGB(100, 100, 100);
                    break;

                case 'physicalLabels':
                case 'marineLabels':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(8, MS_UR, $this->shapes[$name]['encoding']));
                    break;

                case 'hotspotLabels':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "NAME");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(8, MS_UR, "UTF-8"));
                    break;

                case 'ecoregionLabels':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "ECO_NAME");
                    $class = ms_newClassObj($layer);
                    $class->addLabel($this->_createLabel(8, MS_UR, "UTF-8"));
                    break;

                default:
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
            $class->settext("http://www.simplemappr.net");

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

            $string  = 'LAYER name "grid"' . "\n";
            $string .= 'GRID' . "\n";
            $string .= 'labelformat "'.$labelformat.'" maxarcs '.$maxarcs.' maxinterval '.$maxinterval.' maxsubdivide '.$maxsubdivide . "\n";
            $string .= 'END' . "\n";
            $string .= 'END' . "\n";
            $layer->updateFromString($string);
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
            self::$accepted_projections[$output_projection]['proj'] = preg_replace('/lon_0=(.*?),/', 'lon_0='.$this->origin.',', self::getProjection($output_projection));
        }
    }

    /**
     * Convert hex colour (eg for css) to RGB
     *
     * @param string $hex The hexidecimal string for the colour.
     *
     * @return array of RGB
     */
    private function _hex2Rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        if (strlen($hex) == 3) {
            $red = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $green = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $blue = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        }
        return array($red, $green, $blue);
    }

    /**
     * Build ecoregion layer classes from SLD file
     *
     * @param obj $layer The MapScript layer object.
     *
     * @return void
     */
    private function _setEcoregionClasses($layer)
    {
        $xml = simplexml_load_file($this->shape_path . "/wwf_terr_ecos/wwf_terr_ecos.sld");
        $xml->registerXPathNamespace('sld', 'http://www.opengis.net/sld');
        $xml->registerXPathNamespace('ogc', 'http://www.opengis.net/ogc');
        foreach ($xml->xpath('//sld:Rule') as $rule) {
            $class = ms_newClassObj($layer);
            $class->setExpression("([ECO_SYM] = ".$rule->xpath('.//sld:Name')[0].")");
            $style = ms_newStyleObj($class);
            $color = $this->_hex2Rgb($rule->xpath('.//sld:CssParameter')[0]);
            $style->color->setRGB($color[0], $color[1], $color[2]);
            $style->outlinecolor->setRGB(30, 30, 30);
        }
    }

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
