<?php
require_once ('../lib-common.php');

$fdata = DB_getItem($_TABLES['myshop_product'], 'image', "productid={$_GET['productid']}");
$imgext = DB_getItem($_TABLES['myshop_product'], 'imgext', "productid={$_GET['productid']}");

$arr = explode(",", $fdata);
foreach($arr as $val) {
  $tmp = pack("c*", $val);
  $str .= $tmp;
}
$fdata = $str;

header("Content-type: image/{$imgext}");
echo $fdata;
?>
