<?php
$ip = "109.230.238.17";
$service = "AuthSrv2";
$serverName = "109.230.238.17";
$WH_connectionInfo = array("Database"=>"GameWarehouseDB", "UID"=>"sa", "PWD"=>"MUzk967NRCffX9E");
$Game_connectionInfo = array("Database"=>"BlGame01", "UID"=>"sa", "PWD"=>"MUzk967NRCffX9E");
$Goodid_container = 80405;
$level50_voucher_id = 111;
$password = "";
$account_name = "";

function cut_str($begin,$end,$str)
{
	$b = \mb_strpos($str,$begin) + \mb_strlen($begin);
	//echo $b."|";
	$e = \mb_strpos($str,$end, $b) - $b;
	//echo $e."|";
	$rs = \mb_substr($str,$b,$e);
	//echo $rs."|";
	return $rs;
 }

function get_owneraccid($charname) {
	global $serverName, $Game_connectionInfo;
	$game_conn = sqlsrv_connect($serverName, $Game_connectionInfo);
	$sql = "SELECT game_account_id FROM CreatureProperty WHERE name = '$charname'";
	$stmt = sqlsrv_query($game_conn, $sql);
	if($stmt === false) {
		die(print_r(sqlsrv_errors(), true) );
	}

	if( sqlsrv_fetch($stmt) === false) {
		die(print_r(sqlsrv_errors(), true));
	}
	$owneraccid = sqlsrv_get_field($stmt, 0);
	sqlsrv_free_stmt($stmt);
	sqlsrv_close($game_conn);
	return $owneraccid;
}

if (!empty($_GET)) {
	$account_name = $_GET["account_name"];
	$password = $_GET["account_password"];
	if (trim($account_name) == "" || trim($password) == "") {
		echo "Account name and password cannot be empty.";
	}
	else {
		$conn = sqlsrv_connect($serverName, $WH_connectionInfo);
		if( $conn === false )
		{
			echo "Could not connect.\n";
			print('<pre>');
			die(print_r(sqlsrv_errors(), true));
			print('</pre>');
		}
		else {
			// $OwnerAccountID = get_owneraccid($charname);
			// if ($OwnerAccountID == "") {
				// die(print("Character name not found."));
			// }
			
			$cURLConnection = curl_init();
			curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/apps-state");
			curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($cURLConnection);
			curl_close($cURLConnection);
			$resultapp = cut_str("<AppName>{$service}</AppName>","</App>",$result);
			$resultapp = cut_str("<Epoch>","</Epoch>",$resultapp);
			
			$postRequest = array(
				'loginName' => $account_name.'@ncsoft.com',
				'userName' => $account_name,
				'password' => $password,
				'effectiveUntil' => '',
				'loginNameValidated' => 1,
				'userCenter' => 17
			);
			$cheaders = array(
				'Accept: */*',
				'Accept-Encoding: gzip, deflate',
				'Accept-Language: en-US,en;q=0.9',
				'Connection: keep-alive',
				'Content-Length: 0',
				'Host: 127.0.0.1:6605',
				'Origin: http://'.$ip.':6605',
				'Referer: http://'.$ip.':6605/spawned/VirtualCurrencySrv.1.'.$resultapp.'/test/',
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36',
			);
			
			$cURLConnection = curl_init();
			curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/spawned/{$service}.1.{$resultapp}/test/create_account?loginName=".rawurlencode($postRequest['loginName'])."&userName=".rawurlencode($postRequest['userName'])."&password=".rawurlencode($postRequest['password'])."&effectiveUntil=".rawurlencode($postRequest['effectiveUntil'])."&loginNameValidated=".rawurlencode($postRequest['loginNameValidated'])."&userCenter=".rawurlencode($postRequest['userCenter'])."");
			curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURLConnection, CURLOPT_POST, 0);
			
			$result = curl_exec($cURLConnection);
			curl_close($cURLConnection);			
		}
		echo "Signup Result<pre>".htmlentities($result)."</pre>";

	}
}

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
	Account name: <input type="text" name="account_name" value="<?php echo $account_name; ?>"><br>
	Password: <input type="text" name="account_password" value="<?php echo $password; ?>"><br>
	<input type="submit">

	<style type="text/css">
		form
		{
			width: auto;
			height: auto;
			margin: auto;
			font-size: 5em;
			margin: 0;
			padding:0;
			text-align: center;
			font-family: fantasy;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translateX(-50%);
			transform: translateY(-50%);

					}
					input 
					{
						width: 500px;
						height: 61px;
						border-radius: 5px;
					}
					body 
					{
						background-image:url("wallpaper.jpg"); 
					}
	</style>
</form>

