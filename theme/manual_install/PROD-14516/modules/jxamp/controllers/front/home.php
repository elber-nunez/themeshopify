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
use PrestaShop\PrestaShop\Adapter\NewProducts\NewProductsProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\PricesDrop\PricesDropProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\BestSales\BestSalesProductSearchProvider;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class JxAmpHomeModuleFrontController extends AMPFrontControllerCore
{
    public $amp;
    public function __construct($pageName, $templateName)
    {
        $pageName = 'index';
        $templateName = $pageName;
        $this->amp = new Jxamp();
        parent::__construct($pageName, $templateName);
    }

    public function initContent()
    {
        parent::initContent();
        $link = new Link();
        $content = '';
        $this->context->smarty->assign('amp_canonical', $link->getPageLink($this->pageName));
        if ($contentList = $this->buildFrontendBlocks()) {
            ksort($contentList);
            foreach ($contentList as $item) {
                if ($item == 'JXAMP_HOMEPAGE_SLIDER') {
                    if (Module::isInstalled('ps_imageslider') && Module::isEnabled('ps_imageslider')) {
                        include_once(_PS_MODULE_DIR_.'ps_imageslider/ps_imageslider.php');
                        $imageSlider = new Ps_ImageSlider();
                        $this->context->smarty->assign('slider', $imageSlider->getWidgetVariables());
                    }
                    $content .= $this->amp->display($this->amp->templatesPath(), 'views/templates/hooks/home-slider.tpl');
                }
                if ($item == 'JXAMP_HOMEPAGE_FEATURED_PRODUCTS') {
                    $this->context->smarty->assign('featured_products', $this->getHomeFeatured(Configuration::get('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER')));
                    $content .= $this->amp->display($this->amp->templatesPath(), 'views/templates/hooks/home-featured-products.tpl');
                }
                if ($item == 'JXAMP_HOMEPAGE_NEW_PRODUCTS') {
                    $this->context->smarty->assign('new_products', $this->getHomeNew(Configuration::get('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER')));
                    $content .= $this->amp->display($this->amp->templatesPath(), 'views/templates/hooks/home-new-products.tpl');
                }
                if ($item == 'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS') {
                    $this->context->smarty->assign('special_products', $this->getHomeSpecials(Configuration::get('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER')));
                    $content .= $this->amp->display($this->amp->templatesPath(), 'views/templates/hooks/home-special-products.tpl');
                }
                if ($item == 'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS') {
                    $this->context->smarty->assign('best_sellers', $this->getHomeBestsellers(Configuration::get('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER')));
                    $content .= $this->amp->display($this->amp->templatesPath(), 'views/templates/hooks/home-bestsellers.tpl');
                }
            }
        }
        $this->context->smarty->assign('homepage_content', $content);

        $this->setTemplate('module:jxamp/views/templates/front/amp/'.$this->templateName.'.tpl');
    }

    private function buildFrontendBlocks()
    {
        $result = array();
        foreach ($this->amp->homepageBlocks as $block) {
            if (Configuration::get($block)) {
                $result[Configuration::get($block.'_SORT_ORDER')] = $block;
            }
        }

        return $result;
    }

    public function getHomeFeatured($number)
    {
        $this->getProvider(new CategoryProductSearchProvider(
            $this->context->getTranslator(),
            new Category(Configuration::get('PS_HOME_CATEGORY'), $this->context->language->id)
        ), $number);

        return parent::getProducts();
    }

    public function getHomeNew($number)
    {
        $this->getProvider(new NewProductsProductSearchProvider(
            $this->context->getTranslator()
        ), $number);

        return parent::getProducts();
    }

    public function getHomeSpecials($number)
    {
        $this->getProvider(new PricesDropProductSearchProvider(
            $this->context->getTranslator()
        ), $number);

        return parent::getProducts();
    }

    public function getHomeBestsellers($number)
    {
        $this->getProvider(new BestSalesProductSearchProvider(
            $this->context->getTranslator()
        ), $number);

        return parent::getProducts();
    }

    private function getProvider($searchProvider, $number = 6)
    {
        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();
        $query
            ->setResultsPerPage($number)
            ->setSortOrder(new SortOrder('product', 'name', 'asc'));

        $this->products = $searchProvider->runQuery(
            $context,
            $query
        );
    }
}
