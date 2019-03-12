<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | Admin : index.php                                                         |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2019 MeYan                                                  |
// +---------------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// +---------------------------------------------------------------------------+

/**
 * Geeklog common function library
 */
require_once('../../../lib-common.php');

/**
 * Security check to ensure user even belongs on this page
 */
require_once('../../auth.inc.php');

if (!SEC_hasRights('myshop.admin')) {
    $display .= COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
    $display = COM_createHTMLDocument($display, array('pagetitle' => $MESSAGE[30]));

    // Log attempt to access.log
    COM_accessLog("User {$_USER['username']} tried to illegally access the event administration screen.");
    COM_output($display);
    
    exit;
}

// +---------------------------------------------------------------------------+
// | Get Mode                                                                  |
// +---------------------------------------------------------------------------+
if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else {
    if ( isset($_POST['mode']) ) {
        $mode = COM_applyFilter($_POST['mode']);
    } else {
        $mode = '';
    }
}

// +---------------------------------------------------------------------------+
// | MAIN                                                                      |
// +---------------------------------------------------------------------------+
switch ($mode) {

    case "config":
        MyShop_Config($_REQUEST);
        break;

    case "order":
        MyShop_Order($_REQUEST);
        break;

    case "order_save":
        MyShop_OrderSave($_REQUEST);
        MyShop_Order($_REQUEST);
        break;

    case "order_delete":
        MyShop_OrderDelete($_REQUEST);
        MyShop_Order($_REQUEST);
        break;

    case "config_save":
        MyShop_UpdateConfig($_REQUEST);
        MyShop_Config($_POST);
        break;

    case "product_save":
        MyShop_UpdateProduct($_REQUEST);
        MyShop_Main("");
        break;

    case "product_delete":
        MyShop_DeleteProduct($_REQUEST);
        MyShop_Main("");
        break;

    case "cancel":
    default:
        MyShop_Main("");
        break;
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_Config
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_Config($args) {
    global $_CONF, $_TABLES, $_MYSHOP_CONF;

    $sql = "SELECT username,password,signature,sandbox from {$_TABLES['myshop_config']}";
    $result = DB_query($sql);
    $num = DB_numrows($result);

    if($num > 0)
    {
        $A = DB_fetchArray($result);
        $username  = $A['username'];
        $password  = $A['password'];
        $signature = $A['signature'];
        if ($A['sandbox'] == "on")
        {
            $sandbox = " checked";
        }
    }

    $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

    $T->set_file(array (
        'topmenu' => 'topmenu.thtml',
        'config'  => 'config.thtml',
    ));

    $T->set_var(array(
        'lang_piname'  => $_MYSHOP_CONF['pi_display_name'],
        'icon_url'     => $_CONF['site_url'] . '/myshop/images/myshop.png',
        'title'       => MyShop_str('pi_name'),
        'caption'      => MyShop_str('top_menu_caption'),
        'menu1'       => MyShop_str('top_menu1'),
        'menu2'       => MyShop_str('top_menu2'),
        'menu3'       => MyShop_str('top_menu3'),
        'menu4'       => MyShop_str('top_menu4'),
        'username'     => $username,
        'password'     => $password,
        'signature'    => $signature,
        'sandbox'      => $sandbox,
    ));

    $T->parse('output', 'topmenu');
    $content .= $T->finish($T->get_var('output'));

    $T->parse('output', 'config');
    $content .= $T->finish($T->get_var('output'));
    
    $display = COM_createHTMLDocument($content);
    COM_output($display);
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_OrderDelete
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_OrderDelete($args) {
    global $_TABLES;

    $sql = "DELETE FROM {$_TABLES['myshop_order']} WHERE orderid = '{$args['orderid']}'";
    DB_query($sql);
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_OrderSave
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_OrderSave($args) {
    global $_TABLES;

    $sql = "UPDATE {$_TABLES['myshop_order']} SET "
    . "status = 'on' " 
    . "WHERE orderid = '{$args['orderid']}'";
    DB_query($sql);
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_Order
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_Order($args) {
    global $_CONF, $_TABLES, $_MYSHOP_CONF;

    $sql = "SELECT orderid,productid,email,quantity,status from {$_TABLES['myshop_order']}";
    $result = DB_query($sql);
    $num = DB_numrows($result);

    if($num > 0)
    {
        for ($i = 0; $i < $num; $i++)
        {
            $A = DB_fetchArray($result);
            
            $product_name = DB_getItem($_TABLES['myshop_product'], 'name', "productid={$A['productid']}");
            if($A['status'] == "on"){
                $ship = " checked";
            }
            
            $row .= "<form action=\"index.php\" METHOD=\"POST\"><tr>\n";
            $row .= "<input type=\"hidden\" name=\"orderid\" value=\"{$A['orderid']}\">\n";
            $row .= "<td>$product_name</td>";
            $row .= "<td>{$A['quantity']}</td>";
            $row .= "<td>{$A['email']}</td>";
            $row .= "<td><input type=\"checkbox\" name=\"status\"{$ship}></td>";
            $row .= "<td><button type=\"submit\" name=\"mode\" value=\"order_save\" class=\"uk-button uk-button-primary\">変更</button>";
            $row .= "<button type=\"submit\" name=\"mode\" value=\"order_delete\" class=\"uk-button\">削除</button></td>\n";
            $row .= "</tr></form>\n";
        }
    }
    else {
        $row = MyShop_str('no_order');
    }

    $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

    $T->set_file(array (
        'topmenu' => 'topmenu.thtml',
        'table'   => 'table.thtml',
    ));

    $T->set_var(array(
        'lang_piname' => $_MYSHOP_CONF['pi_display_name'],
        'icon_url'    => $_CONF['site_url'] . '/myshop/images/myshop.png',
        'title'       => MyShop_str('pi_name'),
        'caption'     => MyShop_str('top_menu_caption'),
        'menu1'       => MyShop_str('top_menu1'),
        'menu2'       => MyShop_str('top_menu2'),
        'menu3'       => MyShop_str('top_menu3'),
        'menu4'       => MyShop_str('top_menu4'),
        'notfound'    => $notfound,
        'h1'          => MyShop_str('product_name'),
        'h2'          => MyShop_str('tbl_head_amount'),
        'h3'          => MyShop_str('publish'),
        'h4'          => MyShop_str('finish'),
        'h5'          => "-",
        'list'        => $row,
    ));

    $T->parse('output', 'topmenu');
    $content .= $T->finish($T->get_var('output'));

    $T->parse('output', 'table');
    $content .= $T->finish($T->get_var('output'));
    
    $display = COM_createHTMLDocument($content);
    COM_output($display);
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_UpdateConfig
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_UpdateConfig ($args) {
    global $_CONF, $_TABLES;

    $sql = "SELECT username,password,signature,sandbox from {$_TABLES['myshop_config']}";
    $result = DB_query($sql);
    $num = DB_numrows($result);

    if($num > 0) {
        if ($args['username'] != "") {
            $A = DB_fetchArray($result);
        
            $sql = "UPDATE {$_TABLES['myshop_config']} SET "
            . "username  = '{$_POST['username']}',"
            . "password  = '{$_POST['password']}',"
            . "signature = '{$_POST['signature']}',"
            . "sandbox   = '{$_POST['sandbox']}' " 
            . "WHERE username = '{$A['username']}'";
            DB_query($sql);
        }
    }
    else {
        if ($_POST['username'] != "") {
            $sql = "INSERT INTO {$_TABLES['myshop_config']} "
            . "(username, password, signature, sandbox) "
            . "VALUES "
            . "('{$_POST['username']}','{$_POST['password']}','{$_POST['signature']}','{$_POST['sandbox']}')";
            DB_query($sql);
        }
    }
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_Main
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_Main($args) {
    global $_CONF, $_TABLES, $_MYSHOP_CONF;

    $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

    $T->set_file(array (
        'topmenu' => 'topmenu.thtml',
        'product' => 'product.thtml',
        'table'   => 'table.thtml',
    ));

    $row = MyShop_GetProduct("on");
    if ($row == "")
    {
        $row = MyShop_str('no_product');
    }

    $T->set_var(array(
        'lang_piname' => $_MYSHOP_CONF['pi_display_name'],
        'icon_url'    => $_CONF['site_url'] . '/myshop/images/myshop.png',
        'title'       => MyShop_str('pi_name'),
        'caption'     => MyShop_str('top_menu_caption'),
        'menu1'       => MyShop_str('top_menu1'),
        'menu2'       => MyShop_str('top_menu2'),
        'menu3'       => MyShop_str('top_menu3'),
        'menu4'       => MyShop_str('top_menu4'),
        'meta1'       => MyShop_str('product_name'),
        'meta2'       => MyShop_str('tbl_head_text'),
        'meta3'       => MyShop_str('tbl_head_price'),
        'meta4'       => MyShop_str('stock'),
        'meta5'       => MyShop_str('tbl_head_image'),
        'notfound'    => $notfound,
        'h1'          => MyShop_str('tbl_head_image'),
        'h2'          => MyShop_str('tbl_head_text'),
        'h3'          => MyShop_str('tbl_head_price'),
        'h4'          => MyShop_str('stock'),
        'h5'          => "-",
        'list'        => $row,
    ));

    $T->parse('output', 'topmenu');
    $content .= $T->finish($T->get_var('output'));

    $T->parse('output', 'product');
    $content .= $T->finish($T->get_var('output'));

    $T->parse('output', 'table');
    $content .= $T->finish($T->get_var('output'));
    
    $display = COM_createHTMLDocument($content);
    COM_output($display);
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_DeleteProduct
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_DeleteProduct($args) {
    global $_TABLES;

    $sql = "DELETE FROM {$_TABLES['myshop_product']} WHERE productid = '{$args['productid']}'";
    DB_query($sql);
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_UpdateProduct
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_UpdateProduct($args) {
    global $_CONF, $_TABLES;

    if(is_uploaded_file($_FILES["upfile"]["tmp_name"])) {

        $fp = fopen($_FILES['upfile']['tmp_name'], "rb");

        if($fp) {

            list($img_width, $img_height, $mime_type, $attr) = getimagesize($_FILES['upfile']['tmp_name']);

            switch($mime_type){
                case IMAGETYPE_JPEG:
                    $img_extension = "jpg";
                    break;

                case IMAGETYPE_PNG:
                    $img_extension = "png";
                    break;

                case IMAGETYPE_GIF:
                    $img_extension = "gif";
                    break;
            }

            if($img_extension != "") {
                $fdata = fread($fp, filesize($_FILES["upfile"]["tmp_name"]));
                fclose($fp);
                $fdata = MyShop_unpack($fdata);
                $upd_sql  = "imgext = '{$img_extension}',";
                $upd_sql .= "image = '{$fdata}',";
            }
        }
    }

    $data = $args['text'];
    $data = htmlspecialchars($data, ENT_QUOTES);
    $data = str_replace("?t", '　　', $data);
    $data = str_replace("?n", '<br>', $data);
    $data = str_replace('??','&yen;',$data);
    $data = str_replace('&yen;&yen;','&yen;',$data);
    $data = str_replace('&yen;&quot;','&quot;',$data);
    $data = str_replace('&yen;&#039;','&#039;',$data);
    $data = str_replace("&lt;?php", "<font color='blue'>&lt;?php", $data);
    $data = str_replace("?&gt;", "?&gt;</font>", $data);
    $data = str_replace("/*", "<font color='red'>/*", $data);
    $data = str_replace("*/", "*/</font>", $data);
    $data = str_replace("&lt;style", "<font color='green'>&lt;style", $data);
    $data = str_replace("&lt;/style&gt;", "&lt;/style&gt;</font>", $data);

    if ($args['productid'] != "") {
            $sql = "UPDATE {$_TABLES['myshop_product']} SET "
            . $upd_sql
            . "name  = '{$args['name']}',"
            . "text  = '{$data}',"
            . "price = '{$args['price']}',"
            . "stop  = '{$args['stop']}'" 
            . "WHERE productid = '{$args['productid']}'";
            DB_query($sql);
    }
    else {
        $productid = microtime(true);
        $sql = "INSERT INTO {$_TABLES['myshop_product']} "
        . "(productid, name, text, price, stop, imgext, image) "
        . "VALUES "
        . "('{$productid}','{$args['name']}','{$args['text']}','{$args['price']}','{$args['stop']}','{$img_extension}','{$fdata}')";
        DB_query($sql);
    }
}
?>