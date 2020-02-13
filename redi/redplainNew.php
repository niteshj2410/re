<?php
// call database
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db_conn/config/test_db.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '/db_conn/config/isp.php';


if(strstr($URL,"#0.0.0.0#0#0###00-00-00#")){exit();}
$url9 = explode('#',$URL);
 $emailid = trim($url9[0]); if(empty($emailid)){exit;} // ID from FEC Database
 $offername = trim($url9[5]); if(empty($offername)){exit;} // OFFER_ID
 $subjectid = trim($url9[2]); if(empty($subjectid)){exit;} // SUBJECT_ID
 $sentip = trim($url9[1]); if(empty($sentip)){exit;}// SENT FROM IP
 $enc_id_str  = trim($url9[7]); if(empty($enc_id_str)){exit;}// ENC ID OF String
 $uid = trim($url9[3]); if(empty($uid)){exit;} //USERID
 $senddate = trim($url9[6]); if(empty($senddate)){exit;} // SEND DATE
 $combine_list_isp = trim($url9[4]);
 $midc = trim($url9[8]);

if(preg_match("/^seed[0-9]*/i",trim($combine_list_isp))){exit;} // SEED LIST
 if(empty($combine_list_isp)){exit;} else { $str_Arr = array("-","|","%7C"); $combine_list_isp=str_ireplace($str_Arr,'#',$combine_list_isp); $url10 = explode("#",$combine_list_isp); $listid=trim($url10[0]);$isp=trim($url10[1]); empty($listid)? die( "Error: Missing Listid!!"):true;} // NEWS_DATA | LISTID + ISP// NEWS_DATA | LISTID + ISP

if($sentip=="0.0.0.0" || $senddate=="##00-00-00#" || $senddate=="00-00-00" ){exit();}

// validate isp
if(!isValidIsp($isp)) {exit(); }
// validate list
//if(!isValidList($listid)) {exit(); }

// To check valid ip location and to track proxy clicks
$cip=trim($_SERVER['REMOTE_ADDR']); // IP OF GEO LOCATION
//$ip_result = tocheckRemoteAddress($cip); // Call function to db .php
$ipArrlink =array();
//while( $ipArr= mysql_fetch_array($ip_result) ) // 2, gm, null
//{
//	if(!empty($ipArr)) {
//		$ip_status=trim($ipArr[0]);
//		if($ip_status == 1)
//		{
//			exit(); 
//		}
//		elseif($ip_status == 2 && $ipArr[1] == "$isp")
//		{
//			exit();
//		}
//		elseif($ip_status == 3) // link blocked
//		{
//			$req= strtolower(substr($querystring,0,1));
//			if($req != 'r') { exit();}
//			array_push($ipArrlink,trim($ipArr[2]));
//		}
//		elseif($ip_status == 0)
//		{
//			$flag =1;
//			break;
//		}
//	}	
//}


// Redirectin Section starts
if($response != 'i'){
    $all_result_json=tocheckRemoteAddress_isValidList($cip,$listid);
    $ipArr= json_decode($all_result_json,true); 
}else{
    $ipArr['ip']['status'] = 0;
    $ipArr['listname'] = 1;
}


if(!$ipArr['listname']) { exit(); }else{$GLOBALS['listwise_db']=$ipArr['listname'];}

if(!empty($ipArr['ip'])) {
	$ip_status=trim($ipArr['ip']['status']);
	if($ip_status == 1)
	{
		exit(); 
	}
	elseif($ip_status == 2 && $ipArr['ip']['isp'] == "$isp")
	{
		exit();
	}
	elseif($ip_status == 3) // link blocked
	{
		$req= strtolower(substr($querystring,0,1));
		if($req != 'r') { exit();}
		array_push($ipArrlink,trim($ipArr['ip']['link']));
	}
	elseif($ip_status == 0)
	{
		$flag =1;
		//break;
	}
}


// dropid used for Acquinity offers to pass in link as X1..X5 param
#$dropid = sprintf("%07s",base_convert(trim(sprintf("%u", ip2long($cip))),10,32));
$dropid = str_pad(base_convert($subjectid,10,16), 6, '0', STR_PAD_LEFT);
$subdate = "0000-00-00"; // to store in DB
$setdate = date("Y-m-d H:i:s");

//$sentip = coniptonum($sentip);
// *** Global variable ends here ***

/* Structure of the database 
 * Each link based on clicks, opens and unsub/opt-out will have one function to redirect to sponsor end
 * 
 * 
 * */


// ============== For Storing Info (Impressions) =============


switch($response)
{
	case 't': terms(); //terms link
		break;
	case 'p': privacy(); //privacy link
		break;
	case 'l': datapolicy(); //data policy link
		break;
	case 's': spam(); //report spam link
		break;	
	case 'r': redirect(); //redirect link
		break;
	case 'u': unsub(); //unsub link
		break;
	case 'o': optout(); //optout link
		break;
	case 'i': opens(); //opens link
		break;
	case 'g': botclick(); //bot click link
		break;

}

// ============== For Storing Info (Impressions) =============


// To redirect to sponsor end
function redirect()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$ip_status,$ipArrlink,$enc_id_str,$midc; //Newly added $enc_id_str
	$SG_eml='';

	$isAcquinity = 0;
	$lst = strtolower(trim($listid).'d100');
	if($lst=="frnt01d100") {$lst="padclk02d100";}		
	// call to get offer to offer redirection info

	$offerRedirect = getOfferRedirect($offername, $listid);

	if(!empty($offerRedirect))
	{
		$offername = trim($offerRedirect['to_offerid']);
		if(!empty($offerRedirect['to_list']))
		{
			$lst = trim($offerRedirect['to_list']).'d100';
		}
	}// if
	if(!preg_match("/^frj/",$offername)){
		#$listid = $listid."d100";
	}
	$offerArr = urlLink($offername,$lst);  // Call function to db .php
        $redirect_url=trim($offerArr['url_dep']);$spons=trim($offerArr['spid']);$prepop=trim($offerArr['prepop']);$params=trim($offerArr['params']);$category=trim($offerArr['category']) ;
	$offeriddate=trim($offerArr['offerdate']);
	
        if($ip_status == 3)
	{
		foreach($ipArrlink as $findme)
		{			
			if(strpos($redirect_url,$findme) !== FALSE)
			{
				exit();
			}	
		}
	}
	
	$emailTempFlag = 0;
	$HACK = toCheckEncID($emailid,$enc_id_str);
	$userid = sprintf("%05s",(base_convert($emailid,10,36)));
	$con_ip = sprintf("%07s",base_convert(trim(sprintf("%u", ip2long($sentip))),10,32));
        
	# prepop call check
	if($prepop == 1)
        {
                if(!empty($params)){
                                $redirect_url=trim($redirect_url)."".trim($params);
                                $emailTempFlag = 1;
                }

                $emailArr = getDetails($emailid,$isp,$listid,true);  // Call function to db .php
                
		$emRow = explode("|", $emailArr); // 0=email, 1=fname, 2=lname, 3=zip
                $SG_eml = trim($emRow[0]); // trim($emailArr);

                $prepopArr =array();
                $prepopArr[0]= '[email]';
                $prepopArr[1]= '[fname]';
                $prepopArr[2]= '[lname]';
                $prepopArr[3]= '[zip]';
                $prepopArr[4]= '[dob]';
                $prepopArr[5]= '[city]';
                $prepopArr[6]= '[ph]';
                $prepopArr[7]= '[gen]';
                $prepopArr[8]= '[state]';
                for ($i=0; i < sizeof($emRow); $i++){

                                if(preg_match("#".$prepopArr[$i]."#i",$redirect_url)){

                                                $redirect_url = str_ireplace($prepopArr[$i],trim($emRow[$i]),$redirect_url);
                                }
                                if( sizeof($prepopArr)-1== $i){ break;}
                }

                #### Code Change From Old to New One For Prepop 21 AUG 2014

                if(!$emailTempFlag && (preg_match("/(\?|\&)em=/i",$redirect_url) || preg_match("/(\?|\&)email=/i",$redirect_url) ))
                {
                        if($emRow[0] == ''){
                                $emailArr = getDetails($emailid,$isp,$listid,false);  // Call function to db .php
                                $SG_eml = trim($emailArr);
                        }
                        $redirect_url = str_replace("email=","email=".trim($SG_eml),$redirect_url);
                        $redirect_url = str_replace("em=","em=".$SG_eml,$redirect_url);
                }

                ##### END CODE

        }
	# acquinity offer to check	
	if(preg_match("/\/DefaultPage\.aspx\?/i",$redirect_url))
	{
		if(empty($SG_eml)) { 
			$emailArr = getDetails($emailid,$isp,$listid,false);  // Call function to db .php
			$SG_eml = trim($emailArr);
		}
		$pubId = trim(getSessionID($SG_eml)); // Call function to db .php
		
		// $temp_url = explode('&x1=',$redirect_url);
		// $redirect_url= empty($temp_url[0]) ? $redirect_url : trim($temp_url[0]);	//IMPortant
		
		# if url is modernate or pureads 2
			$redirect_url = str_replace("x1=", "x1=JOE{$userid}", $redirect_url);
			$redirect_url = str_replace("x2=", "x2={$lst}", $redirect_url);
			$redirect_url = str_replace("x3=", "x3={$con_ip}", $redirect_url);
			$redirect_url = str_replace("x4=", "x4={$offername}", $redirect_url);
			$redirect_url = str_replace("x5=", "x5={$dropid}", $redirect_url);
	
			$var_url ="&xxClickId=".$pubId;
			$redirect_url.=$var_url;
			$isAcquinity = 0;
	}
	if(preg_match("/\/slo\.aspx\?/i",$redirect_url))
	{
		if(empty($SG_eml)) { 
			$emailArr = getDetails($emailid,$isp,$listid,false);   // Call function to db .php
			$SG_eml = trim($emailArr);
		}

		$pubId = trim(getSessionID($SG_eml)); // Call function to db .php

//		$temp_url = explode('&x1=',$redirect_url);
//		$redirect_url= empty($temp_url[0]) ? $redirect_url : trim($temp_url[0]);	//IMPortant	
		
		# if url is modernad beta
/*			$var_url="&x1=".$userid;
			$var_url.="&x2=".$lst;
			$var_url.="&x3=".$con_ip;
			$var_url.="&x4=".$offername;
			$var_url.="&x5=".$dropid;
*/
			$redirect_url = str_replace("x1=", "x1={$userid}", $redirect_url);
			$redirect_url = str_replace("x2=", "x2={$lst}", $redirect_url);
			$redirect_url = str_replace("x3=", "x3={$con_ip}", $redirect_url);
			$redirect_url = str_replace("x4=", "x4={$offername}", $redirect_url);
			$redirect_url = str_replace("x5=", "x5={$dropid}", $redirect_url);
	
			$var_url ="&xxClickId=".$pubId;
			$redirect_url.=$var_url;
			$isAcquinity = 0;
	}
	if(preg_match("/\/slost\.aspx\?/i",$redirect_url))
	{
		if(empty($SG_eml)) { 
			$emailArr = getDetails($emailid,$isp,$listid,false);   // Call function to db .php
			$SG_eml = trim($emailArr);
			
		}
		$pubId = trim(getSessionID($SG_eml)); // Call function to db .php
		
		// $temp_url = explode('&x1=',$redirect_url);
		// $redirect_url= empty($temp_url[0]) ? $redirect_url : trim($temp_url[0]);	//IMPortant	
		# if url is other than modernad beta
			$redirect_url = str_replace("x1=", "x1=ANS{$userid}", $redirect_url);
			$redirect_url = str_replace("x2=", "x2={$lst}", $redirect_url);
			$redirect_url = str_replace("x3=", "x3={$con_ip}", $redirect_url);
			$redirect_url = str_replace("x4=", "x4={$offername}", $redirect_url);
			$redirect_url = str_replace("x5=", "x5={$dropid}", $redirect_url);
	
			$var_url ="&xxClickId=".$pubId;
			$redirect_url.=$var_url;
			$isAcquinity = 0;
	}	
	# acquinity offer to check	END

	
	#if url is ClickBooth   
	if ( preg_match("#^http://[^\.]*\.clickbooth.com/\?.*#i", $redirect_url) ){
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;			
	}
        
	#if url is ckadmin   or commission wizard_New /ckadminNew(11-15-2013) / xperinet.net a=65
	if ( preg_match("#^http://[^\.]*\.{0,1}cktrk.net/\?a=14.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}engagedtrace.com/\?a=14.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}positionpaths.com/\?a=515.*#i", $redirect_url) || preg_match("#^http://quickpixel.net/\?a=515.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}xprtrk.com/\?a=65&.*#i", $redirect_url) ){		
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	#if url is The New In_W4_new,Esp_W4_new Network - added by Arijit on 2014-05-25   
	if ( preg_match("#^http://goyore\.com/clicks\?cid=[0-9]*&pub=107669&sid1=.*#i", $redirect_url) || preg_match("#^http://goyore\.com//clicks\?cid=[0-9]*&pub=107669&sid1=.*#i", $redirect_url) || preg_match("#^http://goyore.com/\?cid=[0-9]*&pub=107669&sid1=.*#i", $redirect_url) || preg_match("#^http://goyore.com\?cid=[0-9]*&pub=107669&sid1=.*#i", $redirect_url) || preg_match("#^http://informationcustom\.com.*\?cid=([^&]*)&pub=107670&sid1=.*#i", $redirect_url) || preg_match("#^http://innoadvantage\.com.*\?cid=([^&]*)&pub=107671&sid1=.*#i", $redirect_url) ||  preg_match("#^http://homtell.net\?cid=[0-9]*&pub=105124&sid1=.*#i", $redirect_url) || preg_match("#^http://cuubes.bid\?cid=[0-9]*&pub=105133&sid1=.*#i", $redirect_url) || preg_match("#^http://uncannyvalley.bid\?cid=[0-9]*&pub=106095&sid1=.*#i", $redirect_url) ){			
		$var_url="&sid2=".$listid."|".$userid."|".$dropid;
		$var_url.="&sid3=".$offername;
		$var_url.="&sid4=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}else if ( preg_match("#^http://[^\.].*&pub=105124&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=106095&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=105133&.*#i", $redirect_url)){
		$var_url="&c2=".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	
        if (preg_match("#^http://[^\.].*&pub=820003&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=820004&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=820005&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=160707&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=160706&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=202635&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=200085&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=600268&.*#i", $redirect_url)  ||  preg_match("#^http://[^\.].*&pub=800965&.*#i", $redirect_url)   ||  preg_match("#^http://[^\.].*&pub=232897&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=400807&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=120022&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=100188&.*#i", $redirect_url) ||  preg_match("#^http://[^\.].*&pub=300028&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=201740&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=202530&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=202586&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=160222&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=190015&.*#i", $redirect_url) || preg_match("#^http://[^\.].*&pub=202535&.*#i", $redirect_url)){
		$var_url="&c2=".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	#New redirection pattern for BluCAds --Vishal 
	if(preg_match("#^http://www.mayegg.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://www.pixoloops.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) ||  preg_match("#^http://pixoloops.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) ||  preg_match("#^http://belowfinish.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) ||   preg_match("#^http://boolcomtel.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://www.accessibleabsence.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://accessibleabsence.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) ||  preg_match("#^http://mayegg.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://firedag.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) ||  preg_match("#^http://singlehoptrk.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) ||   preg_match("#^http://vertocomms.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://fikoredires.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://berandorama.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://fikoredires.com/[a-zA-Z0-9\-]*#i", $redirect_url) ||  preg_match("#^http://mekanicrt.com/[a-zA-Z0-9\-]*#i", $redirect_url) ||  preg_match("#^http://commsider.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://receconevd.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://sorthismek.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://ricozzamedia.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://chroncontrol.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://www.frostygold.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://www.digitaldirectpro.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)  || preg_match("#^http://www.tripleny.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)   || preg_match("#^http://www.ventalicis.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://www.circlestraight.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)    || preg_match("#^http://phostertill.com/[a-zA-Z0-9\-]*#i", $redirect_url) ||  preg_match("#^http://sweeterfaster.com/[a-zA-Z0-9\-]*#i", $redirect_url)  || preg_match("#^http://www.optimesout.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)   || preg_match("#^http://capererlimiter.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://solutionhammer.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://intensifybad.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://rank3w.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://rdrtraffic.com/[a-zA-Z0-9\-]*#i", $redirect_url) || preg_match("#^http://rxlinking.com/[a-zA-Z0-9\-]*#i", $redirect_url) ){
		$var_url="/".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
        
	#AddemandDigital Sponsor 
	if(preg_match("#^http://www.slickpickmedia.com/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://www.getclck.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)   || preg_match("#^http://www.Getclck.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)   || preg_match("#^http://www.circlelead.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)   || preg_match("#^http://www.sittingdon.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)  || preg_match("#^http://www.keysnotes.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)   || preg_match("#^http://www.gosendtrk.com/[a-zA-Z0-9~_\-]*#i", $redirect_url)){
		$var_url="/".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
                $offeriddate = date("m-d-y", strtotime($offeriddate));
		$var_url .= "/".$offeriddate;
		$redirect_url.=$var_url;
	}	


	#if url is Adverbid_Bluecads  http://korentilius.com/0/0/0/20d9136a10a0476045a15aade0312198/bulo55d7a_mbskt1nwjm19j
	if(preg_match("#^http://storeausie.com/[0-9]/[0-9]/[0-9]/[a-zA-Z0-9~_\-]*#i", $redirect_url) || preg_match("#^http://korentilius.com/[0-9]/[0-9]/[0-9]/[a-zA-Z0-9~_\-]*#i", $redirect_url) ){
			$var_url="/".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
			$redirect_url.=$var_url;
	}
	
	#if url is pushint
	if ( preg_match("#^http://[^\.].*/c/[a-zA-Z0-9]{26}/\?subid=#i", $redirect_url))
	{
		$var_url="|".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	# if url is from Millionaire
	if ( preg_match("#^http://[^\.]*\.goodfaith.co/.*#i", $redirect_url))
	{
		$var_url="&c2=".$listid."|".$userid."|".$dropid."|".$offername."|".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
        
	#if url is Concise Media Group/Consise Media 20140516
	if ( preg_match("#^http://cmgtrk.com/\?a=13&.*#i", $redirect_url) || preg_match("#^http://[^\.].*/\?a=103&.*#i", $redirect_url) ){			
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url; 
	}
        
	#if url is The Affluent Network
	if ( preg_match("#^http://t.afftrackr.com/\?a=201015&.*#i", $redirect_url) ){			
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
    
	#if url is The Addemand Network
	if ( preg_match("#^http://[^\.]*\.sitelinz.com/\?a=14480&.*#i", $redirect_url) ){			
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	#if url is The New IMS_ClickboothNew  Network - added by Arijit on 2014-05-25 
	if ( preg_match("#^http://[^\.]*\.clickbooth.com/c/aff\?lid=[0-9]*&subid1=.*#i", $redirect_url) ){			
		$var_url="&subid3=".$listid."|".$userid."|".$dropid;
		$var_url.="&subid4=".$offername;
		$var_url.="&subid5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	#if url is The Addemand Network - added by chandrakant on 2014-05-28  
	#http://f4.taplnk.com/?a=14475&c=989&p=c&m=3&s1=
	if (preg_match("#^http://ga01tkpg.com/\?E=.*#i", $redirect_url) || preg_match("#^http://remoldunbar.com/\?E=.*#i", $redirect_url) || preg_match("#^http://trkvdg.com/\?E=.*#i", $redirect_url) ||  preg_match("#^http://lqdstream.com/\?E=.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}nettposts.com/\?.*#i", $redirect_url)  || preg_match("#^http://[^\.]*\.{0,1}brightnett.com/\?.*#i", $redirect_url)  || preg_match("#^http://[^\.]*\.{0,1}mobilenett.com/\?.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}souredrct.com/\?.*#i", $redirect_url) || preg_match("#^http://iafbossage.com/\?.*#i", $redirect_url) || preg_match("#^http://longlyresting.com/\?.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}taplnk.com/\?a=14475&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}taplnk.com/\?a=14577&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}savetrck.com/\?a=14565&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}savetrck.com/\?a=14575&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}taplnc.com/\?a=14475&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}teplnk.com/\?a=14475&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}redtrck.com/\?a=14475&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}redtrck.com/\?a=14577&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}bestdoemane.com/\?a=14575&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}souredrct.com/\?a=14577&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}nettposts.com/\?a=14475&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}mobilenett.com/\?a=14482&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}brightnett.com/\?a=14575&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}skydistance.com/\?a=800965&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}lqdstream.com/\?a=800965&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}beginningstart.com/\?a=800965&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}evenwonder.com/\?a=800965&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}nettposts.com/\?E=.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.{0,1}souredrct.com/\?E=.*#i", $redirect_url)  || preg_match("#^http://[^\.]*\.{0,1}mobilenett.com/\?E=.*#i", $redirect_url)  || preg_match("#^http://[^\.]*\.{0,1}brightnett.com/\?E=.*#i", $redirect_url)){			
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	

	//URL for  EspAdconion  -- add by nitesh jain 2015-01-04
	if ( preg_match("#^http://[^\.]*\.{0,1}rocksroute.com/\?a=14482&.*#i", $redirect_url)){			
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}	
		
	   
	   
	#if url is EspMocanMedia added by Vishal 2014-09-15
        if ( preg_match("#^http://sweepstrk.com/\?a=36&.*#i", $redirect_url) ){
                $var_url="&s3=".$listid."|".$userid."|".$dropid;
                $var_url.="&s4=".$offername;
                $var_url.="&s5=".$con_ip."|".$midc;
                $redirect_url.=$var_url;
        }
        #if url is ImsMocanMedia added by Vishal 2014-09-15
        if ( preg_match("#^http://sweepstrk.com/\?a=37&.*#i", $redirect_url) ){
                $var_url="&s3=".$listid."|".$userid."|".$dropid;
                $var_url.="&s4=".$offername;
                $var_url.="&s5=".$con_ip."|".$midc;
                $redirect_url.=$var_url;
        }


	#URL for IMScupcake/ESPcupcake/UScupcake --added by Vishal on 2014-10-10

        if ( preg_match("#^http://[^\.]*\.trknw.com/\?a=2&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.trknw.com/\?a=3&.*#i", $redirect_url) || preg_match("#^http://[^\.]*\.trknw.com/\?a=4&.*#i", $redirect_url) ){
                $var_url="&s3=".$listid."|".$userid."|".$dropid;
                $var_url.="&s4=".$offername;
                $var_url.="&s5=".$con_ip."|".$midc;
                $redirect_url.=$var_url;
        }

 
	 #URL for Optimal --added by Vishal on 2014-10-10

		if ( preg_match("#^http://tracking.pingmedianetworks.com/aff_c\?offer_id=[0-9]*&aff_id=1070&.*#i", $redirect_url) ||  preg_match("#^http://[^\.]*\.orstr4k.com/aff_c\?offer_id=[0-9]*&aff_id=1008&.*#i", $redirect_url) || preg_match("#^http://mail2mediausa.go2cloud.org/aff_c\?offer_id=[0-9]*&aff_id=1018&.*#i", $redirect_url)){
                $var_url="&aff_sub3=".$listid."|".$userid."|".$dropid;
                $var_url.="&aff_sub4=".$offername;
                $var_url.="&aff_sub5=".$con_ip."|".$midc;
                $redirect_url.=$var_url;
        }

		
	#if url is CX3 added by Vishal J 2014-10-27
	$url_temp =urldecode($redirect_url);
	if((preg_match("#^http://lincolntrk.com/\?E=.*#i", $redirect_url)) ||  (preg_match("#^http://elliottrk.com/\?E=.*#i", $redirect_url))  ||  (preg_match("#^http://visiontimed.com/\?E=.*#i", $redirect_url)) ||   (preg_match("/\?E\=[a-zA-Z0-9\/\=+]{24}&s1\=.*/",$url_temp ) && !preg_match("/.*s3\=.*/",$url_temp ))){
		// add code here for s3= and s4= s5=
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	
	#if url is Revenue Track - added by chandrakant on 8-8-2013/ esp revenue account(11-15-2013)
	if ( preg_match("#^http://rvptrk.com/\?a=11&.*#i", $redirect_url) ||  preg_match("#^http://rvptrk.com/\?a=16&.*#i", $redirect_url)  ||  preg_match("#^http://hitzr.com/\?a=11&.*#i", $redirect_url) || preg_match("#^http://jmpgo.com/\?a=11&.*#i", $redirect_url)  ){
                $var_url="&s3=".$listid."|".$userid."|".$dropid;
                $var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
                $isAcquinity = 1;
	}
        
	#if url is Orangemagnum
	if ( preg_match("#^http://domagnum.com/\?a=77&.*#i", $redirect_url) ){		
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}
	
        #if url is InboxExpress
	if ( preg_match("#^http://ibetrk.com/\?.*#i", $redirect_url) ){		
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}	
	
	#if url is SmartADV  
	if ( (preg_match("#^http://.*/\?.*&s1=.*#i", $redirect_url) && strpos($redirect_url, '&s3=') == false) ){		
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}	
	

	#if url is SmartADV  
	if ( (preg_match("#^https://.*/\?.*&s1=.*#i", $redirect_url) && strpos($redirect_url, '&s3=') == false) ){		
		$var_url="&s3=".$listid."|".$userid."|".$dropid;
		$var_url.="&s4=".$offername;
		$var_url.="&s5=".$con_ip."|".$midc;
		$redirect_url.=$var_url;
	}	
		
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$EX=0; ###----------To find out the errors in redirection link
	if(preg_match("#(&s3=|&c2=|&subid3=|&sid3=|aff_sub3|rank3w\.com|rdrtraffic\.com|rxlinking\.com|capererlimiter\.com|solutionhammer\.com|intensifybad\.com|phostertill\.com|sweeterfaster\.com|gosendtrk\.com|optimesout\.com|circlestraight\.com|ventalicis\.com|tripleny\.com|keysnotes\.com|getclck\.com|Getclck\.com|digitaldirectpro\.com|frostygold\.com|receconevd\.com|commsider\.com|sittingdon\.com|circlelead\.com|chroncontrol\.com|sorthismek\.com|ricozzamedia\.com|mekanicrt\.com|stradivarioos\.com|berandorama\.com|korentilius\.com|fikoredires\.com|vertocomms\.com|singlehoptrk\.com|firedag\.com|mayegg\.com|accessibleabsence\.com|boolcomtel\.com|slickpickmedia\.com|belowfinish\.com|pixoloops\.com|storeausie\.com)#",$redirect_url)){
		$EX=1;
	}
	
	$redirect_url = trimValue($redirect_url);

	// MYSQL close conn
	mysql_close($GLOBALS['link']);
	
	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=ck";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);		
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);										
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=".$isAcquinity; ## add 15-nov -2013 : To provide session id to morocco  by pankajm
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
	$post_query .= "&midc=".urlencode($midc);
	$post_query .= "&category=".urlencode($category);
	$post_query .= "&Exp=".$EX;
 	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);

	// Redirect to sponsor end
	header("Location: $redirect_url");
        exit;
}

// To Terms
function terms()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;

	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=terms";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);

	$loc = trim(getTerms($offername)); // Call function to db .php
	if(empty($loc)){ exit();}
	else
	header("Location: $loc");
        exit;
}

// To Data Policy
function datapolicy()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;

	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=dp";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);



	$loc = trim(getDatapolicy($offername)); // Call function to db .php
	if(empty($loc)){ exit();}
	else	
	header("Location: $loc");
        exit;
}

// To Privacy
function privacy()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;

	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=po";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);



	$loc = trim(getPrivacy($offername)); // Call function to db .php
	if(empty($loc)){ exit();}
	else	
	header("Location: $loc");
        exit;
}

// To Terms
function spam()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;
	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=spam";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);


	$loc="http://".trim($_SERVER['HTTP_HOST'])."/thanks.html";
	header("Location: $loc");
        exit;
}

// Bot clicks
function botclick()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;
	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=bot";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);

}
// To unsubscribe
function unsub()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;
	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=un";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);
	#$emailArr = getDetails($emailid,$isp,$listid,false);
	#$uns_key = md5(trim($emailArr)."|cyne_03454");
	#$loc="http://".trim($_SERVER['HTTP_HOST'])."/unsub.html?uid=".base64_encode(trim($emailArr))."&k=$uns_key";
	#header("Location: $loc");
	###########################
        
        $loc = trim(getUnsub($offername));
        if($loc){
            header("Location: $loc");
        }else{
            include("unsub_mess.php");
        }
        
        ###########################
	//include("unsub_mess.php");
	exit;
}
// To optout
function optout()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;
	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	

	$post_query = "http://ely10.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=opt";
	$post_query .= "&isp=".urlencode($isp);
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);


	
	$loc = trim(getOptout($offername)); // Call function to db .php
	if(strstr($loc,"?email=[=email=]"))
	{
		$emailArr = getDetails($emailid,$isp,$listid, false) ;
	        $loc = str_replace("?email=[=email=]","?email=".trim($emailArr),$loc);
        }		
	if(strstr($loc,"[email]"))
	{
		$emailArr = getDetails($emailid,$isp,$listid,false);
		$loc = str_replace("[email]",trim($emailArr),$loc);
	}
	// MYSQL close conn
	mysql_close($GLOBALS[link]);
		
	header("Location: $loc");
        exit;
}

// To openers
function opens()
{
	global $emailid,$offername,$subjectid,$sentip,$uid,$senddate,$listid,$isp,$dropid,$subdate,$setdate,$midc;
	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	
	$loc = 'http://'.trim($_SERVER['HTTP_HOST'])."/open.php";

	$post_query = "http://ely02.imsinternal.com/redirect_requet/RR.php?email=".urlencode($SG_eml)."&type=imp";	
	$post_query .= "&isp=".urlencode($isp);	
	$post_query .= "&id_email=".urlencode($emailid);
	$post_query .= "&offre=".urlencode($offername);
	$post_query .= "&msgid=".urlencode($subjectid);
	$post_query .= "&ip_clk=".urlencode($sentip);
	$post_query .= "&ip_user=".urlencode(trim($_SERVER['REMOTE_ADDR']));
	$post_query .= "&spons=".urlencode($spons);
	$post_query .= "&listid=".urlencode($listid);
	$post_query .= "&uid=".urlencode($uid);
	$post_query .= "&senddate=".urlencode($senddate);
	$post_query .= "&domain=".urlencode(trim($_SERVER['HTTP_HOST']));
	$post_query .= "&url=";
	$post_query .= "&httpuseragent=".urlencode(trim($useragent));
	$post_query .= "&httpref=".urlencode(trim($_SERVER['HTTP_REFERER']));
        $post_query .= "&midc=".urlencode($midc);
	$regSessID_cmd = "wget -b -O /dev/null -o /dev/null '$post_query' &";
	exec($regSessID_cmd);

	// MYSQL close conn
	mysql_close($GLOBALS[link]);
	header("Location: $loc");
	exit;
}


// To convert into actual parameter
function converthexdec($convert_url)
{
	$convert = explode('!',$convert_url);
	@$convert[1];
	$conid = hexdec($convert[0]).'#';
	@$HEXIP1  = $convert[1];
	@$consid = hexdec($convert[2]).'#';
	@$conuid = hexdec($convert[3]).'#';
	@$conds = $convert[4];
	@$oname = $convert[5];
	@$HEXDATE = $convert[6];
	$ipback = explode('p',$HEXIP1);
	@$fir =  hexdec($ipback[0]);@$sec = hexdec($ipback[1]);@$thi = hexdec($ipback[2]);@$fou = hexdec($ipback[3]);
	$array1 = array($fir, $sec, $thi, $fou);
	$conip = implode(".", $array1).'#';
	$dateback = explode('t',$HEXDATE);
	@$firdat =  hexdec($dateback[0]);@$secdat = hexdec($dateback[1]);@$thidat = hexdec($dateback[2]);
	if($firdat < '10'){ $firdat = '0'. $firdat;}
	if($secdat < '10'){ $secdat= '0'. $secdat;}
	if($thidat < '10'){ $thidat = '0'. $thidat;}

	$array2 = array($thidat,$secdat,$firdat);
	$date = implode("-", $array2);
	$ret = testDate($date);
	if($ret == 0){$newdate = changedateformat($date);}else{$newdate = $date;}

	$condate = $newdate."#";

	#$condate = implode("-", $array2).'#';
	$conds1 = $conds.'#';
	$offer_name = $oname.'#';
	$url5 = "$conid$conip$consid$conuid$conds1$offer_name$condate";
	return $url5;
}

function testDate( $value )
{
	return preg_match( '`^\d{4}-\d{1,2}-\d{1,2}$`' , $value );
}

function changedateformat($date)
{
	$dd = explode("-",$date);
	$newd = $dd[2]."-".$dd[1]."-".$dd[0];
	return $newd;
}
//
function coniptonum($ip)
{
	$newip = explode('.',$ip);
	$ipd = $newip[0].$newip[1].$newip[2].$newip[3];
	return $ipd;
}

function isValidIsp($isp)
{
	global $ispArr;
	//$validISPs = array("aol","att","bell","com","cox","ctr","eth","fb","gm","hot","juno","rr","sbc","ver","yah","oth");
	$validISPs =$ispArr;
	
	if(!in_array($isp, $validISPs)) 
	{ return false; }
	else
	{ return true; }
}// isValidIsp

  function decode_url($url)
  {
  	$data=explode("=",$url);
	list($list,$offerid)=explode("*",$data[1]);
	list($encr_isp,$encr_id,$enc_ip,$encr_subid,$data2)=explode(".",$data[2]);
	list($encr_date,$data3)=explode("__",$data2);
        list($data4,$midc)=explode('/',$data3);
	$decr_id= base_convert($encr_id,36,10);
	$decr_ip=long2ip(base_convert($enc_ip,36,10));
	$decr_subid=base_convert($encr_subid,36,10);
	$decr_date=date("Y-m-d",strtotime(base_convert($encr_date,36,10)));
	$decr_uid = base_convert(substr($data4,0, 3),36,10);
	$encr_sexagecimal = substr($data4,3);
	return $URL = "{$decr_id}#{$decr_ip}#{$decr_subid}#{$decr_uid}#{$list}|{$encr_isp}#{$offerid}#{$decr_date}#{$encr_sexagecimal}#{$midc}";

  } 
  
    function toConvert($id)
    {
        return  encode(log($id+617368,37)*10000); // Please dont change most imp
    }
    
    function encode($n) {
            $tmp = explode('.',$n);
            $n = trim($tmp[1]);
            $s = '';
            $m = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz';
            if ($n === '' || $n === 0) { return 0; }
            while ($n > 0) {
              $d = $n % 60;
              $s = $m[$d] . $s;
              $n = ($n - $d) / 60;
            }
            return ltrim($s, '0');
    }
    function toCheckEncID($id,$encid)
    {
            $enc_out = toConvert($id);
            if($enc_out == $encid){ return TRUE;}
            else {return false;}
    }

	function trimValue($URL){
		return preg_replace(array("#^http://([^\.]*\.{0,1}teplnk.com)/i#","#^http://([^\.]*\.{0,1}taplnc.com)/i#"),"http://f4.taplnk.com/", $URL);
	}  
?>
