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
namespace SimpleMappr\Controller;

use SimpleMappr\Database;

/**
 * Citation model for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Citation implements RestMethods
{
    /**
     * A database connection object
     *
     * @var object $_db
     */
    private $_db;

    /**
     * Citations object produced from queries
     *
     * @var object $_citations
     */
    private $_citations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_db = Database::getInstance();
    }

    /**
     * Implemented index method
     *
     * @param object $params null
     *
     * @return array
     */
    public function index($params = null)
    {
        $sql = "
            SELECT 
                * 
            FROM 
                citations c 
            ORDER BY 
                c.first_author_surname ASC, c.year DESC";

        $this->_db->prepare($sql);
        $this->_citations = $this->_db->fetchAllObject();
        return $this->_response();
    }

    /**
     * Implemented show method
     *
     * @param int $id the citation identifier
     *
     * @return void
     */
    public function show($id)
    {
        $sql = "
            SELECT
                id, year, reference, doi, link, first_author_surname
            FROM 
                citations
            WHERE
                id = :id";

        $this->_db->prepare($sql);
        $this->_db->bindParam(":id", $id, 'integer');
        return $this->_db->fetchFirstObject();
    }

    /**
     * Implemented create method
     *
     * @param array $content The array sent from router
     *
     * @return array
     */
    public function create($content)
    {
        $year = array_key_exists('year', $content) ? (int)$content["year"] : null;
        $reference = array_key_exists('reference', $content) ? $content["reference"] : null;
        $author = array_key_exists('first_author_surname', $content) ? $content["first_author_surname"] : null;
        $doi = array_key_exists('doi', $content) ? $content["doi"] : null;
        $link = array_key_exists('link', $content) ? $content["link"] : null;

        if (empty($year) || empty($reference) || empty($author)) {
            $this->_response('error');
            exit();
        }

        $data = [
            'year' => $year,
            'reference' => $reference,
            'doi' => $doi,
            'link' => $link,
            'first_author_surname' => $author,
            'created' => time()
        ];

        $data['id'] = $this->_db->queryInsert('citations', $data);
        $this->_citations = $data;
        return $this->_response();
    }

    /**
     * Implemented update method
     *
     * @param array  $content The array of content
     * @param string $where   The where clause
     *
     * @return void
     */
    public function update($content, $where)
    {
        $this->_db->queryUpdate('citations', $content, $where);
        $this->_citations = "";
        return $this->_response();
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The citation identifier
     *
     * @return array
     */
    public function destroy($id)
    {
        $this->_db->queryDelete('citations', $id);
        $this->_citations = "";
        return $this->_response();
    }

    /**
     * Produce the JSON response
     *
     * @param string $type The type of response
     *
     * @return array
     */
    private function _response($type = null)
    {
        if ($type == 'error') {
            return ["status" => "error"];
        } else {
            return ["status" => "ok", "citations" => $this->_citations];
        }
    }
}
