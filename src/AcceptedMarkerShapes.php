<?php

/**
 * AcceptedMarkerShapes trait
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
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

trait AcceptedMarkerShapes
{
    /**
     * Accepted marker shapes
     */
    public static $shapes = array(
        'general' => array(
            'plus' => array('style' => 'plus', 'name' => 'plus'),
            'cross' => array('style' => 'cross', 'name' => 'cross'),
            'asterisk' => array('style'=> 'asterisk', 'name' => 'asterisk')
        ),
        'closed' => array(
            'circle' => array('style' => 'circle', 'name' => 'circle (s)'),
            'star' => array('style' => 'star', 'name' => 'star (s)'),
            'square' => array('style' => 'square', 'name' => 'square (s)'),
            'triangle' => array('style' => 'triangle', 'name' => 'triangle (s)'),
            'hexagon' => array('style' => 'hexagon', 'name' => 'hexagon (s)'),
            'inversetriangle' => array('style' => 'inversetriangle', 'name' => 'inverse triangle (s)')
        ),
        'open' => array(
            'opencircle' => array('style' => 'circle', 'name' => 'circle (o)'),
            'openstar' => array('style' => 'star', 'name' => 'star (o)'),
            'opensquare' => array('style' => 'square', 'name' => 'square (o)'),
            'opentriangle' => array('style' => 'triangle', 'name' => 'triangle (o)'),
            'openhexagon' => array('style' => 'hexagon', 'name' => 'hexagon (o)'),
            'inverseopentriangle' => array('style' => 'inversetriangle', 'name' => 'inverse triangle (o)')
        )
    );

    public static function vertices($type)
    {
        $vertices = array();
        switch ($type) {
            case 'plus':
                $vertices = array(
                    0.5, 0,
                    0.5, 1,
                    -99, -99,
                    0, 0.5,
                    1, 0.5
                );
                break;

            case 'cross':
                $vertices = array(
                    0, 0,
                    1, 1,
                    -99, -99,
                    0, 1,
                    1, 0
                );
                break;

            case 'asterisk':
                $vertices = array(
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
                );
                break;

            case 'circle':
                $vertices = array(
                    1, 1
                );
                break;

            case 'star':
                $vertices = array(
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
                );
                break;

            case 'square':
                $vertices = array(
                    0, 1,
                    0, 0,
                    1, 0,
                    1, 1,
                    0, 1
                );
                break;

            case 'triangle':
                $vertices = array(
                    0, 1,
                    0.5, 0,
                    1, 1,
                    0, 1
                );
                break;

            case 'inversetriangle':
                $vertices = array(
                    0, 0,
                    1, 0,
                    0.5, 1,
                    0, 0
                );
                break;

            case 'hexagon':
                $vertices = array(
                    0.23, 0,
                    0, 0.5,
                    0.23, 1,
                    0.77, 1,
                    1, 0.5,
                    0.77, 0,
                    0.23, 0
                );
                break;
        }

        return $vertices;
    }
}