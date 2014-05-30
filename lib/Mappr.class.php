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

abstract class Mappr
{
    abstract function create_output();

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

            PROJECTION
                'init=epsg:4326'
            END

            SYMBOL
                NAME 'star'
                TYPE vector
                FILLED true
                TRANSPARENT 100
                POINTS
                    0 0.375
                    0.35 0.365
                    0.5 0
                    0.65 0.375
                    1 0.375
                    0.75 0.625
                    0.875 1
                    0.5 0.75
                    0.125 1
                    0.25 0.625
                    0 0.375
                END
            END

            SYMBOL
                NAME 'openstar'
                TYPE vector
                FILLED false
                POINTS
                    0 0.375
                    0.35 0.365
                    0.5 0
                    0.65 0.375
                    1 0.375
                    0.75 0.625
                    0.875 1
                    0.5 0.75
                    0.125 1
                    0.25 0.625
                    0 0.375
                END
            END

            SYMBOL
                NAME 'triangle'
                TYPE vector
                FILLED true
                TRANSPARENT 100
                POINTS
                    0 1
                    0.5 0
                    1 1
                    0 1
                END
            END

            SYMBOL
                NAME 'opentriangle'
                TYPE vector
                FILLED false
                TRANSPARENT 100
                POINTS
                    0 1
                    0.5 0
                    1 1
                    0 1
                END
            END

            SYMBOL
                NAME 'square'
                TYPE vector
                FILLED true
                TRANSPARENT 100
                POINTS
                    0 1
                    0 0
                    1 0
                    1 1
                    0 1
                END
            END

            SYMBOL
                NAME 'opensquare'
                TYPE vector
                FILLED false
                POINTS
                    0 1
                    0 0
                    1 0
                    1 1
                    0 1
                END
            END

            SYMBOL
                NAME 'plus'
                TYPE vector
                FILLED false
                POINTS
                    0.5 0
                    0.5 1
                    -99 -99
                    0 0.5
                    1 0.5
                END
            END

            SYMBOL
                NAME 'cross'
                TYPE vector
                FILLED false
                POINTS
                    0 0
                    1 1
                    -99 -99
                    0 1
                    1 0
                END
            END

            SYMBOL
                NAME 'asterisk'
                TYPE vector
                FILLED false
                POINTS
                    0 0
                    1 1
                    -99 -99
                    0 1
                    1 0
                    -99 -99
                    0.5 0
                    0.5 1
                    -99 -99
                    0 0.5
                    1 0.5
                END
            END

            SYMBOL
                NAME 'hexagon'
                TYPE vector
                FILLED true
                TRANSPARENT 100
                POINTS
                    0.23 0
                    0 0.5
                    0.23 1
                    0.77 1
                    1 0.5
                    0.77 0
                    0.23 0
                END
            END

            SYMBOL
                NAME 'openhexagon'
                TYPE vector
                FILLED false
                POINTS
                    0.23 0
                    0 0.5
                    0.23 1
                    0.77 1
                    1 0.5
                    0.77 0
                    0.23 0
                END
            END

            SYMBOL
                NAME 'circle'
                TYPE ellipse
                FILLED true
                TRANSPARENT 100
                POINTS
                    1 1
                END
            END

            SYMBOL
                NAME 'opencircle'
                TYPE ellipse
                FILLED false
                POINTS
                    1 1
                END
            END

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
        'openhexagon',
        'circle',
        'star',
        'square',
        'triangle',
        'hexagon'
    );

    /**
     * Remove empty lines from a string.
     *
     * @param string $text String of characters
     * @return string cleansed string with empty lines removed
     */
    public static function remove_empty_lines($text)
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
    }

    /**
     * Add slashes to either a string or an array.
     *
     * @param string/array &$arr_r Array that needs addslashes.
     * @return string/array
     */
    public static function add_slashes_extended(&$arr_r)
    {
        if (is_array($arr_r)) {
            foreach ($arr_r as &$val) {
                is_array($val) ? self::add_slashes_extended($val) : $val = addslashes($val);
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
     * @return string Cleaned string that can be a file name.
     */
    public static function clean_filename($file_name, $extension = "")
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
     * @return real Cleaned coordinate
     */
    public static function clean_coord($coord)
    {
        return preg_replace('/[^\d.-]/i', '', $coord);
    }

    /**
     * Check a DD coordinate object and return true if it fits on globe, false if not
     *
     * @param obj $coord (x,y) coordinates
     * @return bool
     */
    public static function check_coord($coord)
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
     * @return array(latitude, longitude) in DD
     */
    public static function make_coordinates($point)
    {
        $loc = preg_replace(array('/[\p{Z}\s]/u', '/[^\d\s,;.\-NSEWO°ºdms\'"]/i'), array(' ', ''), $point);
        if (preg_match('/[NSEWO]/', $loc) != 0) {
            $coord = preg_split("/[,;]/", $loc); //split by comma or semicolon
            if (count($coord) != 2 || empty($coord[1])) {
                return array(null, null);
            }
            $coord = (preg_match('/[EWO]/', $coord[1]) != 0) ? $coord : array_reverse($coord);
            return array(self::dms_to_deg(trim($coord[0])),self::dms_to_deg(trim($coord[1])));
        } else {
            $coord = preg_split("/[\s,;]+/", $loc); //split by space, comma, or semicolon
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
     * @return float
     */
    public static function dms_to_deg($dms)
    {
        $dms = stripslashes($dms);
        $neg = (preg_match('/[SWO]/i', $dms) == 0) ? 1 : -1;
        $dms = preg_replace('/(^\s?-)|(\s?[NSEWO]\s?)/i', '', $dms);
        $pattern = "/(\\d*\\.?\\d+)(?:[°ºd: ]+)(\\d*\\.?\\d+)*(?:['m′: ])*(\\d*\\.?\\d+)*[\"s″ ]?/i";
        $parts = preg_split($pattern, $dms, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (!$parts) {
            return;
        }
        // parts: 0 = degree, 1 = minutes, 2 = seconds
        $d = isset($parts[0]) ? (float)$parts[0] : 0;
        $m = isset($parts[1]) ? (float)$parts[1] : 0;
        if (strpos($dms, ".") > 1 && isset($parts[2])) {
            $m = (float)($parts[1] . '.' . $parts[2]);
            unset($parts[2]);
        }
        $s = isset($parts[2]) ? (float)$parts[2] : 0;
        $dec = ($d + ($m/60) + ($s/3600))*$neg; 
        return $dec;
    }

    /**
     * Get projection
     *
     * @param string $projection Projection expressed as epsg code
     * @return string PROJ representation of projection
     */
    public static function get_projection($projection)
    {
        if (!array_key_exists($projection, self::$accepted_projections)) {
            $projection = 'epsg:4326';
        }
        return self::$accepted_projections[$projection]['proj'];
    }

    /* placeholder for presence of anything that might need a legend */
    private $_legend_required = false;

    /* base download factor to rescale the resultant image */
    private $_download_factor = 1;

    function __construct()
    {
        if (extension_loaded("MapScript")) {
            $this->map_obj = ms_newMapObjFromString($this->mapfile_string);
        }
    }

    /**
     * Global call method to coordinate setters, getters.
     *
     * @param string $name Name of the object.
     * @param array $arguments Value for the object.
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
     * @param array $extent The maximum extent.
     * @return object $this
     */
    public function set_max_extent($extent = array())
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
    public function get_request()
    {
        $this->coords           = $this->load_param('coords', array());
        $this->regions          = $this->load_param('regions', array());

        $this->output           = $this->load_param('output', 'pnga');
        $this->width            = (float)$this->load_param('width', 900);
        $this->height           = (float)$this->load_param('height', $this->width/2);

        $this->image_size       = array($this->width, $this->height);

        $this->projection       = $this->load_param('projection', 'epsg:4326');
        $this->projection_map   = $this->load_param('projection_map', 'epsg:4326');
        $this->origin           = (int)$this->load_param('origin', false);

        $this->bbox_map         = $this->load_param('bbox_map', '-180,-90,180,90');

        $this->bbox_rubberband  = $this->load_param('bbox_rubberband', array());

        $this->pan              = $this->load_param('pan', false);

        $this->layers           = $this->load_param('layers', array());

        $this->graticules       = (array_key_exists('grid', $this->layers)) ? true : false;

        $this->watermark        = $this->load_param('watermark', false);

        $this->gridspace        = $this->load_param('gridspace', false);

        $this->gridlabel        = (int)$this->load_param('gridlabel', 1);

        $this->download         = $this->load_param('download', false);

        $this->crop             = $this->load_param('crop', false);

        $this->options          = $this->load_param('options', array()); //scalebar, legend, border, linethickness

        $this->border_thickness = (float)$this->load_param('border_thickness', 1.25);

        $this->rotation         = (int)$this->load_param('rotation', 0);
        $this->zoom_out         = $this->load_param('zoom_out', false);

        $this->_download_factor = (int)$this->load_param('download_factor', 1);

        $this->file_name        = $this->load_param('file_name', time());

        $this->download_token   = $this->load_param('download_token', md5(time()));
        setcookie("fileDownloadToken", $this->download_token, time()+3600, "/");

        return $this;
    }

    /**
     * Get a case insensitive request parameter.
     *
     * @param string $name Name of the parameter.
     * @param string $default Default value for the parameter.
     * @return string The parameter value or empty string if null.
     */
    public function load_param($name, $default = '')
    {
        $grep_key = $this->preg_grep_keys("/\b(?<!-)$name(?!-)\b/i", $_REQUEST);
        if (!$grep_key || !array_values($grep_key)[0]) {
            return $default;
        }
        $value = array_values($grep_key)[0];
        if (get_magic_quotes_gpc() != 1) {
            $value = self::add_slashes_extended($value);
        }
        return $value;
    }

    /**
     * Grep on array keys.
     *
     * @param string $pattern A regex.
     * @param array $input An associative array.
     * @param int $flags Preg grep flags.
     * @return array of matched keys.
     */
    private function preg_grep_keys($pattern, $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Load-up all the settings for potential shapes
     */
    private function load_shapes()
    {
        //shaded relief
        $this->shapes['relief'] = array(
            'shape' => $this->shape_path . "/HYP_HR_SR_OB_DR/HYP_HR_SR_OB_DR.tif",
            'type'  => MS_LAYER_RASTER,
            'sort'  => 1
        );

        // Geotiff created by David P. Shorthouse using above file.
        $this->shapes['reliefgrey'] = array(
            'shape' => $this->shape_path . "/GRAY_HR_SR_OB_DR/GRAY_HR_SR_OB_DR.tif",
            'type'  => MS_LAYER_RASTER,
            'sort'  => 1
        );

        //lakes outline
        $this->shapes['lakesOutline'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_lakes",
            'type'  => MS_LAYER_LINE,
            'sort'  => 2
        );

        //lakes
        $this->shapes['lakes'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_lakes",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 3
        );

        //oceans
        $this->shapes['oceans'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_ocean",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 3
        );

        //conservation
        $this->shapes['conservation'] = array(
            'shape' => $this->shape_path . "/conservation_international/hotspots_2011_polygons",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 3
        );

        //base map
        $this->shapes['base'] = array(
            'shape' => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_0_map_units",
            'type'  => MS_LAYER_LINE,
            'sort'  => 4
        );

        //stateprovinces_polygon
        $this->shapes['stateprovinces_polygon'] = array(
            'shape' => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_1_states_provinces",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 5
        );

        //stateprovinces
        $this->shapes['stateprovinces'] = array(
            'shape' => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_1_states_provinces_lines_shp",
            'type'  => MS_LAYER_LINE,
            'sort'  => 6
        );

        //lake names
        $this->shapes['lakenames'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_lakes",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 7
        );

        //rivers
        $this->shapes['rivers'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_rivers_lake_centerlines",
            'type'  => MS_LAYER_LINE,
            'sort'  => 8
        );

        //rivers
        $this->shapes['rivernames'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_rivers_lake_centerlines",
            'type'  => MS_LAYER_LINE,
            'sort'  => 9
        );

        //placename
        $this->shapes['placenames'] = array(
            'shape' => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_populated_places_simple",
            'type'  => MS_LAYER_POINT,
            'sort'  => 10
        );

        //State/Provincial labels
        $this->shapes['stateprovnames'] = array(
            'shape' => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_1_states_provinces",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 11
        );

        //Country labels
        $this->shapes['countrynames'] = array(
            'shape' => $this->shape_path . "/10m_cultural/10m_cultural/ne_10m_admin_0_map_units",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 12
        );

        //physicalLabels
        $this->shapes['physicalLabels'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_geography_regions_polys",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 13
        );

        //marineLabels
        $this->shapes['marineLabels'] = array(
            'shape' => $this->shape_path . "/10m_physical/ne_10m_geography_marine_polys",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 14
        );

        //hotspotLabels
        $this->shapes['hotspotLabels'] = array(
            'shape' => $this->shape_path . "/conservation_international/hotspots_2011_polygons",
            'type'  => MS_LAYER_POLYGON,
            'sort'  => 14
        );

    }

    /**
     * Execute the process. This is the main method that calls other req'd and optional methods.
     */
    public function execute()
    {
        $this->load_shapes();
        $this->set_web_config();
        $this->set_resolution();
        $this->set_units();
        $this->set_map_color();
        $this->set_output_format();
        $this->set_map_extent();
        $this->set_map_size();
        $this->set_zoom();
        $this->set_pan();
        $this->set_rotation();
        $this->set_crop();
        $this->add_regions();
        $this->add_layers();
        $this->add_graticules();
        $this->add_coordinates();
        $this->add_watermark();
        $this->prepare_output();

        return $this;
    }

    /**
     * Set config for web object, storage of tmp files
     */
    private function set_web_config()
    {
        $this->map_obj->set("name", "simplemappr");
        $this->map_obj->setFontSet($this->font_file);
        $this->map_obj->web->set("template", "template.html");
        $this->map_obj->web->set("imagepath", $this->tmp_path);
        $this->map_obj->web->set("imageurl", $this->tmp_url);
    }

    /**
     * Set resolution
     */
    private function set_resolution()
    {
        if ($this->output == 'tif') {
            $this->map_obj->set("defresolution", 300);
            $this->map_obj->set("resolution", 300);
        }
    }

    /**
     * Set units
     */
    private function set_units()
    {
        $units = (isset($this->projection) && $this->projection == $this->default_projection) ? MS_DD : MS_METERS;
        $this->map_obj->set("units", $units);
    }

    /**
     * Set map color
     */
    private function set_map_color()
    {
        $this->map_obj->imagecolor->setRGB(255, 255, 255);
    }

    /**
     * Set output format
     */
    private function set_output_format()
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
     */
    private function add_legend_scalebar()
    {
        if ($this->download && array_key_exists('legend', $this->options) && $this->options['legend']) {
            $this->add_legend();
        } else if (!$this->download) {
            $this->add_legend();
        }
        if ($this->download && array_key_exists('scalebar', $this->options) && $this->options['scalebar']) {
            $this->add_scalebar();
        } else if (!$this->download) {
            $this->add_scalebar();
        }
    }

    /**
     * Set the map extent
     */
    private function set_map_extent()
    {
        $ext = explode(',', $this->bbox_map);
        if (isset($this->projection) && $this->projection != $this->projection_map) {
            $origProjObj = ms_newProjectionObj(self::get_projection($this->projection_map));
            $newProjObj = ms_newProjectionObj(self::get_projection($this->default_projection));

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
     */ 
    private function set_map_size()
    {
        $this->map_obj->setSize($this->image_size[0], $this->image_size[1]);
        if ($this->is_resize()) {
            $this->map_obj->setSize($this->_download_factor*$this->image_size[0], $this->_download_factor*$this->image_size[1]);
        }
    }

    /**
     * Zoom In
     */
    private function set_zoom()
    {
        //Zoom in
        if (isset($this->bbox_rubberband) && $this->bbox_rubberband && !$this->is_resize()) {
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

        //Zoom out
        if (isset($this->zoom_out) && $this->zoom_out) {
            $zoom_point = ms_newPointObj();
            $zoom_point->setXY($this->map_obj->width/2, $this->map_obj->height/2);
            $this->map_obj->zoompoint(-2, $zoom_point, $this->map_obj->width, $this->map_obj->height, $this->map_obj->extent);
        }
    }

    /**
     * Set the pan direction
     */
    private function set_pan()
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
     */
    private function set_rotation()
    {
        if (isset($this->rotation) && $this->rotation != 0) {
            $this->map_obj->setRotation($this->rotation);
            if ($this->projection == $this->default_projection) {
                $this->reproject($this->default_projection, $this->projection);
            }
        }
    }

    /**
     * Set a new extent in the event of a crop action
     */
    private function set_crop()
    {
        if (isset($this->crop) && $this->crop && $this->bbox_rubberband && $this->is_resize()) {

            $bbox_rubberband = explode(',', $this->bbox_rubberband);

            //lower-left coordinate
            $ll_point = new \stdClass();
            $ll_point->x = $bbox_rubberband[0];
            $ll_point->y = $bbox_rubberband[3];
            $ll_coord = $this->pix2geo($ll_point);

            //upper-right coordinate
            $ur_point = new \stdClass();
            $ur_point->x = $bbox_rubberband[2];
            $ur_point->y = $bbox_rubberband[1];
            $ur_coord = $this->pix2geo($ur_point);

            //set the size as selected
            $width = abs($bbox_rubberband[2]-$bbox_rubberband[0]);
            $height = abs($bbox_rubberband[3]-$bbox_rubberband[1]);

            $this->map_obj->setSize($width, $height);
            if ($this->is_resize() && $this->_download_factor > 1) {
                $this->map_obj->setSize($this->_download_factor*$width, $this->_download_factor*$height);
            }

            //set the extent to match that of the crop
            $this->map_obj->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);
        }
    }

    public function get_shapes()
    {
        return $this->shapes;
    }

    /**
     * Add all coordinates to the map
     */
    public function add_coordinates()
    {
        $this->bad_points = array();
        if (isset($this->coords) && $this->coords) {
            //do this in reverse order because the legend will otherwise be presented in reverse order
            for ($j=count($this->coords)-1; $j>=0; $j--) {

                //clear out previous loop's selection
                $size = '';
                $shape = '';
                $color = '';

                $title = ($this->coords[$j]['title']) ? stripslashes($this->coords[$j]['title']) : '';
                $size = ($this->coords[$j]['size']) ? $this->coords[$j]['size'] : 8;
                if ($this->is_resize() && $this->_download_factor > 1) {
                    $size = $this->_download_factor*$size;
                }
                $shape = ($this->coords[$j]['shape']) ? $this->coords[$j]['shape'] : 'circle';
                $color = ($this->coords[$j]['color']) ? explode(" ", $this->coords[$j]['color']) : explode(" ", "0 0 0");
                if (!is_array($color) || !array_key_exists(0, $color) || !array_key_exists(1, $color) || !array_key_exists(2, $color)) {
                    $color = array(0,0,0);
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
                    $layer->setProjection(self::get_projection($this->default_projection));

                    $class = ms_newClassObj($layer);
                    if ($title != "") {
                        $class->set("name", $title);
                    }

                    $style = ms_newStyleObj($class);
                    $style->set("symbolname", $shape);
                    $style->set("size", $size);

                    if (substr($shape, 0, 4) == 'open') {
                        $style->color->setRGB($color[0], $color[1], $color[2]);
                    } else {
                        $style->color->setRGB($color[0], $color[1], $color[2]);
                        $style->outlinecolor->setRGB(85, 85, 85);
                    }

                    $new_shape = ms_newShapeObj(MS_SHAPE_POINT);
                    $new_line = ms_newLineObj();

                    $rows = explode("\n", self::remove_empty_lines($data));  //split the lines that have data
                    $points = array(); //create an array to hold unique locations

                    foreach ($rows as $row) {
                        $coord_array = self::make_coordinates($row);
                        $coord = new \stdClass();
                        $coord->x = ($coord_array[1]) ? self::clean_coord($coord_array[1]) : null;
                        $coord->y = ($coord_array[0]) ? self::clean_coord($coord_array[0]) : null;
                        //only add point when data are good & a title
                        if (self::check_coord($coord) && !empty($title)) {
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
     */
    public function add_regions()
    {
        if (isset($this->regions) && $this->regions) {  
            for ($j=count($this->regions)-1; $j>=0; $j--) {
                //clear out previous loop's selection
                $color = '';
                $title = ($this->regions[$j]['title']) ? stripslashes($this->regions[$j]['title']) : '';
                $color = ($this->regions[$j]['color']) ? explode(" ", $this->regions[$j]['color']) : explode(" ", "0 0 0");
                if (!is_array($color) || !array_key_exists(0, $color) || !array_key_exists(1, $color) || !array_key_exists(2, $color)) {
                    $color = array(0,0,0);
                }

                $data = trim($this->regions[$j]['data']);

                if ($data) {
                    $this->_legend_required = true;
                    $baselayer = true;
                    //grab the textarea for regions & split
                    $rows = explode("\n", self::remove_empty_lines($data));
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
                                    $qry['country'][] = "'[NAME]' ~* '".$region."$' || '[NAME_LONG]' ~* '".$region."$' || '[FORMAL_EN]' ~* '".$region."$'";
                                }
                            }
                        }
                    }

                    $layer = ms_newLayerObj($this->map_obj);
                    $layer->set("name", "query_layer_".$j);

                    if ($baselayer) {
                        $layer->set("data", $this->shapes['base']['shape']);
                        $layer->set("type", MS_LAYER_POLYGON);
                    } else {
                        $layer->set("data", $this->shapes['stateprovinces_polygon']['shape']);
                        $layer->set("type", $this->shapes['stateprovinces_polygon']['type']);
                    }

                    $layer->set("template", "template.html");
                    $layer->setProjection(self::get_projection($this->default_projection));

                    $query = ($baselayer) ? $qry['country'] : $qry['stateprovince'];

                    $layer->setFilter("(".implode(" || ", $query).")");

                    $class = ms_newClassObj($layer);
                    $class->set("name", $title);

                    $style = ms_newStyleObj($class);
                    $style->color->setRGB($color[0], $color[1], $color[2]);
                    $style->outlinecolor->setRGB(30, 30, 30);
                    $style->set("opacity", 75);

                    $layer->set("status", MS_ON);
                }

            }
        }
    }

    /**
     * Add all selected layers to the map
     */
    private function add_layers()
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
                $layer->setMetaData("wfs_encoding", "UTF-8");
                $layer->setMetaData("wms_title", $name);
                $layer->setMetaData("wms_srs", $srs_projections);
                $layer->setMetaData("gml_include_items", "all");
                $layer->setMetaData("gml_featureid", "OBJECTID");
                $layer->set("type", $this->shapes[$name]['type']);
                $layer->set("status", MS_ON);
                $layer->setConnectionType(MS_SHAPEFILE);
                $layer->set("data", $this->shapes[$name]['shape']);
                $layer->setProjection(self::get_projection($this->default_projection));
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

                case 'rivernames':
                case 'lakenames':
                    $layer->set("tolerance", 1);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "CP1252");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
                    $label->set("position", MS_UR);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);
                    break;

                case 'base':
                case 'stateprovinces':
                    $class = ms_newClassObj($layer);
                    $style = ms_newStyleObj($class);
                    $width = 1.25;
                    if (isset($this->border_thickness)) {
                        $width = $this->border_thickness;
                        if ($this->is_resize() 
                            && $this->_download_factor > 1
                            && array_key_exists('scalelinethickness', $this->options)
                            && $this->options['scalelinethickness']
                        ) {
                            $width = $this->border_thickness*$this->_download_factor/2;
                        }
                    }
                    $style->set("width", $width);
                    $style->color->setRGB(10, 10, 10);
                    break;

                case 'countrynames':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "UTF8");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*9 : 12);
                    $label->set("position", MS_CC);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);
                    break;

                case 'stateprovnames':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "UTF8");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*8 : 10);
                    $label->set("position", MS_CC);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);
                    break;

                case 'placenames':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "UTF8");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
                    $label->set("position", MS_UR);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);

                    $style = ms_newStyleObj($class);
                    $style->set("symbolname", "circle");
                    $style->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 6);
                    $style->color->setRGB(100, 100, 100);
                    break;

                case 'physicalLabels':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "UTF8");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
                    $label->set("position", MS_UR);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);
                    break;

                case 'marineLabels':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "name");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "UTF8");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
                    $label->set("position", MS_UR);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);
                    break;

                case 'hotspotLabels':
                    $layer->set("tolerance", 5);
                    $layer->set("toleranceunits", "pixels");
                    $layer->set("labelitem", "NAME");

                    $label = new \labelObj();
                    $label->set("font", "arial");
                    $label->set("type", MS_TRUETYPE);
                    $label->set("encoding", "UTF8");
                    $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*7 : 8);
                    $label->set("position", MS_UR);
                    $label->set("offsetx", 3);
                    $label->set("offsety", 3);
                    $label->set("partials", MS_FALSE);
                    $label->color->setRGB(10, 10, 10);

                    $class = ms_newClassObj($layer);
                    $class->addLabel($label);
                    break;

                default:
                }
            }
        }
    }

    /**
     * Add a watermark
     */
    private function add_watermark()
    {
        if (isset($this->watermark) && $this->watermark) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'watermark');
            $layer->set("type", MS_LAYER_ANNOTATION);
            $layer->set("status", MS_ON);
            $layer->set("transform", MS_FALSE);
            $layer->set("sizeunits", MS_PIXELS);

            $class = ms_newClassObj($layer);
            $class->settext("http://www.simplemappr.net");

            $label = new \labelObj();
            $label->set("font", "arial");
            $label->set("type", MS_TRUETYPE);
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
     */
    public function add_graticules()
    {
        if (isset($this->graticules) && $this->graticules) {
            $layer = ms_newLayerObj($this->map_obj);
            $layer->set("name", 'grid');
            $layer->set("type", MS_LAYER_LINE);
            $layer->set("status", MS_ON);
            $layer->setProjection(self::get_projection($this->default_projection));

            $class = ms_newClassObj($layer);

            if ($this->gridlabel != 0) {
                $label = new \labelObj();
                $label->set("font", "arial");
                $label->set("type", MS_TRUETYPE);
                $label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*9 : 10);
                $label->set("position", MS_CC);
                $label->color->setRGB(30, 30, 30);
                $class->addLabel($label);
            }

            $style = ms_newStyleObj($class);
            $style->color->setRGB(200, 200, 200);

            ms_newGridObj($layer);
            $minx = $this->map_obj->extent->minx;
            $maxx = $this->map_obj->extent->maxx;

            //project the extent back to default such that we can work with proper tick marks
            if ($this->projection != $this->default_projection && $this->projection == $this->projection_map) {
                $origProjObj = ms_newProjectionObj(self::get_projection($this->projection));
                $newProjObj = ms_newProjectionObj(self::get_projection($this->default_projection));

                $poPoint1 = ms_newPointObj();
                $poPoint1->setXY($this->map_obj->extent->minx, $this->map_obj->extent->miny);

                $poPoint2 = ms_newPointObj();
                $poPoint2->setXY($this->map_obj->extent->maxx, $this->map_obj->extent->maxy);

                $poPoint1->project($origProjObj, $newProjObj);
                $poPoint2->project($origProjObj, $newProjObj);

                $minx = $poPoint1->x;
                $maxx = $poPoint2->x;
            }

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

            $layer->grid->set("labelformat", ($this->gridspace) ? "DD" : $labelformat);
            $layer->grid->set("maxarcs", $ticks);
            $layer->grid->set("maxinterval", ($this->gridspace) ? $this->gridspace : $ticks);
            $layer->grid->set("maxsubdivide", 2);

        }
    }

    /**
     * Create the legend file
     */
    public function add_legend()
    {
        if ($this->_legend_required) {
            $this->map_obj->legend->set("keysizex", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*15 : 20);
            $this->map_obj->legend->set("keysizey", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*15 : 20);
            $this->map_obj->legend->set("keyspacingx", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*3 : 5);
            $this->map_obj->legend->set("keyspacingy", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*3 : 5);
            $this->map_obj->legend->set("postlabelcache", 1);
            $this->map_obj->legend->label->set("font", "arial");
            $this->map_obj->legend->label->set("type", MS_TRUETYPE);
            $this->map_obj->legend->label->set("position", 1);
            $this->map_obj->legend->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*8 : 10);
            $this->map_obj->legend->label->set("antialias", 50);
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
    private function is_resize()
    {
        if ($this->download || $this->output == 'pptx' || $this->output == 'docx') {
            return true;
        }
        return false;
    }

    /**
     * Create a scalebar image
     */
    public function add_scalebar()
    {
        $this->map_obj->scalebar->set("style", 0);
        $this->map_obj->scalebar->set("intervals", 3);
        $this->map_obj->scalebar->set("height", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*4 : 8);
        $this->map_obj->scalebar->set("width", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*100 : 200);
        $this->map_obj->scalebar->color->setRGB(30, 30, 30);
        $this->map_obj->scalebar->outlinecolor->setRGB(0, 0, 0);
        $this->map_obj->scalebar->set("units", 4); // 1 feet, 2 miles, 3 meter, 4 km
        $this->map_obj->scalebar->label->set("font", "arial");
        $this->map_obj->scalebar->label->set("type", MS_TRUETYPE);
        $this->map_obj->scalebar->label->set("size", ($this->is_resize() && $this->_download_factor > 1) ? $this->_download_factor*5 : 8);
        $this->map_obj->scalebar->label->set("antialias", 50);
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
    public function get_bad_points()
    {
        return implode('<br />', $this->bad_points);
    }

    /**
     * Add a border to a downloaded map image
     */
    private function add_border()
    {
        if ($this->is_resize() && array_key_exists('border', $this->options) && ($this->options['border'] == 1 || $this->options['border'] == 'true')) {
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
     */
    private function prepare_output()
    {
        if (isset($this->projection)) {
            $this->reproject($this->projection_map, $this->projection);
            $this->add_legend_scalebar();
            $this->add_border();
            $this->image = $this->map_obj->drawQuery();
        }
    }

    /**
     * Reproject a $map from one projection to another.
     *
     * @param string $input_projection The input projection.
     * @param string $output_projection The output projection.
     * @return void
     */
    private function reproject($input_projection, $output_projection)
    {
        $this->set_origin($output_projection);

        $origProjObj = ms_newProjectionObj(self::get_projection($input_projection));
        $newProjObj = ms_newProjectionObj(self::get_projection($output_projection));

        $oRect = $this->map_obj->extent;
        $oRect->project($origProjObj, $newProjObj);

        $this->map_obj->setExtent($oRect->minx, $oRect->miny, $oRect->maxx, $oRect->maxy);
        $this->map_obj->setProjection(self::get_projection($output_projection));
    }

    /**
     * Change the longitude of the natural origin for Lambert projections.
     *
     * @param string $output_projection The output projection.
     */
    private function set_origin($output_projection)
    {
        $lambert_projections = array('esri:102009', 'esri:102015', 'esri:102014', 'esri:102102', 'esri:102024', 'epsg:3112');

        if (in_array($this->projection, $lambert_projections) && $this->origin && ($this->origin >= -180) && ($this->origin <= 180)) {
            self::$accepted_projections[$output_projection]['proj'] = preg_replace('/lon_0=(.*?),/', 'lon_0='.$this->origin.',', self::get_projection($output_projection));
        }
    }

    /**
     * Convert image coordinates to map coordinates
     *
     * @param obj $point, (x,y) coordinates in pixels
     * @return obj $newPoint reprojected point in map coordinates
     */
    public function pix2geo($point)
    {
        $newPoint = new \stdClass();
        $deltaX = abs($this->map_obj->extent->maxx - $this->map_obj->extent->minx);
        $deltaY = abs($this->map_obj->extent->maxy - $this->map_obj->extent->miny);

        $newPoint->x = $this->map_obj->extent->minx + ($point->x*$deltaX)/(float)$this->image_size[0];
        $newPoint->y = $this->map_obj->extent->miny + (((float)$this->image_size[1] - $point->y)*$deltaY)/(float)$this->image_size[1];
        return $newPoint;
    }

}
