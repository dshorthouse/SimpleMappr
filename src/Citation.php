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
 * Citation handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Citation implements RestMethods
{
    private $_db;
    private $_citations;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct()
    {
        $this->_db = new Database();
    }

    /**
     * Implemented index method
     *
     * @param object $params null
     *
     * @return response array
     */
    public function index($params = null)
    {
        $sql = "
            SELECT 
                * 
            FROM 
                citations c 
            ORDER BY 
                c.reference ASC, c.year DESC";

        $this->_db->prepare($sql);
        $this->_citations = $this->_db->fetchAllObject();
        return $this->response();
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
    }

    /**
     * Implemented create method
     *
     * @param object $params The parameters send from router
     *
     * @return response array
     */
    public function create($params)
    {
        $year = property_exists($params, 'year') ? (int)$params->year : null;
        $reference = property_exists($params, 'reference') ? $params->reference : null;
        $author = property_exists($params, 'first_author_surname') ? $params->first_author_surname : null;
        $doi = property_exists($params, 'doi') ? $params->doi : null;
        $link = property_exists($params, 'link') ? $params->link : null;

        if (empty($year) || empty($reference) || empty($author)) {
            $this->response('error');
            exit();
        }

        $data = array(
            'year' => $year,
            'reference' => $reference,
            'doi' => $doi,
            'link' => $link,
            'first_author_surname' => $author
        );

        $data['id'] = $this->_db->queryInsert('citations', $data);
        $this->_citations = $data;
        return $this->response();
    }

    /**
     * Implemented update method
     *
     * @param int $param the citation identifier
     *
     * @return void
     */
    public function update($param)
    {
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The citation identifier
     *
     * @return response array
     */
    public function destroy($id)
    {
        $sql = "
           DELETE 
            c 
           FROM 
            citations c 
           WHERE 
            c.id=:id";
        $this->_db->prepare($sql);
        $this->_db->bindParam(":id", $id, "integer");
        $this->_db->execute();
        $this->_citations = "";
        return $this->response();
    }

    /**
     * Produce the JSON response
     *
     * @param string $type The type of response
     *
     * @return void
     */
    private function response($type = null)
    {
        if ($type == 'error') {
            return array("status" => "error");
        } else {
            return array("status" => "ok", "citations" => $this->_citations);
        }
    }

}