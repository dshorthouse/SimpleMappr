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
 * Web Map Service (WMS) for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
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
    private $_default_wms_layers = array(
        'lakes' => 'on',
        'rivers' => 'on',
        'oceans' => 'on',
        'conservation' => 'on',
        'stateprovinces' => 'on',
        'relief' => 'on',
        'reliefgrey' => 'on',
        'blueMarble' => 'on',
        'ecoregions' => 'on'
    );

    private $_wms_layers = array();

    public function __construct($layers = array())
    {
        if (!empty($layers)) {
            foreach($layers as $layer) {
                if (in_array($layer, array_keys($this->_default_wms_layers))) {
                    $this->_wms_layers[$layer] = 'on';
                }
            }
        } else {
            $this->_wms_layers = $this->_default_wms_layers;
        }
        parent::__construct();
    }

    /**
     * Implement getRequest method
     *
     * @return object $this
     */
    public function getRequest()
    {
        $this->params['VERSION']      = Utility::loadParam('VERSION', '1.1.1');
        $this->params['REQUEST']      = Utility::loadParam('REQUEST', 'GetCapabilities');
        $this->params['LAYERS']       = Utility::loadParam('LAYERS', "");
        $this->params['MAXFEATURES']  = Utility::loadParam('MAXFEATURES', $this->_getMaxFeatures());
        $this->params['FORMAT']       = Utility::loadParam('FORMAT', 'image/png');
        $this->params['FILTER']       = Utility::loadParam('FILTER', null);
        $this->params['SRS']          = Utility::loadParam('SRS', 'epsg:4326');
        $this->params['CRS']          = Utility::loadParam('CRS', 'CRS:84');
        $this->params['BBOX']         = Utility::loadParam('BBOX', '-180,-90,180,90');
        $this->params['WIDTH']        = Utility::loadParam('WIDTH', '200');
        $this->params['HEIGHT']       = Utility::loadParam('HEIGHT', '100');
        $this->params['TRANSPARENT']  = Utility::loadParam('TRANSPARENT', true);

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

        $this->layers     = $this->_wms_layers;
        $this->bbox_map   = Utility::loadParam('bbox', '-180,-90,180,90');
        $this->download   = false;
        $this->output     = false;
        $this->image_size = array(900,450);

        return $this;
    }

    /**
     * Set the simplification filter for a WMS request
     *
     * @param int $int The maximum number of features
     *
     * @return void
     */
    public function setMaxFeatures($int)
    {
        $this->_filter_simplify = $int;
    }

    /**
     * Get the maximum number of features
     *
     * @return int
     */
    private function _getMaxFeatures()
    {
        return $this->_filter_simplify;
    }

    /**
     * Construct metadata for WMS
     *
     * @return object $this
     */
    public function makeService()
    {
        $this->map_obj->setMetaData("name", "SimpleMappr Web Map Service");
        $this->map_obj->setMetaData("wms_title", "SimpleMappr Web Map Service");
        $this->map_obj->setMetaData("wms_onlineresource", "http://" . $_SERVER['HTTP_HOST'] . "/wms/?");

        $srs_projections = implode(array_keys(AcceptedProjections::$projections), " ");

        $this->map_obj->setMetaData("wms_srs", $srs_projections);
        $this->map_obj->setMetaData("wms_abstract", "SimpleMappr Web Map Service");
        $this->map_obj->setMetaData("wms_enable_request", "*");
        $this->map_obj->setMetaData("wms_connectiontimeout", "60");

        $this->_makeRequest();
        return $this;
    }

    /**
     * Make the request
     *
     * @return object $this
     */
    private function _makeRequest()
    {
        $this->_req = ms_newOwsRequestObj();
        $this->_req->setParameter("SERVICE", "wms");
        $this->_req->setParameter("VERSION", $this->request->params['VERSION']);
        $this->_req->setParameter("REQUEST", $this->request->params['REQUEST']);
        $this->_req->setParameter("BBOX", $this->request->params['BBOX']);
        $this->_req->setParameter("LAYERS", $this->request->params['LAYERS']);
        $this->_req->setParameter("SRS", $this->request->params['SRS']);
        $this->_req->setParameter("WIDTH", $this->request->params['WIDTH']);
        $this->_req->setParameter("HEIGHT", $this->request->params['HEIGHT']);
        $this->_req->setParameter("TRANSPARENT", $this->request->params['TRANSPARENT']);

        if ($this->params["REQUEST"] != 'DescribeFeatureType') {
            $this->_req->setParameter('FORMAT', $this->request->params['FORMAT']);
        }
        if ($this->params["FILTER"]) {
            $this->_req->setParameter('FILTER', $this->request->params['FILTER']);
        }

        return $this;
    }

    /**
     * Implement createOutput method
     *
     * @return void
     */
    public function createOutput()
    {
        ms_ioinstallstdouttobuffer();
        $this->map_obj->owsDispatch($this->_req);
        $contenttype = ms_iostripstdoutbuffercontenttype();
        if (strtolower($this->request->params['REQUEST']) == 'getcapabilities') {
            Header::setHeader("xml");
            echo ms_iogetstdoutbufferstring();
        } else if (strtolower($this->request->params['REQUEST']) == 'getmap' || strtolower($this->request->params['REQUEST']) == 'getlegendgraphic') {
            Header::setHeader();
            header('Content-type: ' . $contenttype);
            ms_iogetstdoutbufferbytes();
        }
        ms_ioresethandlers();
    }

}