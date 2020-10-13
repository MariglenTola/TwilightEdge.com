<?php
$serverName = "127.0.0.1";
$WH_connectionInfo = array("Database"=>"GameWarehouseDB", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq");
$Game_connectionInfo = array("Database"=>"BlGame01", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq");
$Goodid_container = 80405;
$itemid = "";
$charname = "";

function wh_flush() {
	//flush not working
	$result = CURL("127.0.0.1:6605/apps-state",null,'get');
	$resultapp = $this->cut_str("<AppName>WarehouseSrv</AppName>","</App>",$result);
	$resultapp = $this->cut_str("<Epoch>","</Epoch>",$resultapp);
	$result = CURL("http://127.0.0.1:6605/spawned/WarehouseSrv.1.'.$resultapp.'/warehouse/flush","",'get');
}

function update_whstate($conn, $newlableid) {
	$sql = "UPDATE WarehouseGoods SET RegistrationState = 2 WHERE LabelID = {$newlableid}";
	$stmt = sqlsrv_query($conn, $sql);
	if($stmt === false) {
		die(print_r(sqlsrv_errors(), true) );
	}
	sqlsrv_free_stmt($stmt);
	
	$sql = "UPDATE WarehouseItem SET ItemState = 1 WHERE LabelID = {$newlableid}";
	$stmt = sqlsrv_query($conn, $sql);
	if($stmt === false) {
		die(print_r(sqlsrv_errors(), true) );
	}
	sqlsrv_free_stmt($stmt);
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

function get_next_goodsid($conn) {
	$sql = "SELECT max(GoodsID) FROM WarehouseGoods";
	$stmt = sqlsrv_query($conn, $sql);
	if($stmt === false) {
		die(print_r(sqlsrv_errors(), true) );
	}

	if( sqlsrv_fetch($stmt ) === false) {
		die(print_r( sqlsrv_errors(), true));
	}
	$goodsid = sqlsrv_get_field($stmt, 0);
	sqlsrv_free_stmt($stmt);
	return ++$goodsid;
}

if (!empty($_GET)) {
$itemid = $_GET["itemid"];
	$charname = $_GET["charname"];
	if (!is_numeric($itemid) || trim($charname) == "") {
		echo "Item ID or Character name cannot be empty.";
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
			//echo "Connecttion successful.\n";
			$datetime_variable = new DateTime();
			$datetime_formatted = date_format($datetime_variable, 'Y-m-d H:i:s');
			
			$NewLabelID = 0;
			$OwnerAccountID = get_owneraccid($charname);
			if ($OwnerAccountID == "") {
				die(print("Character name not found."));
			}
			$GoodsID = get_next_goodsid($conn);
			$GoodsNumber = $Goodid_container;
			$SenderDescription = null;
			$SenderMessage = null;
			$PurchaseTime = $datetime_formatted;

			// Item 1
			$GoodsItemNumber_1 = $itemid;
			$ItemDataID_1 = $itemid;
			$ItemAmount_1 = 1;
			$UsableDuration_1 = null;

			// Item 2-10
			$GoodsItemNumber_2 = null;
			$ItemDataID_2 = null;
			$ItemAmount_2 = null;
			$UsableDuration_2 = null;
			
			$tsql_callSP = "{call usp_TryWarehouseRegistration(?,?,?,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,? ,?,?,?,?)}";
			
			$params = array(
				array(&$NewLabelID, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
				array($OwnerAccountID, SQLSRV_PARAM_IN),
				array($GoodsID, SQLSRV_PARAM_IN),
				array($GoodsNumber, SQLSRV_PARAM_IN),
				array($SenderDescription, SQLSRV_PARAM_IN),
				array($SenderMessage, SQLSRV_PARAM_IN),
				array($PurchaseTime, SQLSRV_PARAM_IN),
				//Item 1
				array($GoodsItemNumber_1, SQLSRV_PARAM_IN),
				array($ItemDataID_1, SQLSRV_PARAM_IN),
				array($ItemAmount_1, SQLSRV_PARAM_IN),
				array($UsableDuration_1, SQLSRV_PARAM_IN),
				//Item 2-10
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN),
				
				array($GoodsItemNumber_2, SQLSRV_PARAM_IN),
				array($ItemDataID_2, SQLSRV_PARAM_IN),
				array($ItemAmount_2, SQLSRV_PARAM_IN),
				array($UsableDuration_2, SQLSRV_PARAM_IN)
			);
			
			/* Execute the query. */
			$stmt = sqlsrv_query($conn, $tsql_callSP, $params);
			if( $stmt === false )
			{
				echo "Error in executing statement 3.\n";
				print('<pre>');
				die(print_r(sqlsrv_errors(), true));
				print('</pre>');
			}

			/*Free the statement and connection resources. */
			sqlsrv_free_stmt($stmt);
			
			update_whstate($conn, $NewLabelID);
			
		}
		sqlsrv_close($conn);
		echo "LabelID={$NewLabelID}<br />";
		echo "Item added. Please re-log.";

	}
}

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
	ItemID: <input type="text" name="itemid" value="<?php echo $itemid; ?>"><br>
	Character name: <input type="text" name="charname" value="<?php echo $charname; ?>"><br>
	<input type="submit">
	<a href="../index.php">Home</a>
</form>

