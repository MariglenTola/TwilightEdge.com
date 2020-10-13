<?php
function dbget_item($id) {
	global $vars;
	$data = array();
	if(!$vars['Goodsdb_conn']){
		$vars['Goodsdb_conn'] = get_dbconn($vars['serverip'], $vars['Goodsdb_connectionInfo']);
	}
	$sql = "SELECT * FROM Items WHERE ItemId = {$id}";
	$stmt = sqlsrv_query($vars['Goodsdb_conn'], $sql);
	if($stmt === false){
		die(print_r(sqlsrv_errors(), true) );
	}
	while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){    
		 $data[] = $row;
	}
	sqlsrv_free_stmt($stmt);
	return $data;	
}

function cmd_item_add(&$command, $params) {
	global $vars;
	
	list($itemid, $gameitemid, $itemname) = array_pad(explode('|', trim($params), 3), 3, null);
	if(is_null($itemid) || is_null($gameitemid) || is_null($itemname)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter itemid|gameitemid|itemname";
		return;
	}
	if (count(dbget_item($itemid)) > 0 ){
		$command['code'] = 0;
		$command['msg'] = "duplicate itemid";
		return;
	}
	$xml = simpledom_load_file($vars['xml_template']['Items']);
	$xml->ItemId = $itemid;
	$xml->ItemName = $itemname;
	$xml->ItemDisplays->ItemDisplay->ItemDisplayName = $itemname;
	$xml->ItemDisplays->ItemDisplay->ItemDisplayDescription = $itemname;
	$xml->GameItem->GameItemKey = gen_gameitemkey($gameitemid);
	$data = array(
		'XML' => $xml->outerXML()
	);
	$rtn = call_service("GoodsSrv", "goods/add_item_game", 1, $data);
	if(trim($rtn) == "<Reply/>") {
		$command['code'] = 1;
		$command['msg'] = "ok";
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "service error:".$rtn;
	}
}

function cmd_item_del(&$command, $params) {
	global $vars;
	
	list($itemid, $other) = array_pad(explode('|', trim($params), 2), 2, null);
	if(is_null($itemid)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter itemid";
		return;
	}
	if (count(dbget_item($itemid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "itemid not found";
		return;
	}
	$data = array(
		'ItemId' => $itemid,
		'ItemType' => 3,
		'ChangerAdminAccount' => "TestAdminAccount"
	);
	$rtn = call_service("GoodsSrv", "goods/remove_item", 0, $data);
	if(trim($rtn) == "<Reply/>") {
		$command['code'] = 1;
		$command['msg'] = "ok";
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "service error:".$rtn;
	}
}

function cmd_item_mod(&$command, $params) {
	global $vars;
	
	list($itemid, $gameitemid, $itemname) = array_pad(explode('|', trim($params), 3), 3, null);
	if(is_null($itemid) || is_null($gameitemid) || is_null($itemname)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter itemid|gameitemid|itemname";
		return;
	}
	if (count(dbget_item($itemid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "itemid not found";
		return;
	}
	$xml = simpledom_load_file($vars['xml_template']['Items']);
	$xml->ItemId = $itemid;
	$xml->ItemName = $itemname;
	$xml->ItemDisplays->ItemDisplay->ActionType = 1;
	$xml->ItemDisplays->ItemDisplay->ItemDisplayName = $itemname;
	$xml->ItemDisplays->ItemDisplay->ItemDisplayDescription = $itemname;
	$xml->GameItem->GameItemKey = gen_gameitemkey($gameitemid);
	
	$data = array(
		'XML' => $xml->outerXML()
	);
	$rtn = call_service("GoodsSrv", "goods/change_item_game", 1, $data);
	if(trim($rtn) == "<Reply/>") {
		$command['code'] = 1;
		$command['msg'] = "ok";
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "service error:".$rtn;
	}
}

function cmd_item_set(&$command, $params) {
	global $vars;
	
	list($itemid, $itemname, $itemdesc) = array_pad(explode('|', trim($params), 3), 3, null);
	if(is_null($itemid) || is_null($itemname) || is_null($itemdesc)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter itemid|itemname|itemdesc";
		return;
	}
	if (count(dbget_item($itemid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "itemid not found";
		return;
	}
	$data = array(
		'ItemId' => $itemid,
		'LanguageCode' => 11,
		'ItemDisplayName' => $itemname,
		'ItemDisplayDescription' => $itemdesc,
		'ChangerAdminAccount' => "TestAdminAccount"
	);
	$rtn = call_service("GoodsSrv", "goods/change_item_display", 0, $data);
	if(trim($rtn) == "<Reply/>") {
		$command['code'] = 1;
		$command['msg'] = "ok";
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "service error:".$rtn;
	}
}
?>