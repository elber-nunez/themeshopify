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
 * Class JXLookBookCollections
 */
class JXLookBookCollectionEntity extends ObjectModel
{
    /**
     * @var int
     */
    public $id_collection;
    /**
     * @var int
     */
    public $id_shop;
    /**
     * @var int
     */
    public $sort_order;
    /**
     * @var bool
     */
    public $active;
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
     * @var string
     */
    public $template;
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'jxlookbook',
        'primary' => 'id_collection',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'sort_order' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'required' => true,
                'validate' => 'isBool'
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 150,
                'lang' => true
            ),
            'description' => array(
                'type' => self::TYPE_HTML,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 4000,
                'lang' => true
            ),
            'image' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 1000
            ),
            'template' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 100
            )
        )
    );

    public static function addLang($id_lang, $id_collection)
    {
        //exit(var_dump($id_lang, $id_collection));
        if (!self::checkLang($id_lang, $id_collection)) {
            $table = 'jxlookbook_lang';
            $data = array(
                'id_collection' => $id_collection,
                'id_lang'       => $id_lang,
                'name'          => '',
                'description'   => ''
            );
            if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->insert($table, $data)) {
                return false;
            }

            return $result;
        }
    }

    public static function checkLang($id_lang, $id_collection)
    {
        $sql = 'SELECT *
                FROM '._DB_PREFIX_.'jxlookbook_lang
                WHERE id_lang = '.$id_lang.'
                AND id_collection ='.(int)$id_collection;

        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return true;
        }

        return false;
    }
}
