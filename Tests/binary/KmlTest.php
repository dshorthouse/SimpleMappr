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
        if (session_status() != PHP_SESSION_NONE) {
            session_destroy();
        }
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test production of KML.
     */
    public function test_kml()
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
}
