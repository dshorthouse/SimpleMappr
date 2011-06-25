<?php
require_once ('../includes/mapprservice.class.php');

class MAPPRWFS extends MAPPR {

  /* the request object for WFS and WMS */ 
  private $_req = "";

  /* the filter simplification */
  private $_filter_simplify;

  private $_filter_columns = array();

  /**
  * Override the getRequest() method in the MAPPR class
  */
  public function getRequest() {
    $this->params['VERSION']      = $this->loadParam('VERSION', '1.0.0');
    $this->params['REQUEST']      = $this->loadParam('REQUEST', 'GetCapabilities');
    $this->params['TYPENAME']     = $this->loadParam('TYPENAME', '');
    $this->params['MAXFEATURES']  = $this->loadParam('MAXFEATURES', $this->getMaxFeatures());
    $this->params['OUTPUTFORMAT'] = $this->loadParam('OUTPUTFORMAT', 'gml2');
    $this->params['FILTER']       = $this->loadParam('FILTER', null);

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
		      $this->_filter_columns[$xml2->readString()] = $xml2->readString();
	        }
          }
          break;
        }
      }
    }

    $this->layers           = array('stateprovinces' => 'on', 'placenames' => 'on');
    $this->bbox_map         = $this->loadParam('bbox', '-180,-90,180,90');
    $this->download         = false;

  }

  /*
  * Set the simplification filter for a WFS request
  * @param integer
  */
  public function setMaxFeatures($int) {
    $this->_filter_simplify = $int;
  }

  private function getMaxFeatures() {
    return $this->_filter_simplify;
  }

  private function getFilterColumns() {
    unset($this->_filter_columns['wkb_geometry']);
    $filters = array_filter($this->_filter_columns);
    return ($filters) ? ", t." . implode(", t.", $filters) : "";
  }

  /**
  * Construct metadata for WFS
  */
  public function makeService() {
    $this->_map_obj->setMetaData("name", "SimpleMappr Web Feature Service");
    $this->_map_obj->setMetaData("wfs_title", "SimpleMappr Web Feature Service");
    $this->_map_obj->setMetaData("wfs_onlineresource", "http://" . $_SERVER['HTTP_HOST'] . "/wfs/?");

    $srs_projections = implode(array_keys(MAPPR::$_accepted_projections), " ");

    $this->_map_obj->setMetaData("wfs_srs", $srs_projections);
    $this->_map_obj->setMetaData("wfs_abstract", "SimpleMappr Web Feature Service");
        
    $this->_map_obj->setMetaData("wfs_connectiontimeout", "60");

    $this->makeRequest();
  }

  private function makeRequest() {
    $this->_req = ms_newOwsRequestObj();
    $this->_req->setParameter("SERVICE", "wfs");
    $this->_req->setParameter("VERSION", $this->params['VERSION']);
    $this->_req->setParameter("REQUEST", $this->params['REQUEST']);
        
    $this->_req->setParameter('TYPENAME', $this->params['TYPENAME']);
    $this->_req->setParameter('MAXFEATURES', $this->params['MAXFEATURES']);
    if($this->params['REQUEST'] != 'DescribeFeatureType') $this->_req->setParameter('OUTPUTFORMAT', $this->params['OUTPUTFORMAT']);
    if($this->params['FILTER']) $this->_req->setParameter('FILTER', $this->params['FILTER']);
  }

  /**
  * Produce the  final output
  */
  public function produceOutput() {
    ms_ioinstallstdouttobuffer();
    $this->_map_obj->owsDispatch($this->_req);
    $contenttype = ms_iostripstdoutbuffercontenttype();
    $buffer = ms_iogetstdoutbufferstring();
    header('Content-type: application/xml');
    echo $buffer;
    ms_ioresethandlers();
  }

}
  

?>