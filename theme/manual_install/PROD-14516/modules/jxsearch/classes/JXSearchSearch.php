<?php
/*
* 2017-2018 Zemez
*
* JX Search
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

class JxSearchSearch extends Search
{
    public function jxfind($id_lang, $expr, $category_id, $page_number = 1, $page_size = 1, $order_by = 'position', $order_way = 'desc', $ajax = false, $instant = false)
    {
        if ($ajax) {
            $result = parent::find($id_lang, $expr, $page_number, $page_size, $order_by, $order_way, $ajax);
            return $this->filterSearchByCategory($result, $category_id);
        } elseif ($instant) {
            $result = parent::find($id_lang, $expr, $page_number, 1000, $order_by, $order_way, $ajax);
            return $this->filterSearchByCategory($result['result'], $category_id);
        } else {
            $result = parent::find($id_lang, $expr, 1, 1000, $order_by, $order_way, $ajax);
            return $this->filterSearchByCategory($result['result'], $category_id, $page_number, $page_size);
        }
    }

    protected function filterSearchByCategory($search_result, $category_id, $p = false, $n = false)
    {
        // check whether we searching through the children categories or not
        // if yes we need to get list of all children categories
        // else convert this id to array and start searching
        if (Configuration::get('PS_SEARCH_CHILDREN')) {
            $category = new Category((int)$category_id, Context::getContext()->language->id);
            $categories = $this->getAllChildren(array($category->recurseLiteCategTree(10)));
        } else {
            $categories = array($category_id);
        }

        $filteredSearch = array();
        $filteredSearchPage = array();
        $categoryProductsIds = array();

        foreach ($categories as $category) {
            // if this product doesn't related to any of the categories skip it else add to result
            if (!$product_ids = $this->checkProductsToCategoryEntry($category)) {
                continue;
            }

            $categoryProductsIds = array_merge($categoryProductsIds, $product_ids);
        }
        // if no products satisfy us, return an empty result
        if (!$categoryProductsIds) {
            return false;
        }

        $i = 0;
        foreach ($search_result as $product) {
            if (in_array($product['id_product'], $categoryProductsIds)) {
                $filteredSearch[] = $product;
                if ($p && $n) {
                    $current_pages_items = ($p - 1) * $n;
                    if ($current_pages_items <= $i && $i < $current_pages_items + $n) {
                        $filteredSearchPage[] = $product;
                    }
                    $i ++;
                }
            }
        }

        if ($p && $n) {
            $total = count($filteredSearch);
            $filteredSearch = array('result' => $filteredSearchPage, 'total' => $total);
        }

        return $filteredSearch;
    }

    protected function checkProductsToCategoryEntry($id_category)
    {
        $ids = array();
        $sql = 'SELECT `id_product`
                FROM '._DB_PREFIX_.'category_product
                WHERE `id_category` = '.(int)$id_category;

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $id) {
            $ids[] = $id['id_product'];
        }

        return $ids;
    }

    public function getAllChildren($array)
    {
        static $children = array();
        foreach ($array as $category) {
            array_push($children, $category['id']);
            if (isset($category['children']) && $category['children']) {
                $this->getAllChildren($category['children']);
            }
        }

        return array_unique($children);
    }
}
