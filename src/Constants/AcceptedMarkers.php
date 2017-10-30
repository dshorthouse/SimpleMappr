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
namespace SimpleMappr\Constants;

/**
 * Accepted marker shapes for SimpleMappr
 *
 * @category  Trait
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
trait AcceptedMarkers
{
    /**
     * Accepted marker shapes
     *
     * @var array $shapes
     */
    public static $shapes = [
        'general' => [
            'plus' => ['style' => 'plus', 'name' => 'plus'],
            'cross' => ['style' => 'cross', 'name' => 'cross'],
            'asterisk' => ['style'=> 'asterisk', 'name' => 'asterisk']
        ],
        'closed' => [
            'circle' => ['style' => 'circle', 'name' => 'circle (s)'],
            'star' => ['style' => 'star', 'name' => 'star (s)'],
            'square' => ['style' => 'square', 'name' => 'square (s)'],
            'triangle' => ['style' => 'triangle', 'name' => 'triangle (s)'],
            'hexagon' => ['style' => 'hexagon', 'name' => 'hexagon (s)'],
            'inversetriangle' => [
                'style' => 'inversetriangle', 'name' => 'inverse triangle (s)'
                ]
        ],
        'open' => [
            'opencircle' => ['style' => 'circle', 'name' => 'circle (o)'],
            'openstar' => ['style' => 'star', 'name' => 'star (o)'],
            'opensquare' => ['style' => 'square', 'name' => 'square (o)'],
            'opentriangle' => ['style' => 'triangle', 'name' => 'triangle (o)'],
            'openhexagon' => ['style' => 'hexagon', 'name' => 'hexagon (o)'],
            'inverseopentriangle' => [
                'style' => 'inversetriangle', 'name' => 'inverse triangle (o)'
                ]
        ]
    ];

    /**
     * Return shapes
     *
     * @return array of shapes.
     */
    public static function shapes()
    {
        return array_merge(
            array_keys(self::$shapes['general']),
            array_keys(self::$shapes['closed']),
            array_keys(self::$shapes['open'])
        );
    }

    /**
     * Return range of marker sizes
     *
     * @return array of marker sizes.
     */
    public static function sizes()
    {
        return range(6, 16, 2);
    }

    /**
     * Return vertices for a shape
     *
     * @param string $type A shape, eg 'plus', 'cross'
     *
     * @return array Vertices for the shape
     */
    public static function vertices($type)
    {
        $vertices = [];
        switch ($type) {
        case 'plus':
            $vertices = [
            0.5, 0,
            0.5, 1,
            -99, -99,
            0, 0.5,
            1, 0.5
            ];
            break;

        case 'cross':
            $vertices = [
            0, 0,
            1, 1,
            -99, -99,
            0, 1,
            1, 0
            ];
            break;

        case 'asterisk':
            $vertices = [
            0, 0,
            1, 1,
            -99, -99,
            0, 1,
            1, 0,
            -99, -99,
            0.5, 0,
            0.5, 1,
            -99, -99,
            0, 0.5,
            1, 0.5
            ];
            break;

        case 'circle':
            $vertices = [
            1, 1
            ];
            break;

        case 'star':
            $vertices = [
            0, 0.375,
            0.35, 0.365,
            0.5, 0,
            0.65, 0.375,
            1, 0.375,
            0.75, 0.625,
            0.875, 1,
            0.5, 0.75,
            0.125, 1,
            0.25, 0.625,
            0, 0.375
            ];
            break;

        case 'square':
            $vertices = [
            0, 1,
            0, 0,
            1, 0,
            1, 1,
            0, 1
            ];
            break;

        case 'triangle':
            $vertices = [
            0, 1,
            0.5, 0,
            1, 1,
            0, 1
            ];
            break;

        case 'inversetriangle':
            $vertices = [
            0, 0,
            1, 0,
            0.5, 1,
            0, 0
            ];
            break;

        case 'hexagon':
            $vertices = [
            0.23, 0,
            0, 0.5,
            0.23, 1,
            0.77, 1,
            1, 0.5,
            0.77, 0,
            0.23, 0
            ];
            break;
        }

        return $vertices;
    }
}
