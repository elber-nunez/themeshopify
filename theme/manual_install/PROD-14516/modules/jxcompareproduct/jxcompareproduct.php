<?php
/**
* 2017-2018 Zemez
*
* JX Compare Product
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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class Jxcompareproduct extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'jxcompareproduct';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->bootstrap = true;
        $this->author = 'Zemez';
        parent::__construct();
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->id_shop = Context::getContext()->shop->id;
        $this->displayName = $this->l('JX Compare Product');
        $this->description = $this->l('Display compare product.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    protected function getConfigurations()
    {
        $configurations = array(
            'JX_COMPARE_NUMBER_PRODUCT' => 2,
            'JX_COMPARE_DISPLAY_FOOTER' => true,
            'JX_COMPARE_DISPLAY_HEADER' => false,
        );

        return $configurations;
    }

    public function install()
    {
        $this->clearCache();
        $configurations = $this->getConfigurations();

        foreach ($configurations as $name => $config) {
            Configuration::updateValue($name, $config);
        }

        return parent::install() &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('displayFooter') &&
        $this->registerHook('displayNav2') &&
        $this->registerHook('moduleRoutes') &&
        $this->registerHook('productActions') &&
        $this->registerHook('displayProductAdditionalInfo') &&
        $this->registerHook('displayProductListReviews') &&
        $this->registerHook('displayProductListFunctionalButtons');
    }

    public function uninstall()
    {
        $configurations = $this->getConfigurations();

        foreach (array_keys($configurations) as $config) {
            Configuration::deleteByName($config);
        }

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        $result = '';

        if ((bool)Tools::isSubmit('submitSettings')) {
            if (!$result = $this->preValidateForm()) {
                $output .= $this->postProcess();
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Save all settings.'));
            } else {
                $output = $result;
                $output .= $this->renderTabForm();
            }
        }

        if (!$result) {
            $output .= $this->renderTabForm();
        }

        return $output;
    }

    protected function renderTabForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->l('Count product:'),
                        'name' => 'JX_COMPARE_NUMBER_PRODUCT',
                        'col' => 2
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display in footer'),
                        'name' => 'JX_COMPARE_DISPLAY_FOOTER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display in header info'),
                        'name' => 'JX_COMPARE_DISPLAY_HEADER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $fields = array();
        $configurations = $this->getConfigurations();

        foreach (array_keys($configurations) as $config) {
            $fields[$config] = Configuration::get($config);
        }

        return $fields;
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFieldsValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function preValidateForm()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('JX_COMPARE_NUMBER_PRODUCT'))) {
            $errors[] = $this->l('Count product is required.');
        } else {
            if (!Validate::isUnsignedInt(Tools::getValue('JX_COMPARE_NUMBER_PRODUCT'))) {
                $errors[] = $this->l('Bad count product format');
            }
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->requireAssets(array('font-awesome'));
        if (Configuration::get('JX_COMPARE_DISPLAY_FOOTER') || Configuration::get('JX_COMPARE_DISPLAY_HEADER')) {
            $this->context->controller->addJS($this->_path.'/views/js/jxcompareproduct.js');
            $this->context->controller->addCSS($this->_path.'/views/css/jxcompareproduct.css');
        }
    }

    public function getPreview($id_products)
    {
        if ($id_products) {
            $products = $this->getProducts($id_products);
            $this->context->smarty->assign(array(
                'products' => $products
            ));
        } else {
            $this->context->smarty->assign(array(
                'products' => false
            ));
        }

        return $this->fetch('module:'.$this->name.'/views/templates/front/preview.tpl');
    }

    public function getPreviewProduct($id_product)
    {
        if ($id_product) {
            $this->context->smarty->assign(array(
                'product' => $this->getProduct($id_product)
            ));
        }

        return $this->fetch('module:'.$this->name.'/views/templates/front/preview-product.tpl');
    }

    public function getModal($id_products)
    {
        if ($id_products) {
            $products = $this->getProducts($id_products);
            $features_fields = $this->buildFeaturesFields($products);
            $features_fields_values = $this->buildFeaturesFieldsValues($products, $features_fields);
            $this->context->smarty->assign(array(
                'products' => $products,
                'features_fields_value' => $features_fields_values
            ));
        } else {
            $this->context->smarty->assign(array(
                'products' => false
            ));
        }

        return $this->fetch('module:'.$this->name.'/views/templates/front/modal.tpl');
    }

    public function getProduct($id_product)
    {
        $image = new Image();
        $product = (new ProductAssembler($this->context))->assembleProduct(array('id_product' => $id_product));
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever($this->context->link),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $products['info'] = $presenter->present($presentationSettings, $product, $this->context->language);
        $products['image'] = $image->getCover($id_product);

        return $products;
    }

    public function getProducts($id_products)
    {
        foreach ($id_products as $key => $product) {
            $image = new Image();
            $product = (new ProductAssembler($this->context))->assembleProduct(array('id_product' => $product));
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever($this->context->link),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );
            $products[$key]['info'] = $presenter->present($presentationSettings, $product, $this->context->language);
            $products[$key]['image'] = $image->getCover($product);
        }

        return $products;
    }

    /**
     * Get all features fields related to any of the products
     *
     * @param $products array of products
     *
     * @return array
     */
    public function buildFeaturesFields($products)
    {
        $result = [];
        foreach ($products as $product) {
            if ($features = $product['info']['embedded_attributes']['features']) {
                foreach ($features as $feature) {
                    if (!in_array($feature['name'], $result)) {
                        $result[]= $feature['name'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fill all fields depend on products info, if product doesn't have this feature leave it empty
     *
     * @param $products array list of the products
     * @param $fields array list of the fields
     *
     * @return array
     */
    public function buildFeaturesFieldsValues($products, $fields)
    {
        $result = [];
        foreach ($fields as $field) {
            foreach ($products as $product) {
                $result[$product['info']['id_product']][$field] = '';
                foreach ($product['info']['embedded_attributes']['features'] as $feature) {
                    if (array_search($field, $feature)) {
                        $result[$product['info']['id_product']][$field] = $feature['value'];
                    }
                }
            }
        }

        return $result;
    }

    protected function clearCache()
    {
        $this->_clearCache('jxcompareproduct.tpl');
        $this->_clearCache('jxcompareproduct-footer.tpl');
    }

    public function hookDisplayProductListFunctionalButtons($params)
    {
        if (Configuration::get('JX_COMPARE_DISPLAY_FOOTER') || Configuration::get('JX_COMPARE_DISPLAY_HEADER')) {
            if (!$this->isCached('jxcompareproduct.tpl', $this->getCacheId($params['product']['id_product']))) {
                $this->context->smarty->assign('id_product', $params['product']['id_product']);
            }

            return $this->display($this->_path, '/views/templates/hook/jxcompareproduct.tpl', $this->getCacheId($params['product']['id_product']));
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->hookDisplayProductListFunctionalButtons($params);
    }

    public function hookDisplayProductListReviews($params)
    {
        return $this->hookDisplayProductListFunctionalButtons($params);
    }

    public function hookProductActions($params)
    {
        return $this->hookDisplayProductListFunctionalButtons($params);
    }

    public function hookDisplayFooter()
    {
        if (Configuration::get('JX_COMPARE_DISPLAY_FOOTER')) {
            $this->context->smarty->assign('jxcompareproduct_url', $this->context->link->getModuleLink('jxcompareproduct', 'ajax', array(), true));
            $this->context->smarty->assign('jxcompareproduct_max', Configuration::get('JX_COMPARE_NUMBER_PRODUCT'));
            return $this->display($this->_path, '/views/templates/hook/jxcompareproduct-footer.tpl', $this->getCacheId());
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $this->context->smarty->assign('jxcompareproduct_url', $this->context->link->getModuleLink('jxcompareproduct', 'ajax', array(), true));
        $this->context->smarty->assign('jxcompareproduct_max', Configuration::get('JX_COMPARE_NUMBER_PRODUCT'));
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (Configuration::get('JX_COMPARE_DISPLAY_HEADER')) {
            $this->getWidgetVariables($hookName, $configuration);

            return $this->fetch('module:'.$this->name.'/views/templates/hook/jxcompareproduct-header.tpl');
        }
    }
}
