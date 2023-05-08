<?php
/**
* 2017-2019 Zemez
*
* JX Category Products
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
* @copyright 2017-2019 Zemez
* @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

class AdminJXCategoryProductsController extends ModuleAdminController
{
    public function ajaxProcessGetProducts()
    {
        $jxcategoryproducts = new Jxcategoryproducts();
        $products = $jxcategoryproducts->getProducts(Tools::getValue('id_category'));
        if (!$selected_products = json_decode(Tools::getValue('selected_products'))) {
            $content = $jxcategoryproducts->renderProductList($products);
            die(json_encode(array('status' => 'true', 'content' => $content)));
        }

        foreach ($products as $key => $product) {
            if (is_numeric(array_search($product['id_product'], $selected_products))) {
                unset($products[$key]);
            }
        }

        if (count($products)) {
            $content = $jxcategoryproducts->renderProductList($products);
        } else {
            $content = $jxcategoryproducts->displayWarning($this->l('No products to select'));
        }


        die(json_encode(array('status' => 'true', 'content' => $content)));
    }

    public function ajaxProcessUpdatePosition()
    {
        $clear_cache = new Jxcategoryproducts();
        $clear_cache->clearCache();
        $items = Tools::getValue('item');
        $total = count($items);
        $id_shop = (int)$this->context->shop->id;
        $success = true;

        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'jxcategoryproducts',
                array('sort_order' => $i),
                '`id_tab` = '.preg_replace('/(item_)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_shop` ='.$id_shop
            );
        }
        if (!$success) {
            die(json_encode(array('error' => 'Update Fail')));
        }
        die(json_encode(array('success' => 'Update Success !', 'error' => false)));
    }
}
