<?php

/**
 * AcceptedProjections trait
 *
 * PHP Version >= 5.5
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

trait AcceptedProjections
{
    /**
     * Acceptable projections in PROJ format
     * Included here for performance reasons AND each has 'over' switch to prevent line wraps
     */
    public static $projections = [
        'epsg:4326'   => [
            'name' => 'Geographic',
            'proj' => 'proj=longlat,ellps=WGS84,datum=WGS84,no_defs'],
        'esri:102009' => [
            'name' => 'North America Lambert',
            'proj' => 'proj=lcc,lat_1=20,lat_2=60,lat_0=40,lon_0=-96,x_0=0,y_0=0,ellps=GRS80,datum=NAD83,units=m,over,no_defs'],
        'esri:102015' => [
            'name' => 'South America Lambert',
            'proj' => 'proj=lcc,lat_1=-5,lat_2=-42,lat_0=-32,lon_0=-60,x_0=0,y_0=0,ellps=aust_SA,units=m,over,no_defs'],
        'esri:102014' => [
            'name' => 'Europe Lambert',
            'proj' => 'proj=lcc,lat_1=43,lat_2=62,lat_0=30,lon_0=10,x_0=0,y_0=0,ellps=intl,units=m,over,no_defs'],
        'esri:102012' => [
            'name' => 'Asia Lambert',
            'proj' => 'proj=lcc,lat_1=30,lat_2=62,lat_0=0,lon_0=105,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'],
        'esri:102024' => [
            'name' => 'Africa Lambert',
            'proj' => 'proj=lcc,lat_1=20,lat_2=-23,lat_0=0,lon_0=25,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'],
        'epsg:3112'   => [
            'name' => 'Australia Lambert',
            'proj' => 'proj=lcc,lat_1=-18,lat_2=-36,lat_0=0,lon_0=134,x_0=0,y_0=0,ellps=GRS80,towgs84=0,0,0,0,0,0,0,units=m,over,no_defs'],
        'epsg:102017' => [
            'name' => 'North Pole Azimuthal',
            'proj' => 'proj=laea,lat_0=90,lon_0=0,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs'],
        'epsg:102019' => [
            'name' => 'South Pole Azimuthal',
            'proj' => 'proj=laea,lat_0=-90,lon_0=0,x_0=0,y_0=0,ellps=WGS84,datum=WGS84,units=m,over,no_defs']
      ];
}