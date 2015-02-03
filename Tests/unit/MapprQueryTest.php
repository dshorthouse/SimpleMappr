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
class MapprQueryTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $mappr_query;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequest();
        $this->mappr_query = $this->setMapprDefaults(new \SimpleMappr\MapprQuery());
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
    }

    /**
     * Test return of country name with query.
     */
    public function testCountry()
    {
        $_REQUEST['bbox_query'] = '176,83,176,83';
        $this->mappr_query->get_request()->execute()->query_layer();
        $output = $this->mappr_query->data;
        $this->assertEquals('Canada', $output[0]);
    }

    /**
     * Test that many country names are returned with a large extent.
     */
    public function testManyCountries()
    {
        $_REQUEST['bbox_query'] = '786,272,900,358';
        $this->mappr_query->get_request()->execute()->query_layer();
        $output = $this->mappr_query->data;
        $this->assertTrue(in_array("Australia",$output));
        $this->assertTrue(in_array("New Zealand",$output));
    }

    /**
     * Test that a StateProvince code in returned when qlayer is provided.
     */
    public function testStateProvince()
    {
        $_REQUEST['bbox_query'] = '176,83,176,83';
        $_REQUEST['qlayer'] = 'stateprovinces_polygon';
        $this->mappr_query->get_request()->execute()->query_layer();
        $output = $this->mappr_query->data;
        $this->assertEquals('CAN[SK]', $output[0]);
    }

}