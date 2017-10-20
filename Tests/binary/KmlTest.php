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
use SimpleMappr\Controller\Kml;

class KmlTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $kml;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequestMethod();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->destroySession();
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test production of KML.
     */
    public function test_kml_simple()
    {
        $content = [
            "file_name" => "My_Map",
            "coords" => [
                [
                    'title' => 'Sample Data',
                    'data' => "55, -115\n65, -110",
                    'shape' => 'star',
                    'size' => 14,
                    'color' => '255 32 3'
                ],
                [
                    'title' => 'Sample Data2',
                    'data' => "35, -120\n70, -80",
                    'shape' => 'circle',
                    'size' => 14,
                    'color' => '255 32 3'
                ]
            ]
        ];
        $kml = (new Kml)->create($content);
        $test_file = file_get_contents(ROOT.'/Tests/files/kml.kml');
        $this->assertEquals($kml, $test_file);
    }

    /**
     * Test production of KML.
     */
    public function test_kml_complex()
    {
        $content = [
            "file_name" => "My_Map",
            "coords" => [
                [
                    'title' => 'Sample Data',
                    'data' => "55, -115\n65, -110",
                    'shape' => 'star',
                    'size' => 14,
                    'color' => '255 32 3'
                ],
                [
                    'title' => 'Sample Data2',
                    'data' => "35, -120\n70, -80",
                    'shape' => 'circle',
                    'size' => 14,
                    'color' => '255 32 3'
                ]
            ],
            "wkt" => [
                [
                    'title' => "More Sample Data",
                    'data' => "POLYGON((-102.919921875 56.02294807962746,-103.18359375 56.02294807962746,-103.53515625 55.87531083569679,-103.974609375 55.677584411089526,-104.4140625 55.3791104480105,-104.765625 55.128649068488805,-104.94140625 54.826007999094955,-105.1171875 54.41892996865827,-105.205078125 53.9560855309879,-105.205078125 53.48804553605621,-105.205078125 52.96187505907603,-104.94140625 52.64306343665892,-104.501953125 52.32191088594773,-104.0625 52.10650519075632,-103.53515625 52.05249047600099,-102.919921875 51.94426487902877,-101.77734375 51.94426487902877,-100.8984375 51.94426487902877,-100.37109375 51.94426487902877,-99.755859375 51.94426487902877,-99.31640625 51.94426487902877,-98.96484375 51.890053935216926,-98.701171875 51.83577752045248,-98.4375 51.781435604431195,-98.0859375 51.67255514839674,-97.646484375 51.56341232867588,-97.119140625 51.508742458803326,-96.50390625 51.45400691005982,-95.888671875 51.45400691005982,-95.185546875 51.6180165487737,-94.482421875 51.94426487902877,-93.69140625 52.64306343665892,-93.427734375 53.06762664238738,-93.251953125 53.38332836757155,-93.251953125 53.80065082633023,-93.251953125 54.1109429427243,-93.251953125 54.36775852406841,-93.427734375 54.572061655658516,-93.515625 54.77534585936447,-93.69140625 54.92714186454645,-93.8671875 55.07836723201514,-93.955078125 55.229023057406344,-94.21875 55.3791104480105,-94.39453125 55.528630522571916,-94.658203125 55.62799595426723,-94.921875 55.727110085045986,-95.09765625 55.825973254619015,-95.361328125 55.87531083569679,-95.537109375 55.92458580482951,-95.712890625 55.92458580482951,-96.064453125 55.92458580482951,-96.328125 55.92458580482951,-96.591796875 55.92458580482951,-96.85546875 55.92458580482951,-97.119140625 55.825973254619015,-97.294921875 55.825973254619015,-97.470703125 55.727110085045986,-97.646484375 55.677584411089526,-97.91015625 55.677584411089526,-98.173828125 55.62799595426723,-98.26171875 55.62799595426723,-98.4375 55.62799595426723,-98.525390625 55.677584411089526,-98.61328125 55.677584411089526,-98.876953125 55.87531083569679,-99.052734375 56.07203547180089,-99.228515625 56.218923189166624,-99.31640625 56.316536722113014,-99.4921875 56.413901376006756,-99.580078125 56.46249048388979,-99.580078125 56.46249048388979,-99.580078125 56.46249048388979,-99.66796875 56.46249048388979,-99.66796875 56.46249048388979,-102.919921875 56.02294807962746))",
                    'color' => "255 0 0"
                ]
            ]
        ];
        $kml = (new Kml)->create($content);
        $test_file = file_get_contents(ROOT.'/Tests/files/kml2.kml');
        $this->assertEquals($kml, $test_file);
    }
}
