<?php
namespace SimpleMappr;

/**
 * MapprWms.class.php released under MIT License
 * Extends Mappr class & enables WMS endpoint on SimpleMappr
 *
 * PHP Version >= 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse {{{
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
 * }}}
 */
class MapprWms extends Mappr
{
    /* the request object for WFS and WMS */ 
    private $_req = "";

    /* filter simplification */
    private $_filter_simplify;

    /* columns to filter on */ 
    private $_filter_columns = array();

    /* layers */
    public $wms_layers = array(
        'lakes' => 'on',
        'rivers' => 'on',
        'oceans' => 'on',
        'conservation' => 'on',
        'stateprovinces_polygon' => 'on',
        'relief' => 'on',
        'reliefgrey' => 'on'
    );

    /**
     * Override the method in the MAPPR class
     */
    public function get_request()
    {
        $this->params['VERSION']      = $this->load_param('VERSION', '1.1.1');
        $this->params['REQUEST']      = $this->load_param('REQUEST', 'GetCapabilities');
        $this->params['LAYERS']       = $this->load_param('LAYERS', '');
        $this->params['MAXFEATURES']  = $this->load_param('MAXFEATURES', $this->get_max_features());
        $this->params['FORMAT']       = $this->load_param('FORMAT', 'image/png');
        $this->params['FILTER']       = $this->load_param('FILTER', null);
        $this->params['SRS']          = $this->load_param('SRS', 'epsg:4326');
        $this->params['CRS']          = $this->load_param('CRS', 'CRS:84');
        $this->params['BBOX']         = $this->load_param('BBOX', '-180,-90,180,90');
        $this->params['WIDTH']        = $this->load_param('WIDTH', '200');
        $this->params['HEIGHT']       = $this->load_param('HEIGHT', '100');
        $this->params['TRANSPARENT']  = $this->load_param('TRANSPARENT', true);

        $input = file_get_contents("php://input");
        if ($input) {
            $xml = new \XMLReader();
            $xml2 = new \XMLReader();
            $xml->XML($input);
            while ($xml->read()) {
                if ($xml->name == 'wms:Query') {
                    $this->params['REQUEST'] = 'GetMap';
                    $this->params['LAYERS'] = str_replace("feature:", "",    $xml->getAttribute('typeName'));
                }
                if ($xml->name == 'ogc:Filter') {
                    $filter = $xml->readOuterXML();
                    $this->params['REQUEST'] = 'GetMap';
                    $this->params['FILTER'] = $filter;
                    $xml2->XML($filter);
                    while ($xml2->read()) {
                        if ($xml2->name == 'ogc:PropertyName') {
                            $this->_filter_columns[$xml2->readString()] = $xml2->readString();
                        }
                    }
                    break;
                }
            }
        }

        $this->layers     = $this->wms_layers;
        $this->bbox_map   = $this->load_param('bbox', '-180,-90,180,90');
        $this->download   = false;
        $this->output     = false;
        $this->image_size = array(900,450);

        return $this;
    }

    /**
     * Set the simplification filter for a WFS request
     *
     * @param int $int The maximum number of features
     * @return void
     */
    public function set_max_features($int)
    {
        $this->_filter_simplify = $int;
    }

    private function get_max_features()
    {
        return $this->_filter_simplify;
    }

    /**
     * Construct metadata for WFS
     */
    public function make_service()
    {
        $this->map_obj->setMetaData("name", "SimpleMappr Web Map Service");
        $this->map_obj->setMetaData("wms_title", "SimpleMappr Web Map Service");
        $this->map_obj->setMetaData("wms_onlineresource", "http://" . $_SERVER['HTTP_HOST'] . "/wms/?");

        $srs_projections = implode(array_keys(Mappr::$accepted_projections), " ");

        $this->map_obj->setMetaData("wms_srs", $srs_projections);
        $this->map_obj->setMetaData("wms_abstract", "SimpleMappr Web Map Service");
        $this->map_obj->setMetaData("wms_enable_request", "*");
        $this->map_obj->setMetaData("wms_connectiontimeout", "60");

        $this->make_request();
        return $this;
    }

    private function make_request()
    {
        $this->_req = ms_newOwsRequestObj();
        $this->_req->setParameter("SERVICE", "wms");
        $this->_req->setParameter("VERSION", $this->params['VERSION']);
        $this->_req->setParameter("REQUEST", $this->params['REQUEST']);
        $this->_req->setParameter("BBOX", $this->params['BBOX']);
        $this->_req->setParameter("LAYERS", $this->params['LAYERS']);
        $this->_req->setParameter("SRS", $this->params['SRS']);
        $this->_req->setParameter("WIDTH", $this->params['WIDTH']);
        $this->_req->setParameter("HEIGHT", $this->params['HEIGHT']);
        $this->_req->setParameter("TRANSPARENT", $this->params['TRANSPARENT']);

        if ($this->params["REQUEST"] != 'DescribeFeatureType') {
            $this->_req->setParameter('FORMAT', $this->params['FORMAT']);
        }
        if ($this->params["FILTER"]) {
            $this->_req->setParameter('FILTER', $this->params['FILTER']);
        }

        return $this;
    }

    /**
     * Produce the  final output
     */
    public function create_output()
    {
        ms_ioinstallstdouttobuffer();
        $this->map_obj->owsDispatch($this->_req);
        $contenttype = ms_iostripstdoutbuffercontenttype();
        if (strtolower($this->params['REQUEST']) == 'getcapabilities') {
            Header::set_header("xml");
            echo ms_iogetstdoutbufferstring();
        } else if (strtolower($this->params['REQUEST']) == 'getmap' || strtolower($this->params['REQUEST']) == 'getlegendgraphic') {
            Header::set_header();
            header('Content-type: ' . $contenttype);
            ms_iogetstdoutbufferbytes();
        }
        ms_ioresethandlers();
    }

}