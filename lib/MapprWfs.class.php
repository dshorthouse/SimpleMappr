<?php
namespace SimpleMappr;

/**
 * MapprWfs.class.php released under MIT License
 * Extends Mappr class & enables WFS endpoint on SimpleMappr
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
class MapprWfs extends Mappr
{
    /* the request object for WFS and WMS */ 
    private $_req = "";

    /* filter simplification */
    private $_filter_simplify;

    /* columns to filter on */ 
    private $_filter_columns = array();

    /* layers */
    public $wfs_layers = array(
        'lakes' => 'on',
        'rivers' => 'on',
        'oceans' => 'on',
        'conservation' => 'on',
        'stateprovinces_polygon' => 'on'
    );

    /**
     * Override the method in the MAPPR class
     */
    public function get_request()
    {
        $this->params['VERSION']      = $this->load_param('VERSION', '1.0.0');
        $this->params['REQUEST']      = $this->load_param('REQUEST', 'GetCapabilities');
        $this->params['TYPENAME']     = $this->load_param('TYPENAME', '');
        $this->params['MAXFEATURES']  = $this->load_param('MAXFEATURES', $this->get_max_features());
        $this->params['OUTPUTFORMAT'] = $this->load_param('OUTPUTFORMAT', 'gml2');
        $this->params['FILTER']       = $this->load_param('FILTER', null);

        $input = file_get_contents("php://input");
        if ($input) {
            $xml = new \XMLReader();
            $xml2 = new \XMLReader();
            $xml->XML($input);
            while ($xml->read()) {
                if ($xml->name == 'wfs:Query') {
                    $this->params['REQUEST'] = 'GetFeature';
                    $this->params['TYPENAME'] = str_replace("feature:", "",    $xml->getAttribute('typeName'));
                }
                if ($xml->name == 'ogc:Filter') {
                    $filter = $xml->readOuterXML();
                    $this->params['REQUEST'] = 'GetFeature';
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

        $this->layers     = $this->wfs_layers;
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
        $this->map_obj->setMetaData("name", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_title", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_onlineresource", "http://" . $_SERVER['HTTP_HOST'] . "/wfs/?");

        $srs_projections = implode(array_keys(Mappr::$accepted_projections), " ");

        $this->map_obj->setMetaData("wfs_srs", $srs_projections);
        $this->map_obj->setMetaData("wfs_abstract", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_enable_request", "*");
        $this->map_obj->setMetaData("wfs_connectiontimeout", "60");

        $this->make_request();

        return $this;
    }

    private function make_request()
    {
        $this->_req = ms_newOwsRequestObj();
        $this->_req->setParameter("SERVICE", "wfs");
        $this->_req->setParameter("VERSION", $this->params['VERSION']);
        $this->_req->setParameter("REQUEST", $this->params['REQUEST']);
        $this->_req->setParameter("TYPENAME", $this->params['TYPENAME']);
        $this->_req->setParameter("MAXFEATURES", $this->params['MAXFEATURES']);

        if ($this->params["REQUEST"] != 'DescribeFeatureType') {
            $this->_req->setParameter('OUTPUTFORMAT', $this->params['OUTPUTFORMAT']);
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
        Header::set_header("xml");
        echo ms_iogetstdoutbufferstring();
        ms_ioresethandlers();
    }

}