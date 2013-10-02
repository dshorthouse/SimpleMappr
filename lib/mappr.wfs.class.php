<?php

/**************************************************************************

File: mappr.wfs.class.php

Description: Extends the base map class for SimpleMappr to support WFS. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010 David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

require_once('mappr.class.php');

class MapprWfs extends Mappr {

  /* the request object for WFS and WMS */ 
  private $req = "";

  /* filter simplification */
  private $filter_simplify;

  /* columns to filter on */ 
  private $filter_columns = array();

  /**
  * Override the method in the MAPPR class
  */
  public function get_request() {
    $this->params['VERSION']      = $this->load_param('VERSION', '1.0.0');
    $this->params['REQUEST']      = $this->load_param('REQUEST', 'GetCapabilities');
    $this->params['TYPENAME']     = $this->load_param('TYPENAME', '');
    $this->params['MAXFEATURES']  = $this->load_param('MAXFEATURES', $this->get_max_features());
    $this->params['OUTPUTFORMAT'] = $this->load_param('OUTPUTFORMAT', 'gml2');
    $this->params['FILTER']       = $this->load_param('FILTER', null);

    $input = file_get_contents("php://input");
    if($input) {
      $xml = new XMLReader();
      $xml2 = new XMLReader();
      $xml->XML($input);
      while($xml->read()) {
        if($xml->name == 'wfs:Query') {
          $this->params['REQUEST'] = 'GetFeature';
          $this->params['TYPENAME'] = str_replace("feature:", "",    $xml->getAttribute('typeName'));
        }
        if($xml->name == 'ogc:Filter') {
          $filter = $xml->readOuterXML();
          $this->params['REQUEST'] = 'GetFeature';
          $this->params['FILTER'] = $filter;
          $xml2->XML($filter);
          while($xml2->read()) {
            if($xml2->name == 'ogc:PropertyName') {
              $this->filter_columns[$xml2->readString()] = $xml2->readString();
            }
          }
          break;
        }
      }
    }

    $this->layers     = array('stateprovinces_polygon' => 'on');
    $this->bbox_map   = $this->load_param('bbox', '-180,-90,180,90');
    $this->download   = false;
    $this->output     = false;
    $this->image_size = array(900,450);

    return $this;
  }

  /*
  * Set the simplification filter for a WFS request
  * @param integer
  */
  public function set_max_features($int) {
    $this->filter_simplify = $int;
  }

  private function get_max_features() {
    return $this->filter_simplify;
  }

  /**
  * Construct metadata for WFS
  */
  public function make_service() {
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

  private function make_request() {
    $this->req = ms_newOwsRequestObj();
    $this->req->setParameter("SERVICE", "wfs");
    $this->req->setParameter("VERSION", $this->params['VERSION']);
    $this->req->setParameter("REQUEST", $this->params['REQUEST']);

    $this->req->setParameter('TYPENAME', 'stateprovinces_polygon');
    $this->req->setParameter('MAXFEATURES', $this->params['MAXFEATURES']);
    if($this->params['REQUEST'] != 'DescribeFeatureType') { $this->req->setParameter('OUTPUTFORMAT', $this->params['OUTPUTFORMAT']); }
    if($this->params['FILTER']) { $this->req->setParameter('FILTER', $this->params['FILTER']); }

    return $this;
  }

  /**
  * Produce the  final output
  */
  public function get_output() {
    ms_ioinstallstdouttobuffer();
    $this->map_obj->owsDispatch($this->req);
    $contenttype = ms_iostripstdoutbuffercontenttype();
    $buffer = ms_iogetstdoutbufferstring();
    header('Content-type: application/xml');
    echo $buffer;
    ms_ioresethandlers();
  }

}
?>