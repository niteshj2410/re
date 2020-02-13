<?php
$que = urldecode($_SERVER['QUERY_STRING']);
if(empty($que)) { exit(); }
$r=explode("=",$que);

if(preg_match("/%/",$r[1]))
{
$que=rawurldecode($r[1]);
}
elseif(preg_match("/\&/",$r[1]))
{
$que=strDc($r[1]);
}
else
{
$que=base64_decode($r[1]);
}
$replace=array("~","!","@","#","$","&","*","(",")","/");
$r[0] = replaceSingleChar($r[0]);
// $reverse = strrev($r[0]); // do we really need this?
$reverse = trim($r[0]);
$que = $reverse[0].$que;

$querystring=str_replace($replace,"!",$que);
// echo $querystring; exit; 

include("redplain.php");

/*** function definition below */
function replaceSingleChar($rr)
{
	$r = '';
	switch($rr)
	{
		case "rr": $r="r"; break;
		case "un": $r="u"; break;
		case "op": $r="o"; break;
		case "im": $r="i"; break;
		case "pp": $r="p"; break;
		case "un": $r="u"; break;
		case "s":
		case "p":
		case "g": $r = $rr; break;
		default: exit(); // c, fa, you, my, tag, twi
	}// switch
	
	return $r;
}// replaceSingleChar

function strDc($strEn)
{
	$strArr = str_split($strEn);
	$otputStr='';
	for( $i = 0; $i < count($strArr) ; $i++ )
	{
		if( $i % 2 == 0)
		{
			if( ord($strArr[$i]) >= 97 && ord($strArr[$i]) <= 125 )
			{
				$otputStr .= chr(ord($strArr[$i]) - 3 );
			}
			if( ord($strArr[$i]) >= 65 && ord($strArr[$i]) <= 93 )
			{
				$otputStr .= chr(ord($strArr[$i]) - 3 );
			}
			if( ord($strArr[$i]) >= 48 && ord($strArr[$i]) <= 57 )
			{
				$otputStr .= chr(ord($strArr[$i]));
			}
			if( ord($strArr[$i]) == 38 || ord($strArr[$i]) == 46 )
			{
				if(ord($strArr[$i]) == 38){$otputStr .= chr(33);}
				if(ord($strArr[$i]) == 46){$otputStr .= chr(124);}
			}
		}// if
		else
		{
			if( ord($strArr[$i]) >= 97 && ord($strArr[$i]) <= 125 )
			{
				$otputStr .= chr(ord($strArr[$i]) - 2 );
			}
			if( ord($strArr[$i]) >= 65 && ord($strArr[$i]) <= 93 )
			{
				$otputStr .= chr(ord($strArr[$i]) - 2 );
			}
			if( ord($strArr[$i]) >= 48 && ord($strArr[$i]) <= 57 )
			{
				$otputStr .= chr(ord($strArr[$i]));
			}
			if( ord($strArr[$i]) == 38 || ord($strArr[$i]) == 46 )
			{
				if(ord($strArr[$i]) == 38){$otputStr .= chr(33);}
				if(ord($strArr[$i]) == 46){$otputStr .= chr(124);}
			}
		}// else
	}// for
	return $otputStr;
}// strDc

?>