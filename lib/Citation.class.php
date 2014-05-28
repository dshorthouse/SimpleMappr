<?php
namespace SimpleMappr;

/**
 * Citation.class.php released under MIT License
 * Manages references that cite SimpleMappr
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse {{{
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
 * }}}
 */
class Citation extends Rest implements RestMethods
{
    private $_db;
    private $_citations;

    function __construct($id = null)
    {
        $this->id = $id;
        $this->_db = new Database();
    }

    public function get_citations()
    {
        $this->index();
        return $this->_citations;
    }

    public function execute()
    {
        $this->restful_action()->response();
    }

    /**
     * Implemented index method
     */
    public function index()
    {
        $sql = "
            SELECT 
                * 
            FROM 
                citations c 
            ORDER BY 
                c.reference ASC, c.year DESC";

        $this->_db->prepare($sql);
        $this->_citations = $this->_db->fetch_all_object();
        return $this;
    }

    /**
     * Implemented show method
     *
     * @param int $id The citation identifier
     * @return void
     */
    public function show($id)
    {
        $this->not_implemented();
    }

    /**
     * Implemented create method
     */
    public function create()
    {
        User::check_permission();
        $year = isset($_POST['citation']['year']) ? (int)$_POST['citation']['year'] : null;
        $reference = isset($_POST['citation']['reference']) ? $_POST['citation']['reference'] : null;
        $author = isset($_POST['citation']['first_author_surname']) ? $_POST['citation']['first_author_surname'] : null;
        $doi = isset($_POST['citation']['doi']) ? $_POST['citation']['doi'] : null;
        $link = isset($_POST['citation']['link']) ? $_POST['citation']['link'] : null;

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

        $data['id'] = $this->_db->query_insert('citations', $data);
        $this->_citations = $data;
        return $this;
    }

    /**
     * Implemented update method
     */
    public function update()
    {
        $this->not_implemented();
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The citation identifier
     * @return void
     */
    public function destroy($id)
    {
        User::check_permission();
        $sql = "
           DELETE 
            c 
           FROM 
            citations c 
           WHERE 
            c.id=:id";
        $this->_db->prepare($sql);
        $this->_db->bind_param(":id", $id, "integer");
        $this->_db->execute();
        return $this;
    }

    /**
     * Produce the JSON response
     *
     * @param string $type The type of response
     * @return void
     */
    private function response($type = null)
    {
        switch($type) {
        case 'error':
            $output = array(
                "status" => "error"
            );
            break;

        default:
            $output = array(
                "status"    => "ok",
                "citations" => $this->_citations
            );
        }
        Header::set_header("json");
        echo json_encode($output);
    }

}