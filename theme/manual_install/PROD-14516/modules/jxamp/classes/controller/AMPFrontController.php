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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Category\CategoryDataProvider;

class AMPFrontControllerCore extends ModuleFrontController
{
    protected $pageName = 'index';
    protected $templateName = 'index';
    public $resultPerPage = 6;
    public $page = 1;
    public $parameters = array();
    protected $products = array();
    protected $sortBy = 'name';
    protected $sortWay = 'asc';
    public $origin_url;
    public $request_url;

    public function __construct($pageName, $templateName)
    {
        $this->displayHeader = false;
        $this->displayFooter = false;
        $this->resultPerPage = Configuration::get('JXAMP_LISTING_PRODUCTS_PER_PAGE');
        $this->sortBy = Configuration::get('JXAMP_LISTING_SORT_BY');
        $this->sortWay = Configuration::get('JXAMP_LISTING_SORT_WAY');
        $this->pageName = $pageName;
        $this->templateName = $templateName;
        $this->origin_url = 'https://'.$_SERVER['HTTP_HOST'];
        $this->request_url = isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] ? $_SERVER['HTTP_ORIGIN'] : $this->origin_url;

        parent::__construct();
    }

    protected function getModulePath()
    {
        $module = new jxamp();
        return $module->templatesPath().'views/templates/front/amp/';
    }

    public function initContent()
    {
        $mainMenu = array();
        $mainMenu['categories'] = $this->getMainMenu();
        $link = new Link();
        $this->context->smarty->assign('amp_styles', $this->getAmpFrontStyles());
        $this->context->smarty->assign('ampFilesPath', self::getModulePath());
        parent::initContent();
        $this->context->smarty->assign('current_url', $link->getModuleLink('jxamp', $this->pageName, $this->parameters));
        $this->context->smarty->assign('search_url', $link->getModuleLink('jxamp', 'search', $this->parameters));
        $this->context->smarty->assign('shop', $this->getTemplateVarShop());
        $this->context->smarty->assign('page', $this->getTemplateVarPage());
        $this->context->smarty->assign('main_menu', $mainMenu);
        $colors = $this->getAttributeBackgrounds();
        $this->context->smarty->assign('colors', $colors);
        $this->context->smarty->assign('listing_type', Configuration::get('JXAMP_LISTING_VIEW'));
        $this->context->smarty->assign('listing_info_btn', Configuration::get('JXAMP_LISTING_INFO_BUTTON'));

        $this->setTemplate('module:jxamp/views/templates/front/amp/catalog/listing/'.$this->templateName.'.tpl');
    }

    public function getTemplateVarShop()
    {
        $link = new Link();
        $shop = array();
        $shop['name'] = Configuration::get('PS_SHOP_NAME');
        $shop['email'] = Configuration::get('PS_SHOP_EMAIL');
        $shop['registration_numer'] = Configuration::get('PS_SHOP_DETAILS');

        $shop['logo'] = (Configuration::get('PS_LOGO')) ? _PS_IMG_.Configuration::get('PS_LOGO') : '';
        $shop['url'] = $link->getPageLink('index');
        $shop['stores_icon'] = (Configuration::get('PS_STORES_ICON')) ? _PS_IMG_.Configuration::get('PS_STORES_ICON') : '';
        $shop['favicon'] = (Configuration::get('PS_FAVICON')) ? _PS_IMG_.Configuration::get('PS_FAVICON') : '';
        $shop['favicon_update_time'] = Configuration::get('PS_IMG_UPDATE_TIME');

        $shop['phone'] = Configuration::get('PS_SHOP_PHONE');
        $shop['fax'] = Configuration::get('PS_SHOP_FAX');

        return $shop;
    }

    public function getPageName()
    {
        return $this->pageName;
    }

    public function getTemplateVarPage()
    {
        $pageName = $this->getPageName();
        $meta_tags = Meta::getMetaTags($this->context->language->id, $pageName);

        $body_classes = array(
            'lang-'.$this->context->language->iso_code => true,
            'lang-rtl' => (bool) $this->context->language->is_rtl,
            'country-'.$this->context->country->iso_code => true,
            'currency-'.$this->context->currency->iso_code => true,
            'page-'.$pageName => true
        );

        $page = array(
            'title' => '',
            'canonical' => '',
            'meta' => array(
                'title' => $meta_tags['meta_title'],
                'description' => $meta_tags['meta_description'],
                'keywords' => $meta_tags['meta_keywords'],
                'robots' => 'index',
            ),
            'page_name' => $pageName,
            'body_classes' => $body_classes,
            'admin_notifications' => array(),
        );

        return $page;
    }

    public function getProducts()
    {
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $products_for_template = array();
        $products_for_template['totalProducts'] = $this->products->getTotalProductsCount();
        $products_for_template['resultPerPage'] = $this->resultPerPage;
        $products_for_template['currentPage'] = $this->page;
        $products_for_template['totalPages'] = Tools::ps_round($products_for_template['totalProducts'] / $products_for_template['resultPerPage'], 0, PS_ROUND_UP);
        $products_for_template['view_more'] = Configuration::get('JXAMP_LISTING_INFO_BUTTON');
        $products_for_template['show_image'] = Configuration::get('JXAMP_LISTING_PRODUCT_IMAGE');
        $products_for_template['totalPages'] = Tools::ps_round($products_for_template['totalProducts'] / $products_for_template['resultPerPage'], 0, PS_ROUND_UP);
        $products_for_template['products'] = array();

        foreach ($this->products->getProducts() as $rawProduct) {
            $products_for_template['products'][] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }

    public function getMainMenu()
    {
        $categoryProvider = new CategoryDataProvider(new LegacyContext());

        return $categoryProvider->getNestedCategories();
    }

    public function getAttributeBackgrounds()
    {
        $result = array();
        $allAttr = Attribute::getAttributes($this->context->language->id);
        foreach ($allAttr as $key => $value) {
            if (!$value['is_color_group']) {
                unset($allAttr[$key]);
            }
        }
        foreach ($allAttr as $attr) {
            $attribute = new Attribute($attr['id_attribute']);
            if (!$attribute->color) {
                $result[$attribute->id] = 'url('.__PS_BASE_URI__.'img/co/'.$attribute->id.'.jpg)';
            } else {
                $result[$attribute->id] = $attribute->color;
            }
        }

        return $result;
    }

    private function getAmpFrontStyles()
    {
        $amp = new jxamp();
        $style = $amp->getLocalPath().'views/css/amp_theme.css';
        $overrideStyles = _PS_THEME_DIR_.'modules/jxamp/views/css/amp_theme.css';
        if (file_exists($overrideStyles)) {
            $style = $overrideStyles;
        }

        return Tools::file_get_contents($style);
    }

    public function setHeaders()
    {
        ob_end_clean();
        header("Content-type: application/json");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Origin: " . $this->request_url);
        header("AMP-Access-Control-Allow-Source-Origin: " . $this->origin_url);
        header("Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin");
    }
}
