<?php
/**
* 2017-2018 Zemez
*
* JX Product List Gallery
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

class Jxproductlistgallery extends Module
{
    public function __construct()
    {
        $this->name = 'jxproductlistgallery';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'Zemez';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('JX Product List Gallery');
        $this->description = $this->l('Show all images of product on product listing');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        $this->clearCache();
        $settings = $this->getModuleSettings();

        foreach ($settings as $name => $value) {
            Configuration::updateValue($name, $value);
        }

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('displayProductListGallery')
            && $this->registerHook('actionProductAdd')
            && $this->registerHook('actionProductSave')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionProductDelete');
    }

    public function uninstall()
    {
        $this->clearCache();
        $settings = $this->getModuleSettings();
        foreach (array_keys($settings) as $name) {
            Configuration::deleteByName($name);
        }

        return parent::uninstall();
    }

    /**
     * Array with all settings and default values
     * @return array $setting
     */
    protected function getModuleSettings()
    {
        $settings = array(
            'JX_PLG_LIVE_MODE' => true,
            'JX_PLG_TYPE' => 'gallery',
            'JX_PLG_ROLLOVER_ANIMATION' => 'horizontal_slide',
            'JX_PLG_DISPLAY_ITEMS' => 8,
            'JX_PLG_USE_CAROUSEL' => false,
            'JX_PLG_CAROUSEL_NB' => 3,
            'JX_PLG_USE_PAGER' => false,
            'JX_PLG_USE_CONTROLS' => false
        );

        return $settings;
    }

    public function getContent()
    {
        $output = '';

        if (((bool)Tools::isSubmit('submitJxproductlistgalleryModule')) == true) {
            if (!$errors = $this->validateSettings()) {
                $this->postProcess();
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Settings updated successful.'));
            } else {
                $output .= $errors;
            }
        }

        return $output.$this->renderForm();
    }

    /**
     * Validate filed values
     * @return array|bool errors or false if no errors
     */
    protected function validateSettings()
    {
        $errors = array();

        if (!Tools::isEmpty(Tools::getValue('JX_PLG_DISPLAY_ITEMS'))
            && (!Validate::isInt(Tools::getValue('JX_PLG_DISPLAY_ITEMS'))
                || Tools::getValue('JX_PLG_DISPLAY_ITEMS') < 0)) {
            $errors[] = $this->l('"Display items" value error. Only integer numbers are allowed.');
        }

        if (!Tools::isEmpty(Tools::getValue('JX_PLG_CAROUSEL_NB'))
            && (!Validate::isInt(Tools::getValue('JX_PLG_CAROUSEL_NB'))
                || Tools::getValue('JX_PLG_CAROUSEL_NB') < 0)) {
            $errors[] = $this->l('"Visible items" value error. Only integer numbers are allowed.');
        }

        if (Tools::getValue('JX_PLG_CAROUSEL_NB') > Tools::getValue('JX_PLG_DISPLAY_ITEMS')) {
            $errors[] = $this->l('"Visible items" value error. Value must be less or equal value - "Display items".');
        }

        if ($errors) {
            return $this->displayError(implode('<br />', $errors));
        } else {
            return false;
        }
    }

    /**
     * Build the module form
     * @return mixed
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJxproductlistgalleryModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'image_path' => $this->_path.'views/img',
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Draw the module form
     * @return array
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'form_group_class' => 'rollover-type gallery-type slideshow-type',
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'JX_PLG_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
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
                        'form_group_class' => 'rollover-type gallery-type slideshow-type',
                        'type' => 'select',
                        'label' => $this->l('Type'),
                        'name' => 'JX_PLG_TYPE',
                        'desc' => $this->l('Possible Values: Rollover, Gallery, Slideshow'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'rollover',
                                    'name' => $this->l('rollover')),
                                array(
                                    'id' => 'gallery',
                                    'name' => $this->l('gallery')),
                                array(
                                    'id' => 'slideshow',
                                    'name' => $this->l('slideshow'))
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'form_group_class' => 'rollover-type',
                        'class' => 'fixed-width-md',
                        'type' => 'select',
                        'name' => 'JX_PLG_ROLLOVER_ANIMATION',
                        'label' => $this->l('Animation type'),
                        'desc' => $this->l('Animation type variants'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'opacity',
                                    'name' => $this->l('opacity slide')),
                                array(
                                    'id' => 'horizontal_slide',
                                    'name' => $this->l('horizontal slide')),
                                array(
                                    'id' => 'vertical_slide',
                                    'name' => $this->l('vertical slide'))
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'form_group_class' => 'gallery-type slideshow-type',
                        'class' => 'fixed-width-xs',
                        'type' => 'text',
                        'name' => 'JX_PLG_DISPLAY_ITEMS',
                        'label' => $this->l('Display items')
                    ),
                    array(
                        'form_group_class' => 'slideshow-type',
                        'type' => 'switch',
                        'label' => $this->l('Use pager'),
                        'name' => 'JX_PLG_USE_PAGER',
                        'is_bool' => true,
                        'desc' => $this->l('Use pager in slide show'),
                        'class' => 'my-class',
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
                        'form_group_class' => 'slideshow-type',
                        'type' => 'switch',
                        'label' => $this->l('Use controls'),
                        'name' => 'JX_PLG_USE_CONTROLS',
                        'is_bool' => true,
                        'desc' => $this->l('Use controls in slide show'),
                        'class' => 'my-class',
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
                        'form_group_class' => 'gallery-type',
                        'type' => 'text',
                        'label' => $this->l('Visible items in row'),
                        'name' => 'JX_PLG_CAROUSEL_NB',
                        'class' => 'fixed-width-xs'
                    ),
                    array(
                        'form_group_class' => 'gallery-type',
                        'type' => 'switch',
                        'label' => $this->l('Use carousel'),
                        'name' => 'JX_PLG_USE_CAROUSEL',
                        'is_bool' => true,
                        'desc' => $this->l('Use carousel for this block'),
                        'class' => 'my-class',
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
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Fill the module form values
     * @return array
     */
    protected function getConfigFormValues()
    {
        $filled_settings = array();
        $settings = $this->getModuleSettings();

        foreach (array_keys($settings) as $name) {
            $filled_settings[$name] = Configuration::get($name);
        }

        return $filled_settings;
    }

    /**
     * Update Configuration values
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Get configuration field data type, because return only string
     * @param $string value from configuration table
     *
     * @return string data type (int|bool|float|string)
     */
    protected function getStringValueType($string)
    {
        if (Validate::isInt($string)) {
            return 'int';
        } elseif (Validate::isFloat($string)) {
            return 'float';
        } elseif (Validate::isBool($string)) {
            return 'bool';
        } else {
            return 'string';
        }
    }

    protected function getGallerySettings()
    {
        $settings = $this->getModuleSettings();
        $get_settings = array();
        foreach (array_keys($settings) as $name) {
            $data = Configuration::get($name);
            $get_settings[$name] = array('value' => $data, 'type' => $this->getStringValueType($data));
        }

        return $get_settings;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/jxproductlistgallery_admin.js');
        }
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->requireAssets(array('font-awesome'));
        $this->context->controller->registerJavascript('module-jxproductlistgallery', 'modules/' .$this->name. '/views/js/jxproductlistgallery.js');
        $this->context->controller->registerStylesheet('module-jxproductlistgallery', 'modules/' .$this->name. '/views/css/jxproductlistgallery.css');

        if (!$this->isCached('jxproductlistgallery-header.tpl', $this->getCacheId())) {
            $this->context->smarty->assign('settings', $this->getGallerySettings());
        }

        return $this->display($this->_path, '/views/templates/hook/jxproductlistgallery-header.tpl', $this->getCacheId());
    }

    public function hookActionProductAdd()
    {
        $this->clearCache();
    }

    public function hookActionProductSave()
    {
        $this->clearCache();
    }

    public function hookActionProductUpdate()
    {
        $this->clearCache();
    }

    public function hookActionProductDelete()
    {
        $this->clearCache();
    }

    protected function clearCache()
    {
        $this->_clearCache('jxproductlistgallery.tpl');
        $this->_clearCache('jxproductlistgallery-header.tpl');
    }

    public function getCacheId($id_product = null)
    {
        return parent::getCacheId().'|'.(int)$id_product;
    }

    protected function getSmartySettings()
    {
        return array(
            'st_display' => Configuration::get('JX_PLG_LIVE_MODE'),
            'st_visible' => Configuration::get('JX_PLG_CAROUSEL_NB'),
            'st_type' => Configuration::get('JX_PLG_TYPE'),
            'st_rollover_animation' => Configuration::get('JX_PLG_ROLLOVER_ANIMATION'),
            'st_nb_items' => Configuration::get('JX_PLG_DISPLAY_ITEMS'),
            'st_gall_carousel' => Configuration::get('JX_PLG_USE_CAROUSEL'),
            'st_slider_pager' => Configuration::get('JX_PLG_USE_PAGER'),
            'st_slider_controls' => Configuration::get('JX_PLG_USE_CONTROLS'),
        );
    }

    public function hookDisplayProductListGallery($params)
    {
        $id_product = (int)$params['product']['id_product'];

        if (!$this->isCached('jxproductlistgallery.tpl', $this->getCacheId($id_product))) {
            $this->smarty->assign(array(
                'product' => $params['product'],
                'settings' => $this->getGallerySettings(),
                'smarty_settings' => $this->getSmartySettings()
            ));
        }
        return $this->display(__FILE__, 'views/templates/hook/jxproductlistgallery.tpl', $this->getCacheId($id_product));
    }
}
