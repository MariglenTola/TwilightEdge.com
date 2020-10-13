<?php
$itemid = "";
$itemencode = "";
$encodechar = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'),array('+'), array('/')); 
if (!empty($_GET)) {
    $itemid = $_GET["itemid"];
    $encode_str = array();
    if (is_numeric($itemid)) {
        $itemno = $itemid * 16;
        while ($itemno >= 64) {
            $encode_str[] = $encodechar[fmod($itemno,64)];
            $itemno = floor($itemno / 64);
        }
        $encode_str[] = $encodechar[$itemno];
        $itemencode = "AA".implode(array_reverse($encode_str))."==";
    }
}
?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
    Item ID: <input type="text" name="itemid" value="<?php echo $itemid; ?>"><br>
    GameItemKey: <input type="text" name="itemencode" value="<?php echo $itemencode; ?>"><br>
    <input type="submit">
	<a href="../index.php">Home</a>
</form>