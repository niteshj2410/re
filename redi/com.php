<?php 

$que = urldecode($_SERVER['QUERY_STRING']);
if(empty($que)){ exit;}
$r=explode("-",$que,2);
if(preg_match("/!/", $r[1])){$que=$r[1]; }
else
{
	$que=base64_decode($r[1]);
}

$replace=array("~","!","@","#","$","%","&","*","(",")","/");
$reverse=strrev($r[0]);
$que=trim($reverse[0]).trim($que);
$querystring=str_replace($replace,"!",$que);

include_once("redplain.php");

?>