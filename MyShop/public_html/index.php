<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | Public : index.php                                                        |
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
require_once '../lib-common.php';

if (!in_array('myshop', $_PLUGINS)) {
    COM_handle404();
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
if (session_id() == "") 
    session_start();

switch ($mode) {

  case 'check':
      MyShop_SetExpressCheckout($_REQUEST);
      break;

  case 'review':
      MyShop_GetExpressCheckoutDetails();
      break;

  case 'confirm':
      MyShop_DoExpressCheckoutPayment();
      break;

  default:
      MyShop_NoArgs();
      break;
}

/*--------------------------------------------------------------------------------------------------------------
* MyShop_NoArgs
--------------------------------------------------------------------------------------------------------------*/
function MyShop_NoArgs() {
    global $_CONF;

    $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

    $T->set_file(array (
        'blockheader' => 'blockheader.thtml',
        'table'       => 'table.thtml',
    ));

    $row = MyShop_GetProduct("");
    if ($row == "")
    {
        $row = MyShop_str('no_product');
    }

    $T->set_var(array(
        'header_title' => MyShop_str('header_title'),
        'header_text'  => MyShop_str('header_text'),
        'notfound'     => $notfound,
        'h1'           => MyShop_str('tbl_head_image'),
        'h2'           => MyShop_str('tbl_head_text'),
        'h3'           => MyShop_str('tbl_head_price'),
        'h4'           => MyShop_str('tbl_head_amount'),
        'h5'           => "-",
        'list'         => $row,
    ));

    $T->parse('output', 'blockheader');
    $content .= $T->finish($T->get_var('output'));

    $T->parse('output', 'table');
    $content .= $T->finish($T->get_var('output'));
    
    $display = COM_createHTMLDocument($content);
    COM_output($display);
}

/*--------------------------------------------------------------------------------------------------------------
* MyShop_SetExpressCheckout
--------------------------------------------------------------------------------------------------------------*/
function MyShop_SetExpressCheckout($args) {
    global $_CONF;

    $methodName       = "SetExpressCheckout";
    $version          = "64";
    $currencyCodeType = "JPY";
    $paymentType      = "Sale";
    $paymentAmount    = $args["qty"] * $args["price"];
    $returnURL        = $_CONF['site_url'] . '/myshop/index.php?mode=review';
    $cancelURL        = $_CONF['site_url'] . '/myshop/index.php';

    $paypal = MyShop_paypal();

    //NVPRequest for submitting to server
    $nvpreq = "";
    $nvpreq .= "METHOD=".$methodName;
    $nvpreq .= "&VERSION=".$version;
    $nvpreq .= "&USER=".$paypal['API_UserName'];
    $nvpreq .= "&PWD=".$paypal['API_Password'];
    $nvpreq .= "&SIGNATURE=".$paypal['API_Signature'];
    $nvpreq .= "&PAYMENTREQUEST_0_AMT=".$paymentAmount;
    $nvpreq .= "&PAYMENTREQUEST_0_CURRENCYCODE=".$currencyCodeType;
    $nvpreq .= "&PAYMENTREQUEST_0_PAYMENTACTION=".$paymentType;
    $nvpreq .= "&L_PAYMENTREQUEST_0_NUMBER0=".$args['productid'];
    $nvpreq .= "&L_PAYMENTREQUEST_0_NAME0=".$args['name'];
    $nvpreq .= "&L_PAYMENTREQUEST_0_QTY0=".$args['qty'];
    $nvpreq .= "&L_PAYMENTREQUEST_0_AMT0=".$args['price'];
    $nvpreq .= "&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital";
    $nvpreq .= "&REQCONFIRMSHIPPING=0";
    $nvpreq .= "&NOSHIPPING=1";
    $nvpreq .= "&RETURNURL=".$returnURL;
    $nvpreq .= "&CANCELURL=".$cancelURL;

    $resArray = MyShop_paypal_hash($paypal['API_Endpoint'], $nvpreq);

    $ack = strtoupper($resArray["ACK"]);
    if($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING") {
        $_SESSION['token'] = $resArray["TOKEN"];
        $_SESSION['PAYPAL_URL'] = $resArray["TOKEN"];
        header("Location: ".$paypal['PAYPAL_URL'].$resArray["TOKEN"]);
        exit; 
    }
    else {
        $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

        $T->set_file(array (
            'blockheader' => 'blockheader.thtml',
            'error'       => 'error.thtml',
        ));

        $T->set_var(array(
            'header_title' => MyShop_str('header_title'),
            'header_text'  => MyShop_str('header_text'),
            'msg_title'    => $resArray["L_SHORTMESSAGE0"],
            'msg_text'     => $resArray["L_LONGMESSAGE0"],
        ));

        $T->parse('output', 'blockheader');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'error');
        $content .= $T->finish($T->get_var('output'));

        $display = COM_createHTMLDocument($content);
        COM_output($display);
    }
}

/*--------------------------------------------------------------------------------------------------------------
* MyShop_GetExpressCheckoutDetails
--------------------------------------------------------------------------------------------------------------*/
function MyShop_GetExpressCheckoutDetails() {
    global $_CONF;

    $methodName       = "GetExpressCheckoutDetails";
    $version          = "64";

    $paypal = MyShop_paypal();

    $nvpreq = "";
    $nvpreq .= "METHOD=".$methodName;
    $nvpreq .= "&VERSION=".$version;
    $nvpreq .= "&USER=".$paypal['API_UserName'];
    $nvpreq .= "&PWD=".$paypal['API_Password'];
    $nvpreq .= "&SIGNATURE=".$paypal['API_Signature'];
    $nvpreq .= "&TOKEN=".$_SESSION['token'];

    $resArray = MyShop_paypal_hash($paypal['API_Endpoint'], $nvpreq);

    $ack = strtoupper($resArray["ACK"]);
    if($ack == "SUCCESS" || $ack=="SUCCESSWITHWARNING") {
        $_SESSION['payer_id'] = $resArray["PAYERID"];
        $_SESSION['Payment_Amount'] = $resArray["AMT"];
        $_SESSION['currencyCodeType'] = $resArray["CURRENCYCODE"];
        $_SESSION['product_id'] = $resArray["L_PAYMENTREQUEST_0_NUMBER0"];
        $_SESSION['product_qty'] = $resArray["L_PAYMENTREQUEST_0_QTY0"];
        $_SESSION['publish'] = $resArray["EMAIL"];

        $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

        $T->set_file(array (
            'blockheader' => 'blockheader.thtml',
            'orderdetail' => 'orderdetail.thtml',
            'table'       => 'table.thtml',
        ));

        $row = MyShop_GetProduct("");
        if ($row == "")
        {
            $row = MyShop_str('no_product');
        }

        $T->set_var(array(
            'header_title'       => MyShop_str('header_title'),
            'header_text'        => MyShop_str('header_text'),
            'msg_title'          => MyShop_str('order_title'),
            'msg_text'           => MyShop_str('order_msg'),
            'item1'              => MyShop_str('tbl_head_image'),
            'item2'              => MyShop_str('publish'),
            'item3'              => MyShop_str('tbl_head_amount'),
            'email'              => $resArray["EMAIL"],
            'productname'        => $resArray["L_PAYMENTREQUEST_0_NAME0"],
            'quantity'           => $resArray["L_PAYMENTREQUEST_0_QTY0"],
            'total_amount_title' => MyShop_str('total_amount_title'),
            'total_amount_value' => number_format($resArray["PAYMENTREQUEST_0_AMT"]),
            'list'               => $row,
        ));

        $T->parse('output', 'blockheader');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'orderdetail');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'table');
        $content .= $T->finish($T->get_var('output'));

        $display = COM_createHTMLDocument($content);
        COM_output($display);
    }
    else {
        $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

        $T->set_file(array (
            'blockheader' => 'blockheader.thtml',
            'error'       => 'error.thtml',
        ));

        $T->set_var(array(
            'header_title' => MyShop_str('header_title'),
            'header_text'  => MyShop_str('header_text'),
            'msg_title'    => $resArray["L_SHORTMESSAGE0"],
            'msg_text'     => $resArray["L_LONGMESSAGE0"],
        ));

        $T->parse('output', 'blockheader');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'error');
        $content .= $T->finish($T->get_var('output'));

        $display = COM_createHTMLDocument($content);
        COM_output($display);
    }
}

/*--------------------------------------------------------------------------------------------------------------
* MyShop_DoExpressCheckoutPayment
--------------------------------------------------------------------------------------------------------------*/
function MyShop_DoExpressCheckoutPayment() {
    global $_CONF, $_TABLES;;

    $methodName       = "DoExpressCheckoutPayment";
    $version          = "64";
    $paymentType      = "Sale";

    $paypal = MyShop_paypal();

    $nvpreq = "";
    $nvpreq .= "METHOD=".$methodName;
    $nvpreq .= "&VERSION=".$version;
    $nvpreq .= "&USER=".$paypal['API_UserName'];
    $nvpreq .= "&PWD=".$paypal['API_Password'];
    $nvpreq .= "&SIGNATURE=".$paypal['API_Signature'];
    $nvpreq .= "&TOKEN=".$_SESSION['token'];
    $nvpreq .= "&PAYERID=".$_SESSION['payer_id'];
    $nvpreq .= "&PAYMENTREQUEST_0_AMT=".$_SESSION['Payment_Amount'];
    $nvpreq .= "&PAYMENTREQUEST_0_CURRENCYCODE=".$_SESSION['currencyCodeType'];
    $nvpreq .= "&PAYMENTREQUEST_0_PAYMENTACTION=".$paymentType;

    $resArray = MyShop_paypal_hash($paypal['API_Endpoint'], $nvpreq);

    $ack = strtoupper($resArray["ACK"]);
    if($ack == "SUCCESS" || $ack=="SUCCESSWITHWARNING") {

        $sql = "INSERT INTO {$_TABLES['myshop_order']} "
        . "(orderid, productid, quantity, email) "
        . "VALUES "
        . "('{$resArray['PAYMENTINFO_0_TRANSACTIONID']}','{$_SESSION['product_id']}','{$_SESSION['product_qty']}','{$_SESSION['publish']}')";
        DB_query($sql);
        
        $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

        $T->set_file(array (
            'blockheader' => 'blockheader.thtml',
            'message'     => 'message.thtml',
            'table'       => 'table.thtml',
        ));

        $row = MyShop_GetProduct("");
        if ($row == "")
        {
            $row = MyShop_str('no_product');
        }

        $T->set_var(array(
            'header_title' => MyShop_str('product_name'),
            'header_text'  => MyShop_str('header_text'),
            'msg_title'    => MyShop_str('comfirm_title'),
            'msg_text'     => MyShop_str('thank_you_pay'),
            'list'         => $row,
        ));

        $T->parse('output', 'blockheader');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'message');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'table');
        $content .= $T->finish($T->get_var('output'));

        $display = COM_createHTMLDocument($content);
        COM_output($display);
    }
    else {
        $T = new Template($_CONF['path'] . 'plugins/myshop/templates');

        $T->set_file(array (
            'blockheader' => 'blockheader.thtml',
            'error'       => 'error.thtml',
        ));

        $T->set_var(array(
            'header_title' => MyShop_str('header_title'),
            'header_text'  => MyShop_str('header_text'),
            'msg_title'    => $resArray["L_SHORTMESSAGE0"],
            'msg_text'     => $resArray["L_LONGMESSAGE0"],
        ));

        $T->parse('output', 'blockheader');
        $content .= $T->finish($T->get_var('output'));

        $T->parse('output', 'error');
        $content .= $T->finish($T->get_var('output'));

        $display = COM_createHTMLDocument($content);
        COM_output($display);
    }
}
?>