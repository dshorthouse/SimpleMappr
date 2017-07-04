<?php

/**
 * Unit tests for converting coordinates using static methods in Mappr class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Utility;

class UtilityTest extends TestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test that a hex color is properly converted to array of RGB.
     */
    public function test_hex()
    {
        $color = "#FF1177";
        $converted = Utility::hex2Rgb($color);
        $this->assertEquals([255, 17, 119], $converted);
    }

    /**
     * Test that a filename is cleaned of unrecognized characters.
     */
    public function test_filename1()
    {
        $filename = "My@ New()*? ë  [Filename]'";
        $cleaned = Utility::cleanFilename($filename, "jpg");
        $this->assertEquals("My_New_ë_Filename_.jpg", $cleaned);
    }

    /**
     * Test that a filename is cleaned of unrecognized characters.
     */
    public function test_filename2()
    {
        $filename = "My Map";
        $cleaned = Utility::cleanFilename($filename, "jpg");
        $this->assertEquals("My_Map.jpg", $cleaned);
    }

    /**
     * Test that a param is loaded.
     */
    public function test_loadParam()
    {
        $req = [
            'points' => '45,-120'
        ];
        $this->setRequest($req);
        $points = Utility::loadParam('points', '75,100');
        $this->assertEquals('45,-120', $points);
    }

    /**
     * Test that a default value for a param is loaded.
     */
    public function test_loadParam_default()
    {
        $req = [
            'stuff' => '45,-120'
        ];
        $this->setRequest($req);
        $points = Utility::loadParam('points', '75,100');
        $this->assertEquals('75,100', $points);
    }

    /**
     * Test that slashes are added to param value.
     */
    public function test_loadParam_slashes()
    {
        $req = [
            'stuff' => "here's Some \" Stuff on a \n new line"
        ];
        $this->setRequest($req);
        $points = Utility::loadParam('stuff', '');
        $this->assertEquals("here\'s Some \\\" Stuff on a \n new line", $points);
    }

    /**
     * Test that empty lines are removed from a value.
     */
    public function test_removeEmptyLines()
    {
        $text = "Here \n is \n\n some \n\n\n stuff";
        $cleaned = Utility::removeEmptyLines($text);
        $this->assertEquals("Here \n is \n some \n stuff", $cleaned);
    }
}
