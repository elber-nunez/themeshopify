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

class HeaderAccount extends ObjectModel
{
    public $id;
    public $id_customer;
    public $id_shop;
    public $social_id;
    public $social_type;
    public $avatar_url;

    public static $definition = array(
        'table'        => 'customer_jxheaderaccount',
        'primary'      => 'id',
        'multilang'    => false,
        'fields'       => array(
            'id_customer'    => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_shop'        => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'social_id'      => array('type' => self::TYPE_STRING),
            'social_type'    => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128),
            'avatar_url'     => array('type' => self::TYPE_STRING),
        ),
    );

    /**
     * Get social id from db
     *
     * @param $type
     * @param $id_customer
     * @param $id_shop
     *
     * @return bool|false|null|string Social id
     */
    public function getSocialId($type, $id_customer)
    {
        $sql = 'SELECT `social_id`
                FROM `'._DB_PREFIX_.'customer_jxheaderaccount`
                WHERE `social_type` = \''.pSQL($type).'\'
                AND `id_customer` = '.(int)$id_customer;
        if (!$social_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return false;
        }

        return $social_id;
    }

    /**
     * Get user avatar from db
     *
     * @param $type
     * @param $id_customer
     * @param $id_shop
     *
     * @return bool|false|null|string Link
     */
    public function getImageUrl($type, $id_customer)
    {
        $sql = 'SELECT `avatar_url`
                FROM `'._DB_PREFIX_.'customer_jxheaderaccount`
                WHERE `social_type` = \''.pSQL($type).'\'
                AND `id_customer` = '.(int)$id_customer;
        if (!$avatar_url = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return false;
        }

        return $avatar_url;
    }

    public function getCustomerId($social_id, $social_type, $id_shop)
    {
        $sql = 'SELECT `id_customer`
            FROM `'._DB_PREFIX_.'customer_jxheaderaccount`
            WHERE `social_id` = \''.pSQL($social_id).'\'
            AND `social_type` = \''.pSQL($social_type).'\'
            AND `id_shop` = '.(int)$id_shop;
        if (!$id_customer = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return false;
        }

        return $id_customer;
    }

    public function getCustomerEmail($social_id, $type)
    {
        $sql = 'SELECT c.`email`
                FROM `'._DB_PREFIX_.'customer` c
                LEFT JOIN `'._DB_PREFIX_.'customer_jxheaderaccount` ct ON ct.id_customer = c.id_customer
                WHERE ct.`social_id` = '.pSQL($social_id).'
                AND `social_type` = \''.pSQL($type).'\'
                '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');

        if (!$email = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return false;
        }

        return $email;
    }

    public function getSocialIdByRest($type, $id_customer)
    {
        $sql = 'SELECT `social_id`
                FROM `'._DB_PREFIX_.'customer_jxheaderaccount`
                WHERE `id_customer` = \''.(int)$id_customer.'\'
                AND `social_type` = \''.pSQL($type).'\'
                '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

        if (!$social_id = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return false;
        }

        return $social_id;
    }

    public function updateAvatar($profile_image_url, $id_customer)
    {
        Db::getInstance()->update(
            'customer_jxheaderaccount',
            array(
                'avatar_url' => pSQL($profile_image_url)
            ),
            '`id_customer` = \''.(int)$id_customer.'\'
                    AND `social_type` = \'google\'
                    '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER)
        );
    }

    public static function getGDPRCustomerInfoById($id_customer)
    {
        return Db::getInstance()->executeS('
            SELECT `id_customer` as "ID Customer", `social_type` as "Social Network", `social_id` as "Social Identificator", `avatar_url` as "Avatar"
            FROM '._DB_PREFIX_.'customer_jxheaderaccount
            WHERE `id_customer` = '.(int)$id_customer);
    }

    public static function removeEntriesByCustomerId($id_customer)
    {
        return Db::getInstance()->delete('customer_jxheaderaccount', '`id_customer` = '.(int)$id_customer.' AND `id_shop` = '.(int)Context::getContext()->shop->id);
    }
}
