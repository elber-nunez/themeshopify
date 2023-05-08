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
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once(_PS_MODULE_DIR_.'jxcategoryproducts/src/CategoryProducts.php');
include_once(_PS_MODULE_DIR_.'jxcategoryproducts/src/JXCategoryProductsRepository.php');

class Jxcategoryproducts extends Module implements WidgetInterface
{
    protected $ssl = 'http://';
    public  $repository;
    private $defaultHook;
    private $currentHook;
    private $hooks = false;

    public function __construct()
    {
        $this->name = 'jxcategoryproducts';
        $this->tab = 'front_office_features';
        $this->version = '2.0.6';
        $this->author = 'Zemez';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '79d1fb476ec1f172e5d27aef1e759021';
        parent::__construct();
        $this->displayName = $this->l('JX Category Products');
        $this->description = $this->l('This module displays category products on homepage');
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->id_shop = Context::getContext()->shop->id;
        if (Configuration::get('PS_SSL_ENABLED')) {
            $this->ssl = 'https://';
        }
        $this->carousel_def = array(
            'carousel_nb'           => 4,
            'carousel_slide_margin' => 20,
            'carousel_auto'         => false,
            'carousel_item_scroll'  => 1,
            'carousel_speed'        => 500,
            'carousel_auto_pause'   => 3000,
            'carousel_loop'         => true,
            'carousel_hide_control' => true,
            'carousel_pager'        => false,
            'carousel_control'      => false
        );
        $this->languages = Language::getLanguages(false);
        $this->defaultHook = 'displayHome';
        $this->currentHook = $this->defaultHook;
        $this->repository = new JXCategoryProductsRepository(
            Db::getInstance(),
            $this->context->shop,
            $this->context->language
        );
        $h = $this->repository->getAllModuleHooks($this->name, $this->defaultHook);
        if (is_array($h)) {
            $this->hooks = $h;
        } elseif ($h) {
            $this->currentHook = $h;
        }
    }

    public function install()
    {
        return parent::install()
        && $this->repository->createTables()
        && $this->registerHook('header')
        && $this->registerHook('backOfficeHeader')
        && $this->registerHook('displayHome')
        && $this->registerHook('actionCategoryDelete')
        && $this->registerHook('actionProductDelete')
        && $this->registerHook('actionProductUpdate')
        && $this->registerHook('actionCategoryUpdate')
        && $this->registerHook('actionProductAdd')
        && $this->registerHook('displayBeforeBodyClosingTag')
        && $this->createAjaxController();
    }

    public function uninstall()
    {
        return $this->clearCache()
        && $this->repository->dropTables()
        && $this->removeAjaxContoller()
        && parent::uninstall();
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'jxcategoryproducts';
            }
        }
        $tab->class_name = 'AdminJXCategoryProducts';
        $tab->module = $this->name;
        $tab->id_parent = -1;

        return (bool)$tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminJXCategoryProducts')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }

        return true;
    }

    public function getContent()
    {
        if ($currentHook = Tools::getValue('hookName')) {
            $this->currentHook = $currentHook;
        }
        $content = $this->getPageContent();
        $this->getErrors();
        $this->getWarnings();
        $this->getConfirmations();

        return $content;
    }

    public function getErrors()
    {
        $this->context->controller->errors = $this->_errors;
    }

    public function getConfirmations()
    {
        $this->context->controller->confirmations = $this->_confirmations;
    }

    protected function getWarnings()
    {
        $this->context->controller->warnings = $this->warning;
    }

    protected function getPageContent($content = '')
    {
        if ($this->hooks) {
            $content .= $this->renderHooksForm($this->hooks);
        }
        if (Tools::getValue('configure') == $this->name) {
            if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
                $this->_errors = $this->l('You cannot add/edit elements from a "All Shops" or a "Group Shop" context');

                return false;
            } elseif ($this->id_shop != Tools::getValue('id_shop')) {
                $token = Tools::getAdminTokenLite('AdminModules');
                $current_index = AdminController::$currentIndex;
                Tools::redirectAdmin(
                    $current_index.'&configure='.$this->name.'&token='.$token.'&shopselected&id_shop='.$this->id_shop.($this->hooks ? '&hookName='.$this->currentHook : '')
                );
            } elseif ((bool)Tools::isSubmit('deletecategoryproducts_blocks')) {
                $this->deleteItem();

                return $content.$this->renderBlocksList();
            } elseif ((bool)Tools::isSubmit('addblock') || (bool)Tools::isSubmit('updatecategoryproducts_blocks')) {
                return $this->renderBlockForm();
            } elseif ((bool)Tools::isSubmit('saveblock')) {
                $this->validateBlockFields();
                if (count($this->_errors) == 0) {
                    $this->saveBlock();

                    return $content.$this->renderBlocksList();
                } else {
                    return $this->renderBlockForm();
                }
            } elseif ((bool)Tools::isSubmit('statuscategoryproducts_blocks')) {
                $this->updateStatus();

                return $content.$this->renderBlocksList();
            } else {
                return $content.$this->renderBlocksList();
            }
        }

        return true;
    }

    protected function renderHooksForm($availableHooks, array $hooks = [])
    {
        foreach ($availableHooks as $hook) {
            $hooks[] = ['id' => $hook['name'], 'name' => $hook['name']];
        }
        $fields_form = array(
            'form' => array(
                'input'   => array(
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Select the hook to set up'),
                        'name'    => 'hookName',
                        'options' => array(
                            'query' => $hooks,
                            'id'    => 'id',
                            'name'  => 'name'
                        )
                    ),
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getHooksFormValues(), /* Add values for your inputs */
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getHooksFormValues()
    {
        return array('hookName' => $this->currentHook);
    }

    protected function renderBlocksList()
    {
        $fields_values = $this->getConfigBlockListValues();
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_tab';
        $helper->actions = array('edit', 'delete');
        $helper->table = 'categoryproducts_blocks';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Block list');
        $helper->listTotal = count($fields_values);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&id_shop='.$this->id_shop.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addblock&token='.Tools::getAdminTokenLite(
                    'AdminModules'
                ).'&id_shop='.$this->id_shop.($this->hooks ? '&hookName='.$this->currentHook : ''),
            'desc' => $this->l('Add new')
        );

        return $helper->generateList($fields_values, $this->getConfigblockList());
    }

    protected function getConfigBlockList()
    {
        return array(
            'id_tab' => array(
                'title'   => ($this->l('Tab id')),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false
            ),
            'name'   => array(
                'title'   => ($this->l('Block category')),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false
            ),
            'active' => array(
                'title'   => $this->l('Status'),
                'type'    => 'bool',
                'align'   => 'center',
                'active'  => 'status',
                'search'  => false,
                'orderby' => false
            )
        );
    }

    protected function getConfigBlockListValues()
    {
        $blocks = $this->repository->getAllItems($this->currentHook);
        if (isset($blocks) && count($blocks) > 0) {
            foreach ($blocks as $key => $block) {
                $category = new Category($block['category'], $this->context->language->id, $this->id_shop);
                $blocks[$key]['name'] = $category->name;
            }

            return $blocks;
        }

        return array();
    }

    protected function renderBlockForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&saveblock'.'&id_shop='.$this->id_shop.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigBlockFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigBlockForm()));
    }

    protected function getConfigBlockForm()
    {
        return array(
            'form' => array(
                'legend'  => array(
                    'title' => ((int)Tools::getValue('id_tab')
                        ? $this->l('Update block')
                        : $this->l('Add block')),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    array(
                        'col'   => 2,
                        'type'  => 'text',
                        'name'  => 'id_tab',
                        'class' => 'hidden'
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Status'),
                        'name'    => 'active',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Select category'),
                        'name'    => 'category',
                        'options' => array(
                            'query' => $this->getCategoriesList(),
                            'id'    => 'id',
                            'name'  => 'name'
                        )
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use name in front'),
                        'name'    => 'use_name',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'         => 'text',
                        'label'        => $this->l('Name'),
                        'name'         => 'name',
                        'col'          => 6,
                        'autoload_rte' => true,
                        'lang'         => true
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Select products to display'),
                        'name'    => 'select_products',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'select_product_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'select_product_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'  => 'product_list',
                        'name'  => 'selected_products',
                        'label' => $this->l('Products to display:'),
                        'col'   => 3
                    ),
                    array(
                        'col'      => 2,
                        'type'     => 'text',
                        'desc'     => $this->l('Number of products to display'),
                        'name'     => 'num',
                        'label'    => $this->l('Number of products to display'),
                        'col'      => 3,
                        'required' => true
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use carousel'),
                        'name'    => 'use_carousel',
                        'is_bool' => true,
                        'desc'    => $this->l('Use carousel for this block'),
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Visible items'),
                        'name'  => 'carousel_nb',
                        'class' => 'carousel-settings',
                        'col'   => 3
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Items scroll'),
                        'name'  => 'carousel_item_scroll',
                        'class' => 'carousel-settings',
                        'col'   => 3
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Slide Margin'),
                        'name'  => 'carousel_slide_margin',
                        'class' => 'carousel-settings',
                        'col'   => 3
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Auto scroll'),
                        'name'   => 'carousel_auto',
                        'class'  => 'carousel-settings',
                        'desc'   => $this->l('Use auto scroll in carousel.'),
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Carousel speed'),
                        'name'  => 'carousel_speed',
                        'class' => 'carousel-settings',
                        'desc'  => 'Slide transition duration (in ms)',
                        'col'   => 3
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Pause'),
                        'name'  => 'carousel_auto_pause',
                        'class' => 'carousel-settings',
                        'desc'  => 'The amount of time (in ms) between each auto transition',
                        'col'   => 3
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Carousel loop'),
                        'name'   => 'carousel_loop',
                        'desc'   => $this->l('Show the first slide after the last slide has been reached.'),
                        'class'  => 'carousel-settings',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Hide control at the end'),
                        'name'   => 'carousel_hide_control',
                        'desc'   => $this->l('Control will be hidden on the last slide.'),
                        'class'  => 'carousel-settings',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Pager'),
                        'name'   => 'carousel_pager',
                        'desc'   => $this->l('Pager settings.'),
                        'class'  => 'carousel-settings',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type'   => 'switch',
                        'label'  => $this->l('Control'),
                        'name'   => 'carousel_control',
                        'desc'   => $this->l('Prev/Next buttons.'),
                        'class'  => 'carousel-settings',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col'   => 2,
                        'type'  => 'text',
                        'name'  => 'sort_order',
                        'class' => 'hidden'
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                    'type'  => 'submit',
                    'name'  => 'saveblock'
                ),
                'buttons' => array(
                    array(
                        'href'  => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite(
                                'AdminModules'
                            ).'&id_shop='.$this->id_shop.($this->hooks ? '&hookName='.$this->currentHook : ''),
                        'title' => $this->l('Cancle'),
                        'icon'  => 'process-icon-cancel'
                    )
                )
            )
        );
    }

    protected function getConfigBlockFormValues()
    {
        if (Tools::getValue('configure') == $this->name) {
            if ((int)Tools::getValue('id_tab') > 0) {
                $block = new CategoryProducts((int)Tools::getValue('id_tab'));
                $carousel_settings = $this->getCarouselSettings($block->carousel_settings);
            } else {
                $block = new CategoryProducts();
                $carousel_settings = $this->carousel_def;
            }
            $name = array();
            foreach ($this->languages as $lang) {
                $name[$lang['id_lang']] = Tools::getValue('name_'.$lang['id_lang'], $block->name[$lang['id_lang']]);
            }

            return array_merge(
                array(
                    'id_tab'            => Tools::getValue('id_tab'),
                    'category'          => Tools::getValue('category', $block->category),
                    'num'               => Tools::getValue('num', $block->num),
                    'active'            => Tools::getValue('active', $block->active),
                    'select_products'   => Tools::getValue('select_products', $block->select_products),
                    'use_name'          => Tools::getValue('active', $block->use_name),
                    'name'              => $name,
                    'sort_order'        => Tools::getValue('sort_order', $block->sort_order),
                    'selected_products' => array(
                        'json'     => Tools::getValue('selected_products', $block->selected_products),
                        'products' => $this->getProductsConfig(
                            json_decode(Tools::getValue('selected_products', $block->selected_products))
                        )
                    ),
                    'use_carousel'      => Tools::getValue('use_carousel', $block->use_carousel),
                ), $carousel_settings
            );
        }

        return true;
    }

    protected function saveBlock()
    {
        if (Tools::getValue('configure') == $this->name) {
            if ((int)Tools::getValue('id_tab') > 0) {
                $block = new CategoryProducts((int)Tools::getValue('id_tab'));
            } else {
                $block = new CategoryProducts();
            }
            $max_sort_order = $this->repository->getMaxSortOrder($this->currentHook);
            if (!is_numeric($max_sort_order[0]['sort_order'])) {
                $max_sort_order = 0;
            } else {
                $max_sort_order = $max_sort_order[0]['sort_order'] + 1;
            }
            $block->category = Tools::getValue('category');
            $block->hook_name = $this->currentHook;
            $block->num = Tools::getValue('num');
            $block->active = Tools::getValue('active', $block->active);
            $block->id_shop = $this->id_shop;
            $block->select_products = Tools::getValue('select_products', $block->select_products);
            $block->selected_products = Tools::getValue('selected_products', $block->selected_products);
            $block->use_carousel = Tools::getValue('use_carousel', $block->use_carousel);
            $block->carousel_settings = $this->getCarouselSettings($block->carousel_settings, true);
            $block->sort_order = Tools::getValue('sort_order', $max_sort_order);
            $block->use_name = Tools::getValue('use_name', $block->use_name);
            foreach ($this->languages as $lang) {
                if (!Tools::isEmpty(Tools::getValue('name_'.$lang['id_lang']))) {
                    $block->name[$lang['id_lang']] = Tools::getValue('name_'.$lang['id_lang']);
                } else {
                    $block->name[$lang['id_lang']] = Tools::getValue('name_'.$this->context->language->id);
                }
            }
            if ($block->select_products) {
                $block->num = 0;
            }
            if (!$block->use_carousel) {
                $block->carousel_settings = json_encode($this->carousel_def);
            }
            if (!$block->save()) {
                $this->_errors[] = $this->l('Can\'t save the tab');
            } else {
                $this->_confirmations[] = $this->l('Block saved');
            }
            $this->clearCache();
        }

        return true;
    }

    protected function getCarouselSettings($carousel_settings_json, $json = false)
    {
        $carousel_settings = json_decode($carousel_settings_json, true);
        $settings = array(
            'carousel_nb'           => Tools::getValue('carousel_nb', $carousel_settings['carousel_nb']),
            'carousel_slide_margin' => Tools::getValue(
                'carousel_slide_margin', $carousel_settings['carousel_slide_margin']
            ),
            'carousel_auto'         => Tools::getValue('carousel_auto', $carousel_settings['carousel_auto']),
            'carousel_item_scroll'  => Tools::getValue(
                'carousel_item_scroll', $carousel_settings['carousel_item_scroll']
            ),
            'carousel_speed'        => Tools::getValue('carousel_speed', $carousel_settings['carousel_speed']),
            'carousel_auto_pause'   => Tools::getValue(
                'carousel_auto_pause', $carousel_settings['carousel_auto_pause']
            ),
            'carousel_loop'         => Tools::getValue('carousel_loop', $carousel_settings['carousel_loop']),
            'carousel_hide_control' => Tools::getValue(
                'carousel_hide_control', $carousel_settings['carousel_hide_control']
            ),
            'carousel_pager'        => Tools::getValue('carousel_pager', $carousel_settings['carousel_pager']),
            'carousel_control'      => Tools::getValue('carousel_control', $carousel_settings['carousel_control'])
        );
        if ($json) {
            return json_encode($settings);
        }

        return $settings;
    }

    public function deleteItem()
    {
        if (Tools::getValue('configure') == $this->name) {
            $tab = new CategoryProducts((int)Tools::getValue('id_tab'));
            if (!$tab->delete()) {
                $this->_errors[] = $this->l('Can\'t delete item');
            } else {
                $this->_confirmations[] = $this->l('Item deleted');
            }
            $this->clearCache();
        }

        return true;
    }

    public function updateStatus()
    {
        $item = new CategoryProducts(Tools::getValue('id_tab'));
        if (!$item->toggleStatus()) {
            $this->_errors[] = $this->l('Item status can\'t be updated');
        } else {
            $this->_confirmations[] = $this->l('Item status updated');
        }
        $this->clearCache();
    }

    protected function validateBlockFields()
    {
        if (!Tools::getValue('select_products')) {
            if (!ValidateCore::isUnsignedInt(Tools::getValue('num'))) {
                $this->_errors[] = $this->l('Bad \'Number of products to display\' field value');
            }
        }
        if (Tools::getValue('use_name')) {
            foreach ($this->languages as $lang) {
                if (!Tools::isEmpty(Tools::getValue('name_'.$lang['id_lang']))) {
                    if (!Validate::isName(Tools::getValue('name_'.$lang['id_lang']))) {
                        $this->_errors[] = $this->l('Bad name format');
                    }
                } else {
                    $this->_errors[] = $this->l('Name is empty').' ('.$lang['name'].')';
                }
            }
        }
        if (Tools::getValue('use_carousel')) {
            if (!ValidateCore::isUnsignedInt(Tools::getValue('carousel_nb'))) {
                $this->_errors[] = $this->l('Bad \'Visible items\' field value');
            }
            if (!ValidateCore::isUnsignedInt(Tools::getValue('carousel_item_scroll'))) {
                $this->_errors[] = $this->l('Bad \'Items scroll\' field value');
            }
            if (!ValidateCore::isUnsignedInt(Tools::getValue('carousel_slide_margin'))) {
                $this->_errors[] = $this->l('Bad \'Slide Margin\' field value');
            }
            if (!ValidateCore::isUnsignedInt(Tools::getValue('carousel_speed'))) {
                $this->_errors[] = $this->l('Bad \'Carousel speed\' field value');
            }
            if (!ValidateCore::isUnsignedInt(Tools::getValue('carousel_auto_pause'))) {
                $this->_errors[] = $this->l('Bad \'Pause\' field value');
            }
        }
    }

    protected function getCategoriesList()
    {
        $category = new Category();

        return $this->generateCategoriesOption(
            $category->getNestedCategories((int)Configuration::get('PS_HOME_CATEGORY'), $this->context->language->id, false), array()
        );
    }

    protected function getImageLink($id_product, $id_image, $image_type, $productObj = null)
    {
        $link = new Link();
        if ($productObj == null) {
            $productObj = new Product($id_product, true, $this->context->language->id);
        }
        if (!$result = $this->ssl.$link->getImageLink(
                $productObj->link_rewrite, $id_product.'-'.$id_image, ImageType::getFormattedName($image_type)
            )
        ) {
            return false;
        }

        return $result;
    }

    protected function getCoverImageLink($id_product, $image_type)
    {
        $result = null;
        $product = new Product($id_product, true, $this->context->language->id);
        if (!$result = $product->getCover($id_product)) {
            return false;
        } else {
            if (!$result = $this->getImageLink($id_product, $result['id_image'], $image_type, $product)) {
                return false;
            }
        }

        return $result;
    }

    protected function generateCategoriesOption($categories, $list)
    {
        foreach ($categories as $category) {
            array_push(
                $list,
                array(
                    'id'   => (int)$category['id_category'],
                    'name' => str_repeat('&nbsp;', 5 * (int)$category['level_depth']).$category['name']
                )
            );
            if (isset($category['children']) && !empty($category['children'])) {
                $list = $this->generateCategoriesOption($category['children'], $list);
            }
        }

        return $list;
    }

    public function getProducts($id_category)
    {
        $products_list = array();
        $category = new Category((int)$id_category, $this->context->language->id, $this->id_shop);
        $products_ids = $category->getProductsWs();
        foreach ($products_ids as $key => $product_id) {
            $products_list = array_merge($products_list, $this->getProduct($product_id['id']));
        }

        return $products_list;
    }

    protected function getProduct($id_product)
    {
        $product_list = array();
        $product = new Product($id_product, true, $this->context->language->id, $this->id_shop);
        $product_list[$id_product]['id_product'] = $product->id;
        $product_list[$id_product]['name'] = $product->name;
        $product_list[$id_product]['image'] = $this->getCoverImageLink($product->id, 'small');

        return $product_list;
    }

    protected function getProductsConfig($products_ids)
    {
        if (isset($products_ids) && count($products_ids) > 0) {
            $products_list = array();
            foreach ($products_ids as $key => $product_id) {
                $products_list = array_merge($products_list, $this->getProduct($product_id));
            }

            return $products_list;
        }

        return array();
    }

    protected function deleteProduct($id_product)
    {
        $categories = $this->repository->getAllItems();
        foreach ($categories as $category) {
            $products = json_decode($category['selected_products'], true);
            if (isset($products) && count($products) > 0) {
                if (is_numeric($id = array_search($id_product, $products))) {
                    unset($products[$id]);
                    $category_obj = new CategoryProducts($category['id_tab']);
                    $category_obj->selected_products = json_encode($products);
                    $category_obj->save();
                }
            }
        }
    }

    public function renderProductList($products)
    {
        $this->context->smarty->assign(
            array(
                'products' => $products
            )
        );

        return $this->display($this->_path, '/views/templates/admin/product_list.tpl');
    }

    protected function buildTemplateProduct($products)
    {
        $template_products = [];
        foreach ($products as $product) {
            $product = (new ProductAssembler($this->context))
                ->assembleProduct($product);
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
            $template_products[] = $presenter->present(
                $presentationSettings,
                $product,
                $this->context->language
            );
        }

        return $template_products;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }
        Media::addJsDefL('jxcp_theme_url', $this->context->link->getAdminLink('AdminJXCategoryProducts'));
        Media::addJsDefL('jxcp_category_warning', $this->l('All selected products will cleared'));
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJS($this->_path.'/views/js/jxcategoryproducts_admin.js');
        $this->context->controller->addCSS($this->_path.'/views/css/jxcategoryproducts_admin.css');
    }

    /**
     * Clean smarty cache
     */
    public function clearCache()
    {
        if ($this->hooks) {
            foreach ($this->hooks as $hook) {
                parent::_clearCache($this->name.'.tpl', $this->name.'_'.Tools::strtolower($hook['name']));
            }
        } else {
            parent::_clearCache($this->name.'.tpl', $this->name.'_'.Tools::strtolower($this->defaultHook));
        }

        return true;
    }

    public function hookHeader()
    {
        $this->context->controller->registerJavascript('swiper', 'modules/' .$this->name. '/views/js/swiper.min.js', ['media' => 'all', 'priority' => 10]);
        $this->context->controller->registerStylesheet('swiper', 'modules/' .$this->name. '/views/css/swiper.min.css', ['media' => 'all', 'priority' => 10]);
        $this->context->controller->registerStylesheet('module-jxcategoryproducts', 'modules/'.$this->name.'/views/css/jxcategoryproducts.css');
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $result = array();

        $blocks = $this->repository->getAllItems($hookName, true);

        if ((bool)$blocks) {
            foreach ($blocks as $key => $block) {
                $products = array();
                $category = new Category((int)$block['category'], $this->context->language->id, $this->id_shop);
                $result[$key]['id'] = $category->id;
                $result[$key]['id_tab'] = $block['id_tab'];
                $result[$key]['hook_name'] = Tools::strtolower($block['hook_name']);
                $result[$key]['name'] = $category->name;
                $result[$key]['use_carousel'] = $block['use_carousel'];
                $result[$key]['carousel_settings'] = json_decode($block['carousel_settings'], true);
                if ((bool)$block['use_name']) {
                    $result[$key]['name'] = $block['name'];
                }
                if ((bool)$block['select_products']) {
                    $productsList = json_decode($block['selected_products'], true);
                    if ($productsList) {
                        foreach ($productsList as $keys=>$item) {
                            $products[$keys]['id_product'] = $item;
                        }
                    }
                } else {
                    $products = $category->getProducts(
                        (int)$this->context->language->id, 1, (int)$block['num'], 'date_add', 'ASC'
                    );
                }
                $result[$key]['products'] = $this->buildTemplateProduct($products);
            }
        }

        return [
            'blocks' => $result
        ];
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $templatePath = 'views/templates/hook/'.$this->name.'.tpl';
        if ($this->getTemplatePath('views/templates/hook/'.Tools::strtolower($hookName).'/'.$this->name.'.tpl')) {
            $templatePath = 'views/templates/hook/'.Tools::strtolower($hookName).'/'.$this->name.'.tpl';
        }

        $cacheName = $this->name.'_'.Tools::strtolower($hookName);
        if (!$this->isCached($this->name.'.tpl', $this->getCacheId($cacheName))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            $this->smarty->assign('hook_class', Tools::strtolower($hookName));
        }

        return $this->display(__FILE__, $templatePath, $this->getCacheId($cacheName));
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        $block_settings = $this->getWidgetVariables();
        $this->context->smarty->assign('block_settings', $block_settings['blocks']);

        return $this->display($this->_path, '/views/templates/hook/jxcategoryproducts-script.tpl');
    }

    public function hookActionCategoryDelete($config)
    {
        $this->repository->deleteByCategory($config['category']->id_category);
        $this->clearCache();
    }

    public function hookActionProductDelete($config)
    {
        $this->deleteProduct($config['product']->id);
        $this->clearCache();
    }

    public function hookActionProductUpdate()
    {
        $this->clearCache();
    }

    public function hookActionProductAdd()
    {
        $this->clearCache();
    }

    public function hookActionCategoryUpdate()
    {
        $this->clearCache();
    }
}
