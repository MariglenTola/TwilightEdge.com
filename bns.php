<?php
include 'SimpleDOM.php';
include 'vars.php';
include 'funcs.php';
include 'item.php';
include 'goods.php';
include 'category.php';
// include 'character.php';
// include 'system.php';

function exec_cmd(&$command) {
	global $vars;
	if(in_array($command['type'], $vars['valid_cmd'])) {
		list($cmd, $args) = array_pad(explode(' ', $command['cmd'], 2), 2, null);
		$cmd = ltrim($cmd, '.');
		$action = 'cmd_'.$command['type'].'_'.$cmd;
		if (function_exists($action)) {
			call_user_func_array($action, array(&$command, $args));
		}
		else {
			$command['code'] = 0;
			$command['msg'] = "invalid command action";
		}
	}
	else {
		$command['code'] = 0;
		$command['msg'] = "invalid command";
	}
}

if(!empty($_POST) && isset($_POST['command'])) {
	$commands = explode("\n", $_POST['command']);
	$cmds = array();
	$cmd_type = "";
	
	foreach($commands as $command) {
		$command = trim($command);
		if (mb_substr($command, 0, 2, "UTF-8") == "//") {
			continue;
		}
		elseif (mb_substr($command, 0, 1, "UTF-8") == ":") {
			$cmd_type = mb_substr($command, 1, null, "UTF-8");
		}
		elseif($cmd_type != "" && $command != ""){
			$cmd = ['type' => $cmd_type, 'cmd' => $command];
			exec_cmd($cmd);
			$cmds[] = $cmd;
		}
	}
	if ($_POST['command'] == "getcattree") {
		echo json_encode(get_cat_tree_data());
		exit;
	}
	
	//GoodsSrv.1.613564964/goods/cache_flush?
	echo json_encode($cmds);
	exit;
}
?>
<html>
<script src="js/jquery-3.5.1.min.js"></script>
<script src="js/jqxcore.js"></script>
<script src="js/jqxtree.js"></script>
<script>
function populateUL($ul, data) {
	var builddata = function () {
		var source = [];
		var items = [];
		// build hierarchical source.
		for (i = 0; i < data.length; i++) {
			var item = data[i];
			var label = item["CategoryName"]+":"+item["CategoryId"];
			var parentid = item["ParentCategoryId"];
			var id = item["CategoryId"];

			if (items[parentid]) {
				var item = { parentid: parentid, label: label, item: item };
				if (!items[parentid].items) {
					items[parentid].items = [];
				}
				items[parentid].items[items[parentid].items.length] = item;
				items[id] = item;
			}
			else {
				items[id] = { parentid: parentid, label: label, item: item };
				source[id] = items[id];
			}
		}
		return source;
	}
	var source = builddata();
	$ul.jqxTree({theme: 'summer', source: source, width: '300px' });
	$ul.jqxTree('expandAll');
}
$(document).ready(function() {
	$('#Button_Submit').click(function(){
		var cmd = $.trim($("textarea#txtcommand").val());
		$.ajax({ 
			url: '<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
			data: {"command": cmd},
			type: 'post',
			dataType: 'json',
			success: function (response) {
				var cmds_result = "";
				$.each(response, function(i, c) {
					cmds_result += i+1 + "." + c.type + ":" + c.cmd + ":" + c.msg + "\n";
				});
				$("textarea#txtresult").val(cmds_result);
				return true;
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
				return false;
			}
		});
	});	
	$('#Button_Tree').click(function(){
		$.ajax({ 
			url: '<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
			data: {"command": 'getcattree'},
			type: 'post',
			dataType: 'json',
			success: function (response) {
				populateUL($("#cattree"), response);
				return true;
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
				return false;
			}
		});
	});	
	$('#Button_Reset').click(function(){
		$("textarea#txtcommand").val(":item\n\n:category\n\n:goods\n\n:character\n\n:system\n\n");
	});
	$("#Button_Reset").click();
	$("#Button_Tree").click();
});
</script>
<link rel="stylesheet" href="css/jqx.base.css" type="text/css" />
<link rel="stylesheet" href="css/jqx.summer.css" type="text/css" />
<style> 
	textarea {
		white-space: pre;
		overflow-wrap: normal;
		overflow-x: scroll;
	}
	#txtcommand { 
		height: 300px; 
		width: 500px; 
		text-align:justify; 
	}
	#txtresult { 
		height: 300px; 
		width: 600px; 
		text-align:justify; 
	} 
	.column {
	  float: left;
	}
	.left, .right {
	  width: 500px;
	}
	.right {
	  width: 300px;
	}

	.middle {
	  width: 600px;
	}
	.row:after {
	  content: "";
	  display: table;
	  clear: both;
	}
</style>
<body>
<div class="row">
	<a href="../index.php">Home</a>
  <div class="column left">Command:<br>
	<textarea id="txtcommand" name="txtcommand"></textarea><br /><br />	  
	<input id="Button_Submit" value="Submit" type="button" name="Button_Submit">
	<input id="Button_Reset" value="Reset" type="button" name="Button_Reset">
	<pre>
:item command
 add ItemId|GameItemId|Name //add 90001|850559|泰圣护拳10段
 mod ItemId|GameItemId|Name //mod 90001|850556|天机项链10段
 del ItemId //del 90001
:category
 add CategoryId|CategoryName|DisplayOrder|ParentCategoryId //add 910|Category|2|51
 mod CategoryId|CategoryName|DisplayOrder|ParentCategoryId //mod 910|Weapons|3|52
 del CategoryId //del 900
:goods
 add GoodsId|GoodsName|Price|CategoryId|ItemId:Qty{max:10} //add 90001|Accessory Bundle 1|555|910|90001:1,90003:1
 mod GoodsId|GoodsName|Price|CategoryId|ItemId:Qty{max:10} //mod 90001|Accessory Bundle|555|910|90001:1,90002:1,90003:1
 del GoodsId //del 90001
:character
 senditem CharName|GameItemId:Qty{max:10} //senditem Anastasia|850559:1,850556:1,815295:50
 set CharName -l:level -hl:hoongmon level -fl:faction level //set Anastasia -l:55 -hl:50 -fl:15
:system
 adduser userid|password|GameItemId:Qty{max:10}  //add test002|123456|104:5,105:5,106:6,108:6
 chgpwd userid|password //chgpwd test002|1234567
 addncoin userid|amount //addncoin test002|5000
 kick userid //kick test002
 ban userid //ban test002
 unban userid //unban test002

	</pre>
  </div>
  <div class="column middle">Result:<br>
	<textarea id="txtresult" name="txtresult"></textarea></div>
  <div class="column right"><input id="Button_Tree" value="Refresh Shop Category" type="button" name="Button_Tree"><br />
		<div id='cattree'></div></div>
  </div>
</body>
</html>