<?php
/**
* 2017-2019 Zemez
*
* JX Category Products
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
*  @author    Zemez (Alexander Grosul)
*  @copyright 2017-2019 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class CategoryProducts extends ObjectModel
{
    public $id_shop;
    public $hook_name;
    public $category;
    public $num;
    public $active;
    public $use_carousel;
    public $select_products;
    public $selected_products;
    public $carousel_settings;
    public $name;
    public $use_name;
    public $sort_order;

    public static $definition = array(
        'table' => 'jxcategoryproducts',
        'primary' => 'id_tab',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'category' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'num' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'sort_order' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'hook_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 128),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'select_products' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'selected_products' => array('type' => self::TYPE_STRING),
            'use_carousel' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'use_name' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'carousel_settings' => array('type' => self::TYPE_STRING),
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
        ),
    );
}
