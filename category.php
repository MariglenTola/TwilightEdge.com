<?php
function dbget_category($id) {
	global $vars;
	$data = array();
	if(!$vars['Goodsdb_conn']){
		$vars['Goodsdb_conn'] = get_dbconn($vars['serverip'], $vars['Goodsdb_connectionInfo']);
	}
	$sql = "SELECT * FROM Categories WHERE CategoryId = {$id}";
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

function get_cat_tree_data() {
	global $vars;
	$data = array();
	if(!$vars['Goodsdb_conn']){
		$vars['Goodsdb_conn'] = get_dbconn($vars['serverip'], $vars['Goodsdb_connectionInfo']);
	}
	$sql = "SELECT CategoryId, CategoryName, ISNULL(ParentCategoryId,'') AS ParentCategoryId FROM Categories ORDER BY ParentCategoryId, CategoryId  ASC";
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

function cmd_category_add(&$command, $params) {
	global $vars;
	
	list($categoryid, $categoryname, $order, $parent_categoryid) = array_pad(explode('|', trim($params), 4), 4, null);
	if(is_null($categoryid) || is_null($categoryname) || is_null($order) || is_null($parent_categoryid)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter categoryid|categoryname|order|parent_categoryid";
		return;
	}
	if (count(dbget_category($categoryid)) > 0 ){
		$command['code'] = 0;
		$command['msg'] = "duplicate categoryid";
		return;
	}
	if (count(dbget_category($parent_categoryid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "parent_categoryid not found";
		return;
	}
	
	$xml = simpledom_load_file($vars['xml_template']['Category']);
	$xml->CategoryId = $categoryid;
	$xml->CategoryName = $categoryname;
	$xml->DisplayOrder = $order;
	$xml->ParentCategoryId = $parent_categoryid;
	$xml->CategoryDisplays->CategoryDisplay->CategoryDisplayName = $categoryname;
	$xml->CategoryDisplays->CategoryDisplay->CategoryDisplayDescription = $categoryname;
	$data = array(
		'XML' => $xml->outerXML()
	);
	//echo $xml->outerXML();
	$rtn = call_service("GoodsSrv", "goods/add_category", 1, $data);
	if(trim($rtn) == "<Reply/>") {
		$command['code'] = 1;
		$command['msg'] = "ok";
	}
	else {
		print_r($rtn);
		$command['code'] = 0;
		$command['msg'] = "service error:".$rtn;
	}
}	

function cmd_category_mod(&$command, $params) {
	global $vars;
	
	list($categoryid, $categoryname, $order, $parent_categoryid) = array_pad(explode('|', trim($params), 4), 4, null);
	if(is_null($categoryid) || is_null($categoryname) || is_null($order) || is_null($parent_categoryid)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter categoryid|categoryname|order|parent_categoryid";
		return;
	}
	if (count(dbget_category($categoryid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "categoryid not found";
		return;
	}
	if (count(dbget_category($parent_categoryid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "parent_categoryid not found";
		return;
	}
	
	$xml = simpledom_load_file($vars['xml_template']['Category']);
	$xml->CategoryId = $categoryid;
	$xml->CategoryName = $categoryname;
	$xml->DisplayOrder = $order;
	$xml->ParentCategoryId = $parent_categoryid;
	$xml->CategoryDisplays->CategoryDisplay->ActionType = 1;
	$xml->CategoryDisplays->CategoryDisplay->CategoryDisplayName = $categoryname;
	$xml->CategoryDisplays->CategoryDisplay->CategoryDisplayDescription = $categoryname;
	$data = array(
		'XML' => $xml->outerXML()
	);
	//echo $xml->outerXML();
	$rtn = call_service("GoodsSrv", "goods/change_category", 1, $data);
	if(trim($rtn) == "<Reply/>") {
		$command['code'] = 1;
		$command['msg'] = "ok";
	}
	else {
		print_r($rtn);
		$command['code'] = 0;
		$command['msg'] = "service error:".$rtn;
	}
}

function cmd_category_del(&$command, $params) {
	global $vars;	
	list($categoryid, $other) = array_pad(explode('|', trim($params), 2), 2, null);
	if(is_null($categoryid)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter categoryid";
		return;
	}
	if (count(dbget_category($categoryid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "categoryid not found";
		return;
	}
	$data = array(
		'CategoryId' => $categoryid,
		'ChangerAdminAccount' => "TestAdminAccount"
	);
	$rtn = call_service("GoodsSrv", "goods/remove_category", 0, $data);
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