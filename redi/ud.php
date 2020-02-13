<?php
$que = urldecode($_SERVER['QUERY_STRING']);  

if(empty($que)){ exit;}
$que = str_replace('=3D','=',$que);

	$r=explode("==",$que,2);
		
	$rev=base64_decode($r[0].'==');

	$reverse=substr($rev,0,1);

	$deco_arr1 = str_split($r[1]);

	$mydecochar = "FGHdu04R5cDTUBZ12abhj3NOPQSCziXYA9tnV_W|opv8sywx67.IJKLMklmefgqrE"; 
	$deco_array = str_split($mydecochar);

	$myencochar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!_|";
	$enco_array = str_split($myencochar);


	$deco_array2 = array_combine($deco_array,$enco_array);
			$deco_array2['/'] = '/';
			$deco_array2['='] = '=';
	//print_r($deco_array2);

	$deco_url ='';
	foreach($deco_arr1 as $arr)
	{
		if($arr=='q'){
				$deco_url = $deco_url.'!';
		}
		else if($arr=='r'){
				$deco_url = $deco_url.'_';
		}
		else if($arr=='E'){
				$deco_url = $deco_url.'|';
		}
		else if($arr=='|'){
				$deco_url = $deco_url.'N';
		}
		else if($arr=='_'){
				$deco_url = $deco_url.'L';
		}
		else if($arr=='.'){
				$deco_url = $deco_url.'Y';
		}
		else 
		{
				$deco_url = $deco_url.$deco_array2[$arr];
		}
	}
			
	$deco_array = explode('!',$deco_url);
	
	$deco_array[2] = str_replace("/","",$deco_array[2]);
	
	$deco_array[4] =  substr($deco_array[4], 0, -3);

	$deco_array[6] = str_replace("/","",$deco_array[6]);
	
	$deco_array[5] = base64_decode($deco_array[5]);

	$deco_array[7] = base64_decode($deco_array[7]);

	$deco_url=implode('!',$deco_array);

	$replace=array("~","!","@","#","$","%","&","*","(",")","/");

	$deco_url=trim($reverse).trim($deco_url);
	$querystring=str_replace($replace,"!",$deco_url);

include_once("redplain.php");	
?>
