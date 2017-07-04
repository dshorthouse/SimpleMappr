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
namespace SimpleMappr\Controller;

use SimpleMappr\Utility;
use SimpleMappr\Constants\AcceptedMarkerShapes;
use SimpleMappr\Constants\AcceptedOutputs;
use SimpleMappr\Constants\AcceptedProjections;
use SimpleMappr\Mappr\Mappr;

/**
 * OpenAPI handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2016 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class OpenApi implements RestMethods
{
    /**
     * Implemented index method
     *
     * @param object $params null
     *
     * @return array
     */
    public function index($params = null)
    {
        return $this->_swaggerData();
    }

    /**
     * Implemented show method.
     *
     * @param int $id identifier for the place.
     *
     * @return void
     */
    public function show($id)
    {
    }

    /**
     * Implemented create method
     *
     * @param array $content The content to create
     *
     * @return void
     */
    public function create($content)
    {
    }

    /**
     * Implemented update method
     *
     * @param string $content The array of content
     * @param string $where The where clause
     *
     * @return void
     */
    public function update($content, $where)
    {
    }

    /**
     * Implemented destroy method.
     *
     * @param int $id identifier for the place.
     *
     * @return void
     */
    public function destroy($id)
    {
    }

    /**
     * Return the swagger data as an array
     *
     * @return array $swagger
     */
    private function _swaggerData()
    {
        $url_parts = Utility::parsedURL();
        $url_whole = implode("://", $url_parts);
        $swagger = [
        'swagger' => '2.0',
        'info' => [
          'title' => 'SimpleMappr API',
          'description' => 'Create free point maps for publications and presentations. Find out more at ['.$url_whole.']('.$url_whole.').',
          'version' => '1.0.0',
          'contact' => [
            'name' => 'David P. Shorthouse',
            'email' => 'davidpshorthouse@gmail.com'
          ],
          'license' => [
            'name' => 'CC0',
            'url' => 'http://creativecommons.org/publicdomain/zero/1.0/'
          ]
        ],
        'host' => $url_parts["host"],
        'schemes' => [$url_parts["scheme"]],
        'paths' => [
          '/api' => [
            'get' => [
              'summary' => 'GET to /api',
              'description' => 'GET to /api to produce an image',
              'produces' => [
                'image/png',
                'image/jpeg',
                'image/tiff',
                'image/svg+xml',
                'application/json'
              ],
              'parameters' => $this->_apiParameters("GET"),
              'responses' => [
                200 => [
                  'description' => 'success',
                  'examples' => [
                    'application/json' => [
                      'status' => 'ok',
                    ]
                  ]
                ]
              ]
            ],
            'post' => [
              'summary' => 'POST to /api',
              'description' => 'POST to /api to produce a JSON response containing URL to image',
              'consumes' => [
                'multipart/form-data'
              ],
              'produces' => [
                'application/json',
              ],
              'parameters' => $this->_apiParameters("POST"),
              'responses' => [
                200 => [
                  'description' => 'success',
                  'examples' => [
                    'application/json' => [
                      'status' => 'ok',
                    ]
                  ]
                ],
                303 => [
                  'description' => 'redirect to image URL',
                  'headers' => [
                    'Location' => [
                      'type' => 'string'
                    ]
                  ],
                  'examples' => [
                    'application/json' => [
                      'imageURL' => MAPPR_MAPS_URL . '/50778960_464f_0.png',
                      'expiry' => '2016-11-14T11:42:46-05:00',
                      'bad_points' => [],
                      'bad_drawings' => []
                    ]
                  ]
                ]
              ]
            ]
          ]
        ]
      ];
        return $swagger;
    }

    /**
     * Get the API parameters and their definitions
     *
     * @param string Indicate the request method.
     *
     * @return array of API parameters
     */
    private function _apiParameters($request_method = "GET")
    {
        array_walk(AcceptedProjections::$projections, function ($val, $key) use (&$projections) {
            $projections[] = $key . " (" . $val['name'] . ")";
        });
        $params = [
        [
          'name' => 'ping',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'if ping=true is included, a JSON response will be produced in place of an image.',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'url',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'a URL-encoded, remote tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated points. It may also be a URL-encoded GeoRSS, GeoJSON, or KML feed.',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'file',
          'in' => 'formData',
          'description' => 'tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated. Send via POST with enctype "multipart/form-data". The initial response will be JSON with an imageURL element and an expiry element, which indicates when the file will likely be deleted from the server.',
          'required' => false,
          'type' => 'file'
        ],
        [
          'name' => 'points[x]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'single or multiple markers written as latitude,longitude in decimal degrees, DDMMSS, or DD mm.mm. Multiple markers are separated by line-breaks, \n and these are best used in a POST request. If a POST request is used, the initial response will be JSON as above.',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'wkt[x][data]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'data for array of well-known text shape x expressed as POINT, LINESTRING, POLYGON, MULTIPOINT, MULTILINESTRING, or MULTIPOLYGON',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'wkt[x][title]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'title for well-known text shape x',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'wkt[x][border]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'border for well-known text shape x; only applies to POLYGON or MULTIPOLYGON',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'wkt[x][color]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'color for well-known text shape x, e.g. 80,80,80',
          'required' => false,
          'type' => 'array',
          'default' => [80,80,80],
          'minItems' => 3,
          'maxItems' => 3,
          'items' => [
            'type' => 'integer',
            'format' => 'int32',
            'minimum' => 0,
            'maximum' => 255
          ],
          'collectionFormat' => 'csv'
        ],
        [
          'name' => 'shape[x]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'shape of marker for column x, accepted values are one of: ' . implode(", ", AcceptedMarkerShapes::shapes()),
          'required' => false,
          'type' => 'string',
          'enum' => AcceptedMarkerShapes::shapes()
        ],
        [
          'name' => 'size[x]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'integer-based point size of marker in column x',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'minimum' => 1,
          'maximum' => 14
        ],
        [
          'name' => 'color[x]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'comma-separated RGB colors for marker in column x, e.g. 0,0,0',
          'required' => false,
          'type' => 'array',
          'default' => [0,0,0],
          'minItems' => 3,
          'maxItems' => 3,
          'items' => [
            'type' => 'integer',
            'format' => 'int32',
            'minimum' => 0,
            'maximum' => 255
          ],
          'collectionFormat' => 'csv'
        ],
        [
          'name' => 'outlinecolor',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'comma-separated RGB colors for halo around all solid markers, e.g. 120,120,120',
          'required' => false,
          'type' => 'array',
          'default' => [120,120,120],
          'minItems' => 3,
          'maxItems' => 3,
          'items' => [
            'type' => 'integer',
            'format' => 'int32',
            'minimum' => 0,
            'maximum' => 255
          ],
          'collectionFormat' => 'csv'
        ],
        [
          'name' => 'zoom',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'integer from 1 to 10, centered on the geographic midpoint of all coordinates',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'minimum' => 1,
          'maximum' => 10
        ],
        [
          'name' => 'bbox',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'comma-separated bounding box in decimal degrees expressed as minx,miny,maxx,maxy',
          'required' => false,
          'type' => 'array',
          'default' => [-180,-90,180,90],
          'minItems' => 4,
          'maxItems' => 4,
          'items' => [
            'type' => 'integer',
            'format' => 'int32',
            'minimum' => -180,
            'maximum' => 180
          ],
          'collectionFormat' => 'csv'
        ],
        [
          'name' => 'shade[places]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'comma-separated State, Province or Country names or the three-letter ISO country code with pipe-separated States or Provinces flanked by brackets',
          'required' => false,
          'type' => 'array',
          'items' => [
            'type' => 'string'
          ],
          'collectionFormat' => 'csv'
        ],
        [
          'name' => 'shade[title]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'the title for the shaded places',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'shade[color]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'comma-separated RGB fill colors for shaded places, e.g. 80,80,80',
          'required' => false,
          'type' => 'array',
          'default' => [80,80,80],
          'minItems' => 3,
          'maxItems' => 3,
          'items' => [
            'type' => 'integer',
            'format' => 'int32',
            'minimum' => 0,
            'maximum' => 255
          ],
          'collectionFormat' => 'csv',
        ],
        [
          'name' => 'layers',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'comma-separated cultural or physical layers; one or more of: ' . implode(", ", array_keys(Mappr::getShapefileConfig())),
          'required' => false,
          'type' => 'array',
          'items' => [
            'type' => 'string'
          ],
          'collectionFormat' => 'csv',
        ],
        [
          'name' => 'projection',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'the output projection in either EPSG or ESRI references, accepted values are one of ' . implode(", ", $projections),
          'required' => false,
          'type' => 'string',
          'enum' => array_keys(AcceptedProjections::$projections)
        ],
        [
          'name' => 'origin',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'longitude of natural origin used in Lambert projections',
          'required' => false,
          'type' => 'number',
          'format' => 'float',
          'minimum' => -180,
          'maximum' => 180
        ],
        [
          'name' => 'graticules',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'display the graticules',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'spacing',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'display the graticules with defined spacing in degrees',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'minimum' => 1,
          'maximum' => 50
        ],
        [
          'name' => 'width',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'integer-based output width in pixels',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'minimum' => 600,
          'maximum' => 4500
        ],
        [
          'name' => 'height',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'integer-based output height in pixels; if height is not provided, it will be half the width',
          'required' => false,
          'type' => 'integer',
          'format' => 'int32',
          'minimum' => 300,
          'maximum' => 4500
        ],
        [
          'name' => 'output',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'file format of the image or vector produced',
          'required' => false,
          'type' => 'string',
          'enum' => AcceptedOutputs::outputList()
        ],
        [
          'name' => 'scalebar',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'embed a scalebar in the lower right of the image',
          'required' => false,
          'type' => 'boolean'
        ],
        [
          'name' => 'legend[x]',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'URL-encode a title for an item in a legend, embedded in the upper right of the image. If you have a url or file parameter, use legend=true instead',
          'required' => false,
          'type' => 'string'
        ],
        [
          'name' => 'watermark',
          'in' => ($request_method == "GET") ? 'query' : 'formData',
          'description' => 'if watermark=false is included, the SimpleMappr .',
          'required' => false,
          'type' => 'boolean'
        ],
      ];
        if ($request_method == "GET") {
            foreach ($params as $param => $value) {
                if ($value['name'] == 'file') {
                    unset($params[$param]);
                    break;
                }
            }
        }
        return array_values($params);
    }
}
