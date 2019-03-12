<?php
// +---------------------------------------------------------------------------+
// | MyShop Plugin                                                             |
// +---------------------------------------------------------------------------+
// | geeklog/plugins/myshop/language/japanese_utf-8.php                        |
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
if (stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die ('This file can not be used on its own.');
}

$LANG_MYSHOP = array(
    'pi_name'            => 'MyShop',
    'menu_label'         => '製品一覧',
    'no_product'         => '現在、販売中の製品はありません。',
    'no_order'           => '現在、受注した注文はありません。',
    'header_title'       => 'Happa CMS Siteでは決済手続きにPayPalを使用しています。',
    'header_text'        => 'ユーザー数は世界で2億5000万人以上、1800万以上の店舗で利用されている安心でかんたんなオンライン決済サービスです。ペイパルが買い手と売り手を仲介することで、支払い情報をお店に伝えずに決済ができるので安心です。 ',
    'tbl_head_image'     => '製品イメージ',
    'tbl_head_text'      => '内容',
    'tbl_head_price'     => '価格',
    'tbl_head_amount'    => '数量',
    'order_title'        => '注文内容の詳細',
    'order_msg'          => 'この内容で注文しても宜しいでしょうか？',
    'publish'            => 'ライセンスの発行先',
    'license'            => 'ライセンス',
    'total_amount_title' => '合計金額',
    'comfirm_title'      => '注文内容の確定',
    'thank_you_pay'      => 'この度はご利用ありがとうございました。',
    'top_menu_caption'   => '商品の登録や削除をするには「商品一覧」を、受注した注文の見るには「注文一覧」を、PayPalの接続設定情報を編集する場合は「PayPal設定」をクリックしてください。',
    'top_menu1'          => '商品一覧',
    'top_menu2'          => '注文一覧',
    'top_menu3'          => 'PayPal設定',
    'top_menu4'          => '管理画面',
    'stock'              => '出品停止',
    'product_name'       => '製品名',
    'finish'             => '納品済み',
);

?>
