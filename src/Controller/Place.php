<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
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
namespace SimpleMappr\Controller;

use SimpleMappr\Database;

/**
 * Place model for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Place implements RestMethods
{
    /**
     * Query result object
     *
     * @var object $results
     */
    public $results;

    /**
     * Identifier for database query
     *
     * @var int $id
     */
    public $id;

    /**
     * Database connection instance
     *
     * @var object $db
     */
    private $_db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_db = Database::getInstance();
        $this->results = new \stdClass();
    }

    /**
     * Implemented index method.
     *
     * @param array $content The parameters from the router
     *
     * @return object $this
     */
    public function index($content)
    {
        if (array_key_exists('filter', $content) && $content["filter"] != "") {
            $sql = "SELECT * FROM stateprovinces WHERE country LIKE :filter";
            $this->_db->prepare($sql);
            $this->_db->bindParam(':filter', '%'.$content["filter"].'%', 'string');
        } elseif (array_key_exists('term', $content) || $this->id) {
            $term = $this->id;
            if (array_key_exists('term', $content)) {
                $term = $content["term"];
            }
            $this->_db->prepare(
                "SELECT DISTINCT
                    sp.country as label, sp.country as value
                FROM
                    stateprovinces sp
                WHERE
                    sp.country LIKE :term
                ORDER BY
                    sp.country
                LIMIT 5"
            );
            $this->_db->bindParam(':term', $term.'%', 'string');
        } else {
            $sql = "SELECT * FROM stateprovinces ORDER BY country, stateprovince";
            $this->_db->prepare($sql);
        }
        $this->results = $this->_db->fetchAllObject();
        return $this;
    }

    /**
     * Implemented show method.
     *
     * @param int $id identifier for places.
     *
     * @return object result A single resultset
     */
    public function show($id)
    {
        $this->id = $id;
        return $this->index();
    }

    /**
     * Implemented create method.
     *
     * @param array $content The parameters
     *
     * @return void
     */
    public function create($content)
    {
    }

    /**
     * Implemented update method.
     *
     * @param array  $content The array of content
     * @param string $where   The where string
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
}
