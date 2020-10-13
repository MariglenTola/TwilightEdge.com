<?php
$ip = "127.0.0.1";
$service = "VirtualCurrencySrv";
$serverName = "127.0.0.1";
$WH_connectionInfo = array("Database"=>"GameWarehouseDB", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq");
$Game_connectionInfo = array("Database"=>"BlGame01", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq");
$Goodid_container = 80405;
$amount = "";
$charname = "";

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
	$amount = $_GET["amount"];
	$charname = $_GET["charname"];
	if (!is_numeric($amount) || trim($charname) == "") {
		echo "Amount or Character name cannot be empty.";
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
			$OwnerAccountID = get_owneraccid($charname);
			if ($OwnerAccountID == "") {
				die(print("Character name not found."));
			}
			
			$cURLConnection = curl_init();
			curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/apps-state");
			curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($cURLConnection);
			curl_close($cURLConnection);
			$resultapp = cut_str("<AppName>{$service}</AppName>","</App>",$result);
			$resultapp = cut_str("<Epoch>","</Epoch>",$resultapp);
			$request_code = rand(1,10000);
			$postRequest = array(
				'protocol' => 'VirtualCurrency',
				'command' => 'Deposit',
				'from' => '',
				'to' => $OwnerAccountID,
				'message' => '<Request>
	  <CurrencyId>51</CurrencyId>
	  <Amount>'.$amount.'</Amount>
	  <EffectiveTo>2099-05-05T03:30:30+09:00</EffectiveTo>
	  <IsRefundable>0</IsRefundable>
	  <DepositReasonCode>5</DepositReasonCode>
	  <DepositReason>입금사유</DepositReason>
	  <RequestCode>'.$request_code.'</RequestCode>
	  <RequestId>efb8205d-0261-aa9f-8709-aff33e052091</RequestId>
	</Request>
	'
			);
			$cheaders = array(
				'Accept: */*',
				'Accept-Encoding: gzip, deflate',
				'Accept-Language: en-US,en;q=0.9',
				'Connection: keep-alive',
				'Content-Length: 0',
				'Host: 192.168.0.10:6605',
				'Origin: http://'.$ip.':6605',
				'Referer: http://'.$ip.':6605/spawned/VirtualCurrencySrv.1.'.$resultapp.'/test/',
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36',
			);
			
			$cURLConnection = curl_init();
			curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/spawned/{$service}.1.{$resultapp}/test/command_console?protocol=".rawurlencode($postRequest['protocol'])."&command=".rawurlencode($postRequest['command'])."&from=".rawurlencode($postRequest['from'])."&to=".rawurlencode($postRequest['to'])."&message=".rawurlencode($postRequest['message'])."");
			curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURLConnection, CURLOPT_POST, 1);
			
			$result = curl_exec($cURLConnection);
			curl_close($cURLConnection);			
		}
		echo "Topup Result<pre>".htmlentities($result)."</pre>";

	}
}

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
	Ncoin Amount: <input type="text" name="amount" value="<?php echo $amount; ?>"><br>
	Character name: <input type="text" name="charname" value="<?php echo $charname; ?>"><br>
	<input type="submit">
	<a href="../index.php">Home</a>
</form>

