<?php
$querystring= urldecode(trim($_SERVER['QUERY_STRING']));
if(empty($querystring)){ exit;}

include("redplain.php");
?>
