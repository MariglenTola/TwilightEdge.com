<?php
function dbget_goods($id) {
	global $vars;
	$data = array();
	if(!$vars['Goodsdb_conn']){
		$vars['Goodsdb_conn'] = get_dbconn($vars['serverip'], $vars['Goodsdb_connectionInfo']);
	}
	$sql = "SELECT * FROM Goods WHERE GoodsId = {$id}";
	$stmt = sqlsrv_query($vars['Goodsdb_conn'], $sql);
	if($stmt === false){
		echo $sql;
		die(print_r(sqlsrv_errors(), true) );
	}
	while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){    
		 $data[] = $row;
	}
	sqlsrv_free_stmt($stmt);
	return $data;	
}

function dbget_itemid($items) {
	global $vars;
	$data = array();
	if(!$vars['Goodsdb_conn']){
		$vars['Goodsdb_conn'] = get_dbconn($vars['serverip'], $vars['Goodsdb_connectionInfo']);
	}
	
	$itemid = implode(",", $items);
	$sql = "SELECT ItemId FROM Items WHERE ItemId IN({$itemid})";
	$stmt = sqlsrv_query($vars['Goodsdb_conn'], $sql);
	if($stmt === false){
		echo $sql;
		die(print_r(sqlsrv_errors(), true) );
	}
	while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){    
		 $data[] = $row;
	}
	sqlsrv_free_stmt($stmt);
	return $data;	
}

function dbset_goods_salestatus($id, $status) {
	global $vars;
	$data = array();
	if(!$vars['Goodsdb_conn']){
		$vars['Goodsdb_conn'] = get_dbconn($vars['serverip'], $vars['Goodsdb_connectionInfo']);
	}
	$sql = "UPDATE Goods SET SaleStatus = {$status} WHERE GoodsId = {$id}";
	$stmt = sqlsrv_query($vars['Goodsdb_conn'], $sql);
	if($stmt === false){
		echo $sql;
		die(print_r(sqlsrv_errors(), true) );
	}
	sqlsrv_free_stmt($stmt);
	return $stmt;	
}

function cmd_goods_add(&$command, $params) {
	global $vars;
	
	list($goodsid, $goodsname, $price, $categoryid, $items) = array_pad(explode('|', trim($params), 5), 5, null);
	if(is_null($goodsid) || is_null($goodsname) || is_null($price) || is_null($categoryid) || is_null($items)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter goodsid|goodsname|price|categoryid|items";
		return;
	}
	if (count(dbget_goods($goodsid)) > 0 ){
		$command['code'] = 0;
		$command['msg'] = "duplicate goodsid";
		return;
	}
	$category = dbget_category($categoryid);
	if (count($category) > 0 ){
		$parent_categoryid = $category[0]['ParentCategoryId'];
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "categoryid not found";
		return;
	}
	$itemparams = array_pad(explode(',', trim($items), 10), 10, null);
	$item = array();
	$itemids = array();
	foreach ($itemparams as $value) {
		if (!$value) {
			continue;
		}
		list($itemid, $qty) = array_pad(explode(':', trim($value), 2), 2, null);
		if(is_null($itemid) || is_null($qty)) {
			$command['code'] = 0;
			$command['msg'] = "invalid items parameters";
			return;
		}
		if(!is_numeric($qty)) {
			$command['code'] = 0;
			$command['msg'] = "invalid item qty";
			return;
		}
		$item[$itemid] = $qty;
		$itemids[] = $itemid;
	}
	$itemcount = count($item);
	if (count(dbget_itemid($itemids)) != $itemcount) {
		$command['code'] = 0;
		$command['msg'] = "items not found";
		return;
	}
	$xml = simpledom_load_file($vars['xml_template']['Goods']);
	$xml->GoodsId = $goodsid;
	$xml->GoodsName = $goodsname;
	$xml->GoodsDescription = $goodsname;
	if ($itemcount == 1) {
		$xml->GoodsData = "AAAAAAE=";
	}
	$xml->GoodsCategories->GoodsCategory->CategoryId = $categoryid;
	$xml->GoodsCategories->GoodsCategory->DisplayOrder = 1;
	if ($parent_categoryid != $vars['top_categoryid']) {
		$GoodsCategories = $xml->GoodsCategories;
		$newGoodsCategory = $GoodsCategories->appendChild($GoodsCategories->GoodsCategory->cloneNode(true));
		$newGoodsCategory->CategoryId = $parent_categoryid;
	}
	$xml->GoodsDisplays->GoodsDisplay->GoodsDisplayName = $goodsname;
	$xml->GoodsDisplays->GoodsDisplay->GoodsDisplayDescription = $goodsname;
	$xml->GoodsBasicPrices->GoodsBasicPrice->BasicSalePrice = $price;
	$xml->GoodsSalePricePolicies->GoodsSalePricePolicy->SalePrice = $price;
	$GoodsItems = $xml->GoodsItems;
	$itemcount = 0;
	foreach ($item as $id => $qty) {
		if($itemcount > 0) {
			$GoodsItem = $GoodsItems->appendChild($GoodsItems->GoodsItem->cloneNode(true));
		}
		else {
			$GoodsItem = $GoodsItems->GoodsItem;
		}
		$GoodsItem->ItemId = $id;
		$GoodsItem->ItemQuantity = $qty;	
		$GoodsItem->GoodsItemBasicPrices->GoodsItemBasicPrice->BasicSalePrice = $price;	
		$itemcount++;
	}
	$data = array(
		'XML' => $xml->outerXML()
	);
	//echo $xml->outerXML();
	$rtn = call_service("GoodsSrv", "goods/add_goods", 1, $data);
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

function cmd_goods_mod(&$command, $params) {
	global $vars;
	
	list($goodsid, $goodsname, $price, $categoryid, $items) = array_pad(explode('|', trim($params), 5), 5, null);
	if(is_null($goodsid) || is_null($goodsname) || is_null($price) || is_null($categoryid) || is_null($items)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter goodsid|goodsname|price|categoryid|items";
		return;
	}
	if (count(dbget_goods($goodsid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "goodsid not found";
		return;
	}
	$category = dbget_category($categoryid);
	if (count($category) > 0 ){
		$parent_categoryid = $category[0]['ParentCategoryId'];
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "categoryid not found";
		return;
	}
	$itemparams = array_pad(explode(',', trim($items), 10), 10, null);
	$item = array();
	$itemids = array();
	foreach ($itemparams as $value) {
		if (!$value) {
			continue;
		}
		list($itemid, $qty) = array_pad(explode(':', trim($value), 2), 2, null);
		if(is_null($itemid) || is_null($qty)) {
			$command['code'] = 0;
			$command['msg'] = "invalid items parameters";
			return;
		}
		if(!is_numeric($qty)) {
			$command['code'] = 0;
			$command['msg'] = "invalid item qty";
			return;
		}
		$item[$itemid] = $qty;
		$itemids[] = $itemid;
	}
	$itemcount = count($item);
	if (count(dbget_itemid($itemids)) != $itemcount) {
		$command['code'] = 0;
		$command['msg'] = "items not found";
		return;
	}
	cmd_goods_del($command, $params);
	if ($command['code'] == 0) {
		return;
	}
	cmd_goods_add($command, $params);
}

function cmd_goods_del(&$command, $params) {
	global $vars;	
	list($goodsid, $other) = array_pad(explode('|', trim($params), 2), 2, null);
	if(is_null($goodsid)) {
		$command['code'] = 0;
		$command['msg'] = "invalid parameter goodsid";
		return;
	}
	if (count(dbget_goods($goodsid)) == 0 ){
		$command['code'] = 0;
		$command['msg'] = "goodsid not found";
		return;
	}
	dbset_goods_salestatus($goodsid, 1);
	$data = array(
		'GoodsId' => $goodsid,
		'ChangerAdminAccount' => "TestAdminAccount"
	);
	$rtn = call_service("GoodsSrv", "goods/remove_goods", 0, $data);
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