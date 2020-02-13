<?php
$que = urldecode($_SERVER['QUERY_STRING']);  

if(empty($que)){ exit;}
$que = str_replace('=3D','=',$que);

	$que =  substr($que, 0, -5);
	 
	$r=explode("-",$que,2);
		
	$rev=$r[0].'-';

	$reverse=substr($rev,0,1);

	$deco_arr1 = str_split($r[1]);

	$mydecochar = "UBVW|XYZ1_wx67zA9tnoPQSCDT2abhipk.IJKLMNOj3v8du04lmefgqrEFGHR5csy"; 
	$deco_array = str_split($mydecochar);

	$myencochar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!_|";
	$enco_array = str_split($myencochar);


	
	$deco_array2 = array_combine($deco_array,$enco_array);
	$deco_array2['/'] = '/';
 
 

	$deco_url ='';
			foreach($deco_arr1 as $arr)
			{
	 
					if($arr=='c'){
							$deco_url = $deco_url.'!';
					}
					else if($arr=='s'){
							$deco_url = $deco_url.'_';
					}
					else if($arr=='y'){
							$deco_url = $deco_url.'|';
					}
					else if($arr=='|'){
							$deco_url = $deco_url.'e';
					}
					else if($arr=='_'){
							$deco_url = $deco_url.'j';
					}
					else if($arr=='.'){
							$deco_url = $deco_url.'H';
					}

					else 
					{
							$deco_url = $deco_url.$deco_array2[$arr];
					}

			}
			
	$deco_array = explode('!',$deco_url);
	
	$deco_array[4] = str_replace("/","",$deco_array[4]);
	$deco_array[5] = base64_decode($deco_array[5]);

	$deco_array[7] = base64_decode($deco_array[7]);

	$deco_url=implode('!',$deco_array);

	$replace=array("~","!","@","#","$","%","&","*","(",")","/");

	$deco_url=trim($reverse).trim($deco_url);

    $querystring=str_replace($replace,"!",$deco_url);

include_once("redplain.php");	
?>
