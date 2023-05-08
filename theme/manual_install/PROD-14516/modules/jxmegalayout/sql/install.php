<?php
/**
* 2017-2018 Zemez
*
* JX Mega Layout
*
* NOTICE OF LICENSE
*
* This source file is subject to the General Public License (GPL 2.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/GPL-2.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade the module to newer
* versions in the future.
*
*  @author    Zemez (Alexander Grosul & Alexander Pervakov)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout` (
    `id_layout` int(11) NOT NULL AUTO_INCREMENT,
	`hook_name` VARCHAR(100),
	`id_shop` int(11) NOT NULL,
    `layout_name` VARCHAR(100),
	`status` int(11) NOT NULL,
    PRIMARY KEY (`id_layout`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_items` (
    `id_item` int(11) NOT NULL AUTO_INCREMENT,
	`id_layout` int(11) NOT NULL,
    `id_parent` int(11) NOT NULL,
	`type` VARCHAR(100),
	`sort_order` int(11) NOT NULL,
	`specific_class` text,
	`col` VARCHAR(100),
	`col_xs` VARCHAR(100),
	`col_sm` VARCHAR(100),
	`col_md` VARCHAR(100),
	`col_lg` VARCHAR(100),
	`col_xl` VARCHAR(100),
	`col_xxl` VARCHAR(100),
    `module_name` VARCHAR(100),
	`id_unique` VARCHAR(100),
	`origin_hook` VARCHAR(100),
	`extra_css` VARCHAR(100),
    PRIMARY KEY (`id_item`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_pages` (
    `id_item` int(11) NOT NULL AUTO_INCREMENT,
	`id_layout` int(11) NOT NULL,
	`id_shop` int(11) NOT NULL,
        `page_name` VARCHAR(100),
	`status` int(11) NOT NULL,
    PRIMARY KEY (`id_item`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_hook_module_exceptions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_exceptions` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

// tables for an extra content (HTML, Banners, Videos etc.)
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_html` (
    `id_extra_html` int(11) NOT NULL AUTO_INCREMENT,
    `specific_class` text,
    PRIMARY KEY (`id_extra_html`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_html_lang` (
    `id_extra_html` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(11) NOT NULL,
    `name` VARCHAR(100),
    `content` text,
    PRIMARY KEY  (`id_extra_html`, `id_lang`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_banner` (
    `id_extra_banner` int(11) NOT NULL AUTO_INCREMENT,
    `specific_class` text,
    PRIMARY KEY (`id_extra_banner`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_banner_lang` (
    `id_extra_banner` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(11) NOT NULL,
    `name` VARCHAR(100),
    `img` VARCHAR(100),
    `link` VARCHAR(100),
    `content` text,
    PRIMARY KEY (`id_extra_banner`, `id_lang`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_video` (
    `id_extra_video` int(11) NOT NULL AUTO_INCREMENT,
    `specific_class` text,
    PRIMARY KEY  (`id_extra_video`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_video_lang` (
    `id_extra_video` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(11) NOT NULL,
    `name` VARCHAR(100),
    `url` VARCHAR(100),
    `content` text,
    PRIMARY KEY (`id_extra_video`, `id_lang`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_slider` (
    `id_extra_slider` int(11) NOT NULL AUTO_INCREMENT,
    `specific_class` text,
    `visible_items` int(11),
    `items_scroll` int(11),
    `margin` int(11),
    `speed` int(11),
    `auto_scroll` int(11),
    `pause` int(11),
    `loop` int(11),
    `pager` int(11),
    `controls` int(11),
    `auto_height` int(11),
    PRIMARY KEY (`id_extra_slider`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_slider_lang` (
    `id_extra_slider` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(11) NOT NULL,
    `name` VARCHAR(100),
    `content` text,
    PRIMARY KEY  (`id_extra_slider`, `id_lang`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_slider_item` (
    `id_item` int(11) NOT NULL AUTO_INCREMENT,
    `id_extra_slider` int(11),
    `type` VARCHAR(100),
    `id_content` VARCHAR(100),
    `position` int(11) NOT NULL,
    PRIMARY KEY  (`id_item`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
