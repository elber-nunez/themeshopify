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
 * Class JXLookBookTabEntity
 */
class JXLookBookTabEntity extends ObjectModel
{
    /**
     * @var int
     */
    public $id_tab;
    /**
     * @var int
     */
    public $id_collection;
    /**
     * @var int
     */
    public $sort_order;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $image;
    /**
     * @var bool
     */
    public $active;
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'jxlookbook_tab',
        'primary' => 'id_tab',
        'multilang' => true,
        'fields' => array(
            'id_collection' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'sort_order' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'lang' => true,
                'size' => 150
            ),
            'description' => array(
                'type' => self::TYPE_HTML,
                'required' => true,
                'validate' => 'isCleanHtml',
                'lang' => true,
                'size' => 4000
            ),
            'image' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 1000
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'required' => true,
                'validate' => 'isBool'
            )
        )
    );

    public static function addLang($id_lang, $id_tab)
    {
        if (!JXLookBookTabEntity::checkLang($id_lang, $id_tab)) {
            $table = 'jxlookbook_tab_lang';
            $data = array(
                'id_tab'      => $id_tab,
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

    public static function checkLang($id_lang, $id_tab)
    {
        $sql = 'SELECT *
                FROM '._DB_PREFIX_.'jxlookbook_tab_lang
                WHERE id_lang = '.$id_lang . '
                AND id_tab ='.(int)$id_tab;

        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return true;
        }

        return false;
    }
}