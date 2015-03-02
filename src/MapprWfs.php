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

/**
 * Web Feature Service (WFS) for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
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
        'stateprovinces_polygon' => 'on',
        'ecoregions' => 'on'
    );

    /**
     * Override the method in the parent class
     *
     * @return object $this
     */
    public function getRequest()
    {
        $this->params['VERSION']      = $this->loadParam('VERSION', '1.0.0');
        $this->params['REQUEST']      = $this->loadParam('REQUEST', 'GetCapabilities');
        $this->params['TYPENAME']     = $this->loadParam('TYPENAME', "");
        $this->params['MAXFEATURES']  = $this->loadParam('MAXFEATURES', $this->_getMaxFeatures());
        $this->params['OUTPUTFORMAT'] = $this->loadParam('OUTPUTFORMAT', 'gml2');
        $this->params['FILTER']       = $this->loadParam('FILTER', null);

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
        $this->bbox_map   = $this->loadParam('bbox', '-180,-90,180,90');
        $this->download   = false;
        $this->output     = false;
        $this->image_size = array(900,450);

        return $this;
    }

    /**
     * Set the simplification filter for a WFS request
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
     * Construct metadata for WFS
     *
     * @return object $this
     */
    public function makeService()
    {
        $this->map_obj->setMetaData("name", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetadata("wfs_encoding", "CP1252");
        $this->map_obj->setMetaData("wfs_title", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_onlineresource", "http://" . $_SERVER['HTTP_HOST'] . "/wfs/?");

        $srs_projections = strtoupper(implode(array_keys(Mappr::$accepted_projections), " "));

        $this->map_obj->setMetaData("wfs_srs", $srs_projections);
        $this->map_obj->setMetaData("wfs_abstract", "SimpleMappr Web Feature Service");
        $this->map_obj->setMetaData("wfs_enable_request", "*");
        $this->map_obj->setMetaData("wfs_connectiontimeout", "60");

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
        $this->_req->setParameter("SERVICE", "WFS");
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
     * Implement method in parent class to createOutput
     *
     * @return string The buffer content
     */
    public function createOutput()
    {
        ms_ioinstallstdouttobuffer();
        $this->map_obj->owsDispatch($this->_req);
        $contenttype = ms_iostripstdoutbuffercontenttype();
        return ms_iogetstdoutbufferstring();
        ms_ioresethandlers();
    }

}