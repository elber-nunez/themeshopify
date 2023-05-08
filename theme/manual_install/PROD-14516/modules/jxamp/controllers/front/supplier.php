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

use PrestaShop\PrestaShop\Adapter\Supplier\SupplierProductSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class JxAmpSupplierModuleFrontController extends AMPFrontControllerCore
{
    private $supplier;

    public function __construct($pageName, $templateName)
    {
        $pageName = 'supplier';
        $templateName = $pageName;
        $this->parameters = array('id_supplier' => Tools::getValue('id_supplier'));
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
        $searchProvider = new SupplierProductSearchProvider(
            $this->context->getTranslator(),
            $this->supplier
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();
        $query
            ->setIdSupplier($this->supplier->id)
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
        $this->supplier = new Supplier(Tools::getValue('id_supplier'), $this->context->language->id);
        $link = new Link();
        $structuredData = new AMPStructuredDataList();
        $this->context->smarty->assign('amp_canonical', $link->getSupplierLink($this->supplier));
        $this->context->smarty->assign('supplier', $this->objectPresenter->present($this->supplier));
        $products = $this->getProducts();
        $this->context->smarty->assign('products', $products);
        if ($products['totalProducts']) {
            $microdata = $structuredData
                ->setSupplier($this->supplier)
                ->setSupplierName()
                ->setSupplierDescription()
                ->setSupplierUrl()
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
