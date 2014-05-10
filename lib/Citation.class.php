<?php

/********************************************************************

Citation.class.php released under MIT License
Manages references that cite SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2013 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

namespace SimpleMappr;

class Citation extends Rest implements RestMethods {

  private $db;
  private $citations;

  function __construct($id = NULL) {
    $this->id = $id;
    $this->db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
  }

  public function get_citations() {
    $this->index();
    return $this->citations;
  }

  public function execute() {
    $this->restful_action()->response();
  }

  /*
  * Implemented index method
  */
  public function index() {
    $sql = "
      SELECT
        *
      FROM
        citations c
      ORDER BY c.reference ASC, c.year DESC";

    $this->citations = $this->db->fetch_all_array($sql);
    return $this;
  }

  /*
  * Implemented show method
  */
  public function show($id) {
    $this->not_implemented();
  }

  /*
  * Implemented create method
  */
  public function create() {
    User::check_permission();
    $year = isset($_POST['citation']['year']) ? (int)$_POST['citation']['year'] : NULL;
    $reference = isset($_POST['citation']['reference']) ? $_POST['citation']['reference'] : NULL;
    $author = isset($_POST['citation']['first_author_surname']) ? $_POST['citation']['first_author_surname'] : NULL;
    $doi = isset($_POST['citation']['doi']) ? $_POST['citation']['doi'] : NULL;
    $link = isset($_POST['citation']['link']) ? $_POST['citation']['link'] : NULL;

    if(empty($year) || empty($reference) || empty($author)) {
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

    $data['id'] = $this->db->query_insert('citations', $data);
    $this->citations = $data;
    return $this;
  }

  /*
  * Implemented update method
  */
  public function update() {
    $this->not_implemented();
  }

  /*
  * Implemented destroy method
  */
  public function destroy($id) {
    User::check_permission();
    $sql = "
        DELETE
          c
        FROM
          citations c
        WHERE 
          c.id=".$this->db->escape($id);
    $this->db->query($sql);
    return $this;
  }

  private function response($type = NULL) {
    switch($type) {
      case 'error':
        $output = array(
          "status" => "error"
        );
        break;

      default:
        $output = array(
          "status"    => "ok",
          "citations" => $this->citations
        );
        break;
    }
    Header::set_header("json");
    echo json_encode($output);
  }

}