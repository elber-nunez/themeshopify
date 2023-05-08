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

class MegaMenuBanner extends ObjectModel
{
    public $id_shop;
    public $specific_class;
    public $title;
    public $blank;
    public $url;
    public $image;
    public $public_title;
    public $description;

    public static $definition = array(
        'table'         => 'jxmegamenu_banner',
        'primary'       => 'id_item',
        'multilang'     => true,
        'fields'        => array(
            'id_shop'           => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'specific_class'    => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 128),
            'title'             => array('type' => self::TYPE_STRING, 'lang' => true,
                                         'validate' => 'isGenericName', 'required' => true, 'size' => 128),
            'blank'             => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'url'               => array('type' => self::TYPE_STRING, 'lang' => true,
                                         'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'image'             => array('type' => self::TYPE_STRING, 'lang' => true,
                                         'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'public_title'      => array('type' => self::TYPE_STRING, 'lang' => true,
                                         'validate' => 'isGenericName', 'size' => 128),
            'description'       => array('type' => self::TYPE_HTML, 'lang' => true,
                                         'validate' => 'isCleanHtml', 'size' => 4000),
        )
    );

    public function add($autodate = true, $null_values = false)
    {
        $res = parent::add($autodate, $null_values);
        return $res;
    }

    public function delete()
    {
        $res = true;

        $images = $this->image;
        if ($images) {
            foreach ($images as $image) {
                if ($image && file_exists(_PS_MODULE_DIR_.'jxmegamenu/images/'.$image)) {
                    $res &= @unlink(_PS_MODULE_DIR_.'jxmegamenu/images/'.$image);
                }
            }
        }

        $res &= parent::delete();
        return $res;
    }

    /*****
    ****** Get list of banners
    ****** return all items data
    *****/
    public function getBannersList()
    {
        $sql = 'SELECT jxb.*, jxbl.`title`, jxbl.`url`, jxbl.`image`
                FROM `'._DB_PREFIX_.'jxmegamenu_banner` jxb
                LEFT JOIN `'._DB_PREFIX_.'jxmegamenu_banner_lang` jxbl
                ON (jxb.`id_item` = jxbl.`id_item`)
                WHERE jxb.`id_shop` = '.(int)Context::getContext()->shop->id.'
                AND jxbl.`id_lang` = '.(int)Context::getContext()->language->id;

        return Db::getInstance()->executeS($sql);
    }
}
