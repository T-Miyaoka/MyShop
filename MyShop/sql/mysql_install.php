<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | geeklog/plugins/myshop/sql/mysql_install.php                              |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2019 MeYan                                                  |
// +---------------------------------------------------------------------------|
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
// +---------------------------------------------------------------------------|

$_SQL[] = "CREATE TABLE {$_TABLES['myshop_config']} (
  username varchar(80) NOT NULL,
  password varchar(80),
  signature varchar(80),
  sandbox varchar(2),
  PRIMARY KEY (username)
) ENGINE=MyISAM;";

$_SQL[] = "CREATE TABLE {$_TABLES['myshop_product']} (
  productid varchar(14) NOT NULL default '0',
  name varchar(80),
  text varchar(255),
  price mediumint(8),
  stop varchar(2),
  imgext varchar(3),
  image longtext,
  PRIMARY KEY (productid)
) ENGINE=MyISAM;";

$_SQL[] = "CREATE TABLE {$_TABLES['myshop_order']} (
  orderid varchar(17) NOT NULL default '0',
  productid varchar(14) NOT NULL default '0',
  quantity mediumint(8),
  email varchar(80),
  status varchar(2),
  PRIMARY KEY (orderid)
) ENGINE=MyISAM;";
?>
