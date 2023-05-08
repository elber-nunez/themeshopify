<?php
/**
 * 2017-2018 Zemez
 *
 * JX Accelerated Mobile Page
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

use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class JxAmpCategoryModuleFrontController extends AMPFrontControllerCore
{
    private $category;

    public function __construct($pageName, $templateName)
    {
        $pageName = 'category';
        $templateName = $pageName;
        if (Tools::getValue('id_category')) {
            $this->parameters = array('id_category' => Tools::getValue('id_category'));
        }
        parent::__construct($pageName, $templateName);
        if (Tools::getValue('page')) {
            $this->page = Tools::getValue('page');
        }
        if (Tools::getValue('orderWay') && Tools::getValue('orderWay') != 'null') {
            $this->sortWay = Tools::getValue('orderWay');
        }
        if (Tools::getValue('orderBy') && Tools::getValue('orderBy') != 'null') {
            $this->sortBy = Tools::getValue('orderBy');
        }
    }

    public function getProducts()
    {
        $searchProvider = new CategoryProductSearchProvider(
            $this->context->getTranslator(),
            $this->category
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();
        $query
            ->setIdCategory($this->category->id)
            ->setPage($this->page)
            ->setResultsPerPage($this->resultPerPage)
            ->setSortOrder(new SortOrder('product', $this->sortBy, $this->sortWay));

        $this->products = $searchProvider->runQuery(
            $context,
            $query
        );

        return parent::getProducts();
    }

    public function initContent()
    {
        $this->category = new Category(Tools::getValue('id_category'), $this->context->language->id);
        $structuredData = new AMPStructuredDataList();
        $link = new Link();
        $this->context->smarty->assign('amp_canonical', $link->getCategoryLink($this->category));
        $this->context->smarty->assign('category', $this->objectPresenter->present($this->category));
        $products = $this->getProducts();
        $this->context->smarty->assign('products', $products);
        if ($products['totalProducts']) {
            $microdata = $structuredData
                ->setCategory($this->category)
                ->setCategoryName()
                ->setCategoryDescription()
                ->setCategoryUrl()
                ->addStructuredProducts($products)
                ->structureResult();
            $this->context->smarty->assign('microdata', json_encode($microdata));
        }
        if (Tools::getIsset('ajax')) {
            parent::setHeaders();
            die(json_encode(array('items'=> $products)));
        }

        parent::initContent();
    }
}
