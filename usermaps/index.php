<style type="text/css">
#map-mymaps table{background:none repeat scroll 0 0 #e6e6e6;border:1px solid gray;border-collapse:collapse;width:90%;color:#555;}
#map-mymaps table thead tr{height:1.75em;}
#map-mymaps table thead td{background-color:#e9e9e9;font-weight:normal;text-align:center;}
#map-mymaps table thead td.left-align{text-align:left;padding-left:20px;}
#map-mymaps table thead td a.toolsRefresh:after{background-position:-240px -324px;margin:0 0 0 0.2em;}
#map-mymaps table tr{border:1px solid #aaa;}
#map-mymaps table tr.odd{background:none repeat scroll 0 0 #fff;}
#map-mymaps table td.actions{width:14em;text-align:right;padding-right:10px;}
#map-mymaps table td.actions a{display:inline-block;}
#map-mymaps table tbody td.actions a{margin-left:10px;}
#map-mymaps table td.actions a.map-delete:before{background-position:-260px -284px;margin:0.2em;}
#map-mymaps table td.actions a.map-delete:after{display:inline;content:"";clear:both;height:0;visibility:hidden;}
#map-mymaps table td.actions a.map-load:before{background-position:-200px -284px;margin:0.2em;}
#map-mymaps table td.actions a.map-load:after{display:inline;content:"";clear:both;height:0;visibility:hidden;}
#map-mymaps table td input{vertical-align:top;}
</style>
<?php
require_once('../lib/mapprservice.usermaps.class.php');

$usermaps = new USERMAPS();
?>