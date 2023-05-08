<?php
/**
 * 2017-2018 Zemez
 *
 * JX Google Map
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
 * @author    Zemez
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class StoreContacts extends ObjectModel
{
    public $id_tab;
    public $hook_name;
    public $default;
    public $id_shop;
    public $id_store;
    public $status;
    public $marker;
    public $content;
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'     => 'jxgooglemap',
        'primary'   => 'id_tab',
        'multilang' => true,
        'fields'    => array(
            'hook_name' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isGenericName', 'size' => 128),
            'id_store'  => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'default'   => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'id_shop'   => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
            'status'    => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'marker'    => array('type' => self::TYPE_STRING, 'validate' => 'isFileName'),
            'content'   => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
        ),
    );

    public function delete()
    {
        $res = true;
        $marker = $this->marker;
        if ($marker) {
            if (file_exists(_PS_MODULE_DIR_.'jxgooglemap/img/markers/'.$marker)) {
                $res &= @unlink(_PS_MODULE_DIR_.'jxgooglemap/img/markers/'.$marker);
            }
        }
        $res &= parent::delete();
        return $res;
    }

    public function resetDefault($hookName, $id_shop)
    {
        $sql = 'SELECT `id_tab`
                FROM '._DB_PREFIX_.'jxgooglemap
                WHERE `id_shop` = '.(int)$id_shop.'
                AND `hook_name` = "'.$hookName.'"
                AND `default` = 1';
        if ($id_tab = Db::getInstance()->getValue($sql)) {
            return Db::getInstance()->update(
                'jxgooglemap', array('default' => 0), '`id_tab` = '.(int)$id_tab.' AND `hook_name` = "'.$hookName.'"'
            );
        }

        return true;
    }
}
