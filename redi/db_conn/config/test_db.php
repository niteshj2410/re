<?php
// Database connectivity script created by Pankaj Moon. Date: 11Th Nov 2012
# Set a connection time out of mysql in 3 sec
ini_set("mysql.connect_timeout",7);

date_default_timezone_set('America/New_York');
$today = date("Y-m-d"); 
$GLOBALS['mysql_host'] = 'ely10.imsinternal.com';
$GLOBALS['mysql_host_gamma'] = 'h72.imsinternal.com';
$GLOBALS['mysql_login'] = 'redirect';
$GLOBALS['mysql_passwd'] = 'mitra6548.';
$GLOBALS['link'] = '';
$GLOBALS['server_conn'] = '';


//mysql to connect database
function m_connect($bPersistant = false)
{
	if($bPersistant)
	{
		$GLOBALS['link'] = mysql_pconnect($GLOBALS['mysql_host'], $GLOBALS['mysql_login'],$GLOBALS['mysql_passwd'] );$GLOBALS['server_conn'] ='ely10';
		if ($GLOBALS['link'] == '') {
			$GLOBALS['link'] = mysql_pconnect($GLOBALS['mysql_host_gamma'], $GLOBALS['mysql_login'],$GLOBALS['mysql_passwd'] );$GLOBALS['server_conn'] ='gamma';
		}				
	}
	else
	{
		$GLOBALS['link'] = mysql_connect($GLOBALS['mysql_host'], $GLOBALS['mysql_login'],$GLOBALS['mysql_passwd'] );$GLOBALS['server_conn'] ='ely10';
		if ($GLOBALS['link'] == '') {
			$GLOBALS['link'] = mysql_connect($GLOBALS['mysql_host_gamma'], $GLOBALS['mysql_login'],$GLOBALS['mysql_passwd'] );$GLOBALS['server_conn'] ='gamma';
		}		
	}	
//	mysql_select_db('redirectiondb',$GLOBALS['link']);
}


function explicit_connect($hostname, $bPersistant = false)
{
	if($bPersistant)
	{	
		$GLOBALS['link'] = mysql_pconnect($hostname, $GLOBALS['mysql_login'],$GLOBALS['mysql_passwd'] );$GLOBALS['server_conn'] ='ely10';	
	}
	else
	{
		$GLOBALS['link'] = mysql_connect($hostname, $GLOBALS['mysql_login'],$GLOBALS['mysql_passwd'] );$GLOBALS['server_conn'] ='ely10';
	}	
}

//mysql select single row
function select($sql,$Cnx){
	$res = mysql_query($sql,$Cnx) or die('Query failed India select function: ' . mysql_error());
	return @mysql_fetch_assoc($res);
}

//mysql select all rows
function select_all($sql,$Cnx){  
	$res = mysql_query($sql,$Cnx) or die('Query failed India select all: ' . mysql_error());
	return $res;
}

//mysql num_rows
function num_rows($sql,$Cnx){
	$res = mysql_query($sql,$Cnx) or die('Query failed India num_row : ' . mysql_error());
	return @mysql_num_rows($res);
}

//mysql insert
function insert($sql,$Cnx){
	$res = mysql_query($sql,$Cnx)  or die('Query failed India insert: ' . mysql_error());
	return mysql_affected_rows();
}

//url link
function urlLink($offername,$listid)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}		
	$sSql = "select url_dep,spid,prepop,params,offername,status,category,campaign_id,sponcername,uniqueid  from redirectiondb.offerDetails where offerid='".trim($offername)."'and listname='".trim($listid)."'";
	$result =  select($sSql,$GLOBALS['link']);
	if(empty($result['url_dep']))
	{
		mysql_close($GLOBALS['link']);
		explicit_connect($GLOBALS['mysql_host']); //explicitly mention the server name 
		$result =  select($sSql,$GLOBALS['link']);
	}
	if($result['status'] == 0)
	{
		exit();
	} 
	if($result['offername'] != 'empty')
	{
		$sSql = "select url_dep,spid,prepop,params,offername,status,category,campaign_id,sponcername,uniqueid  from redirectiondb.offerDetails where offerid='".trim($result['offername'])."'and listname='".trim($listid)."'";		
		$result =  select($sSql,$GLOBALS['link']);		
	}
	
	//-------------  check use country and offer allow country 
	
	$output = get_country_RemoteAddress($_SERVER['REMOTE_ADDR']);
	$outputArr = json_decode($output, true);
	$u_country =  $outputArr['country'];
 

 
	if($u_country!='' && $u_country!='US' &&  $u_country!='UK')
	{
		$country_chk = "CALL redirectiondb.CheckNewUserCountry('".$result['campaign_id']."','".$result['sponcername']."','".$u_country."',@newcpaid)";

		$q = mysql_query($country_chk,$GLOBALS['link']) or die(mysql_error());
		$q_link = mysql_query("SELECT @newcpaid;");
		$res = mysql_fetch_array($q_link);
		
		if($res[0]!='')
		{
			$result['oldurl_dep'] =$result['url_dep'];
			$result['url_dep'] ='http://www.grsecurtrk.com/rd/r.php?sid='.$res[0].'&pub=202535&c1='.$result['uniqueid'];  
		}
	}
	
	return $result;
}

function get_country_RemoteAddress($ip)
{
	if($ip!=''){
		$output = file_get_contents('http://h72.imsinternal.com/webRequest/check_country.php?ip='.$ip);
		
		if (empty($output)) {$output = file_get_contents('http://ely10.imsinternal.com/webRequest/check_country.php?ip='.$ip); } 
		if ($cache = $output){
			return $output;
		}else{
			return false;
		}
	}
	else
	{
		return false;
	}
}

// To check ipadress whethere valid or not OR skip insertion process
//function tocheckRemoteAddress($cip)
//{
//	$ciprange = substr($cip,0,strrpos($cip,"."));
//	// Call to remote database
//	if ($GLOBALS['link'] == '') {
//		m_connect();
//	}
//	$query = "select status,isp,link from redirectiondb.ip_block where ip = '$cip' or ip ='$ciprange'";
//	return select_all($query,$GLOBALS['link']);
////	return @mysql_result($result,0,0);
//}


//function tocheckRemoteAddress($cip)
//{
//	$retVal = true;
//	$url1 = file_get_contents('http://h72.imsinternal.com/webRequest/memcache_ipblock.php?ip='.$cip.'');
//	if (empty($url1)) {$url1 = file_get_contents('http://ely10.imsinternal.com/webRequest/memcache_ipblock.php?ip='.$cip.''); }
//        if ($cache = $url1){
//                return  $cache;
//        }else{
//                return false;
//        }
//}


// get email id, zip code and first name and last name
function getDetails($emailid,$isp,$listname,$param=false) 
{
        if($GLOBALS['listwise_db'])
	{
                $domain = 'ely01.imsinternal.com';
		$listDb = 1;
        }
        else{
                $domain = 'ely02.imsinternal.com';
		$listDb =0;
        }
        if($param == TRUE) { $URL = "http://".trim($domain)."/get_email_latest.php?pre=".trim($isp)."|".trim($emailid)."|param|".trim($listname)."|$listDb"; }
        else { $URL = "http://".trim($domain)."/get_email_latest.php?pre=".trim($isp)."|".trim($emailid)."|false|".trim($listname)."|$listDb"; }

        exec("wget -qO- --connect-timeout=2 -t 1 '$URL'",$output,$err); 
        return $output[0];
/*
        if($err == 0)
        {
                return $output);
        }
        else
        {
                echo "Try some other URL";
        }
*/
}

function getSessionID($SG_eml)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}
	if(!empty($SG_eml))
	{
		$sSql = "select pub_id from redirectiondb.sessionID where email='$SG_eml'";
		$result = mysql_query($sSql,$GLOBALS['link']);
		return @mysql_result($result,0,0);
	}
	else
	 return 0;
}

function getOptout($offername)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}	
	$sSql = "select link from redirectiondb.red_optouts where offerid='".$offername."'";
	$result = mysql_query($sSql,$GLOBALS['link']);
	return @mysql_result($result,0,0);	
}

function getTerms($offername)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}
	$sSql = "select link from redirectiondb.red_terms where offerid='".$offername."'";
	$result = mysql_query($sSql,$GLOBALS['link']);
	return @mysql_result($result,0,0);
}

function getDatapolicy($offername)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}
	$sSql = "select link from redirectiondb.red_data where offerid='".$offername."'";
	$result = mysql_query($sSql,$GLOBALS['link']);
	return @mysql_result($result,0,0);	
	
}

function getPrivacy($offername)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}
	$sSql = "select link from redirectiondb.red_privacy  where offerid='".$offername."'";
	$result = mysql_query($sSql,$GLOBALS['link']) or die(mysql_error());
	return @mysql_result($result,0,0);

}

//function isValidList($listid)
//{
//	$retVal = true;
//	// Call to remote database
//	if ($GLOBALS['link'] == '') {
//		m_connect();
//	}
//	 $sSql = "select listwise_db from redirectiondb.listDetails where listname ='{$listid}'";
//	$result = mysql_query($sSql,$GLOBALS['link']);
//	if(!$result) {$retVal = false;}
//	$row = mysql_fetch_array($result); // we get only one row, contaning the count.
//	if(mysql_num_rows($result)<= 0) {$retVal = false;}
//	else{ $retVal = true; $GLOBALS['listwise_db']=$row[0] ;}
//	
//	return $retVal;
//} // isValidList

//function isValidList($listid)
//{
//        $retVal = true;
//	$url1 = file_get_contents('http://h72.imsinternal.com/webRequest/memcached.php?listname='.$listid.'');
//	if (empty($url1)) {$url1 = file_get_contents('http://ely10.imsinternal.com/webRequest/memcached.php?listname='.$listid.''); }
//        if ($cache = $url1){
//                $GLOBALS['listwise_db']= $cache;
//                return $retVal;
//        }else{
//                return false;
//        }
//
//        // Call to remote database
//        if ($GLOBALS['link'] == '') {
//                m_connect();
//        }
//         $sSql = "select listwise_db from redirectiondb.listDetails where listname ='{$listid}'";
//        $result = mysql_query($sSql,$GLOBALS['link']);
//        if(!$result) {$retVal = false;}
//        $row = mysql_fetch_array($result); // we get only one row, contaning the count.
//        if(mysql_num_rows($result)<= 0) {$retVal = false;}
//        else{ $retVal = true; $GLOBALS['listwise_db']=$row[0] ;}
//
//        return $retVal;
//} // isValidList


function tocheckRemoteAddress_isValidList($cip,$listid)
{
        $retVal = true;
	$url1 = file_get_contents('http://h72.imsinternal.com/webRequest/webRest.php?ip='.$cip.'&listname='.$listid.'');
	if (empty($url1)) {$url1 = file_get_contents('http://ely10.imsinternal.com/webRequest/webRest.php?ip='.$cip.'&listname='.$listid.''); }
        if ($cache = $url1){
                return $url1;
        }else{
                return false;
        }
} 


function getOfferRedirect($offerid, $list)
{
	// Call to remote database
	if ($GLOBALS['link'] == '') {
		m_connect();
	}

	if(empty($offerid)) {return 0;}
	
	$query = "SELECT from_list, to_offerid, to_list FROM redirectiondb.offer_redirect WHERE from_offerid='$offerid' AND (from_list='' OR from_list='$list')";
	// if(!empty($list)) { $query .= " AND from_list='$list'";}
	$result = mysql_query($query, $GLOBALS['link']);
	// since we have unique key on from_offerid, we would get only one row per offerid.
	if(mysql_num_rows($result) <= 0)
	{ return 0; }
	else 
	{
		$row = mysql_fetch_assoc($result);
		return $row;
	}
}// getOfferRedirect
?>


