<?php
$ip = "127.0.0.1";
$service = "AuthSrv2";
$password = "";
$accountid = "";
$out = "";

function cut_str($begin,$end,$str)
{
	$b = mb_strpos($str,$begin) + mb_strlen($begin);
	$e = mb_strpos($str,$end, $b) - $b;
	$rs = mb_substr($str,$b,$e);
	return $rs;
 }

if (!empty($_GET)) {
	$accountid = $_GET["accountid"];
	$password = $_GET["password"];
	if (trim($password) == "" || trim($accountid) == "") {
		echo "Account ID and password cannot be empty.";
	}
	else {
		$cURLConnection = curl_init();
		curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/apps-state");
		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($cURLConnection);
		curl_close($cURLConnection);
		$resultapp = cut_str("<AppName>{$service}</AppName>","</App>",$result);
		$resultapp = cut_str("<Epoch>","</Epoch>",$resultapp);
		$ranid = rand(1,99);
		
		$postRequest = array(
			'old_login_name' => $accountid."@ncsoft.com",
			'fake_login_name' => $accountid.$ranid."@ncsoft.com",
			'new_login_name' => $accountid."@ncsoft.com",
			'password' => $password,
			'not_login_name_validated' => 'on',
			'not_kick' => 'on'
		);
				
		$cURLConnection = curl_init();
		curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/spawned/{$service}.1.{$resultapp}/account/change_login_name?old_login_name=".rawurlencode($postRequest['old_login_name'])."&new_login_name=".rawurlencode($postRequest['fake_login_name'])."&password=".rawurlencode($postRequest['password'])."&not_login_name_validated=".rawurlencode($postRequest['not_login_name_validated'])."&not_kick=".rawurlencode($postRequest['not_kick'])."");
		curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURLConnection, CURLOPT_POST, 0);
		
		$result = curl_exec($cURLConnection);
		if (trim($result) == "<Reply/>") {
			curl_setopt($cURLConnection, CURLOPT_URL, "http://{$ip}:6605/spawned/{$service}.1.{$resultapp}/account/change_login_name?old_login_name=".rawurlencode($postRequest['fake_login_name'])."&new_login_name=".rawurlencode($postRequest['new_login_name'])."&password=".rawurlencode($postRequest['password'])."&not_login_name_validated=".rawurlencode($postRequest['not_login_name_validated'])."&not_kick=".rawurlencode($postRequest['not_kick'])."");
			curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURLConnection, CURLOPT_POST, 0);
			
			$result1 = curl_exec($cURLConnection);
			if (trim($result1) == "<Reply/>") {
				$out = "Password changed <br />";
			}
			else {
				$xml = simplexml_load_string($result1);
				if ($xml === false) {
					echo "Failed loading result1 XML: ";
					foreach(libxml_get_errors() as $error) {
						echo "<br>", $error->message;
					}
				}
				else {
					if ($xml['code'] == "3002") {
						$out = "Fake Account ID not found <br />";
					}
					else if ($xml['code'] == "3043") {
						$out = "Account ID already exist <br />";
					}
				}
			}
		}
		else {
			$xml = simplexml_load_string($result);
			if ($xml === false) {
				echo "Failed loading result XML: ";
				foreach(libxml_get_errors() as $error) {
					echo "<br>", $error->message;
				}
			}	
			else {
				if ($xml['code'] == "3002") {
					$out = "Account ID not found <br />";
				}
				else if ($xml['code'] == "3043") {
					$out = "Fake Account ID already exist <br />";
				}
			}
		}	
		
		curl_close($cURLConnection);
	}
	echo "Change Password Result<pre>".htmlentities($out)."</pre>";
}

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
	Account ID: <input type="text" name="accountid" value="<?php echo $accountid; ?>"><br>
	Password: <input type="text" name="password" value="<?php echo $password; ?>"><br>
	<input type="submit">
	<a href="../index.php">Home</a>
</form>