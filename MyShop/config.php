<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | geeklog/plugins/myshop/config.php                                         |
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
global $_DB_table_prefix, $_TABLES, $_MYSHOP_CONF;

// Set Plugin Table Prefix the Same as Geeklogs
$_MYSHOP_table_prefix = $_DB_table_prefix.'myshop_';

// Add to $_TABLES array the tables your plugin uses
$_TABLES['myshop_config']  = $_MYSHOP_table_prefix.'config';
$_TABLES['myshop_product'] = $_MYSHOP_table_prefix.'product';
$_TABLES['myshop_order'] = $_MYSHOP_table_prefix.'order';

// Plugin info
$_MYSHOP_CONF = array();
$_MYSHOP_CONF['pi_name']         = 'myshop';				// Plugin Name
$_MYSHOP_CONF['pi_display_name'] = 'MyShop';				// Plugin Display Name
$_MYSHOP_CONF['pi_version']      = '0.0.1';					// Plugin Version
$_MYSHOP_CONF['pi_gl_version']   = '2.0.0';					// Geeklog Version plugin for
$_MYSHOP_CONF['pi_url']          = 'http://www.happa.bz/';	// Plugin Homepage

$_MYSHOP_CONF['groups'] = array(
	'MyShop Admin' => 'Users in this group can administer the MyShop plugin'
);
$_MYSHOP_CONF['features'] = array(
	'myshop.admin' => 'Access to MyShop plugin editor',
);
$_MYSHOP_CONF['mappings'] = array(
	'myshop.admin' => array('MyShop Admin'),
);
$_MYSHOP_CONF['requires'] = array(
        array(
               'db'      => 'mysql',
               'version' => '4.1'
             )
);
?>
