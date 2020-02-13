<?php
$que = urldecode($_SERVER['QUERY_STRING']);  

if(empty($que)){ exit;}
$que = str_replace('=3D','=',$que);
$r=explode("=",$que,2);
$pattern13_array = array('avi'=>'i','nas'=>'r','hpa'=>'o','til'=>'u','san'=>'p','ghp'=>'g','als'=>'t','onv'=>'l','ane'=>'s','spn'=>'x');
$rev=$pattern13_array[$r[0]];
$reverse=substr($rev,0,1);

        $deco_arr1 = str_split($r[1]);

		$mydecochar = "FGHIJ12bhij3Zdu04R5csywaKLMNOPQSCD6v8TUBVW|XY7zA9tnopklmefgqrEx"; 
        $deco_array = str_split($mydecochar);

		$myencochar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!";
        $enco_array = str_split($myencochar);


        $deco_array2 = array_combine($deco_array,$enco_array);

 
        $deco_url ='';
		foreach($deco_arr1 as $arr)
		{
			if($arr=='x'){
					$deco_url = $deco_url.'!';
			}
                        else if($arr=='|'){
					$deco_url = $deco_url.'Q';
			}
			else 
			{
					$deco_url = $deco_url.$deco_array2[$arr];
			}
		}

        $deco_array = explode('!',$deco_url);
        $deco_array[4] = base64_decode($deco_array[4]);
        $deco_array[5] = base64_decode($deco_array[5]);
        $deco_array[7] = base64_decode($deco_array[7]);
        
        $pattenc =  'pattenc'.$deco_array[0].$deco_array[2].$deco_array[3].$deco_array[5];
        $encmd5 = md5($pattenc);
        $encMD5val =  substr($encmd5,0,5);
    
        
        if($encMD5val != $deco_array[8])
        {
            exit;
        }
        unset($deco_array[8]);
        
        $deco_url=implode('!',$deco_array);

 //1b61!58pd8p4dp25!772604!1463!ref_02   |gm!inssfdssfdsdfdsdddfd!18t7t7e1!000013
 //1b61!32p1ep21p91!b2a83 !1463!rvp_us_01|gm!insnewmail!4t3t7df!000000

$replace=array("~","!","@","#","$","%","&","*","(",")","/");

$deco_url=trim($reverse).trim($deco_url);
$querystring=str_replace($replace,"!",$deco_url);	
include_once("redplain.php");

?>
