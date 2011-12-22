<style type="text/css">
#map-users table{background:none repeat scroll 0 0 #e6e6e6;border:1px solid gray;border-collapse:collapse;width:90%;color:#555;}
#map-users table thead tr{height:1.75em;}
#map-users table thead td{background-color:#e9e9e9;font-weight:normal;text-align:center;}
#map-users table thead td.left-align{text-align:left;padding-left:20px;}
#map-users table thead td a.toolsRefresh:after{background-position:-240px -324px;margin:0 0 0 0.2em;}
#map-users table tr{border:1px solid #aaa;}
#map-users table tr.odd{background:none repeat scroll 0 0 #fff;}
#map-users table tr td.usermaps-center{text-align:center;}
#map-users table td.actions{width:7em;text-align:right;padding-right:10px;}
#map-users table td.actions a{display:inline-block;}
#map-users table td.actions a.user-delete:before{background-position:-260px -284px;margin-top:0.2em;}
</style>
<?php
require_once('../lib/mapprservice.users.class.php');

$users = new USERS();
?>