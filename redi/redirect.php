<?php
ob_start();

#$url = urldecode($_SERVER['QUERY_STRING']); 
#$urlArr = parse_url($url);

$a = substr($_REQUEST['a'],1);
$b = substr($_REQUEST['b'],1);
$c = substr($_REQUEST['c'],1);

$url_link = $a.$b.$c;

$link =  base64_decode($url_link);   
header("Location: $link");

?>

