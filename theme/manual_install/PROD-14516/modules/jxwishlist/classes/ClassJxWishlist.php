<?php
/**
* 2017-2018 Zemez
*
* JX Wishlist
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

class ClassJxWishlist extends ObjectModel
{
    public $id_wishlist;
    public $id_customer;
    public $id_shop;
    public $token;
    public $name;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
     'table' => 'jxwishlist',
     'primary' => 'id_wishlist',
     'fields' => array(
         'id_shop' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isunsignedInt'),
         'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
         'name' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'required' => true),
         'token' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'required' => true),
       ),
    );

    /**
     * Delete products if exists id_wishlist
     *
     * @return  results
     */
    public function delete()
    {
        Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'jxwishlist_product`
            WHERE `id_wishlist` = '.(int)$this->id
        );

        if (isset($this->context->cookie->id_wishlist)) {
            unset($this->context->cookie->id_wishlist);
        }

        return parent::delete();
    }

    /**
     * Is exists by name for user
     * @param string $name
     *
     * @return  results
     */
    public static function isExistsByNameForUser($name)
    {
        $context = Context::getContext();

        return Db::getInstance()->getValue('
			SELECT COUNT(*) AS total
			FROM `'._DB_PREFIX_.'jxwishlist`
			WHERE `name` = \''.pSQL($name).'\'
            AND `id_customer` = '.(int)$context->customer->id.'
            AND `id_shop` = '.(int)Context::getContext()->shop->id.'
            ');
    }

    /**
     * Get Wishlists by Customer ID
     * @param int $id_customer
     * @return array results
     */
    public static function getByIdCustomer($id_customer)
    {
        return Db::getInstance()->executeS('
			SELECT jxw.`id_wishlist`, jxw.`name`, jxw.`token`
			FROM `'._DB_PREFIX_.'jxwishlist` jxw
			WHERE `id_customer` = '.(int)$id_customer.'
			AND `id_shop` = '.(int)Context::getContext()->shop->id.'
			ORDER BY jxw.`name` ASC');
    }

    /**
     * Return true if wish lists exists else false
     *
     * @param int $id_wishlist
     * @param int $id_customer
     * @param bool $return
     *
     * @return boolean exists
     */
    public static function exists($id_wishlist, $id_customer, $return = false)
    {
        $result = Db::getInstance()->getRow('
		SELECT `id_wishlist`, `name`, `token`
		FROM `'._DB_PREFIX_.'jxwishlist`
		WHERE `id_wishlist` = '.(int)$id_wishlist.'
		AND `id_customer` = '.(int)$id_customer.'
		AND `id_shop` = '.(int)Context::getContext()->shop->id);
        if (empty($result) === false && $result != false && sizeof($result)) {
            if ($return === false) {
                return true;
            } else {
                return $result;
            }
        }
        return false;

    }

    /**
     * Add product to ID wish list
     *
     * @param int $id_wishlist
     * @param int $id_customer
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $quantity
     *
     * @return boolean succeed
     */
    public static function addProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity)
    {
        $now = date('Y-m-d H:i:00');

        $result = Db::getInstance()->getRow(
            'SELECT jxwp.`quantity`
            FROM `'._DB_PREFIX_.'jxwishlist_product` jxwp
            JOIN `'._DB_PREFIX_.'jxwishlist` jxw
            ON (jxw.`id_wishlist` = jxwp.`id_wishlist`)
            WHERE jxwp.`id_wishlist` = '.(int)$id_wishlist.'
            AND jxw.`id_customer` = '.(int)$id_customer.'
            AND jxwp.`id_product` = '.(int)$id_product.'
            AND jxwp.`id_product_attribute` = '.(int)$id_product_attribute
        );

        if (empty($result) === false && sizeof($result)) {
            if (($result['quantity'] + $quantity) <= 0) {
                return (ClassJxWishlist::removeProduct($id_wishlist, $id_product, $id_product_attribute));
            } else {
                return (Db::getInstance()->execute('
				UPDATE `' . _DB_PREFIX_ . 'jxwishlist_product` SET
				`quantity` = ' . (int)($quantity + $result['quantity']) . '
				WHERE `id_wishlist` = ' . (int)$id_wishlist . '
				AND `id_product` = ' . (int)$id_product . '
				AND `id_product_attribute` = ' . (int)$id_product_attribute));
            }
        } else {
            return (Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'jxwishlist_product` (`id_wishlist`, `id_product`, `id_product_attribute`, `quantity`, `date_add`) VALUES(
                '.(int)$id_wishlist.',
                '.(int)$id_product.',
                '.(int)$id_product_attribute.',
                '.(int)$quantity.',
                \''.$now.'\'
                )')
            );
        }
    }

    /**
     * Remove product from wishlist
     *
     * @param int $id_wishlist
     * @param int $id_product
     * @param int $id_product_attribute
     *
     * @return boolean succeed
     */
    public static function removeProduct($id_wishlist, $id_product, $id_product_attribute)
    {
        return Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'jxwishlist_product`
            WHERE `id_wishlist` = '.(int)$id_wishlist.'
            AND `id_product` = '.(int)$id_product.'
            AND `id_product_attribute` = '.(int)$id_product_attribute
        );
    }

    /**
     * Get order product by stats.
     *
     * @return array result
     */
    public static function getProductByStatsOrders()
    {
        return Db::getInstance()->executeS(
            'SELECT SQL_CALC_FOUND_ROWS p.`id_product`, pl.`name`,
            IFNULL(SUM(od.`product_quantity`), 0) AS totalQuantitySold
            FROM `'._DB_PREFIX_.'product` p
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
            ON p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)Context::getContext()->language->id.'
            LEFT JOIN `'._DB_PREFIX_.'order_detail` od
            ON od.`product_id` = p.`id_product`
            LEFT JOIN `'._DB_PREFIX_.'orders` o
            ON od.`id_order` = o.`id_order`
            WHERE o.`valid` = 1
            GROUP BY od.`product_id`'
        );
    }

    /**
     * Get adds product by stats.
     *
     * @return array result
     */
    public static function getProductByStatsAdds()
    {
        $jxwishlist = new jxwishlist;
        $date_between = $jxwishlist->getDateByClassJxWishlist();

        return Db::getInstance()->executeS(
            'SELECT SQL_CALC_FOUND_ROWS p.`id_product`, pl.`name`,
            IFNULL(SUM(jxwp.`quantity`), 0) AS totalQuantityAdds
            FROM `'._DB_PREFIX_.'product` p
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
            ON p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)Context::getContext()->language->id.'
            JOIN `'._DB_PREFIX_.'jxwishlist_product` jxwp
            ON p.`id_product` = jxwp.`id_product`
            AND jxwp.`date_add` BETWEEN '.$date_between.'
            GROUP BY jxwp.`id_product`
            ORDER BY max(jxwp.`quantity`) desc
            LIMIT 10'
        );
    }

    /**
     * Get product by id wishlist.
     *
     * @param int $id_wishlist
     *
     * @return array $products
     */
    public static function getProductByIdWishlist($id_wishlist)
    {
        $products = Db::getInstance()->executeS(
            'SELECT DISTINCT jxwp.`id_wishlist`, jxwp.`id_product`, jxwp.`quantity`, p.`quantity` AS product_quantity, p.`price`, p.`show_price`, pl.`name`, jxwp.`id_product_attribute`, pl.link_rewrite
            FROM `'._DB_PREFIX_.'jxwishlist_product` jxwp
            LEFT JOIN `'._DB_PREFIX_.'product` p
            ON p.`id_product` = jxwp.`id_product`
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
            ON pl.`id_product` = jxwp.`id_product`
            LEFT JOIN `'._DB_PREFIX_.'jxwishlist` jxw
            ON jxw.`id_wishlist` = jxwp.`id_wishlist`
            WHERE jxwp.`id_wishlist` = '.(int)$id_wishlist.'
            AND pl.`id_lang` = '.(int)Context::getContext()->language->id
        );

        if (empty($products) === true || !sizeof($products)) {
            return array();
        }

        foreach ($products as $k => $pr) {
            $product= new Product((int)($pr['id_product']), false, (int)Context::getContext()->language->id);
            $quantity = Product::getQuantity((int)$pr['id_product'], $pr['id_product_attribute']);
            $products[$k]['product_quantity'] = $quantity;
            if ($pr['id_product_attribute'] != 0) {
                $img_combination = $product->getCombinationImages((int)Context::getContext()->language->id);
                if (isset($img_combination[$pr['id_product_attribute']][0])) {
                    $products[$k]['cover'] = $product->id.'-'.$img_combination[$pr['id_product_attribute']][0]['id_image'];
                } else {
                    $cover = Product::getCover($product->id);
                    $products[$k]['cover'] = $product->id.'-'.$cover['id_image'];
                }
            } else {
                $images = $product->getImages((int)Context::getContext()->language->id);
                foreach ($images as $image) {
                    if ($image['cover']) {
                        $products[$k]['cover'] = $product->id.'-'.$image['id_image'];
                        break;
                    }
                }
            }
            if (!isset($products[$k]['cover'])) {
                $products[$k]['cover'] = (int)Context::getContext()->language->iso_code.'-default';
            }
        }

        return $products;
    }

    /**
     * Get ID wishlist by Token
     *
     * @param int $token
     *
     * @return array Results
     */
    public static function getByToken($token)
    {
        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT jxw.`id_wishlist`, jxw.`name`, jxw.`id_customer`
            FROM `'._DB_PREFIX_.'jxwishlist` jxw
            INNER JOIN `'._DB_PREFIX_.'customer` c
            ON c.`id_customer` = jxw.`id_customer`
            WHERE `token` = \''.pSQL($token).'\''
        ));
    }

    public static function getUsersWishlistsOnRequest($id_customer)
    {
        $wishlists = self::getByIdCustomer($id_customer);
        if (!$wishlists) {
            return false;
        }
        $result = array();
        foreach ($wishlists as $key => $wishlist) {
            $result[$key]['Name'] = $wishlist['name'];
            $p = array();
            if($products = self::getProductByIdWishlist($wishlist['id_wishlist'])) {
                foreach ($products as $product) {
                    $p[] = $product['name'];
                }
            }
            $result[$key]['Products'] = implode(', ', $p);
        }

        return $result;
    }

    public static function removeUsersWishlistsOnRequest($id_customer)
    {
        $result = true;

        if (!$wishlists = self::getByIdCustomer($id_customer)) {
            return $result;
        }

        foreach ($wishlists as $wishlist) {
            $result &= Db::getInstance()->delete('jxwishlist_product', '`id_wishlist` = '.(int)$wishlist['id_wishlist']);
            $result &= Db::getInstance()->delete('jxwishlist', '`id_wishlist` = '.(int)$wishlist['id_wishlist']);
        }

        return $result;
    }
}
