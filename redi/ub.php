<?php
date_default_timezone_set('America/New_York');
$que = urldecode($_SERVER['QUERY_STRING']);
if(empty($que)){ exit;}
$response=decodeurl6(substr($que,0,1));
$redirection1=substr($que,1);

$que1 = explode('.',$redirection1);
$list = decodeurl6(trim($que1[0]));
$que2 =  explode(':',$que1[1]);
$offername = decodeurl6(trim($que2[0]));


$isp = $que2[1];
$emailid = base_convert(trim($que2[2]),36,10);
$sentip = long2ip(base_convert($que2[3],36,10));
$subjectid = base_convert($que2[4],36,10);
$que3 =  explode(',',$que2[5]);
$HEXDATE = date("Y-m-d",strtotime(base_convert($que3[0],36,10)));
$ouid = base_convert(substr($que3[1],0, 3),36,10);
$id = decodeurl6($que3[2]);
$midc = $que3[3];

$URL = "{$emailid}#{$sentip}#{$subjectid}#{$ouid}#{$list}|{$isp}#{$offername}#{$HEXDATE}#{$id}#{$midc}";


include_once("redplainNew.php");


function decodeurl6($str)
{
    $arr1=str_split($str);
    $enco_url='';

    $mydecochar = "Vl9dSQNLy8u06TqzDgRhM4P2KZ7pUvOmnYxF3rt1IiefWEGjJkwHboXc5aBsCA";
    $deco_array = str_split($mydecochar);
    $myencochar = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $enco_array = str_split($myencochar);
    $com_array = array_combine($deco_array,$enco_array);


    foreach($arr1 as $arr)
    {
        if(in_array($arr,$deco_array))
        {
            $enco_url = $enco_url.$com_array[$arr];
        }else{
            $enco_url= $enco_url.$arr;
        }
    }
    return $enco_url;
}

?>