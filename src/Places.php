<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link      http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @package   SimpleMappr
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
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class Places implements RestMethods
{
    public $results;
    public $id;

    protected $_db;

    function __construct()
    {
        $this->_db = new Database();
        $this->results = new \stdClass();
    }

    /**
     * Implemented index method.
     */
    public function index()
    {
        if (isset($_REQUEST['filter']) && $_REQUEST['filter'] != "") {
            $this->_db->prepare("SELECT * FROM stateprovinces WHERE country LIKE :filter");
            $this->_db->bind_param(':filter', '%'.$_REQUEST['filter'].'%', 'string');
        } else if (isset($_REQUEST['term']) || $this->id) {
            $term = (isset($_REQUEST['term'])) ? $_REQUEST['term'] : $this->id;
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
            $this->_db->bind_param(':term', $term.'%', 'string');
        } else {
            $this->_db->prepare("SELECT * FROM stateprovinces ORDER BY country, stateprovince");
        }
        $this->results = $this->_db->fetch_all_object();
        return $this;
    }

    /**
     * Implemented show method.
     *
     * @param int $id Identifier for places.
     * @return void
     */
    public function show($id)
    {
        $this->id = $id;
        return $this->index();
    }

    /**
     * Implemented create method.
     */
    public function create()
    {
        $this->index();
    }

    /**
     * Implemented update method.
     */
    public function update($id)
    {
    }

    /**
     * Implemented destroy method.
     *
     * @param int $id Identifier for the place.
     * @return void
     */
    public function destroy($id)
    {
    }

}