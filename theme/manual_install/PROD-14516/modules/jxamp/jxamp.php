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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'jxamp/classes/controller/AMPFrontController.php');
include_once(_PS_MODULE_DIR_.'jxamp/classes/AMPStructuredDataList.php');
include_once(_PS_MODULE_DIR_.'jxamp/classes/AMPStructuredDataProduct.php');


class Jxamp extends Module
{
    protected $config_form = false;
    public $homepageBlocks;

    public function __construct()
    {
        $this->name = 'jxamp';
        $this->tab = 'mobile';
        $this->version = '0.1.0';
        $this->author = 'Zemez (Alexander Grosul)';
        $this->need_instance = 1;
        $this->controllers = array(
            'home',
            'newproducts',
            'pricesdrop',
            'bestsales',
            'category',
            'manufacturer',
            'supplier',
            'search',
            'product'
        );
        $this->availablePages = array(
            'index',
            'new-products',
            'prices-drop',
            'best-sales',
            'search',
            'manufacturer',
            'supplier',
            'category',
            'product'
        );
        $this->homepageBlocks = array(
            'JXAMP_HOMEPAGE_FEATURED_PRODUCTS',
            'JXAMP_HOMEPAGE_NEW_PRODUCTS',
            'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS',
            'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS',
            'JXAMP_HOMEPAGE_SLIDER'
        );
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('JX Accelerated Mobile Page');
        $this->description = $this->l('This module provides new opportunities for mobile devices and increase pages loading speed');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('moduleRoutes') &&
            $this->setDefaultValues();
    }

    public function setDefaultValues()
    {
        /* BLOCK #1*/
        Configuration::updateValue('JXAMP_USE_FULL_SITE_BUTTON', true);
        Configuration::updateValue('JXAMP_USE_HOMEPAGE', true);
        Configuration::updateValue('JXAMP_USE_CATEGORY_LISTING', true);
        Configuration::updateValue('JXAMP_USE_MANUFACTURER_LISTING', true);
        Configuration::updateValue('JXAMP_USE_SUPPLIER_LISTING', true);
        Configuration::updateValue('JXAMP_USE_NEW_PRODUCTS_LISTING', true);
        Configuration::updateValue('JXAMP_USE_BESTSELLER_LISTING', true);
        Configuration::updateValue('JXAMP_USE_SPECIAL_PRODUCTS_LISTING', true);
        Configuration::updateValue('JXAMP_USE_SEARCH_PRODUCTS_LISTING', true);
        Configuration::updateValue('JXAMP_USE_PRODUCT_INFO', true);
        /* BLOCK #2*/
        Configuration::updateValue('JXAMP_HOMEPAGE_SLIDER', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_SLIDER_SORT_ORDER', 1);
        Configuration::updateValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER', 6);
        Configuration::updateValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_CAROUSEL', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_SORT_ORDER', 2);
        Configuration::updateValue('JXAMP_HOMEPAGE_NEW_PRODUCTS', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER', 6);
        Configuration::updateValue('JXAMP_HOMEPAGE_NEW_PRODUCTS_CAROUSEL', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_NEW_PRODUCTS_SORT_ORDER', 3);
        Configuration::updateValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER', 6);
        Configuration::updateValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_CAROUSEL', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_SORT_ORDER', 4);
        Configuration::updateValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS', false);
        Configuration::updateValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER', 6);
        Configuration::updateValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_CAROUSEL', true);
        Configuration::updateValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_SORT_ORDER', 5);
        /*BLOCK #3*/
        Configuration::updateValue('JXAMP_LISTING_SORT_BY', 'name');
        Configuration::updateValue('JXAMP_LISTING_SORT_WAY', 'asc');
        Configuration::updateValue('JXAMP_LISTING_PRODUCTS_PER_PAGE', 6);
        Configuration::updateValue('JXAMP_LISTING_VIEW', 'grid');
        Configuration::updateValue('JXAMP_LISTING_PRODUCT_IMAGE', true);
        Configuration::updateValue('JXAMP_LISTING_INFO_BUTTON', true);
        /*BLOCK 4*/
        Configuration::updateValue('JXAMP_PRODUCT_SHARE_BTNS', true);
        Configuration::updateValue('JXAMP_PRODUCT_EXTRA_IMAGES', true);
        /*BLOCK 5*/
        Configuration::updateValue('JXAMP_SHARE_BTN_FACEBOOK', false);
        Configuration::updateValue('JXAMP_SHARE_BTN_FACEBOOK_KEY', '');
        Configuration::updateValue('JXAMP_SHARE_BTN_GPLUS', true);
        Configuration::updateValue('JXAMP_SHARE_BTN_PINTERST', true);
        Configuration::updateValue('JXAMP_SHARE_BTN_TWITTER', true);
        /*BLOCK #6*/
        Configuration::updateValue('JXAMP_ANALYTIC_STATUS', false);
        Configuration::updateValue('JXAMP_ANALYTIC_KEY', null);
        /*BLOCK #7*/
        Configuration::updateValue('JXAMP_EXTRA_STYLES_BACKGROUND', '');

        return true;
    }

    public function unsetDefaults()
    {
        /* BLOCK #1*/
        Configuration::deleteByName('JXAMP_USE_FULL_SITE_BUTTON');
        Configuration::deleteByName('JXAMP_USE_HOMEPAGE');
        Configuration::deleteByName('JXAMP_USE_CATEGORY_LISTING');
        Configuration::deleteByName('JXAMP_USE_MANUFACTURER_LISTING');
        Configuration::deleteByName('JXAMP_USE_SUPPLIER_LISTING');
        Configuration::deleteByName('JXAMP_USE_NEW_PRODUCTS_LISTING');
        Configuration::deleteByName('JXAMP_USE_BESTSELLER_LISTING');
        Configuration::deleteByName('JXAMP_USE_SPECIAL_PRODUCTS_LISTING');
        Configuration::deleteByName('JXAMP_USE_SEARCH_PRODUCTS_LISTING');
        Configuration::deleteByName('JXAMP_USE_PRODUCT_INFO');
        /* BLOCK #2*/
        Configuration::deleteByName('JXAMP_HOMEPAGE_SLIDER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_SLIDER_SORT_ORDER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_FEATURED_PRODUCTS');
        Configuration::deleteByName('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_CAROUSEL');
        Configuration::deleteByName('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_SORT_ORDER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_NEW_PRODUCTS');
        Configuration::deleteByName('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_NEW_PRODUCTS_CAROUSEL');
        Configuration::deleteByName('JXAMP_HOMEPAGE_NEW_PRODUCTS_SORT_ORDER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS');
        Configuration::deleteByName('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_CAROUSEL');
        Configuration::deleteByName('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_SORT_ORDER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS');
        Configuration::deleteByName('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER');
        Configuration::deleteByName('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_CAROUSEL');
        Configuration::deleteByName('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_SORT_ORDER');
        /*BLOCK #3*/
        Configuration::deleteByName('JXAMP_LISTING_SORT_BY');
        Configuration::deleteByName('JXAMP_LISTING_SORT_WAY');
        Configuration::deleteByName('JXAMP_LISTING_PRODUCTS_PER_PAGE');
        Configuration::deleteByName('JXAMP_LISTING_VIEW');
        Configuration::deleteByName('JXAMP_LISTING_PRODUCT_IMAGE');
        Configuration::deleteByName('JXAMP_LISTING_INFO_BUTTON');
        /*BLOCK 4*/
        Configuration::deleteByName('JXAMP_PRODUCT_SHARE_BTNS');
        Configuration::deleteByName('JXAMP_PRODUCT_EXTRA_IMAGES');
        /*BLOCK 5*/
        Configuration::deleteByName('JXAMP_SHARE_BTN_FACEBOOK');
        Configuration::deleteByName('JXAMP_SHARE_BTN_FACEBOOK_KEY');
        Configuration::deleteByName('JXAMP_SHARE_BTN_GPLUS');
        Configuration::deleteByName('JXAMP_SHARE_BTN_PINTERST');
        Configuration::deleteByName('JXAMP_SHARE_BTN_TWITTER');
        /*BLOCK #6*/
        Configuration::deleteByName('JXAMP_ANALYTIC_STATUS');
        Configuration::deleteByName('JXAMP_ANALYTIC_KEY');
        /*BLOCK #7*/
        Configuration::deleteByName('JXAMP_EXTRA_STYLES_BACKGROUND');

        return true;
    }

    public function uninstall()
    {
        $this->unsetDefaults();

        return parent::uninstall();
    }

    public function templatesPath()
    {
        return self::getLocalPath();
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-jxamp-pricesdrop'  => array(
                'controller' => 'pricesdrop',
                'rule'       => 'jxamp/prices-drop',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-newproducts'  => array(
                'controller' => 'newproducts',
                'rule'       => 'jxamp/new-products',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-bestsales'  => array(
                'controller' => 'bestsales',
                'rule'       => 'jxamp/best-sales',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-manufacturer'  => array(
                'controller' => 'manufacturer',
                'rule'       => 'jxamp/manufacturer/{id_manufacturer}',
                'keywords'   => array(
                    'id_manufacturer' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'id_manufacturer'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-supplier'  => array(
                'controller' => 'supplier',
                'rule'       => 'jxamp/supplier/{id_supplier}',
                'keywords'   => array(
                    'id_supplier' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'id_supplier'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-category'  => array(
                'controller' => 'category',
                'rule'       => 'jxamp/category/{id_category}',
                'keywords'   => array(
                    'id_category' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'id_category'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-index'  => array(
                'controller' => 'home',
                'rule'       => 'jxamp/',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-search'  => array(
                'controller' => 'search',
                'rule'       => 'jxamp/search',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            ),
            'module-jxamp-product'  => array(
                'controller' => 'product',
                'rule'       => 'jxamp/product/{id_product}',
                'keywords'   => array(
                    'id_product' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'id_product'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxamp',
                )
            )
        );
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitJXAMPPagesForm')) {
            $this->postProcess();
        }
        if (Tools::isSubmit('submitJXAMPHomepageForm')) {
            if ($error = $this->postHomepagePreProcess()) {
                $output .= $this->displayError($error);
            } else {
                $this->postHomepageProcess();
            }
        }
        if (Tools::isSubmit('submitJXAMPListingForm')) {
            if ($error = $this->postListingPreProcess()) {
                $output .= $this->displayError($error);
            } else {
                $this->postListingProcess();
            }
        }
        if (Tools::isSubmit('submitJXAMPProductForm')) {
            $this->postProductProcess();
        }
        if (Tools::isSubmit('submitJXAMPShareButtonsForm')) {
            if ($error = $this->postShareButtonsPreProcess()) {
                $output .= $this->displayError($error);
            } else {
                $this->postShareButtonsProcess();
            }
        }
        if (Tools::isSubmit('submitJXAMPAnalyticForm')) {
            $this->postAnalyticProcess();
        }
        if (Tools::isSubmit('submitJXAMPExtraStylesForm')) {
            $this->postExtraStylesProcess();
        }

        $module_link = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $this->context->smarty->assign('module_url', $module_link);

        if (!Tools::getIsset('ampTab') || Tools::getValue('ampTab') == 1) {
            $content = $this->renderForm();
        } elseif (Tools::getValue('ampTab') == 2) {
            $content = $this->renderHomepageForm();
        } elseif (Tools::getValue('ampTab') == 3) {
            $content = $this->renderListingForm();
        } elseif (Tools::getValue('ampTab') == 4) {
            $content = $this->renderProductForm();
        } elseif (Tools::getValue('ampTab') == 5) {
            $content = $this->renderShareButtonsForm();
        } elseif (Tools::getValue('ampTab') == 6) {
            $content = $this->renderAnalyticForm();
        } elseif (Tools::getValue('ampTab') == 7) {
            $content = $this->renderExtraStylesForm();
        }
        $this->context->smarty->assign('content', $content);
        $this->context->smarty->assign('active', Tools::getValue('ampTab'));
        $output .= $this->display($this->local_path, 'views/templates/admin/tabs.tpl');

        return $output;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPPagesForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Use "Full page" button'),
                        'name' => 'JXAMP_USE_FULL_SITE_BUTTON',
                        'is_bool' => true,
                        'desc' => $this->l('Would you like to leave an opportunity to switch to the full page version?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Homepage'),
                        'name' => 'JXAMP_USE_HOMEPAGE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Homepage?').' <a target="_blank" href='.$this->getPageAmpLink('index', true).'>'.$this->l('Preview page').'</a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Category pages'),
                        'name' => 'JXAMP_USE_CATEGORY_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Categories listing pages?').' <a target="_blank" href='.$this->getPageAmpLink('category', Configuration::get('PS_HOME_CATEGORY')).'>'.$this->l('Preview page').'</a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Manufacturer pages'),
                        'name' => 'JXAMP_USE_MANUFACTURER_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Manufacturer listing pages?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Supplier pages'),
                        'name' => 'JXAMP_USE_SUPPLIER_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Supplier listing pages?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Product info'),
                        'name' => 'JXAMP_USE_PRODUCT_INFO',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Products info pages?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('New products'),
                        'name' => 'JXAMP_USE_NEW_PRODUCTS_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for New products page?').' <a target="_blank" href='.$this->getPageAmpLink('new-products', true).'>'.$this->l('Preview page').'</a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Bestseller products'),
                        'name' => 'JXAMP_USE_BESTSELLER_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Bestseller products page?').' <a target="_blank" href='.$this->getPageAmpLink('best-sales', true).'>'.$this->l('Preview page').'</a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Special products'),
                        'name' => 'JXAMP_USE_SPECIAL_PRODUCTS_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Special products page?').' <a target="_blank" href='.$this->getPageAmpLink('prices-drop', true).'>'.$this->l('Preview page').'</a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Search products'),
                        'name' => 'JXAMP_USE_SEARCH_PRODUCTS_LISTING',
                        'is_bool' => true,
                        'desc' => $this->l('Enable AMP for Search products page?').' <a target="_blank" href='.$this->getPageAmpLink('search', 'lorem').'>'.$this->l('Preview page').'</a>',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'JXAMP_USE_HOMEPAGE' => Configuration::get('JXAMP_USE_HOMEPAGE', true),
            'JXAMP_USE_FULL_SITE_BUTTON' => Configuration::get('JXAMP_USE_FULL_SITE_BUTTON', true),
            'JXAMP_USE_CATEGORY_LISTING' => Configuration::get('JXAMP_USE_CATEGORY_LISTING', true),
            'JXAMP_USE_MANUFACTURER_LISTING' => Configuration::get('JXAMP_USE_MANUFACTURER_LISTING', true),
            'JXAMP_USE_SUPPLIER_LISTING' => Configuration::get('JXAMP_USE_SUPPLIER_LISTING', true),
            'JXAMP_USE_NEW_PRODUCTS_LISTING' => Configuration::get('JXAMP_USE_NEW_PRODUCTS_LISTING', true),
            'JXAMP_USE_BESTSELLER_LISTING' => Configuration::get('JXAMP_USE_BESTSELLER_LISTING', true),
            'JXAMP_USE_SPECIAL_PRODUCTS_LISTING' => Configuration::get('JXAMP_USE_SPECIAL_PRODUCTS_LISTING', true),
            'JXAMP_USE_PRODUCT_INFO' => Configuration::get('JXAMP_USE_PRODUCT_INFO', true),
            'JXAMP_USE_SEARCH_PRODUCTS_LISTING' => Configuration::get('JXAMP_USE_SEARCH_PRODUCTS_LISTING', true)
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function renderHomepageForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPHomepageForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ampTab='.Tools::getValue('ampTab');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getHomepageConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getHomepageConfigForm()));
    }

    protected function getHomepageConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Homepage slider'),
                        'name' => 'JXAMP_HOMEPAGE_SLIDER',
                        'is_bool' => true,
                        'desc' => $this->l('Display Homeoage slider block?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Sort order of the block on the homepage'),
                        'name' => 'JXAMP_HOMEPAGE_SLIDER_SORT_ORDER',
                        'label' => $this->l('Sort order'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Featured Products'),
                        'name' => 'JXAMP_HOMEPAGE_FEATURED_PRODUCTS',
                        'is_bool' => true,
                        'desc' => $this->l('Display Featured Products block on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Featured Products Carousel'),
                        'name' => 'JXAMP_HOMEPAGE_FEATURED_PRODUCTS_CAROUSEL',
                        'is_bool' => true,
                        'desc' => $this->l('Display Featured Products block carousel on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Number of Featured Products on the Homepage'),
                        'name' => 'JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER',
                        'label' => $this->l('Number of Featured Products'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'JXAMP_HOMEPAGE_FEATURED_PRODUCTS_SORT_ORDER',
                        'label' => $this->l('Featured Products sort order'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('New Products'),
                        'name' => 'JXAMP_HOMEPAGE_NEW_PRODUCTS',
                        'is_bool' => true,
                        'desc' => $this->l('Display New Products block on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('New Products Carousel'),
                        'name' => 'JXAMP_HOMEPAGE_NEW_PRODUCTS_CAROUSEL',
                        'is_bool' => true,
                        'desc' => $this->l('Display New Products block carousel on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Number of New Products on the Homepage'),
                        'name' => 'JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER',
                        'label' => $this->l('Number of New Products'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'JXAMP_HOMEPAGE_NEW_PRODUCTS_SORT_ORDER',
                        'label' => $this->l('New Products sort order'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Special Products'),
                        'name' => 'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS',
                        'is_bool' => true,
                        'desc' => $this->l('Display Special Products block on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Special Products Carousel'),
                        'name' => 'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_CAROUSEL',
                        'is_bool' => true,
                        'desc' => $this->l('Display Special Products block carousel on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Number of Special Products on the Homepage'),
                        'name' => 'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER',
                        'label' => $this->l('Number of Special Products'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_SORT_ORDER',
                        'label' => $this->l('Special Products sort order'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Bestseller Products'),
                        'name' => 'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS',
                        'is_bool' => true,
                        'desc' => $this->l('Display Bestseller Products block on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Bestseller Products Carousel'),
                        'name' => 'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_CAROUSEL',
                        'is_bool' => true,
                        'desc' => $this->l('Display Bestseller Products block carousel on the Homepage?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Number of Bestseller Products on the Homepage'),
                        'name' => 'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER',
                        'label' => $this->l('Number of Bestseller Products'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_SORT_ORDER',
                        'label' => $this->l('Bestseller Products sort order'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getHomepageConfigFormValues()
    {
        return array(
            'JXAMP_HOMEPAGE_SLIDER' => Configuration::get('JXAMP_HOMEPAGE_SLIDER', true),
            'JXAMP_HOMEPAGE_SLIDER_SORT_ORDER' => Configuration::get('JXAMP_HOMEPAGE_SLIDER_SORT_ORDER', 1),
            'JXAMP_HOMEPAGE_FEATURED_PRODUCTS' => Configuration::get('JXAMP_HOMEPAGE_FEATURED_PRODUCTS', true),
            'JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER' => Configuration::get('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER', 6),
            'JXAMP_HOMEPAGE_FEATURED_PRODUCTS_CAROUSEL' => Configuration::get('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_CAROUSEL', true),
            'JXAMP_HOMEPAGE_FEATURED_PRODUCTS_SORT_ORDER' => Configuration::get('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_SORT_ORDER', 2),
            'JXAMP_HOMEPAGE_NEW_PRODUCTS' => Configuration::get('JXAMP_HOMEPAGE_NEW_PRODUCTS', true),
            'JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER' => Configuration::get('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER', 6),
            'JXAMP_HOMEPAGE_NEW_PRODUCTS_CAROUSEL' => Configuration::get('JXAMP_HOMEPAGE_NEW_PRODUCTS_CAROUSEL', true),
            'JXAMP_HOMEPAGE_NEW_PRODUCTS_SORT_ORDER' => Configuration::get('JXAMP_HOMEPAGE_NEW_PRODUCTS_SORT_ORDER', 3),
            'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS' => Configuration::get('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS', true),
            'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER' => Configuration::get('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER', 6),
            'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_CAROUSEL' => Configuration::get('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_CAROUSEL', true),
            'JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_SORT_ORDER' => Configuration::get('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_SORT_ORDER', 4),
            'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS' => Configuration::get('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS', false),
            'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER' => Configuration::get('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER', 6),
            'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_CAROUSEL' => Configuration::get('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_CAROUSEL', true),
            'JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_SORT_ORDER' => Configuration::get('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_SORT_ORDER', 5)
        );
    }

    protected function postHomepagePreProcess()
    {
        $errors = array();
        if (Tools::isEmpty(Tools::getValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER'))
            || !Validate::isInt(Tools::getValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER'))
            || Tools::getValue('JXAMP_HOMEPAGE_FEATURED_PRODUCTS_NUMBER') < 1) {
                $errors[] = $this->l('"Number of Featured Products" field is invalid, it cannot be empty and must have an integer value');
        }
        if (Tools::isEmpty(Tools::getValue('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER'))
            || !Validate::isInt(Tools::getValue('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER'))
            || Tools::getValue('JXAMP_HOMEPAGE_NEW_PRODUCTS_NUMBER') < 1) {
            $errors[] = $this->l('"Number of New Products" field is invalid, it cannot be empty and must have an integer value');
        }
        if (Tools::isEmpty(Tools::getValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER'))
            || !Validate::isInt(Tools::getValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER'))
            || Tools::getValue('JXAMP_HOMEPAGE_SPECIAL_PRODUCTS_NUMBER') < 1) {
            $errors[] = $this->l('"Number of Special Products" field is invalid, it cannot be empty and must have an integer value');
        }
        if (Tools::isEmpty(Tools::getValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER'))
            || !Validate::isInt(Tools::getValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER'))
            || Tools::getValue('JXAMP_HOMEPAGE_BESTSELLER_PRODUCTS_NUMBER') < 1) {
            $errors[] = $this->l('"Number of Bestseller Products" field is invalid, it cannot be empty and must have an integer value');
        }

        $orders = array();
        foreach ($this->homepageBlocks as $block) {
            $bVal = $block.'_SORT_ORDER';
            if (Tools::isEmpty(Tools::getValue($bVal)) || !Validate::isInt(Tools::getValue($bVal)) || Tools::getValue($bVal) < 1) {
                $errors[] = $this->l('It seems that you left an empty block sort order filed. All sort order fields must be filled and contain unique number values.');
            } elseif (in_array(Tools::getValue($bVal), $orders)) {
                $errors[] = $this->l('It seems that you try to use not a unique sort order for some blocks. All sort orders values have to be unique');
            } else {
                $orders[] = Tools::getValue($bVal);
            }
        }

        if (count($errors)) {
            return implode('<br />', $errors);
        }

        return false;
    }

    protected function postHomepageProcess()
    {
        $form_values = $this->getHomepageConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function renderAnalyticForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPAnalyticForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ampTab='.Tools::getValue('ampTab');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getAnalyticConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getAnalyticConfigForm()));
    }

    protected function getAnalyticConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status'),
                        'name' => 'JXAMP_ANALYTIC_STATUS',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Google Analytic script?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('For correct working be sure that you use valid Google Analytic Key'),
                        'name' => 'JXAMP_ANALYTIC_KEY',
                        'label' => $this->l('Google Analytic Key'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getAnalyticConfigFormValues()
    {
        return array(
            'JXAMP_ANALYTIC_STATUS' => Configuration::get('JXAMP_ANALYTIC_STATUS', false),
            'JXAMP_ANALYTIC_KEY' => Configuration::get('JXAMP_ANALYTIC_KEY', null)
        );
    }

    protected function postAnalyticProcess()
    {
        $form_values = $this->getAnalyticConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function renderListingForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPListingForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ampTab='.Tools::getValue('ampTab');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getListingConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getListingConfigForm()));
    }

    protected function getListingConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'select',
                        'name' => 'JXAMP_LISTING_SORT_BY',
                        'label' => $this->l('Sort By'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'name',
                                    'name' => $this->l('name')
                                ),
                                array(
                                    'id' => 'position',
                                    'name' => $this->l('position')
                                ),
                                array(
                                    'id' => 'price',
                                    'name' => $this->l('price')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'JXAMP_LISTING_SORT_WAY',
                        'label' => $this->l('Sort Way'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'asc',
                                    'name' => $this->l('asc')
                                ),
                                array(
                                    'id' => 'desc',
                                    'name' => $this->l('desc')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('View more'),
                        'name' => 'JXAMP_LISTING_INFO_BUTTON',
                        'is_bool' => true,
                        'desc' => $this->l('Enable "View" button on listing pages?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Products per page'),
                        'name' => 'JXAMP_LISTING_PRODUCTS_PER_PAGE',
                        'col' => 3,
                        'desc' => $this->l('Set up quantity of products per listing page')
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'JXAMP_LISTING_VIEW',
                        'label' => $this->l('Products Displaying Type'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'list',
                                    'name' => $this->l('list')
                                ),
                                array(
                                    'id' => 'grid',
                                    'name' => $this->l('grid')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display image'),
                        'name' => 'JXAMP_LISTING_PRODUCT_IMAGE',
                        'is_bool' => true,
                        'desc' => $this->l('Display product image in miniature?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getListingConfigFormValues()
    {
        return array(
            'JXAMP_LISTING_SORT_BY' => Configuration::get('JXAMP_LISTING_SORT_BY', 'name'),
            'JXAMP_LISTING_SORT_WAY' => Configuration::get('JXAMP_LISTING_SORT_WAY', 'asc'),
            'JXAMP_LISTING_PRODUCTS_PER_PAGE' => Configuration::get('JXAMP_LISTING_PRODUCTS_PER_PAGE', 6),
            'JXAMP_LISTING_VIEW' => Configuration::get('JXAMP_LISTING_VIEW', 'grid'),
            'JXAMP_LISTING_PRODUCT_IMAGE' => Configuration::get('JXAMP_LISTING_PRODUCT_IMAGE', 'grid'),
            'JXAMP_LISTING_INFO_BUTTON' => Configuration::get('JXAMP_LISTING_INFO_BUTTON', true)
        );
    }

    protected function postListingPreProcess()
    {
        if (Tools::isEmpty(Tools::getValue('JXAMP_LISTING_PRODUCTS_PER_PAGE')) || !Validate::isInt(Tools::getValue('JXAMP_LISTING_PRODUCTS_PER_PAGE')) || Tools::getValue('JXAMP_LISTING_PRODUCTS_PER_PAGE') < 1) {
            return $this->l('Invalid "Products per page" field value. The field must be filled and contain a number value');
        }

        return false;
    }

    protected function postShareButtonsPreProcess()
    {
        if (Tools::getValue('JXAMP_SHARE_BTN_FACEBOOK') && Tools::isEmpty(Tools::getValue('JXAMP_SHARE_BTN_FACEBOOK_KEY'))) {
            return $this->l('Invalid "Facebook API Key" field value. The field must be filled and contain a valid Facebook API key');
        }

        return false;
    }

    protected function postListingProcess()
    {
        $form_values = $this->getListingConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function renderProductForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPProductForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ampTab='.Tools::getValue('ampTab');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getProductConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getProductConfigForm()));
    }

    protected function getProductConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Extra images'),
                        'name' => 'JXAMP_PRODUCT_EXTRA_IMAGES',
                        'is_bool' => true,
                        'desc' => $this->l('Display product\'s extra images on a product info page?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Share buttons'),
                        'name' => 'JXAMP_PRODUCT_SHARE_BTNS',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Share buttons on the product info page?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getProductConfigFormValues()
    {
        return array(
            'JXAMP_PRODUCT_SHARE_BTNS' => Configuration::get('JXAMP_PRODUCT_SHARE_BTNS', true),
            'JXAMP_PRODUCT_EXTRA_IMAGES' => Configuration::get('JXAMP_PRODUCT_EXTRA_IMAGES', true)
        );
    }

    protected function postProductProcess()
    {
        $form_values = $this->getProductConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function renderShareButtonsForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPShareButtonsForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ampTab='.Tools::getValue('ampTab');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getShareButtonsConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getShareButtonsConfigForm()));
    }

    protected function getShareButtonsConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Facebook button'),
                        'name' => 'JXAMP_SHARE_BTN_FACEBOOK',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Facebook button?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->l('Facebook API Key'),
                        'name' => 'JXAMP_SHARE_BTN_FACEBOOK_KEY',
                        'col' => 3,
                        'desc' => $this->l('TODO: put something here')
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Google+ button'),
                        'name' => 'JXAMP_SHARE_BTN_GPLUS',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Google+ button?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Pinterest button'),
                        'name' => 'JXAMP_SHARE_BTN_PINTEREST',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Pinterest button?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Twitter button'),
                        'name' => 'JXAMP_SHARE_BTN_TWITTER',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Twitter button?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getShareButtonsConfigFormValues()
    {
        return array(
            'JXAMP_SHARE_BTN_FACEBOOK' => Configuration::get('JXAMP_SHARE_BTN_FACEBOOK', true),
            'JXAMP_SHARE_BTN_FACEBOOK_KEY' => Configuration::get('JXAMP_SHARE_BTN_FACEBOOK_KEY', true),
            'JXAMP_SHARE_BTN_GPLUS' => Configuration::get('JXAMP_SHARE_BTN_GPLUS', true),
            'JXAMP_SHARE_BTN_PINTEREST' => Configuration::get('JXAMP_SHARE_BTN_PINTEREST', true),
            'JXAMP_SHARE_BTN_TWITTER' => Configuration::get('JXAMP_SHARE_BTN_TWITTER', true)
        );
    }

    protected function postShareButtonsProcess()
    {
        $form_values = $this->getShareButtonsConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function renderExtraStylesForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJXAMPExtraStylesForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&ampTab='.Tools::getValue('ampTab');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getExtraStylesConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getExtraStylesConfigForm()));
    }

    protected function getExtraStylesConfigForm()
    {
        return array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'color',
                        'label' => $this->l('Main background color'),
                        'name' => 'JXAMP_EXTRA_STYLES_BACKGROUND',
                        'desc' => $this->l('Pick main background color?')
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getExtraStylesConfigFormValues()
    {
        return array(
            'JXAMP_EXTRA_STYLES_BACKGROUND' => Configuration::get('JXAMP_EXTRA_STYLES_BACKGROUND')
        );
    }

    protected function postExtraStylesProcess()
    {
        $form_values = $this->getExtraStylesConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/jxamp_admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/jxamp_admin.css');
        }
    }

    public function hookHeader()
    {
        if ($amphtml = $this->getPageAmpLink($this->context->controller->php_self)) {
            $this->context->smarty->assign('ampurl', $amphtml);
            return $this->display($this->local_path, 'views/templates/hooks/header.tpl');
        }
    }

    private function getPageAmpLink($pageName, $previewValue = false)
    {
        if (Tools::getIsset('no_amp') && Tools::getValue('no_amp')) {
            return false;
        }
        $link = new Link();
        switch ($pageName) {
            case 'manufacturer':
                if (!Configuration::get('JXAMP_USE_MANUFACTURER_LISTING') && !$previewValue) {
                    return false;
                }
                return $link->getModuleLink($this->name, $pageName, array('id_manufacturer' => Tools::getValue('id_manufacturer')));
                break;
            case 'supplier':
                if (!Configuration::get('JXAMP_USE_SUPPLIER_LISTING') && !$previewValue) {
                    return false;
                }
                return $link->getModuleLink($this->name, $pageName, array('id_supplier' => Tools::getValue('id_supplier')));
                break;
            case 'category':
                if (!Configuration::get('JXAMP_USE_CATEGORY_LISTING') && !$previewValue) {
                    return false;
                }
                return $link->getModuleLink($this->name, $pageName, array('id_category' => $previewValue ? $previewValue : Tools::getValue('id_category')));
                break;
            case 'search':
                if (!Configuration::get('JXAMP_USE_SEARCH_PRODUCTS_LISTING') && !$previewValue) {
                    return false;
                }
                return $link->getModuleLink($this->name, $pageName, array('s' => $previewValue ? $previewValue : Tools::getValue('s')));
                break;
            case 'product':
                if (!Configuration::get('JXAMP_USE_PRODUCT_INFO') && !$previewValue) {
                    return false;
                }
                return $link->getModuleLink($this->name, $pageName, array('id_product' => Tools::getValue('id_product')));
                break;
            default:
                if (!$previewValue && (($pageName == 'index' && !Configuration::get('JXAMP_USE_HOMEPAGE'))
                    || ($pageName == 'new-products' && !Configuration::get('JXAMP_USE_NEW_PRODUCTS_LISTING'))
                    || ($pageName == 'prices-drop' && !Configuration::get('JXAMP_USE_SPECIAL_PRODUCTS_LISTING'))
                    || ($pageName == 'best-sales' && !Configuration::get('JXAMP_USE_BESTSELLER_LISTING'))
                    || ($pageName == 'search' && !Configuration::get('JXAMP_USE_SEARCH_PRODUCTS_LISTING')))) {
                    return false;
                }
                if (!in_array($pageName, $this->availablePages)) {
                    return false;
                }
                return $link->getModuleLink($this->name, $pageName);
        }
    }
}
