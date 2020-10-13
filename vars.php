<?php
$vars = [
	'serverip' => '10.0.0.6',
	'Goodsdb_conn' => null,
	'Goodsdb_connectionInfo' => array("Database"=>"GoodsDb", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq", "CharacterSet" => "UTF-8"),
	'WHdb_conn' => null,
	'WHdb_connectionInfo' => array("Database"=>"GameWarehouseDB", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq", "CharacterSet" => "UTF-8"),
	'Gamedb_conn' => null,
	'Gamedb_connectionInfo' => array("Database"=>"BlGame01", "UID"=>"sa", "PWD"=>"FSmElsXuj3ls8Fq", "CharacterSet" => "UTF-8"),
	'Goodid_container' => 80405,
	'valid_cmd' => array("category","goods","item","character","system"),
	'top_category_id' => 48,
	'xml_template' => array(
		'Goods' => 'xml/Goods.xml',
		'Category' => 'xml/Category.xml',
		'Items' => 'xml/Items.xml'
	),
	'service_instanceid' => array(
		'GoodsSrv' => 0,
		'VirtualCurrencySrv' => 0,
		'AuthSrv2' => 0
	),
	'top_categoryid' => 48,
];

?>