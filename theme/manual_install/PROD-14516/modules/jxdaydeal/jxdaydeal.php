<?php
/**
 * 2017-2018 Zemez
 *
 * JX Deal of Day
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
 *  @author    Zemez (Sergiy Sakun)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once(_PS_MODULE_DIR_.'jxdaydeal/classes/DayDeal.php');

class Jxdaydeal extends Module
{
    public function __construct()
    {
        $this->name = 'jxdaydeal';
        $this->tab = 'administration';
        $this->version = '1.2.1';
        $this->author = 'Zemez (Sergiy Sakun)';
        $this->need_instance = 0;
        $this->lang = true;
        $this->module_key = 'e9338bceb8ef0cebd52fa0db917a0927';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('JX Deal of the day');
        $this->description = $this->l('The deal of the day module shows the daily deals with time counter on your products.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('displayProductPriceBlock') &&
            $this->registerHook('actionProductDelete') &&
            $this->registerHook('displayBeforeBodyClosingTag') &&
            Configuration::updateValue('JX_DEAL_DAY_NB', 4) &&
            Configuration::updateValue('JX_DEAL_DAY_RANDOM', false) &&
            $this->createAjaxController();
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('JX_DEAL_DAY_NB') &&
        Configuration::deleteByName('JX_DEAL_DAY_RANDOM') &&
        $this->removeAjaxContoller();

        return parent::uninstall();
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'jxdaydeal';
            }
        }
        $tab->class_name = 'AdminJXDayDealProducts';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        return (bool)$tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminJXDayDealProducts')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        $checker = false;
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->displayError($this->l('You cannot add/edit elements from \"All Shops\" or \"Group Shop\".'));
        } else {
            if ((bool)Tools::isSubmit('submitJxdaydealModule')) {
                if (!$result = $this->preValidateForm()) {
                    $output .= $this->addTab();
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name);
                } else {
                    $checker = true;
                    $output = $result;
                }
            }
            if ((bool)Tools::isSubmit('deletejxdaydeal')) {
                $output .= $this->deleteTab();
            }

            if ((bool)Tools::isSubmit('statusjxdaydeal')) {
                $output .= $this->updateStatusTab();
            }
            if ((bool)Tools::isSubmit('submitSettingsForm')) {
                if (!$errors = $this->checkTabFields()) {
                    $output .= $this->updateSettingsFieldsValues();
                    $output = $this->displayConfirmation($this->l('Settings are saved'));
                } else {
                    $output = $errors;
                    $checker = true;
                    $output .= $this->renderSettingsForm();
                }
            }
            if (Tools::getIsset('updatejxdaydeal') || Tools::getValue('updatejxdaydeal')) {
                if ($this->context->shop->id != Tools::getValue('id_shop')) {
                    $link_redirect = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name;
                    Tools::redirectAdmin($link_redirect);
                } else {
                    $output .= $this->renderForm();
                }
            } elseif ((bool)Tools::isSubmit('addjxdaydeal')) {
                   $output .= $this->renderForm();
            } elseif ((bool)Tools::isSubmit('updateSettings')) {
                   $output .= $this->renderSettingsForm();
            } elseif (!$checker) {
                if (!$this->getWarningMultishop()) {
                    $output .= $this->renderTabList();
                    $output .= $this->renderConfigButtons();
                } else {
                    $output .= $this->getWarningMultishop();
                }
            }
        }
        return $output;

    }

    protected function renderConfigButtons()
    {
        $fields_form = array(
            'form' => array(
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-cogs',
                        'title' => $this->l('Settings'),
                        'type' => 'submit',
                        'name' => 'updateSettings',
                    ),
                ),
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {

         $fields_form = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Add item'),
                'icon' => 'icon-cogs',
                 ),
                 'input' => array(
                    array(
                        'type' => 'custom_autocomplete',
                        'name' => 'id_product',
                        'label' => $this->l('Select a product'),
                        'id' => 'products'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status'),
                        'name' => 'status',
                        'is_bool' => true,
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
                         )
                    ),
                    array(
                        'type' => 'datetime',
                        'label' => $this->l('Start Date'),
                        'name' => 'data_start',
                        'col' => 6,
                        'required' => true
                    ),
                    array(
                        'type' => 'datetime',
                        'label' => $this->l('End Date'),
                        'name' => 'data_end',
                        'col' => 6,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Label'),
                        'col' => 2,
                        'name' => 'label',
                        'lang' => true,
                        'hint' => $this->l('Invalid characters:').' 0-9!&amp;lt;&amp;gt;,;?=+()@#"�{}_$%:'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Discount price'),
                        'col' => 2,
                        'required' => true,
                        'name' => 'discount_price',
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'reduction_type',
                        'options' => array(
                            'id' => 'id_option',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id_option' => 'amount',
                                    'name' => 'amount'
                                ),
                                array(
                                    'id_option' => 'percentage',
                                    'name' => '%'
                                ),
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'reduction_tax',
                        'options' => array(
                            'id' => 'id_option',
                            'name' => 'name',
                            'query' => array(
                                array(
                                    'id_option' => 0,
                                    'name' => 'Tax excluded'
                                ),
                                array(
                                    'id_option' => 1,
                                    'name' => 'Tax included'
                                ),
                            )
                       )
                    ),
                    array(
                        'type' => 'block_specific',
                        'name' => 'block_specific',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                 ),
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
             ),
         );

        if ((bool)Tools::getIsset('updatejxdaydeal') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new DayDeal((int)Tools::getValue('id_tab'));
            $fields_form['form']['input'][] = array(
                'type' => 'hidden',
                'name' => 'id_tab',
                'value' => (int)$tab->id);
            $fields_form['form']['input'][] = array(
                'type' => 'hidden',
                'name' => 'id_specific_price',
                'value' => $tab->id_specific_price
            );
        }

        $this->displayJXDayDealWarning();

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJxdaydealModule';

        if (Tools::isSubmit('submitJxdaydealModule')) {
            if ($this->preValidateForm()) {
                if (Tools::getValue('id_tab')) {
                    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&updatejxdaydeal&id_shop='.(int)$this->context->shop->id.'&id_tab='.Tools::getValue('id_tab');
                } else {
                    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&addjxdaydeal&id_shop='.(int)$this->context->shop->id;
                }
            } else {
                $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
            }
        } else {
            if ($this->preValidateForm()) {
                if (Tools::getValue('id_tab')) {
                    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&updatejxdaydeal&id_shop='.(int)$this->context->shop->id.'&id_tab='.Tools::getValue('id_tab');
                } else {
                    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&addjxdaydeal&id_shop='.(int)$this->context->shop->id;
                }
            } else {
                $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
            }
        }

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'theme_url' => $this->context->link->getAdminLink('AdminJXDayDealProducts')
        );

        Media::addJsDef(array('theme_url' => $this->context->link->getAdminLink('AdminJXDayDealProducts')));
        return $helper->generateForm(array($fields_form));
    }

    /**
     * Set values for the tabs.
     * @return array $fields_values
     */
    protected function getConfigFormValues()
    {
        if ((bool)Tools::getIsset('updatejxdaydeal') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new DayDeal((int)Tools::getValue('id_tab'));
        } else {
            $tab = new DayDeal();
        }

        $fields_values = array(
            'id_tab' => Tools::getValue('id_tab'),
            'id_specific_price' => Tools::getValue('id_specific_price', $tab->id_specific_price),
            'id_product' => Tools::getValue('id_product', $tab->id_product),
            'data_start' => Tools::getValue('data_start', $tab->data_start),
            'data_end' => Tools::getValue('data_end', $tab->data_end),
            'status' => Tools::getValue('status', $tab->status),
            'label' => Tools::getValue('label', $tab->label),
            'discount_price' => Tools::getValue('discount_price', $tab->discount_price),
            'reduction_type' => Tools::getValue('reduction_type', $tab->reduction_type),
            'reduction_tax' => Tools::getValue('reduction_tax', $tab->reduction_tax)
        );

        return $fields_values;

    }

    /**
     * Create warning of select product in form.
	 * @return array $data
     */
    public function displayJXDayDealWarning()
    {
        $data = array();

        if (Tools::getIsset('updatejxdaydeal')) {
            $tab = new DayDeal((int)Tools::getValue('id_tab'));
            $specificprice_list = DayDeal::getProductsBySpecificPrice($tab->id_product);
            if (is_array($specificprice_list)) {
                foreach ($specificprice_list as $key => $specificprice) {
                    $data[$key]['id_specific_price'] = $specificprice['id_specific_price'];
                    $data[$key]['reduction_type'] = $specificprice['reduction_type'];
                    if ($data[$key]['reduction_type'] != 'amount') {
                        $data[$key]['reduction_type'] = '%';
                    } else {
                        $data[$key]['reduction_type'] = 'amount';
                    }
                    if ($specificprice['reduction_type'] != 'amount') {
                        $data[$key]['reduction'] = round($specificprice['reduction'] * 100);
                    } else {
                        $data[$key]['reduction'] = round($specificprice['reduction']);
                    }
                    $data[$key]['from'] = $specificprice['from'];
                    $data[$key]['to'] = $specificprice['to'];
                    $data[$key]['reduction_tax'] = $specificprice['reduction_tax'];
                    $data[$key]['status'] = DayDeal::checkEntity($data[$key]['id_specific_price']);
                }
            }
        }
        $this->context->smarty->assign('specific_prices_data', $data);

        return $data;
    }

    /**
     * Create the structure of your form.
     * @param bool $tab
	 * @return array $tabs and $fields_list
     */
    public function renderTabList($tab = false)
    {

        if (!$tabs = DayDeal::getTabList($tab)) {
            $tabs = array();
        }

        $fields_list = array(
            'id_tab' => array(
                'title' => $this->l('Id'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
             ),
            'id_specific_price' => array(
                'title' => $this->l('Specific price id'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
             ),
             'id_product' => array(
                'title' => $this->l('Product id'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
             ),
             'name' => array(
                'title' => $this->l('Product name'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
             ),
             'data_start' => array(
                'title' => $this->l('Start Data'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'data_end' => array(
                'title' => $this->l('End Data'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'label' => array(
                'title' => $this->l('Label'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'discount_price' => array(
                'title' => $this->l('Discount price'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'reduction_type' => array(
                'title' => $this->l('Reduction type'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'search' => false,
                'orderby' => false,
            ),
        );
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_tab';
        $helper->table = $this->name;
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->title = $this->displayName;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex
                .'&configure='.$this->name.'&add'.$this->name
                     .'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );
        $helper->currentIndex = AdminController::$currentIndex
            .'&configure='.$this->name.'&id_shop='.(int)$this->context->shop->id;
        return $helper->generateList($tabs, $fields_list);
    }

    public function renderSettingsForm()
    {
         $fields_form = array(
             'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                 ),
                'input' => array(
                     array(
                        'type' => 'text',
                        'label' => $this->l('Products to display'),
                        'name' => 'JX_DEAL_DAY_NB',
                        'class' => 'fixed-width-xs',
                        'desc' => 'Define the number of products to be displayed in this block on home page.'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Random products'),
                        'name' => 'JX_DEAL_DAY_RANDOM',
                        'desc' => 'Enable if you wish the products to be displayed randomly (default: no).',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettingsForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getSettingsFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getSettingsFieldsValues()
    {
        return array(
            'JX_DEAL_DAY_NB' => Tools::getValue('JX_DEAL_DAY_NB', Configuration::get('JX_DEAL_DAY_NB')),
            'JX_DEAL_DAY_RANDOM' => Tools::getValue('JX_DEAL_DAY_RANDOM', Configuration::get('JX_DEAL_DAY_RANDOM'))
        );
    }

    protected function updateSettingsFieldsValues()
    {
        $form_values = $this->getSettingsFieldsValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    private function getProductId()
    {
        return str_replace('-', '', Tools::getValue('id_product'));
    }

    /**
     * Add tab
     */
    protected function addElement()
    {
        if ((int)Tools::getValue('id_tab') > 0) {
            $tab = new DayDeal((int)Tools::getValue('id_tab'));
            $specificPrice = new SpecificPrice($tab->id_specific_price);
        } else {
            $tab = new DayDeal();
            $specificPrice = new SpecificPrice();
        }
        $tab->id_product = $this->getProductId();
        $tab->id_shop = Context::getContext()->shop->id;
        $tab->data_start = Tools::getValue('data_start');
        $tab->data_end = Tools::getValue('data_end');
        $tab->status = (int)Tools::getValue('status');
        $tab->discount_price = Tools::getValue('discount_price');
        $tab->reduction_type = Tools::getValue('reduction_type');
        $tab->reduction_tax = Tools::getValue('reduction_tax');

        foreach (Language::getLanguages(false) as $lang) {
            $tab->label[$lang['id_lang']] = Tools::getValue('label_'.$lang['id_lang']);
        }

        $specificPrice->id_product = $this->getProductId();
        $specificPrice->id_shop = $this->context->shop->id;
        $specificPrice->id_currency = 0;
        $specificPrice->id_country = 0;
        $specificPrice->id_group = 0;
        $specificPrice->id_customer = 0;
        $specificPrice->from_quantity = 1;
        $specificPrice->price = -1;
        $specificPrice->reduction_type = Tools::getValue('reduction_type');

        if ($tab->reduction_type != 'amount') {
            $specificPrice->reduction = Tools::getValue('discount_price') / 100;
        } else {
            $specificPrice->reduction = Tools::getValue('discount_price');
        }

        $specificPrice->reduction_tax = Tools::getValue('reduction_tax');
        $specificPrice->from = Tools::getValue('data_start');
        $specificPrice->to = Tools::getValue('data_end');


        if (!Tools::getValue('id_tab')) {
            if (!$specificPrice->add()) {
                 return $this->displayError($this->l('The specific price could not be added.'));
            } else {
                 $tab->id_specific_price = $specificPrice->id;
            }
        } else {
            if (!$specificPrice->update()) {
                return $this->displayError($this->l('The specific price could not be updated.'));
            } else {
                $tab->id_specific_price = Tools::getValue('id_specific_price');
            }
        }
        if (!Tools::getValue('id_tab')) {
            if (!$tab->add()) {
                return $this->displayError($this->l('The tab could not be added.'));
            }
        } elseif (!$tab->update()) {
                return $this->displayError($this->l('The tab could not be updated.'));
        }
    }

    /**
     * Add tab if has specific price
     */
    protected function addMarker()
    {
        $tab = new DayDeal((int)Tools::getValue('id_tab'));
        $specificPrice = new SpecificPrice(Tools::getValue('specific_price_old'));

        $tab->id_product = $specificPrice->id_product;
        $tab->id_shop = $this->context->shop->id;
        $tab->id_specific_price = Tools::getValue('specific_price_old');
        $tab->data_start = $specificPrice->from;
        $tab->data_end = $specificPrice->to;
        $tab->reduction_type = $specificPrice->reduction_type;
        $tab->reduction_tax = $specificPrice->reduction_tax;

        if ($tab->reduction_type != 'amount') {
            $tab->discount_price = $specificPrice->reduction * 100;
        } else {
            $tab->discount_price = $specificPrice->reduction;
        }

        if ((int)Tools::getValue('id_tab') > 0) {
            if (!$tab->update()) {
                return $this->displayError($this->l('The tab could not be added.'));
            }
        } else {
            if (!$tab->add()) {
                return $this->displayError($this->l('The tab could not be updated.'));
            }
        }

    }

    /**
     * Add tab
     */
    protected function addTab()
    {
        if (!Tools::getValue('specific_price_old')) {
            $this->addElement();
                return $this->displayConfirmation($this->l('The tab is saved.'));
        } else {
            $this->addMarker();
                return $this->displayConfirmation($this->l('The tab is saved.'));
        }
    }

    /**
     * Delete tab
     */
    protected function deleteTab()
    {
        $tab = new DayDeal(Tools::getValue('id_tab'));
        $specific_price = new SpecificPrice($tab->id_specific_price);
        $specific_price->delete();
        $res = $tab->delete();

        if (!$res) {
            return $this->displayError($this->l('Error occurred when deleting the tab'));
        }

        return $this->displayConfirmation($this->l('The tab is successfully deleted'));

    }

     /**
     * Delete tab
     * 
     * @param int $id_product
     * @param int $id_shop
     */
    public static function deleteItemsByProductId($id_product, $id_shop)
    {
        if ($tabs = DayDeal::getItemsByProductId($id_product, $id_shop)) {
            foreach ($tabs as $value) {
                $tab = new DayDeal($value['id_tab']);
                $tab->delete();
            }
        }
    }

    /**
     * Update status tab
     */
    protected function updateStatusTab()
    {
        $tab = new DayDeal(Tools::getValue('id_tab'));

        if ($tab->status == 1) {
            $tab->status = 0;
        } else {
            $tab->status = 1;
        }
        if (!$tab->update()) {
            return $this->displayError($this->l('The tab status could not be updated.'));
        }

        return $this->displayConfirmation($this->l('The tab status is successfully updated.'));
    }

    /**
     * Display Warning.
	 * return alert with warning multishop
     */
    private function getWarningMultishop()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return '<p class="alert alert-warning">'.
                    $this->l('You cannot manage this module settings from "All Shops" or "Group Shop" context,
                             select the store you want to edit').
                    '</p>';
        } else {
            return '';
        }
    }

    /**
     * Check for item fields validity
     * @return array $errors if invalid or false
     */
    protected function preValidateForm()
    {
        $errors = array();
        $data = array();
        $specificprice = null;
        $from = Tools::getValue('data_start');
        $to = Tools::getValue('data_end');
        $discount_price = Tools::getValue('discount_price');
        $specificprice_list = DayDeal::getProductsSpecificPrice($this->getProductId());

        if (!$this->getProductId() || !Validate::isInt($this->getProductId())) {
            $errors[] = $this->l('No one product was selected, please select product');
        }
        if (is_array($specificprice_list)) {
            foreach ($specificprice_list as $specificprice) {
                $data['to'] = $specificprice['to'];
                $data['from'] = $specificprice['from'];
            }
        }
        if (!Tools::getValue('specific_price_old')) {
            if (!Tools::getValue('id_tab')) {
                if (strtotime($from) == true || strtotime($to) == true) {
                    if (strtotime($specificprice['to']) >= strtotime($to) || strtotime($from) < strtotime($specificprice['to'])) {
                        $errors[] = $this->l('Invalid date range, this period has specific price');
                    }
                }
            }
            if (!Validate::isDate($to) || !Validate::isDate($from)) {
                $errors[] = $this->l('Invalid date field');
            } elseif (strtotime($to) <= strtotime($from)) {
                $errors[] = $this->l('Invalid date range');
            }

            if (!Validate::isPrice($discount_price)) {
                $errors[] = $this->l('Invalid price field');
            }

            foreach (Language::getLanguages(false) as $lang) {
                if (!Validate::isName(Tools::getValue('label_'.$lang['id_lang']))) {
                    $errors[] = sprintf($this->l('Name is invalid: %s'), $lang['iso_code']);
                }
            }
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
     * Check for item fields settings form
     * @return array $errors if invalid or false
     */
    protected function checkTabFields()
    {
        $errors = array();

        $nbr = Tools::getValue('JX_DEAL_DAY_NB');
        if (!Validate::isInt($nbr) || $nbr <= 0) {
            $errors[] = $this->l('The number of products is invalid. Please enter a positive number.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name && (Tools::getIsset('addjxdaydeal') || Tools::getIsset('updatejxdaydeal'))) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/jxdaydeal_admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/jxdaydeal_admin.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/jquery.countdown.js');
        $this->context->controller->addJS($this->_path.'/views/js/jxdaydeal.js');
        $this->context->controller->addCSS($this->_path.'/views/css/jxdaydeal.css');
    }

    /**
     * Returns module content for home page
     */
    public function hookDisplayHome()
    {
        $products = array();
        $products_extra = array();
        $products_data = DayDeal::getDayDealProducts();

        if (is_array($products_data)) {
            foreach ($products_data as $key => $slide) {
                $image = new Image();
                $product = (new ProductAssembler($this->context))->assembleProduct(array('id_product' => $slide['id_product']));
                $presenterFactory = new ProductPresenterFactory($this->context);
                $presentationSettings = $presenterFactory->getPresentationSettings();
                $presenter = new ProductListingPresenter(new ImageRetriever($this->context->link), $this->context->link, new PriceFormatter(), new ProductColorsRetriever(), $this->context->getTranslator());
                $products[$key]['info'] = $presenter->present($presentationSettings, $product, $this->context->language);
                $products[$key]['image'] = $image->getCover($slide['id_product']);
                $products_extra[$product['id_product']] = DayDeal::getDayDealProductsData((int)$slide['id_product']);
            }
        }

        $this->context->smarty->assign(
            array(
                'daydeal_products' => $products,
                'daydeal_products_extra' => $products_extra
            )
        );
        return $this->display(__FILE__, 'jxdaydeal-home.tpl');
    }

    public function hookdisplayProductPriceBlock($params)
    {
        $id_product = '';

        if ($params['type'] != 'old_price') {
            return;
        }

        if (isset($params['product']->id) && $params['product']->id) {
            $id_product = $params['product']->id;
        } elseif (isset($params['product']['id_product']) && $params['product']['id_product']) {
            $id_product = $params['product']['id_product'];
        }

        $products_extra = DayDeal::getDayDealProductsData($id_product);

        $this->context->smarty->assign(
            array(
                'daydeal_products_extra' => $products_extra
            )
        );
        return $this->display(__FILE__, 'jxdaydeal-price-block.tpl');
    }

    public function hookActionProductDelete($params)
    {
        $id_product = (int)$params['id_product'];
        $id_shop = Context::getContext()->shop->id;
        $this->deleteItemsByProductId($id_product, $id_shop);
        
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        return $this->display($this->_path, '/views/templates/hook/jxdaydeal-script.tpl');
    }
}
