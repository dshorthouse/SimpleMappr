<?php
require_once('../config/conf.php');
require_once('../config/conf.db.php');
require_once('../lib/db.class.php');

global $db;
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

if(isset($_POST['token'])) {

  /* STEP 1: Extract token POST parameter */
  $token = $_POST['token'];

  /* STEP 2: Use the token to make the auth_info API call */
  $post_data = array('token' => $_POST['token'],
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


  /* STEP 3: Parse the JSON auth_info response */
  $auth_info = json_decode($raw_json, true);

  if ($auth_info['stat'] == 'ok') {

    /* STEP 3 Continued: Extract the 'identifier' from the response */
    $profile = $auth_info['profile'];
    $identifier = $profile['identifier'];

    $username = (isset($profile['preferredUsername'])) ? $profile['preferredUsername'] : '';
    $email = (isset($profile['email'])) ? $profile['email'] : '';

    $photo_url = (isset($profile['photo'])) ? $profile['photo'] : '';
    $givenname = (isset($profile['givenName'])) ? $profile['givenName'] : '';
    $surname = (isset($profile['familyName'])) ? $profile['familyName'] : '';

    $user = array(
      'identifier' => $identifier,
      'username' => $username,
      'givenname' => $givenname,
      'surname' => $surname,
      'email' => $email,
    );
    
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
      u.identifier = '".$identifier."'";
    
    $record = $db->query_first($sql);
    
    $user['uid'] = (!$record['uid']) ? $db->query_insert('users', $user) : $record['uid'];
    
    //set the session
    session_start();
    $_SESSION['simplemappr'] = $user;

    //set time last logged in
    $db->query_update('users', array('access' => time()), 'uid='.$user['uid']);
    
    //redirect to My Maps tab
    header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
    

/* an error occurred */
} else {
  // gracefully handle the error. Hook this into your native error handling system.
  // echo 'An error occured: ' . $auth_info['err']['msg'];
}
}


?>