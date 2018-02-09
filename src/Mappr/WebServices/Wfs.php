<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
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
namespace SimpleMappr\Mappr\WebServices;

use XMLReader;
use SimpleMappr\Constants\AcceptedProjections;
use SimpleMappr\Utility;
use SimpleMappr\Mappr\Mappr;

/**
 * Web Feature Service (WFS) for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Wfs extends Mappr
{
    /**
     * Request object for WFS and WMS
     *
     * @var object $_req
     */
    private $_req = "";

    /**
     * Columns to filter on
     *
     * @var array $_filter_columns
     */
    private $_filter_columns = [];

    /**
     * Layers to include in WFS request
     *
     * @var array $_wfs_layers
     */
    private $_wfs_layers = [];

    /**
     * Constructor
     *
     * @param array $layers The layers to set as on
     */
    public function __construct($layers = [])
    {
        $shapes = parent::getShapefileConfig();
        if (!empty($layers)) {
            foreach ($layers as $layer) {
                if (in_array($layer, array_keys($shapes))
                    && $shapes[$layer]['type'] !== MS_LAYER_RASTER
                ) {
                    $this->_wfs_layers[$layer] = 'on';
                }
            }
        } else {
            foreach ($shapes as $key => $shape) {
                if ($shape['type'] !== MS_LAYER_RASTER) {
                    $this->_wfs_layers[$key] = 'on';
                }
            }
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
        $this->params['VERSION']      = Utility::loadParam('VERSION', '1.0.0');
        $this->params['REQUEST']      = Utility::loadParam('REQUEST', 'GetCapabilities');
        $this->params['TYPENAME']     = Utility::loadParam('TYPENAME', "");
        $this->params['MAXFEATURES']  = Utility::loadParam('MAXFEATURES', 1000);
        $this->params['OUTPUTFORMAT'] = Utility::loadParam('OUTPUTFORMAT', 'gml2');
        $this->params['FILTER']       = Utility::loadParam('FILTER', null);
        $this->params['BBOX']         = Utility::loadParam('BBOX', '-180,-90,180,90');
        $this->params['SRSNAME']      = Utility::loadParam('SRSNAME', 'EPSG:4326');

        $input = file_get_contents("php://input");
        if ($input) {
            $xml = new XMLReader();
            $xml2 = new XMLReader();
            $xml->XML($input);
            while ($xml->read()) {
                if ($xml->name == 'wfs:Query') {
                    $this->params['REQUEST'] = 'GetFeature';
                    $this->params['TYPENAME'] = str_replace("feature:", "", $xml->getAttribute('typeName'));
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

        $this->layers     = $this->_wfs_layers;
        $this->bbox_map   = Utility::loadParam('bbox', '-180,-90,180,90');
        $this->download   = false;
        $this->output     = false;

        return $this;
    }

    /**
     * Construct metadata for WFS
     *
     * @return object $this
     */
    public function makeService()
    {
        $this->map_obj->setMetaData("name", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetadata("wfs_encoding", "UTF-8");
        $this->map_obj->setMetaData("wfs_title", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_onlineresource", MAPPR_URL . "/wfs/?");

        $srs_projections = strtoupper(implode(array_keys(AcceptedProjections::$projections), " "));

        $this->map_obj->setMetaData("wfs_srs", $srs_projections);
        $this->map_obj->setMetaData("wfs_abstract", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_enable_request", "*");
        $this->map_obj->setMetaData("wfs_connectiontimeout", "60");

        $this->_makeRequest();

        return $this;
    }

    /**
     * Implement createOutput method
     *
     * @return string The buffer content
     */
    public function createOutput()
    {
        ms_ioinstallstdouttobuffer();
        $this->map_obj->owsDispatch($this->_req);
        ms_iostripstdoutbuffercontenttype();
        $buffer = mb_convert_encoding(ms_iogetstdoutbufferstring(), "UTF-8");
        ms_ioresethandlers();
        return $buffer;
    }

    /**
     * Make the request
     *
     * @return object $this
     */
    private function _makeRequest()
    {
        $this->_req = new \OWSRequestObj();
        $this->_req->setParameter("SERVICE", "WFS");
        $this->_req->setParameter("VERSION", $this->request->params['VERSION']);
        $this->_req->setParameter("REQUEST", $this->request->params['REQUEST']);
        $this->_req->setParameter("BBOX", $this->request->params['BBOX']);
        $this->_req->setParameter("TYPENAME", $this->request->params['TYPENAME']);
        $max_features = $this->request->params['MAXFEATURES'];
        if ($this->request->params['MAXFEATURES'] > 1000) {
            $max_features = 1000;
        }
        $this->_req->setParameter("MAXFEATURES", $max_features);
        $this->_req->setParameter("SRSNAME", $this->request->params['SRSNAME']);

        if (strtolower($this->request->params["REQUEST"]) != 'describefeaturetype') {
            $this->_req->setParameter('OUTPUTFORMAT', $this->request->params['OUTPUTFORMAT']);
        }
        if ($this->request->params["FILTER"] != "") {
            $this->_req->setParameter('FILTER', $this->request->params['FILTER']);
        }

        return $this;
    }
}
