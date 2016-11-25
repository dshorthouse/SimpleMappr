<?php

/**
 * Unit tests for static methods and set-up of MapprQuery class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\MapprQuery;

class MapprQueryTest extends TestCase
{
    use SimpleMapprMixin;

    protected $mappr_query;

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
        $this->clearRequestMethod();
    }

    /**
     * Test return of country name with query.
     */
    public function testCountry()
    {
        $req = [
          'bbox_query' => '176,83,176,83'
        ];
        $this->setRequest($req);
        $mappr_query = new MapprQuery;
        $mappr_query->execute()->queryLayer();
        $output = $mappr_query->data;
        $this->assertEquals('Canada', $output[0]);
    }

    /**
     * Test that many country names are returned with a large extent.
     */
    public function testManyCountries()
    {
        $req = [
            'bbox_query' => '786,272,900,358'
        ];
        $this->setRequest($req);
        $mappr_query = new MapprQuery;
        $mappr_query->execute()->queryLayer();
        $output = $mappr_query->data;
        $this->assertTrue(in_array("Australia",$output));
        $this->assertTrue(in_array("New Zealand",$output));
    }

    /**
     * Test that a StateProvince code in returned when qlayer is provided.
     */
    public function testStateProvince()
    {
        $req = [
            'bbox_query' => '176,83,176,83',
            'qlayer' => 'stateprovinces_polygon'
        ];
        $this->setRequest($req);
        $mappr_query = new MapprQuery;
        $mappr_query->execute()->queryLayer();
        $output = $mappr_query->data;
        $this->assertEquals('CAN[SK]', $output[0]);
    }

}