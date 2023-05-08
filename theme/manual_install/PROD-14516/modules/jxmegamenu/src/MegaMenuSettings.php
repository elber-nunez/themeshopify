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
 * @author    Zemez (Alexander Grosul)
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MegaMenuSettings extends ObjectModel
{
    public $id_shop;
    public $hook_name;
    public $googleapi;

    public static $definition = array(
        'table'     => 'jxmegamenu_settings',
        'primary'   => 'id_item',
        'multilang' => false,
        'fields'    => array(
            'id_shop'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'hook_name'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128),
            'googleapi'      => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128)
        ),
    );

    public function getIdByShop($id_shop, $hook_name)
    {
        $sql = 'SELECT `id_item` FROM '._DB_PREFIX_.'jxmegamenu_settings WHERE `id_shop` = '.(int)$id_shop.' AND `hook_name` = "'.pSQL($hook_name).'"';

        return Db::getInstance()->getValue($sql);
    }

    public static function getFieldValueByIdShop($id_shop, $hook_name, $field)
    {
        $sql = 'SELECT `'.$field.'` FROM '._DB_PREFIX_.'jxmegamenu_settings WHERE `id_shop` = '.(int)$id_shop.' AND `hook_name` = "'.pSQL($hook_name).'"';

        return Db::getInstance()->getValue($sql);
    }
}
