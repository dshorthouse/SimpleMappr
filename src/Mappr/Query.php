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
namespace SimpleMappr\Mappr;

use \ForceUTF8\Encoding;

/**
 * Query handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Query extends Mappr
{
    /**
     * @var array $data Regions on shapefile that match a query
     */
    public $data = [];

    /**
     * Implement getRequest method
     *
     * @return object $this
     */
    public function getRequest()
    {
        $attr = new \stdClass();
        $attr->download         = false;
        $attr->options          = [];
        $attr->border_thickness = 1.25;
        $attr->width            = (float)Utility::loadParam('width', 900);
        $attr->height           = (float)Utility::loadParam('height', $attr->width/2);
        $attr->image_size       = [$attr->width, $attr->height];
        $attr->output           = Utility::loadParam('output', 'png');
        $attr->projection       = Utility::loadParam('projection', 'epsg:4326');
        $attr->projection_map   = Utility::loadParam('projection_map', 'epsg:4326');
        $attr->origin           = (int)Utility::loadParam('origin', false);
        $attr->bbox_map         = Utility::loadParam('bbox', '-180,-90,180,90');
        $attr->layers           = Utility::loadParam('layers', []);
        $attr->graticules       = Utility::loadParam('graticules', false);
        $attr->bbox_query       = Utility::loadParam('bbox_query', '0,0,0,0');
        $attr->queryLayer       = Utility::loadParam('qlayer', 'countries');

        return $attr;
    }

    /**
     * Query a layer
     *
     * @return object $this
     */
    public function queryLayer()
    {
        $bbox_query = explode(',', $this->request->bbox_query);

        if (!array_key_exists($this->request->queryLayer, $this->shapes)) {
            $this->request->queryLayer = 'countries';
        }

        //lower-left coordinate
        $ll_point = new \stdClass();
        $ll_point->x = $bbox_query[0];
        $ll_point->y = $bbox_query[3];
        $ll_coord = $this->pix2Geo($ll_point);

        //upper-right coordinate
        $ur_point = new \stdClass();
        $ur_point->x = $bbox_query[2];
        $ur_point->y = $bbox_query[1];
        $ur_coord = $this->pix2Geo($ur_point);

        $layer = ms_newLayerObj($this->map_obj);
        $layer->set("name", "stateprovinces_polygon_query");
        $layer->set("data", $this->shapes[$this->request->queryLayer]['path']);
        $layer->set("type", $this->shapes[$this->request->queryLayer]['type']);
        $layer->set("template", "template.html");
        $layer->setProjection(parent::getProjection($this->default_projection));

        $rect = ms_newRectObj();
        $rect->setExtent($ll_coord->x, $ll_coord->y, $ur_coord->x, $ur_coord->y);

        $return = @$layer->queryByRect($rect); //suppress error in event extent is invalid
        if ($return == MS_SUCCESS) {
            if ($layer->getNumResults() > 0) {
                $layer->open();
                $items = [];
                $count = $layer->getNumResults();
                for ($i = 0; $i < $count; $i++) {
                    $shape = $layer->getShape($layer->getResult($i));
                    if ($this->request->queryLayer == 'stateprovinces_polygon') {
                        $hasc = explode(".", $shape->values['code_hasc']);
                        if (isset($shape->values['adm0_a3']) && isset($hasc[1])) {
                            $items[$shape->values['adm0_a3']][$hasc[1]] = [];
                        }
                    } else {
                        //DigitalEarth ne_10m_admin_0_map_units is inconsistent
                        $this->data[] = (isset($shape->values['geounit'])) ? Encoding::fixUTF8($shape->values['geounit']) : Encoding::fixUTF8($shape->values['GEOUNIT']);
                    }
                }
                if ($this->request->queryLayer == 'stateprovinces_polygon') {
                    foreach ($items as $key => $value) {
                        $this->data[] = $key . "[" . implode(" ", array_keys($value)) . "]";
                    }
                }
                $layer->close();
            }
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
    }

}