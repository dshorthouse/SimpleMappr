<?php
/*
* CloudFlare Cache Flush
*/
$URL = "https://www.cloudflare.com/api_json.html";
$API_KEY = "cloudflare_api_key";
$EMAIL = "example@example.com"

$data = array(
             "a" => "fpurge_ts",
             "z" => "simplemappr.net",
             "email" => $EMAIL,
             "tkn" => $API_KEY,
             "v" => 1
             );

$ch = curl_init();

curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FORBID_REUSE, true); 
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data ); 
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$http_result = curl_exec($ch);
$error = curl_error($ch);

$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

curl_close($ch);
   
if ($http_code != 200) {
  print "Error: $error\n";
} else {
  $response = json_decode($http_result);
  echo $response->result . "\n";
}

?>
