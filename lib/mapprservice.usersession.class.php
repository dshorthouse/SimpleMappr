<?php

/**************************************************************************

File: mapprservice.usersession.class.php

Description: Creates and destroys session

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/

require_once(dirname(dirname(__FILE__)).'/config/conf.php');
require_once(MAPPR_DIRECTORY.'/config/conf.db.php');
require_once(MAPPR_DIRECTORY.'/lib/db.class.php');

class USERSESSION {

  private $_token;

  private $_auth_info = array();

  function __construct() {
    $this->execute();
  }

  public static function destroy() {
    session_start();
    session_unset();
    session_destroy();
    setcookie("simplemappr", "", time() - 3600, "/");
    header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
  }

  public static function set_active_time($uid) {
      $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      $db->query_update('users', array('access' => time()), 'uid='.$db->escape($uid));
  }

  public function execute() {
    $this->get_token();
    $this->make_call();
    $this->make_session();
  }

  private function get_token() {
    if(isset($_POST['token'])) {
      $this->_token = $_POST['token'];
    } else {
      exit();
    }
  }

  private function make_call() {
    $post_data = array('token'  => $this->_token,
                       'apiKey' => RPX_KEY,
                       'format' => 'json');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $raw_json = curl_exec($curl);
    curl_close($curl);

    $this->_auth_info = json_decode($raw_json, true);
  }

  private function make_session() {
    if (isset($this->_auth_info['stat']) && $this->_auth_info['stat'] == 'ok') {
      $profile = $this->_auth_info['profile'];

      $identifier = $profile['identifier'];
      $username   = (isset($profile['preferredUsername'])) ? $profile['preferredUsername'] : '';
      $email      = (isset($profile['email'])) ? $profile['email'] : '';
      $givenname  = (isset($profile['givenName'])) ? $profile['givenName'] : '';
      $surname    = (isset($profile['familyName'])) ? $profile['familyName'] : '';

      $user = array(
        'identifier' => $identifier,
        'username'   => $username,
        'givenname'  => $givenname,
        'surname'    => $surname,
        'email'      => $email,
      );

      $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

      $sql = "
      SELECT
        u.uid,
        u.identifier,
        u.email,
        u.username,
        u.givenname,
        u.surname 
      FROM 
        users u 
      WHERE  
        u.identifier = '".$db->escape($identifier)."'";

      $record = $db->query_first($sql);

      $user['uid'] = (!$record['uid']) ? $db->query_insert('users', $user) : $record['uid'];

      setcookie("simplemappr", json_encode($user), time() + (2 * 7 * 24 * 60 * 60), "/"); //cookie for two weeks
      session_start();
      $_SESSION['simplemappr'] = $user;

      $db->query_update('users', array('access' => time()), 'uid='.$db->escape($user['uid']));

      header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
    } else {
      // echo 'An error occured: ' . $this->_auth_info['err']['msg'];
      exit();
    }
  }

}
?>