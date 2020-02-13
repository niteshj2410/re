<?php
$que = urldecode($_SERVER['QUERY_STRING']);  

if(empty($que)){ exit;}
$que = str_replace('=3D','=',$que);


	$redirect = substr($que,0,1);
	
	$que = substr($que, 1);
	$que =  str_replace("-","a",$que);

	$deco_arr1 = str_split($que);
	
	$mydecochar = "fgqrEFGHwx67zAcsyopklmeLMNOPQSCDT9tn.IJKi4R5UBV_W|XYj3v8du0Z12abh"; 
	$deco_array = str_split($mydecochar);

	$myencochar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!_|";
	$enco_array = str_split($myencochar);


	$deco_array2 = array_combine($deco_array,$enco_array);

	$reverse=$deco_array2[$redirect];	

	 
	$deco_url ='';
	foreach($deco_arr1 as $arr)
	{

			if($arr=='a'){
					$deco_url = $deco_url.'!';
			}
			else if($arr=='b'){
					$deco_url = $deco_url.'_';
			}
			else if($arr=='h'){
					$deco_url = $deco_url.'|';
			}
			else if($arr=='|'){
					$deco_url = $deco_url.'X';
			}
			else if($arr=='_'){
					$deco_url = $deco_url.'V';
			}
			else if($arr=='.'){
					$deco_url = $deco_url.'K';
			}

			else 
			{
					$deco_url = $deco_url.$deco_array2[$arr];
			}

	}
			
	$deco_array = explode('!',$deco_url);
	
	$deco_array[1] = str_replace("-","",$deco_array[1]);
	$deco_array[3] = str_replace("-","",$deco_array[3]);
	$deco_array[4] = str_replace("-","",$deco_array[4]);
	$deco_array[5] = str_replace("-","",$deco_array[5]);	
	
	$deco_array[5] = base64_decode($deco_array[5]);

	$deco_array[7] = base64_decode($deco_array[7]);

	$deco_url=implode('!',$deco_array);

	$replace=array("~","!","@","#","$","%","&","*","(",")","/");

	$deco_url=trim($reverse).trim($deco_url);
		 
	$querystring=str_replace($replace,"!",$deco_url);

include_once("redplain.php");	
?>
