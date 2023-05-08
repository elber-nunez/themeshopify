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

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_items`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_pages`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_hook_module_exceptions`';

// tables for an extra content (HTML, Banners, Videos etc.)
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_html`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_html_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_banner`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_banner_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_video`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_video_lang`';

$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_slider`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_slider_lang`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jxmegalayout_extra_slider_item`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
