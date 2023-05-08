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

class MegaMenuVideo extends ObjectModel
{
    public $id_item;
    public $id_shop;
    public $title;
    public $url;
    public $type;

    public static $definition = array(
        'table'         => 'jxmegamenu_video',
        'primary'       => 'id_item',
        'multilang'     => true,
        'fields'        => array(
            'id_shop'     => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'title'       => array('type' => self::TYPE_STRING, 'lang' => true,
                                   'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'url'         => array('type' => self::TYPE_STRING, 'lang' => true,
                                   'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'type'        => array('type' => self::TYPE_STRING, 'lang' => true,
                                  'validate' => 'isGenericName', 'required' => true, 'size' => 128),
        ),
    );

    /*****
    ****** Get list of Videos
    ****** return all items data
    *****/
    public function getVideosList()
    {
        $sql = 'SELECT jxv.*, jxvl.`title`, jxvl.`url`, jxvl.`type`
                FROM `'._DB_PREFIX_.'jxmegamenu_video` jxv
                LEFT JOIN `'._DB_PREFIX_.'jxmegamenu_video_lang` jxvl
                ON (jxv.`id_item` = jxvl.`id_item`)
                WHERE jxv.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND jxvl.`id_lang` = '.(int)Context::getContext()->language->id;

        return Db::getInstance()->executeS($sql);
    }
}
