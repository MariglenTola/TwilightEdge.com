<?php
function get_dbconn($ip, $param){
	$conn = sqlsrv_connect($ip, $param);
	if($conn === false ){
		echo "Could not connect db\n";
		print('<pre>');
		die(print_r(sqlsrv_errors(), true));
	}
	return $conn;
}

function cut_str($begin,$end,$str){
	$b = mb_strpos($str,$begin) + mb_strlen($begin);
	$e = mb_strpos($str,$end, $b) - $b;
	$rs = mb_substr($str,$b,$e);
	return $rs;
 }
 
function gen_gameitemkey($gameitemid){
	$gameitemkey = "";
	$encodechar = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'),array('+'), array('/')); 
	$encodestr = array();
	if(is_numeric($gameitemid)){
		$itemno = $gameitemid * 16;
		while($itemno >= 64){
			$encodestr[] = $encodechar[fmod($itemno,64)];
			$itemno = floor($itemno / 64);
		}
		$encodestr[] = $encodechar[$itemno];
		$gameitemkey = implode(array_reverse($encodestr))."==";
		$gameitemkey = str_pad($gameitemkey, 8, "A", STR_PAD_LEFT);
	}
	return $gameitemkey;
}

function get_service_instanceid($servicename){
	global $vars;
	$service_instanceid = "";
	$cURLConnection = curl_init();
	curl_setopt($cURLConnection, CURLOPT_URL, "http://{$vars['serverip']}:6605/apps-state");
	curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($cURLConnection);
	curl_close($cURLConnection);
	$resultapp = cut_str("<AppName>{$servicename}</AppName>","</App>",$result);
	$service_instanceid = cut_str("<Epoch>","</Epoch>",$resultapp);
	return $service_instanceid;
}

function call_service($servicename, $action, $method, $params){
	global $vars;
	if(!$vars['service_instanceid'][$servicename]) {
		$vars['service_instanceid'][$servicename] = get_service_instanceid($servicename);
	}
	$url = 'http://'.$vars['serverip'].':6605/spawned/'.$servicename.'.1.'.$vars['service_instanceid'][$servicename];
	$query = "";
	$postfields = "";
	$ch = curl_init();
	if ($method == 1) { //Post
		if($params) {
			foreach ($params as $key => $value) {
				$postfields .= "{$key}=".rawurlencode($value);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
		}
	}
	else { //Get
		if($params) {
			$query = "?".http_build_query($params);
		}
	}
	
	curl_setopt($ch, CURLOPT_URL, trim("{$url}/{$action}{$query}"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, $method);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	// curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
	//echo "{$url}/{$action}{$query}";
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		print_r(curl_error($ch));
	}
	curl_close($ch);
	return $result;
}

?>