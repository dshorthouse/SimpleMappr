<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
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
 *
 */
namespace SimpleMappr;

/**
 * Places handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Places implements RestMethods
{
    public $results;
    public $id;

    protected $_db;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
        $this->_db = new Database();
        $this->results = new \stdClass();
    }

    /**
     * Implemented index method.
     *
     * @param object $params The parameters from the router
     *
     * @return object $this
     */
    public function index($params)
    {
        if (property_exists($params, 'filter') && $params->filter != "") {
            $this->_db->prepare("SELECT * FROM stateprovinces WHERE country LIKE :filter");
            $this->_db->bindParam(':filter', '%'.$params->filter.'%', 'string');
        } else if (property_exists($params, 'term') || $this->id) {
            $term = (property_exists($params, 'term')) ? $params->term : $this->id;
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
            $this->_db->prepare("SELECT * FROM stateprovinces ORDER BY country, stateprovince");
        }
        $this->results = $this->_db->fetchAllObject();
        return $this;
    }

    /**
     * Implemented show method.
     *
     * @param int $id identifier for places.
     *
     * @return void
     */
    public function show($id)
    {
        $this->id = $id;
        return $this->index();
    }

    /**
     * Implemented create method.
     *
     * @param object $params The parameters
     *
     * @return void
     */
    public function create($params)
    {
    }

    /**
     * Implemented update method.
     *
     * @param int $id The integer
     *
     * @return void
     */
    public function update($id)
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