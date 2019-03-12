<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | geeklog/plugins/myshop/functions.inc                                      |
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
if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die ('This file can not be used on its own.');
}
require_once dirname(__FILE__) . '/config.php';

// Language file Include
$langfile = $_CONF['path'] . 'plugins/myshop/language/' . $_CONF['language'] . '.php';
if (file_exists($langfile)) {
    include_once $langfile;
}

// +---------------------------------------------------------------------------+
// | Geeklog Plugin API Implementation                                         |
// +---------------------------------------------------------------------------+
/**
* Return the version for this plugin
*
* @return string VersionNo
*/
function plugin_chkVersion_myshop() {
    global $_MYSHOP_CONF;
    return $_MYSHOP_CONF['pi_version'];
}

/**
* Upgrade the plugin
*
* @return boolean true (= success)
*/
function plugin_upgrade_myshop() {
    global $_TABLES, $_MYSHOP_CONF;

    $sql = "UPDATE {$_TABLES['plugins']} SET "
    . "pi_version = '{$_MYSHOP_CONF['pi_version']}',"
    . "pi_gl_version = '{$_MYSHOP_CONF['pi_gl_version']}' "
    . "WHERE pi_name = '{$_MYSHOP_CONF['pi_name']}'";
    DB_query($sql);

    return True;
}

/**
* Automatic uninstall function for plugins
*
* @return array
*/
function plugin_autouninstall_myshop() {
    global $_MYSHOP_CONF;

    return array(
        'groups'    => array('MyShop Admin'),
        'features'  => array('myshop.admin'),
        'tables'    => array('myshop_config', 'myshop_product', 'myshop_order')
    );
}

/**
* This will put an option for the plugin in the command and control block on
* moderation.php
*
* @return   array   Array containing (plugin name, admin url, url of plugin icon)
*/
function plugin_cclabel_myshop() {
    global $_CONF, $_MYSHOP_CONF;
    
    $pi_display_name = $_MYSHOP_CONF['pi_display_name'];
    $admin_url       = $_CONF['site_admin_url'] . '/plugins/myshop/index.php';
    $icon_url        = $_CONF['site_url'] . '/myshop/images/myshop.png';

    return array($pi_display_name, $admin_url, $icon_url);
}

/**
* Returns the administrative option for this plugin
*
* @return   array   Array containing (plugin name, plugin admin url, # of items
*                   in plugin or '')
*/
function plugin_getadminoption_myshop() {
    global $_CONF, $_MYSHOP_CONF;

    if (SEC_hasRights('myshop.admin')){
        $pi_display_name = $_MYSHOP_CONF['pi_display_name'];
        $admin_url       = $_CONF['site_admin_url'] . '/plugins/myshop/index.php';
        $num_items       = "";

        return array($pi_display_name, $admin_url, $num_items);
    }
}

/**
 * Returns the items for this plugin that should appear on the main menu
 * NOTE: this MUST return the url/value pairs in the following format
 * $<arrayname>[<label>] = <url>
 */
function plugin_getmenuitems_myshop() {
    global $_CONF;
    $menuitems[MyShop_str('menu_label')] = $_CONF['site_url'] . '/myshop/index.php';
    return $menuitems;
}

function plugin_getheadercode_myshop() {
   global $_CONF, $_SCRIPTS;
   $_SCRIPTS->setCSSFile('myshop', '/myshop/style.css', false);
}

// -------------------------------------------------------------------------------------------------------------- //
// MyShop Functions                                                                                               //
// -------------------------------------------------------------------------------------------------------------- //

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_str
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_str($str) {
    global $LANG_MYSHOP;
    return isset($LANG_MYSHOP[$str]) ? $LANG_MYSHOP[$str] : '(undefined)';
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
* MyShop_unpack
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_unpack($data) {
  $tmp = unpack("c*", $data);
  foreach($tmp as $val) {
    if($ret == "") { 
      $ret = $val;
    }
    else {
      $ret .= ",".$val;
    }
  }
  return $ret;
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
* MyShop_paypal
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_paypal() {
    global $_TABLES;
    $sql = "SELECT username, password, signature, sandbox FROM {$_TABLES['myshop_config']}";
    $result = DB_query($sql);

    $num = DB_numrows($result);
    if($num > 0)
    {
        $A = DB_fetchArray($result);
        
        if($A['sandbox'] == "on") {
            $PAYPAL_URL   = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
            $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
        }
        else {
            $PAYPAL_URL   = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
            $API_Endpoint = "https://api-3t.paypal.com/nvp";
        }

        $resArray = array (
            'API_UserName'  => $A['username'],
            'API_Password'  => $A['password'],
            'API_Signature' => $A['signature'],
            'API_Endpoint'  => $API_Endpoint,
            'PAYPAL_URL'    => $PAYPAL_URL,
        );
    }

    return $resArray;
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
* MyShop_paypal_hash
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_paypal_hash($API_Endpoint, $nvpreq) {

    //setting the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);

    //turning off the server and peer verification(TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    //setting the nvpreq as POST FIELD to curl
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

    //getting response from server
    $response = curl_exec($ch);

    //convrting NVPResponse to an Associative Array
    $resArray = MyShop_paypal_deformatNVP($response);

    if (curl_errno($ch))  {
        $_SESSION['curl_error_no']  = curl_errno($ch) ;
        $_SESSION['curl_error_msg'] = curl_error($ch);
    }
    else {
        curl_close($ch);
    }

    return $resArray;
}
/*'-------------------------------------------------------------------------------------------------------------------------------------------
* MyShop_paypal_deformatNVP
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_paypal_deformatNVP($nvpstr) {
    $intial=0;
    $nvpArray = array();

    while(strlen($nvpstr)) {

        $keypos= strpos($nvpstr,'=');
        $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

        /*getting the Key and Value values and storing in a Associative Array*/
        $keyval=substr($nvpstr, $intial, $keypos);
        $valval=substr($nvpstr, $keypos + 1, $valuepos-$keypos-1);

        //decoding the respose
        $nvpArray[urldecode($keyval)] = urldecode($valval);
        $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
    }
    return $nvpArray;
}

/*'-------------------------------------------------------------------------------------------------------------------------------------------
' MyShop_GetProduct
'-------------------------------------------------------------------------------------------------------------------------------------------*/
function MyShop_GetProduct($edit) {
    global $_TABLES, $_CONF;

    $sql = "select productid,name,text,price,stop FROM {$_TABLES['myshop_product']}";
    $result = DB_query($sql);

    $num = DB_numrows($result);
    if($num > 0)
    {
        for ($i = 0; $i < $num; $i++)
        {
            $A = DB_fetchArray($result);
            $text = str_replace(array("\r\n", "\r", "\n"), '<br/>', $A['text']);

            if ($edit == "on") {
            
                if($A['stop'] == "on") {
                    $stop = " checked";
                }

                $row .= "<form enctype=\"multipart/form-data\" action=\"index.php\" METHOD=\"POST\"><tr>\n";
                $row .= "<input type=\"hidden\" name=\"productid\" value=\"{$A['productid']}\">\n";
                $row .= "<td><img src=\"{$_CONF['site_url']}/myshop/image.php?productid={$A['productid']}\"></td>\n";
                $row .= "<td>";
                $row .= "<div class=\"product_edit\"><input type=\"text\" name=\"name\" value=\"{$A['name']}\" class=\"size5\"></div>\n";
                $row .= "<div class=\"product_edit\"><textarea name=\"text\" class=\"size8\">{$text}</textarea></div>\n";
                $row .= "<div class=\"product_edit\"><input type=\"file\" name=\"upfile\" class=\"size5\"></div></td>\n";
                $row .= "<td><input type=\"text\" name=\"price\" value=\"{$A['price']}\" class=\"size1\"></td>\n";
                $row .= "<td><input type=\"checkbox\" name=\"stop\"{$stop}></td>\n";
                $row .= "<td><button type=\"submit\" name=\"mode\" value=\"product_save\" class=\"uk-button uk-button-primary\">変更</button>";
                $row .= "<button type=\"submit\" name=\"mode\" value=\"product_delete\" class=\"uk-button\">削除</button></td>\n";
                $row .= "</tr></form>\n";

            }
            else {

                if($A['stop'] != "on") {
                    $amount = "<select name=\"qty\">\n";
                    for ($c = 1; $c < 101; $c++)
                    {
                        $amount .= "<option value=\"{$c}\">{$c}</option>\n";
                    }
                    $amount .= "</select>";

                    $row .= "<tr><form action=\"index.php\" METHOD=\"POST\">\n";
                    $row .= "<input type=\"hidden\" name=\"mode\" value=\"check\">\n";
                    $row .= "<input type=\"hidden\" name=\"productid\" value=\"{$A['productid']}\">\n";
                    $row .= "<input type=\"hidden\" name=\"name\" value=\"{$A['name']}\">\n";
                    $row .= "<input type=\"hidden\" name=\"price\" value=\"{$A['price']}\">\n";
                    $row .= "<td><img src=\"{$_CONF['site_url']}/myshop/image.php?productid={$A['productid']}\" style=\"max-width: none;\"></td>\n";
                    $row .= "<td><div><h2>{$A['name']}</div><div>{$text}</div>\n";
                    $row .= "<td>￥".number_format($A['price'])."（税込み）</td>\n";
                    $row .= "<td>\n{$amount}\n</td>\n";
                    $row .= "<td><input type=\"image\" name=\"submit\" src=\"https://www.paypal.com/ja_JP/i/btn/btn_xpressCheckout.gif\"></td>\n";
                    $row .= "</form></tr>\n";
                }
            }
        }
    }
    
    return $row;
}
?>