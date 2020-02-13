<?php
$que = urldecode($_SERVER['QUERY_STRING']);  

if(empty($que)){ exit;}
$que = str_replace('=3D','=',$que);
$r=explode("=",$que,2);
$rev=base64_decode($r[0].'=');
$reverse=substr($rev,0,1);

        $deco_arr1 = str_split($r[1]);

        $mydecochar = "du04R5csywx67zA9tnopklmefgqrEFGH.IJKLMNOPQSCDTUBV_W|XYZ12abhij3v8"; 
        $deco_array = str_split($mydecochar);

        $myencochar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!_|";
        $enco_array = str_split($myencochar);


        $deco_array2 = array_combine($deco_array,$enco_array);

        //print_r($deco_array2);

        $deco_url ='';
                foreach($deco_arr1 as $arr)
                {
         
                        if($arr=='3'){
                                $deco_url = $deco_url.'!';
                        }
                        else if($arr=='v'){
                                $deco_url = $deco_url.'_';
                        }
                        else if($arr=='8'){
                                $deco_url = $deco_url.'|';
                        }
                        else if($arr=='|'){
                                $deco_url = $deco_url.'Z';
                        }
                        else if($arr=='_'){
                                $deco_url = $deco_url.'X';
                        }
                        else if($arr=='.'){
                                $deco_url = $deco_url.'G';
                        }

                        else 
                        {
                                $deco_url = $deco_url.$deco_array2[$arr];
                        }

                }

        $deco_array = explode('!',$deco_url);

        $deco_array[5] = base64_decode($deco_array[5]);

        $deco_array[7] = base64_decode($deco_array[7]);

        $deco_url=implode('!',$deco_array);

 //1b61!32p1ep21p91!b2a83!1463!rvp_us_01|gm!insnewmail!4t3t7df!000000

$replace=array("~","!","@","#","$","%","&","*","(",")","/");

$deco_url=trim($reverse).trim($deco_url);
$querystring=str_replace($replace,"!",$deco_url);
include_once("redplain.php");

?>
