<?php
/**
 * 2017-2018 Zemez
 *
 * JX Look Book
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
 *  @author    Zemez
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class JXLookBookHostSpotEntity
 */
class JXLookBookHotSpotEntity extends ObjectModel
{
    /**
     * @var int
     */
    public $id_spot;
    /**
     * @var int
     */
    public $id_tab;
    /**
     * @var int
     */
    public $id_product;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $coordinates;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'jxlookbook_hotspot',
        'primary' => 'id_spot',
        'multilang' => true,
        'fields' => array(
            'id_tab' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'required' => false,
                'validate' => 'isUnsignedInt'
            ),
            'type' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isString'
            ),
            'coordinates' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isString',
                'size' => 1000
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'validate' => 'isCleanHtml',
                'lang' => true,
                'size' => 1000
            ),
            'description' => array(
                'type' => self::TYPE_HTML,
                'required' => false,
                'validate' => 'isCleanHtml',
                'lang' => true,
                'size' => 4000
            )
        )
    );

    public static function addLang($id_lang, $id_hotspot)
    {
        if (!JXLookBookHotSpotEntity::checkLang($id_lang, $id_hotspot)) {
            $table = 'jxlookbook_hotspot_lang';
            $data = array(
                'id_spot'     => $id_hotspot,
                'id_lang'     => $id_lang,
                'name'        => '',
                'description' => ''
            );
            if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->insert($table, $data)) {
                return false;
            }

            return $result;
        }
    }

    public static function checkLang($id_lang, $id_hotspot)
    {
        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'jxlookbook_hotspot_lang
                WHERE id_lang = '.$id_lang . '
                AND id_spot ='.(int)$id_hotspot;

        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return true;
        }

        return false;
    }

    public static function deleteByProductId($id_product)
    {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->delete('jxlookbook_hotspot', '`id_product`='.$id_product)) {
            return false;
        }

        return true;
    }

    public static function getByProductId($id_product)
    {
        $sql = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'jxlookbook_hotspot tlh
                JOIN ' . _DB_PREFIX_ . 'jxlookbook_tab tlt
                ON tlh.id_tab = tlt.id_tab
                AND tlh.id_product ='. (int)$id_product .'
                JOIN '. _DB_PREFIX_ . 'jxlookbook_tab_lang tltl
                ON tlt.id_tab = tltl.id_tab
                AND tltl.id_lang = '. Context::getContext()->language->id .'
                JOIN '._DB_PREFIX_.'jxlookbook tlb
                ON tlb.id_collection = tlt.id_collection
                AND tlb.id_shop = '.Context::getContext()->shop->id;

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return array();
        }

        return $result;
    }

    public static function updateCoordinates($id_spot, $coordinates)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->update('jxlookbook_hotspot', array('coordinates' => $coordinates), '`id_spot` = '.$id_spot);
    }

    public static function deleteByTabId($id_tab)
    {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->delete('jxlookbook_hotspot', '`id_tab`='.$id_tab)) {
            return false;
        }

        return true;
    }
}
