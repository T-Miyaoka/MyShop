<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | geeklog/plugins/myshop/autoinstall.php                                    |
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

/**
* Plugin autoinstall function
*
* @param    string  $pi_name    Plugin name
* @return   array               Plugin information
*
*/
function plugin_autoinstall_myshop($pi_name) {
    global $_MYSHOP_CONF;

    $info = array(
        'pi_name'         => $_MYSHOP_CONF['pi_name'],
        'pi_display_name' => $_MYSHOP_CONF['pi_display_name'],
        'pi_version'      => $_MYSHOP_CONF['pi_version'],
        'pi_gl_version'   => $_MYSHOP_CONF['pi_gl_version'],
        'pi_homepage'     => $_MYSHOP_CONF['pi_url'],
    );

    $inst_parms = array(
        'info'      => $info,
        'groups'    => $_MYSHOP_CONF['groups'],
        'features'  => $_MYSHOP_CONF['features'],
        'mappings'  => $_MYSHOP_CONF['mappings'],
        'tables'    => array('myshop_config', 'myshop_product', 'myshop_order'),
        'requires'  => $_MYSHOP_CONF['requires']
    );

    return $inst_parms;
}
?>
