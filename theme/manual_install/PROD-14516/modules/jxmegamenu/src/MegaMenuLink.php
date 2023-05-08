<?php
/**
* 2017-2018 Zemez
*
* JX Mega Menu
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
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class MegaMenuLink extends ObjectModel
{
    public $id_item;
    public $id_shop;
    public $specific_class;
    public $title;
    public $blank;
    public $url;

    public static $definition = array(
        'table'         => 'jxmegamenu_link',
        'primary'       => 'id_item',
        'multilang'     => true,
        'fields'        => array(
            'id_shop'           => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'specific_class'    => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 128),
            'title'             => array('type' => self::TYPE_STRING, 'lang' => true,
                                         'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'blank'             => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'url'               => array('type' => self::TYPE_STRING, 'lang' => true,
                                         'validate' => 'isUrl', 'required' => true, 'size' => 255),
        ),
    );

    /*****
    ****** Get list of custom Links
    ****** return all items data
    *****/
    public function getLinksList()
    {
        $sql = 'SELECT jxl.*, jxll.`title`, jxll.`url`
                FROM `'._DB_PREFIX_.'jxmegamenu_link` jxl
                LEFT JOIN `'._DB_PREFIX_.'jxmegamenu_link_lang` jxll
                ON (jxl.`id_item` = jxll.`id_item`)
                WHERE jxl.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND jxll.`id_lang` = '.(int)Context::getContext()->language->id;

        return Db::getInstance()->executeS($sql);
    }
}
