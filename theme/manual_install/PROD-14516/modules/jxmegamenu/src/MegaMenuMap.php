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

class MegaMenuMap extends ObjectModel
{
    public $id_item;
    public $id_shop;
    public $title;
    public $description;
    public $latitude;
    public $longitude;
    public $scale;
    public $marker;

    public static $definition = array(
        'table'       => 'jxmegamenu_map',
        'primary'     => 'id_item',
        'multilang'   => true,
        'fields'      => array(
            'id_shop'      => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'title'        => array('type' => self::TYPE_STRING, 'lang' => true,
                                    'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'latitude'     => array('type' => self::TYPE_FLOAT, 'validate' => 'isCoordinate',
                                    'required' => true, 'size' => 13),
            'longitude'    => array('type' => self::TYPE_FLOAT, 'validate' => 'isCoordinate',
                                    'required' => true, 'size' => 13),
            'scale'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'marker'       => array('type' => self::TYPE_STRING, 'validate' => 'isFileName'),
            'description'  => array('type' => self::TYPE_HTML, 'lang' => true,
                                    'validate' => 'isCleanHtml', 'size' => 4000),
        ),
    );

    /*****
    ****** Get list of Maps
    ****** return all items data
    *****/
    public function getMapsList()
    {
        $sql = 'SELECT jxm.*, jxml.`title`, jxml.`description`
                FROM `'._DB_PREFIX_.'jxmegamenu_map` jxm
                LEFT JOIN `'._DB_PREFIX_.'jxmegamenu_map_lang` jxml
                ON (jxm.`id_item` = jxml.`id_item`)
                WHERE jxm.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND jxml.`id_lang` = '.(int)Context::getContext()->language->id;

        return Db::getInstance()->executeS($sql);
    }
}
