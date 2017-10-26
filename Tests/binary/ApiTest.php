<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
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

use PHPUnit\Framework\TestCase;
use SimpleMappr\Mappr\WebServices\Api;

/**
 * Test Binary outputs from the SimpleMappr API
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class ApiTest extends TestCase
{
    use SimpleMapprTestMixin;

    /**
     * Parent setUp function executed before each test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setRequestMethod();
    }

    /**
     * Parent tearDown function executed after each test.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test that a ping request is produced.
     *
     * @return void
     */
    public function testApiPing()
    {
        $this->setRequest(['ping' => true]);
        $mappr_api = new Api;
        $output = $mappr_api->execute()->createOutput();
        $decoded = json_decode($output, true);
        $this->assertArrayHasKey("status", $decoded);
    }

    /**
     * Test that a simple POST request is handled.
     *
     * @return void
     */
    public function testApiOutputPost()
    {
        $this->setRequestMethod('POST');
        $mappr_api = new Api;
        $output = $mappr_api->execute()->createOutput();
        $decoded = json_decode($output, true);
        $this->assertArrayHasKey("imageURL", $decoded);
        $this->assertArrayHasKey("expiry", $decoded);
        $this->assertContains(MAPPR_MAPS_URL, $decoded["imageURL"]);
    }

    /**
     * Test that a simple GET request is handled.
     *
     * @return void
     */
    public function testApiOutputGet()
    {
        $this->setRequest([]);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_get.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_get.png'));
    }

    /**
     * Test that a few API request parameters are handled.
     *
     * @return void
     */
    public function testApiOutputGetParams()
    {
        $req = [
            'bbox' => '-130,40,-60,50',
            'projection' => 'esri:102009',
            'width' => 600,
            'graticules' => true
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_get_params.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_get_params.png'));
    }

    /**
     * Test API response in produced when coordinates are not supplied.
     *
     * @return void
     */
    public function testApiOutputNoCoords()
    {
        $req = [
            'points' => []
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_no_coords.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_no_coords.png'));
    }

    /**
     * Test API response when coordinates are supplied.
     *
     * @return void
     */
    public function testApiOutputCoords()
    {
        $req = [
            'points' => ["45, -120\n52, -100"]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_coords.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_coords.png'));
    }

    /**
     * Test API response to ensure that "Québec" is properly encoded.
     *
     * @return void
     */
    public function testApiOutputEncoding()
    {
        $req = [
            'bbox' => '-91.9348552339,38.8500000000,-47.2856347438,61.3500000000',
            'layers' => 'stateprovnames'
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT."/public/tmp/apioutput_encoding.png";
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_encoding.png'));
    }

    /**
     * Test API response to ensure that regions get shaded.
     *
     * @return void
     */
    public function testApiOutputCountry()
    {
        $req = [
            'shade' => [
                'places' => 'Alberta,USA[MT|WA]'
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_places.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_places.png'));
    }

    /**
     * Test API response to ensure that a tif can be produced.
     *
     * @return void
     */
    public function testApiOutputTif()
    {
        $req = [
            'output' => 'tif',
            'shade' => [
                'places' => 'Alberta,USA[MT|WA]'
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_tif.tif';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_tif.tif'));
    }

    /**
     * Test API response to ensure that svg can be produced.
     *
     * @return void
     */
    public function testApiOutputSvg()
    {
        $req = [
            'output' => 'svg',
            'shade' => [
                'places' => 'CAN[SK]'
            ],
            'bbox' => '-109,50,-105,58',
            'width' => 200,
            'height' => 275,
            'watermark' => 'false'
        ];

        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_svg.svg';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api));

        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_svg.svg'));
    }

    /**
     * Test API response to ensure that image can be produced using WKT parameter.
     *
     * @return void
     */
    public function testApiOutputWkt()
    {
        $req = [
            'wkt' => [
                0 => [
                    'data' => 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))'
                ]
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_wkt.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_wkt.png'));
    }

    /**
     * Test API response to ensure that image can be produced using WKT parameter.
     *
     * @return void
     */
    public function testApiOutputWktBorder()
    {
        $req = [
            'wkt' => [
                0 => [
                    'data' => 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))',
                    'border' => true
                ]
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_wkt_border.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_wkt_border.png'));
    }

    /**
     * Test API response to ensure that image can be produced using txt file as URL parameter.
     *
     * @return void
     */
    public function testApiOutputURLFromTXT()
    {
        $req = [
            'bbox' => '-140,25,-90,75',
            'url' => MAPPR_URL . "/public/files/demo.txt",
            'color' => [
                0 => '255,0,0'
            ],
            'shape' => [
                0 => 'star'
            ],
            'size' => [
                0 => 16
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_txt.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_txt.png'));
    }

    /**
     * Test API response to ensure that image can be produced using csv file as URL parameter.
     *
     * @return void
     */
    public function testApiOutputURLFromCSV()
    {
        $req = [
            'bbox' => '-140,25,-90,75',
            'url' => MAPPR_URL . "/public/files/demo2.csv",
            'color' => [
                0 => '255,0,0'
            ],
            'shape' => [
                0 => 'star'
            ],
            'size' => [
                0 => 16
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        $file = ROOT.'/public/tmp/apioutput_csv.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_api, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_csv.png'));
    }
}
