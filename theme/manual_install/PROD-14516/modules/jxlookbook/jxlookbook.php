<?php
/**
 * 2017-2018 Zemez
 *
 * JX Look Book
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
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface as WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

require(_PS_MODULE_DIR_ . 'jxlookbook/src/repositories/JXLookBookRepository.php');
require(_PS_MODULE_DIR_ . 'jxlookbook/src/entities/JXLookBookCollectionEntity.php');
require(_PS_MODULE_DIR_ . 'jxlookbook/src/entities/JXLookBookTabEntity.php');
require(_PS_MODULE_DIR_ . 'jxlookbook/src/entities/JXLookBookHotSpotEntity.php');
require(_PS_MODULE_DIR_ . 'jxlookbook/src/entities/JXLookBookHookEntity.php');

/**
 * Class Jxlookbook
 */
class Jxlookbook extends Module implements WidgetInterface
{
    /**
     * @var JXLookBookRepository
     */
    public $repository;
    /**
     * @var string
     */
    protected $ssl = 'http://';
    /**
     * @var array
     */
    protected $langs = array();
    /**
     * @var
     */
    protected $id_shop;
    /**
     * @var
     */
    protected $id_lang;
    /**
     * @var array|bool|false|mysqli_result|null|PDOStatement|resource
     */
    private $hooks = array();

    /**
     * Jxlookbook constructor.
     */
    public function __construct()
    {
        $this->name = 'jxlookbook';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Zemez';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('JX Look Book');
        $this->description = $this->l('Add lookbook to your shop');
        $this->ps_versions_compliancy = array(
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        );

        $this->repository = new JXLookBookRepository(
            Db::getInstance(),
            $this->context->shop,
            $this->context->language
        );

        $this->hooks = $this->repository->getFrontHooksList($this->name);

        $this->controllers = array(
            'collections',
            'pages'
        );

        if (Configuration::get('PS_SSL_ENABLED')) {
            $this->ssl = 'https://';
        }

        $this->id_shop = $this->context->shop->id;
        $this->id_lang = $this->context->language->id;
        $this->langs = Language::getLanguages();
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install() &&
        $this->repository->createTables() &&
        $this->installAjaxController() &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('header') &&
        $this->registerHook('displayHome') &&
        $this->registerHook('displayTopColumn') &&
        $this->registerHook('displayProductButtons') &&
        $this->registerHook('displayRightColumnProduct') &&
        $this->registerHook('actionProductDelete') &&
        $this->registerHook('actionProductUpdate') &&
        $this->registerHook('displayBeforeBodyClosingTag') &&
        $this->registerHook('actionObjectLanguageAddAfter');
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall() &&
        $this->repository->dropTables() &&
        $this->uninstallAjaxController();
    }

    /**
     * @return bool
     */
    protected function installAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;

        if (is_array($this->langs)) {
            foreach ($this->langs as $lang) {
                $tab->name[$lang['id_lang']] = $this->name;
            }
        }

        $tab->class_name = 'AdminJxLookBook';
        $tab->module = $this->name;
        $tab->id_parent = -1;

        return (bool)$tab->add();
    }

    /**
     * @return bool
     */
    protected function uninstallAjaxController()
    {
        if ($id_tab = (int)Tab::getIdFromClassName('AdminJXLookBook')) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        return true;
    }

    /**
     *
     */
    public function getErrors()
    {
        $this->context->controller->errors = $this->_errors;
    }

    /**
     *
     */
    public function getConfirmations()
    {
        $this->context->controller->confirmations = $this->_confirmations;
    }

    /**
     *
     */
    protected function getWarnings()
    {
        $this->context->controller->warnings = $this->warning;
    }

    /**
     *
     */
    protected function getMessages()
    {
        $this->getErrors();
        $this->getWarnings();
        $this->getConfirmations();
    }

    /**
     * @return bool
     */
    public function getContent()
    {
        $content = $this->renderContent();
        $this->getMessages();

        return $content;
    }

    /**
     * @return bool
     */
    protected function renderContent()
    {
        if ($this->checkModulePage()) {
            if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
                $this->_errors[] = $this->l('You cannot add/edit elements from a "All Shops" or a "Group Shop" context');
                return false;
            } elseif (Tools::isSubmit('addcollection') || Tools::isSubmit('updatecollection')) {
                return $this->renderCollectionForm();
            } elseif (Tools::isSubmit('savecollection')) {
                if ($this->saveCollection()) {
                    return $this->renderCollectionsList() . $this->renderHooksPanel();
                } else {
                    return $this->renderCollectionForm();
                }
            } elseif (Tools::isSubmit('deletecollection')) {
                $this->deleteCollection();

                return $this->renderCollectionsList() . $this->renderHooksPanel();
            } elseif (Tools::isSubmit('statuscollection')) {
                $this->updateCollectionStatus();

                return $this->renderCollectionsList() . $this->renderHooksPanel();
            } elseif (Tools::isSubmit('deletehook')) {
                $this->deleteHook();

                return $this->renderCollectionsList() . $this->renderHooksPanel();
            } elseif (Tools::isSubmit('statushook')) {
                $this->updateHookStatus();

                return $this->renderCollectionsList() . $this->renderHooksPanel();
            } elseif (Tools::isSubmit('statustab')) {
                $this->updateTabStatus();

                return $this->renderTabsList();
            } elseif (Tools::isSubmit('viewcollection')) {
                return $this->renderTabsList();
            } elseif (Tools::isSubmit('addtab') || Tools::isSubmit('updatetab')) {
                return $this->renderTabForm();
            } else if (Tools::isSubmit('savetab') || Tools::isSubmit('savetabstay')) {
                $this->validateTabFields();
                if (count($this->_errors) > 0) {
                    return $this->renderTabForm();
                } else {
                    $id_tab = $this->saveTab();
                    if (Tools::isSubmit('savetabstay')) {
                        $token = Tools::getAdminTokenLite('AdminModules');
                        $current_index = AdminController::$currentIndex;
                        Tools::redirectAdmin($current_index . '&configure=' . $this->name . '&token=' . $token . '&updatetab&id_tab=' . $id_tab . '&id_shop=' . $this->id_shop);
                    }
                    return $this->renderTabsList();
                }
            } elseif (Tools::isSubmit('addhook') || Tools::isSubmit('updatehook')) {
                return $this->renderHookForm();
            } elseif (Tools::isSubmit('savehook')) {
                $this->saveHook();

                return $this->renderCollectionsList() . $this->renderHooksPanel();
            } else {
                return $this->renderCollectionsList() . $this->renderHooksPanel();
            }
        }
    }

    /**
     * @return mixed
     */
    protected function renderHooksPanel()
    {
        $this->context->smarty->assign(array(
            'tabs' => $this->getTabPanelValues()
        ));

        return $this->display($this->_path, 'views/templates/admin/tab-panel.tpl');
    }

    /**
     * @return array
     */
    protected function getTabPanelValues()
    {
        $tabs = $this->hooks;
        foreach ($this->hooks as $key => $tab) {
            $tabs[$key]['content'] = $this->renderHookList($tab['name']);
        }

        return $tabs;
    }

    /**
     * @param $hookName
     *
     * @return mixed
     */
    protected function renderCollectionsList()
    {
        $values = $this->getConfigCollectionsListValues();
        $configs = $this->getConfigCollectionsList();

        $helper = new HelperList();
        $helper->simple_header = false;
        $helper->identifier = 'id_collection';
        $helper->shopLinkType = '';
        $helper->actions = array(
            'view',
            'edit',
            'delete'
        );

        $helper->table = 'collection';
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Collections');
        $helper->listTotal = count($values);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . "&configure={$this->name}&id_shop={$this->id_shop}";

        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . "&configure={$this->name}&addcollection&token="
                . Tools::getAdminTokenLite('AdminModules') . "&id_shop={$this->id_shop}",
            'desc' => $this->l('Add new')
        );

        $this->context->smarty->assign(array(
            'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__
        ));

        return $helper->generateList($values, $configs);
    }

    /**
     * Get configs fo collection list
     *
     * @return array
     */
    protected function getConfigCollectionsList()
    {
        return array(
            'id_collection' => array(
                'title' => $this->l('Collection id'),
                'type' => 'text',
                'class' => 'hidden',
                'search' => false,
                'orderby' => false
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'type' => 'image',
                'search' => false,
                'orderby' => false
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'sort_order' => array(
                'title' => $this->l('Position'),
                'type' => 'text',
                'class' => 'pointer dragHandle',
                'search' => false,
                'orderby' => false
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'align' => 'center',
                'search' => false,
                'orderby' => false
            )
        );
    }

    /**
     * @param $hookName Name of the hook
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    protected function getConfigCollectionsListValues()
    {
        if ($collections = $this->repository->getCollections()) {
            return $collections;
        }

        return array();
    }

    protected function renderHookList($hookName)
    {
        $values = $this->getConfigHooksListValues($hookName);
        $configs = $this->getConfigHooksList();

        $helper = new HelperList();
        $helper->simple_header = false;
        $helper->identifier = 'id';
        $helper->shopLinkType = '';
        $helper->actions = array(
            'edit',
            'delete'
        );

        $helper->table = 'hook';
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Hooks');
        $helper->listTotal = count($values);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . "&configure={$this->name}&id_shop={$this->id_shop}&hook_name=$hookName";

        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . "&configure={$this->name}&addhook&token="
                . Tools::getAdminTokenLite('AdminModules') . "&id_shop={$this->id_shop}&hook_name=$hookName",
            'desc' => $this->l('Add new')
        );

        $this->context->smarty->assign(array(
            'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__
        ));

        return $helper->generateList($values, $configs);
    }

    protected function getConfigHooksList()
    {
        return array(
            'id' => array(
                'title' => $this->l('Collection id'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'class' => 'hidden'
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'search' => false,
                'orderby' => false,
                'type' => 'image'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'sort_order' => array(
                'title' => $this->l('Position'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'class' => 'pointer dragHandle'
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'align' => 'center',
                'active' => 'status',
                'search' => false,
                'orderby' => false,
            )
        );
    }

    protected function getConfigHooksListValues($hookName)
    {
        if ($hooks = $this->repository->getHooks($hookName)) {
            return $hooks;
        }
        return array();
    }

    protected function renderHookForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->default_form_language = $this->id_lang;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = 'id';
        $helper->currentIndex = $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . "&configure={$this->name}&id_shop={$this->id_shop}&savehook";

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigHookFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($this->getConfigHookForm()));
    }

    protected function getConfigHookForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => ((int)Tools::getValue('id_tab') ? $this->l('Update block') : $this->l('Add block')),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'id',
                        'class' => 'hidden'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status:'),
                        'name' => 'active',
                        'is_bool' => true,
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
                        )
                    ),
                    array(
                        'col' => 9,
                        'label' => $this->l('Type:'),
                        'type' => 'select',
                        'name' => 'type',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_type' => 1,
                                    'name' => $this->l('Page'),
                                ),
                                array(
                                    'id_type' => 2,
                                    'name' => $this->l('Page inner')
                                )
                            ),
                            'id' => 'id_type',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'label' => $this->l('Page:'),
                        'type' => 'select',
                        'name' => 'id_collection',
                        'options' => array(
                            'query' => $this->repository->getCollections(),
                            'id' => 'id_collection',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'sort_order',
                        'class' => 'hidden'
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'hook_name',
                        'class' => 'hidden'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'type' => 'submit',
                    'name' => 'savehook'
                ),
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&id_collection=' . Tools::getValue('id_collection') . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&id_shop=' . $this->id_shop,
                        'title' => $this->l('Cancle'),
                        'icon' => 'process-icon-cancel'
                    )
                )
            )
        );
    }

    protected function createHookObject()
    {
        if ($id_collection = (int)Tools::getValue('id')) {
            return new JXLookBookHookEntity($id_collection);
        }

        return new JXLookBookHookEntity();
    }

    protected function getConfigHookFormValues()
    {
        $hook = $this->createHookObject();
        return array(
            'id' => Tools::getValue('id', $hook->id),
            'id_collection' => Tools::getValue('id_page', $hook->id_collection),
            'type' => Tools::getValue('type', $hook->type),
            'hook_name' => Tools::getValue('hook_name', $hook->hook_name),
            'active' => Tools::getValue('active', $hook->active),
            'sort_order' => Tools::getValue('sort_order', $this->getHookMaxSortOrder($hook, Tools::getValue('hook_name', $hook->hook_name)))
        );
    }

    /**
     * @return mixed
     */
    protected function renderCollectionForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->default_form_language = $this->id_lang;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = 'id_collection';
        $helper->currentIndex = $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . "&configure={$this->name}&id_shop={$this->id_shop}&savecollection";

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigCollectionFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($this->getConfigCollectionForm()));
    }

    /**
     * @return array
     */
    protected function getConfigCollectionForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => ((int)Tools::getValue('id_collection')
                        ? $this->l('Update collection')
                        : $this->l('Add collection')),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'id_collection',
                        'class' => 'hidden'
                    ),
                    array(
                        'label' => $this->l('Status'),
                        'type' => 'switch',
                        'name' => 'active',
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
                        'label' => $this->l('Name'),
                        'type' => 'text',
                        'name' => 'name',
                        'lang' => true,
                        'required' => true,
                        'col' => 4
                    ),
                    array(
                        'label' => $this->l('Description'),
                        'type' => 'textarea',
                        'name' => 'description',
                        'lang' => true,
                        'autoload_rte' => true,
                        'required' => true,
                        'col' => 8
                    ),
                    array(
                        'label' => $this->l('Image'),
                        'type' => 'filemanager_image',
                        'name' => 'image',
                        'required' => true,
                        'col' => 6,
                        'class' => 'collection-image',
                    ),
                    array(
                        'label' => $this->l('Template'),
                        'type' => 'button',
                        'name' => 'template',
                        'required' => true,
                        'class' => 'select-template',
                        'btn_text' => $this->l('Select template')
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'sort_order',
                        'class' => 'hidden'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'type' => 'submit',
                    'name' => 'savecollection'
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel',
                        'href' => AdminController::$currentIndex . "&configure={$this->name}&token=" .
                            Tools::getAdminTokenLite('AdminModules') . "&id_shop={$this->id_shop}",
                    )
                )
            )
        );
    }

    /**
     * @return array
     */
    protected function getConfigCollectionFormValues()
    {
        $collection = $this->createCollectionObject();

        $name = array();
        $description = array();

        foreach ($this->langs as $lang) {
            $name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang'], $collection->name[$lang['id_lang']]);
            $description[$lang['id_lang']] = Tools::getValue('description_' . $lang['id_lang'], $collection->description[$lang['id_lang']]);
        }

        if (!Tools::getValue('image')) {
            $image_url = $collection->image;
        } else {
            $base_url = explode('://', _PS_BASE_URL_);
            $image_url = explode(str_replace('www.', '', $base_url[1]) . __PS_BASE_URI__, Tools::getValue('image'))[1];
        }

        return array(
            'id_collection' => Tools::getValue('id_collection', $collection->id_collection),
            'active' => Tools::getValue('active', $collection->active),
            'sort_order' => Tools::getValue('sort_order', $this->getMaxSortOrder($collection, '', array(
                'key' => 'id_shop',
                'value' => $this->id_shop
            ))),
            'image' => $image_url,
            'name' => $name,
            'description' => $description,
            'template' => Tools::getValue('template', $collection->template),
        );
    }

    protected function renderTabsList()
    {
        $filed_values = $this->getConfigTabsListValues();
        $configs = $this->getConfigTabsList();

        $helper = new HelperList();

        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_tab';
        $helper->actions = array(
            'edit',
            'delete'
        );
        $helper->table = 'tab';
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->title = $this->l('Tabs');
        $helper->listTotal = count($filed_values);
        $helper->token = Tools::getAdminTokenLite(('AdminModules'));
        $helper->currentIndex = AdminController::$currentIndex . "&configure={$this->name}&id_shop={$this->id_shop}&id_collection=" . Tools::getValue('id_collection');
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . "&configure={$this->name}&addtab&id_shop={$this->id_shop}&token=" . Tools::getAdminTokenLite('AdminModules') . '&id_collection=' . Tools::getValue('id_collection'),
            'desc' => $this->l('Add new')
        );

        $helper->toolbar_btn['back'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&id_shop=' . $this->id_shop,
            'desc' => $this->l('Back to main page')
        );

        $helper->context->smarty->assign(array(
            'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__
        ));

        return $helper->generateList($filed_values, $configs);
    }

    protected function getConfigTabsList()
    {
        return array(
            'id_tab' => array(
                'title' => '',
                'type' => 'text',
                'class' => 'hidden',
                'search' => false,
                'orderby' => false
            ),
            'id_collection' => array(
                'title' => '',
                'type' => 'text',
                'class' => 'hidden',
                'search' => false,
                'orderby' => false
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'type' => 'image',
                'search' => false,
                'orderby' => false
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'sort_order' => array(
                'title' => $this->l('Position'),
                'type' => 'text',
                'class' => 'pointer dragHandle',
                'search' => false,
                'orderby' => false
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'align' => 'center',
                'active' => 'status',
                'search' => false,
                'orderby' => false
            )
        );
    }

    protected function getConfigTabsListValues()
    {
        if ($tabs = $this->repository->getTabs(Tools::getValue('id_collection'))) {
            return $tabs;
        }

        return array();
    }

    protected function renderTabForm()
    {
        $helper = new HelperForm();

        $helper->module = $this;
        $helper->default_form_language = $this->id_lang;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = 'id_tab';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . "&configure={$this->name}&savetab&id_shop={$this->id_shop}";
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigTabFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_lang' => $this->id_lang
        );

        return $helper->generateForm(array(
            $this->getConfigTabForm($helper->tpl_vars)
        ));
    }

    protected function getConfigTabForm($tpl_vars)
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => ((int)Tools::getValue('id_tab')
                        ? $this->l('Update lookbook')
                        : $this->l('Add lookbook')),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'id_collection',
                        'class' => 'hidden'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Status'),
                        'name' => 'active',
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
                        'col' => 4,
                        'label' => $this->l('Name'),
                        'type' => 'text',
                        'name' => 'name',
                        'lang' => true,
                        'required' => true
                    ),
                    array(
                        'col' => 8,
                        'label' => $this->l('Description'),
                        'type' => 'textarea',
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                        'required' => true
                    ),
                    array(
                        'type' => 'filemanager_image',
                        'label' => $this->l('Image'),
                        'name' => 'image',
                        'col' => 6,
                        'required' => true,
                        'class' => 'hotspot'
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'hotspots',
                        'class' => 'hidden'
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'sort_order',
                        'class' => 'hidden'
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'id_tab',
                        'class' => 'hidden'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'type' => 'submit',
                    'name' => 'savetab'
                ),
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&viewcollection&id_collection=' . $tpl_vars['fields_value']['id_collection'] . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&id_shop=' . $this->id_shop,
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel'
                    ),
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'savetabstay',
                        'id_collection' => Tools::getValue('id_collection')
                    )
                )
            )
        );
    }

    protected function getConfigTabFormValues()
    {
        $tab = $this->createTabObject();

        $name = array();
        $description = array();
        foreach ($this->langs as $lang) {
            $name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang'], $tab->name[$lang['id_lang']]);
            $description[$lang['id_lang']] = Tools::getValue('description_' . $lang['id_lang'], $tab->description[$lang['id_lang']]);
        }

        if (!$id_tab = Tools::getValue('id_tab', $tab->id_tab)) {
            $hotspots = array();
        } else {
            $hotspots = $this->repository->getHotSpots($id_tab);
        }

        if (!Tools::getValue('image')) {
            $image_url = $tab->image;
        } else {
            $base_url = explode('://', _PS_BASE_URL_);
            $image_url = explode(str_replace('www.', '', $base_url[1]) . __PS_BASE_URI__, Tools::getValue('image'))[1];
        }

        return array(
            'id_tab' => Tools::getValue('id_tab', $tab->id_tab),
            'id_collection' => Tools::getValue('id_collection', $tab->id_collection),
            'active' => Tools::getValue('active', $tab->active),
            'sort_order' => Tools::getValue('sort_order', $this->getMaxSortOrder($tab, '_tab', array(
                'key' => 'id_collection',
                'value' => Tools::getValue('id_collection', $tab->id_collection)
            ))),
            'image' => $image_url,
            'hotspots' => json_encode($hotspots),
            'name' => $name,
            'description' => $description
        );
    }

    public function renderHotSpotForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&savehotspot' . '&id_shop=' . $this->id_shop;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigHopSpotFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigHotSpotForm()));
    }

    protected function getConfigHotSpotForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => ((int)Tools::getValue('id_spot')
                        ? $this->l('Update hot spot')
                        : $this->l('Add hot spot')),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'id_spot',
                        'class' => 'hidden'
                    ),
                    array(
                        'col' => 9,
                        'label' => $this->l('Type:'),
                        'type' => 'select',
                        'name' => 'type',
                        'options' => array(
                            'query' => array(#form-collection  tbody  tr  td.dragHandle, #form-tab  tbody  tr
                                array(
                                    'id_type' => 1,
                                    'name' => $this->l('Product'),
                                ),
                                array(
                                    'id_type' => 2,
                                    'name' => $this->l('Content')
                                )
                            ),
                            'id' => 'id_type',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 9,
                        'label' => $this->l('Name'),
                        'type' => 'text',
                        'name' => 'spot_name',
                        'lang' => true,
                        'class' => 'point-name',
                        'required' => true
                    ),
                    array(
                        'col' => 9,
                        'label' => $this->l('Description'),
                        'type' => 'textarea',
                        'name' => 'spot_description',
                        'autoload_rte' => true,
                        'lang' => true,
                        'class' => 'point-description',
                        'required' => true
                    ),
                    array(
                        'type' => 'button',
                        'name' => 'id_product',
                        'label' => $this->l('Product:'),
                        'btn_text' => $this->l('Select Product'),
                        'class' => 'point-product',
                        'required' => true
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'id_tab',
                        'class' => 'hidden'
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'name' => 'coordinates',
                        'class' => 'hidden'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'type' => 'submit',
                    'name' => 'savehotspot'
                ),
            )
        );
    }

    protected function getConfigHopSpotFormValues()
    {
        //Get hotspot object
        $hotspot = $this->createHotSpotObject();

        $coordinates = Tools::getValue('coordinates');
        if (is_numeric($hotspot->id_spot)) {
            $coordinates = $hotspot->coordinates;
        }
        $name = array();
        $description = array();
        foreach ($this->langs as $lang) {
            $name[$lang['id_lang']] = Tools::getValue('spot_name_' . $lang['id_lang'], $hotspot->name[$lang['id_lang']]);
            $description[$lang['id_lang']] = Tools::getValue('spot_description_' . $lang['id_lang'], $hotspot->description[$lang['id_lang']]);
        }

        $id_product = Tools::getValue('id_produt', $hotspot->id_product);
        $product = new Product($id_product, true, $this->context->language->id);

        return array(
            'block_type' => 'hotspot',
            'type' => Tools::getValue('type', $hotspot->type),
            'id_spot' => Tools::getValue('id_spot', $hotspot->id_spot),
            'id_tab' => Tools::getValue('id_tab', $hotspot->id_tab),
            'coordinates' => $coordinates,
            'id_product' => $id_product,
            'product_name' => $product->name,
            'product_image' => $this->getCoverImageLink($id_product, 'small'),
            'spot_name' => $name,
            'spot_description' => $description
        );
    }

    /**
     * @return JXLookBookCollectionEntity
     */
    protected function createCollectionObject()
    {
        if ($id_collection = (int)Tools::getValue('id_collection')) {
            return new JXLookBookCollectionEntity($id_collection);
        }

        return new JXLookBookCollectionEntity();
    }

    protected function createTabObject()
    {
        if ($id_tab = Tools::getValue('id_tab')) {
            return new JXLookBookTabEntity($id_tab);
        }

        return new JXLookBookTabEntity();
    }

    protected function createHotSpotObject()
    {
        if ($id_spot = Tools::getValue('id_spot')) {
            return new JXLookBookHotSpotEntity($id_spot);
        }

        return new JXLookBookHotSpotEntity();
    }

    /**
     * @return false|ObjectModel
     */
    protected function duplicateCollectionObject()
    {
        $collection = $this->createCollectionObject();

        if (!$collection->id) {
            return false;
        }

        return $collection->duplicateObject();
    }

    protected function duplicateTabObject($id_tab)
    {
        $tab = new JXLookBookTabEntity($id_tab);

        return $tab->duplicateObject();
    }

    protected function duplicateHotSpotObject($id_spot)
    {
        $tab = new JXLookBookHotSpotEntity($id_spot);

        return $tab->duplicateObject();
    }

    /**
     * @param $object
     * @param string $suffix
     * @return array|false|int|mysqli_result|null|PDOStatement|resource
     */
    protected function getMaxSortOrder($object, $suffix = '', $where = false)
    {
        if (!$object->id) {
            $max_sort_order = $this->repository->getMaxSortOrder($suffix, $where);
            if (!$max_sort_order[0]['sort_order']) {
                $max_sort_order = 1;
            } else {
                $max_sort_order = $max_sort_order[0]['sort_order'] + 1;
            }

            return $max_sort_order;
        }

        return $object->sort_order;
    }

    protected function getHookMaxSortOrder($object, $hookName)
    {
        if (!$object->id) {
            $max_sort_order = $this->repository->getHookMaxSortOrder($hookName);
            if (!$max_sort_order[0]['sort_order']) {
                $max_sort_order = 1;
            } else {
                $max_sort_order = $max_sort_order[0]['sort_order'] + 1;
            }

            return $max_sort_order;
        }

        return $object->sort_order;
    }

    /**
     * @return bool
     */
    protected function saveCollection()
    {
        if (!$this->validateCollectionFields()) {
            return false;
        }

        $collection = $this->createCollectionObject();

        $collection->active = (bool)Tools::getValue('active', $collection->active);
        $collection->sort_order = Tools::getValue('sort_order', $this->getMaxSortOrder($collection, '', array(
            'key' => 'id_shop',
            'value' => $this->id_shop
        )));
        $collection->id_shop = Tools::getValue('id_shop', $collection->id_shop);
        $base_url = explode('://', _PS_BASE_URL_);
        $image_url = explode(str_replace('www.', '', $base_url[1]) . __PS_BASE_URI__, Tools::getValue('image', $collection->image));
        $collection->image = $image_url[1];
        $collection->template = Tools::getValue('template', $collection->template);

        foreach ($this->langs as $lang) {
            if (!Tools::isEmpty(Tools::getValue('name_' . $lang['id_lang']))) {
                $collection->name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang']);
            } else {
                $collection->name[$lang['id_lang']] = Tools::getValue('name_' . $this->id_lang);
            }

            if (!Tools::isEmpty(Tools::getValue('description_' . $lang['id_lang']))) {
                $collection->description[$lang['id_lang']] = Tools::getValue('description_' . $lang['id_lang']);
            } else {
                $collection->description[$lang['id_lang']] = Tools::getValue('description_' . $this->id_lang);
            }
        }

        if (!(bool)$collection->save()) {
            $this->_errors[] = $this->l('Can\'t save collection.');

            return false;
        }

        $this->_confirmations[] = $this->l('Collection saved.');

        return true;
    }

    protected function saveTab()
    {
        $tab = $this->createTabObject();

        $tab->active = Tools::getValue('active', $tab->active);
        $tab->sort_order = Tools::getValue('sort_order', $this->getMaxSortOrder($tab, '_tab', array(
            'key' => 'id_collection',
            'value' => Tools::getValue('id_collection', $tab->id_collection)
        )));
        $tab->id_collection = Tools::getValue('id_collection', $tab->id_collection);
        $base_url = explode('://', _PS_BASE_URL_);
        $image_url = explode(str_replace('www.', '', $base_url[1]) . __PS_BASE_URI__, Tools::getValue('image', $tab->image));
        $tab->image = $image_url[1];

        foreach ($this->langs as $lang) {
            if (!Tools::isEmpty(Tools::getValue('name_' . $lang['id_lang']))) {
                $tab->name[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang']);
            } else {
                $tab->name[$lang['id_lang']] = Tools::getValue('name_' . $this->context->language->id);
            }

            if (!Tools::isEmpty(Tools::getValue('description_' . $lang['id_lang']))) {
                $tab->description[$lang['id_lang']] = Tools::getValue('description_' . $lang['id_lang']);
            } else {
                $tab->description[$lang['id_lang']] = Tools::getValue('description_' . $this->context->language->id);
            }
        }
        if (!$tab->save()) {
            $this->_errors = $this->l('Can\'t save lookbook.');

            return false;
        }
        $this->_confirmations = $this->l('Lookbook saved.');

        return $tab->id;
    }

    public function saveHotSpot()
    {
        $hotspot = $this->createHotSpotObject();

        $hotspot->id_tab = Tools::getValue('id_tab', $hotspot->id_tab);
        $hotspot->coordinates = Tools::getValue('coordinates', $hotspot->coordinates);
        $hotspot->type = Tools::getValue('type', $hotspot->type);

        if ($hotspot->type == 1) {
            $hotspot->id_product = Tools::getValue('id_product', $hotspot->id_product);
            foreach ($this->langs as $lang) {
                $hotspot->name[$lang['id_lang']] = '';
                $hotspot->description[$lang['id_lang']] = '';
            }
        } else {
            $hotspot->id_product = '';
            foreach ($this->langs as $lang) {
                if (!Tools::isEmpty(Tools::getValue('spot_name_' . $lang['id_lang']))) {
                    $hotspot->name[$lang['id_lang']] = Tools::getValue('spot_name_' . $lang['id_lang']);
                } else {
                    $hotspot->name[$lang['id_lang']] = Tools::getValue('spot_name_' . $this->context->language->id);
                }

                if (!Tools::isEmpty(Tools::getValue('spot_description_' . $lang['id_lang']))) {
                    $hotspot->description[$lang['id_lang']] = Tools::getValue('spot_description_' . $lang['id_lang']);
                } else {
                    $hotspot->description[$lang['id_lang']] = Tools::getValue('spot_description_' . $this->context->language->id);
                }
            }
        }

        if (!$hotspot->save()) {
            $this->_errors = $this->l('Can\'t save collection.');

            return false;
        }
        $this->_confirmations = $this->l('Collection saved.');

        return $hotspot->id;
    }

    protected function saveHook()
    {
        $hook = $this->createHookObject();

        $hook->id_shop = Tools::getValue('id_shop', $hook->id_shop);
        $hook->hook_name = Tools::getValue('hook_name', $hook->hook_name);
        $hook->type = Tools::getValue('type', $hook->type);
        $hook->id_collection = Tools::getValue('id_collection', $hook->id_collection);
        $hook->sort_order = Tools::getValue('sort_order', $hook->sort_order);
        $hook->active = Tools::getValue('active', $hook->active);

        if (!$hook->save()) {
            $this->_errors[] = $this->l('Can\'t save hook');
        }

        $this->_confirmations[] = $this->l('Hook saved');
    }

    /**
     * @return bool
     */
    protected function deleteCollection()
    {
        $collection = $this->createCollectionObject();

        if (!Tools::getValue('id_collection') || !$collection->delete()) {
            $this->_errors[] = $this->l('Can\'t delete collection');

            return false;
        }

        $tabs = $this->repository->getTabs((int)Tools::getValue('id_collection'));

        foreach ($tabs as $tab) {
            $tab_obj = new JXLookBookTabEntity($tab['id_tab']);
            if ($tab_obj->delete()) {
                $spots = $this->repository->getHotSpots($tab['id_tab']);
                foreach ($spots as $spot) {
                    $spot_obj = new JXLookBookHotSpotEntity($spot['id_spot']);
                    $spot_obj->delete();
                }
            }
        }

        $this->_confirmations[] = $this->l('Collection deleted');

        return true;
    }

    protected function deleteTab()
    {
        if (Tools::getValue('id_tab')) {
            $tab = new JXLookBookTabs((int)Tools::getValue('id_tab'));
            if ($tab->delete()) {
                $spots = $this->repository->getHotSpots($tab->id_tab);
                foreach ($spots as $spot) {
                    $spot = new JXLookBookHotSpotEntity($spot['id_spot']);
                    $spot->delete();
                }
                $this->_confirmations = $this->l('Lookbook deleted');

                return true;
            }
        }

        $this->_errors = $this->l('Can\'t delete lookbook.');
        return false;
    }

    public function deleteHotSpot()
    {
        if (Tools::getValue('id_spot')) {
            $spot = new JXLookBookHotSpotEntity(Tools::getValue('id_spot'));
            if ($spot->delete()) {
                $this->_confirmations = $this->l('Point deleted');

                return $this->_confirmations;
            }
        }

        $this->_errors = $this->l('Can\'t delete point.');
        return false;
    }

    protected function deleteHook()
    {
        $hook = $this->createHookObject();

        if ($hook->delete()) {
            $this->_confirmations = $this->l('Hook deleted');

            return true;
        }

        $this->_errors = $this->l('Can\'t delete hook.');
    }

    /**
     * @return bool
     */
    protected function updateCollectionStatus()
    {
        if ((bool)Tools::getValue('id_collection')) {
            $collection = $this->createCollectionObject();

            if (!$collection->toggleStatus()) {
                $this->_errors[] = $this->l('Can\'t update collection status');

                return false;
            }
        }

        $this->_confirmations[] = $this->l('Collection status updated');

        return true;
    }

    protected function updateTabStatus()
    {
        if (Tools::getValue('id_tab')) {
            $tab = new JXLookBookTabEntity((int)Tools::getValue('id_tab'));
            if ($tab->toggleStatus()) {
                $this->_confirmations = $this->l('Tab status update.');

                return true;
            }
        }
        $this->_errors = $this->l('Can\'t update tab status.');

        return false;
    }

    protected function updateHookStatus()
    {
        if (Tools::getValue('id')) {
            $hook = new JXLookBookHookEntity((int)Tools::getValue('id'));
            if ($hook->toggleStatus()) {
                $this->_confirmations = $this->l('Hook status update.');

                return true;
            }
        }
        $this->_errors = $this->l('Can\'t update hook status.');

        return false;
    }

    protected function getImageLink($id_product, $id_image, $image_type, $productObj = null)
    {
        $link = new Link();

        if ($productObj == null) {
            $productObj = new Product($id_product, true, $this->context->language->id);
        }

        if (!$result = $this->ssl . $link->getImageLink($productObj->link_rewrite, $id_product . '-' . $id_image, ImageType::getFormattedName($image_type))) {
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

    public function getProducts()
    {
        $products = Product::getProducts($this->context->language->id, 0, 100000, 'id_product', 'ASC');
        $products_list = array();

        foreach ($products as $product) {
            $products_list = array_merge($products_list, $this->getProduct($product['id_product']));
        }

        return $products_list;
    }

    public function getProductsById($products_ids)
    {
        $product_list = array();
        if (count($products_ids) > 0) {
            foreach ($products_ids as $key => $id_product) {
                $product = new Product($id_product, true, $this->context->language->id, $this->id_shop);
                $product_list[$key] = get_object_vars($product);
                $product_list[$key]['id_product'] = $product->id;
                $product_list[$key]['image'] = Product::getCover($product->id);
            }
        }

        return Product::getProductsProperties($this->context->language->id, $product_list);
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
        if (count($products_ids) > 0) {
            $products_list = array();
            foreach ($products_ids as $product_id) {
                $products_list = array_merge($products_list, $this->getProduct($product_id));
            }

            return $products_list;
        }

        return array();
    }

    public function renderProductList($products, $type)
    {
        $this->context->smarty->assign(array(
            'products' => $products,
            'type' => $type
        ));

        return $this->display($this->_path, '/views/templates/admin/product_list.tpl');
    }

    /**
     * @return bool
     */
    protected function validateCollectionFields()
    {
        $this->validateNameField();
        $this->validateDescriptionField();
        $this->validateTemplateField();
        $this->validateImageField();

        if (count($this->_errors) > 0) {
            return false;
        }

        return true;
    }

    protected function validateTabFields()
    {
        $this->validateNameField();
        $this->validateDescriptionField();
        $this->validateImageField();
    }

    public function validateHostSpotFields()
    {
        $errors = array();
        if (Tools::getValue('type') == 1) {
            if (Tools::isEmpty(Tools::getValue('id_product'))) {
                $errors[] = $this->l('Select the product');
            }
        } else if (Tools::getValue('type') == 2) {
            if (Tools::isEmpty(Tools::getValue('spot_description_' . $this->context->language->id))) {
                $errors[] = $this->l('Field `Description` is empty ');
            } else {
                foreach ($this->langs as $lang) {
                    if (!ValidateCore::isCleanHtml(Tools::getValue('spot_description_' . $lang['id_lang']))) {
                        $errors[] = $this->l('Bad format of `Description` field ' . $lang['name']);
                    }
                }
            }

            if (Tools::isEmpty(Tools::getValue('spot_name_' . $this->context->language->id))) {
                $errors[] = $this->l('Field `Name` is empty ');
            } else {
                foreach ($this->langs as $lang) {
                    if (!ValidateCore::isGenericName(Tools::getValue('spot_name_' . $lang['id_lang']))) {
                        $errors[] = $this->l('Bad format of `Name` field ' . $lang['name']);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @return bool
     */
    protected function validateNameField()
    {
        if (Tools::isEmpty(Tools::getValue("name_{$this->id_lang}"))) {
            $this->_errors[] = $this->l('Field `Name` is empty');
            return false;
        } else {
            foreach ($this->langs as $lang) {
                if (!Validate::isGenericName(Tools::getValue("name_{$lang['id_lang']}"))) {
                    $this->_errors[] = $this->l('Bad format of `Name` filed') . $lang['name'];
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateDescriptionField()
    {
        if (Tools::isEmpty(Tools::getValue("description_{$this->id_lang}"))) {
            $this->_errors[] = $this->l('Field `Description` is empty ');
            return false;
        } else {
            foreach ($this->langs as $lang) {
                if (!Validate::isCleanHtml(Tools::getValue("description_{$this->id_lang}"))) {
                    $this->_errors[] = $this->l('Bad format of `Description` field ' . $lang['name']);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateTemplateField()
    {
        if (Tools::isEmpty(Tools::getValue('template'))) {
            $this->_errors[] = $this->l('Select some template');
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateImageField()
    {
        $base_url = explode('://', _PS_BASE_URL_);
        $image_url = explode(str_replace('www.', '', $base_url[1]) . __PS_BASE_URI__, Tools::getValue('image'));

        if (empty($image_url[1])) {
            $this->_errors[] = $this->l('Select the image');
            return false;
        }

        return true;
    }

    /**
     * @param $path
     * @param $ext
     * @return array
     */
    protected function getFilesByExt($path, $ext)
    {
        $files = scandir($path);

        foreach ($files as $key => $file) {
            if (pathinfo($path . $file, PATHINFO_EXTENSION) != $ext) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * @return array
     */
    protected function getCollectionTemplates()
    {
        $templates = array();
        $path = "{$this->local_path}views/templates/front/_templates/";
        $ext = 'tpl';
        $tpls = $this->getFilesByExt($path, $ext);

        foreach ($tpls as $key => $tpl) {
            $name = basename($path . $tpl, '.' . $ext);
            $templates[$key] = array(
                'tpl' => $tpl,
                'name' => $name,
                'img' => "{$this->_path}views/img/pages_templates/{$name}.jpg"
            );
        }

        return $templates;
    }

    /**
     * @return mixed
     */
    public function renderCollectionTemplatesForm()
    {
        $this->context->smarty->assign(
            array(
                'templates' => $this->getCollectionTemplates()
            )
        );

        return $this->display($this->_path, 'views/templates/admin/templates.tpl');
    }

    /**
     * Must be realized
     * @return bool
     */
    protected function checkModulePage()
    {
        if (Tools::getValue('configure') == $this->name) {
            return true;
        }

        return false;
    }

    public function hookModuleRoutes($params)
    {
        return array(
            'jxlookbook_rule' => array(
                'controller' => 'collections',
                'rule' => 'jxlookbook',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'jxlookbook',
                ),
            ),
            'jxlookbookpage_rule' => array(
                'controller' => 'pages',
                'rule' => 'jxlookbook/page/{id_page}',
                'keywords' => array(
                    'id_page' => array('regexp' => '[0-9]+', 'param' => 'id_page'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'jxlookbook'
                ),
            )
        );
    }

    /**
     * @param       $hookName
     * @param array $configuration
     */

    protected function getHookTemplatesPath($hookName, $filename)
    {
        $path = $this->_path.'/views/templates/hook';
        if (!file_exists($path . '/' . $hookName . '/' . $filename)) {
            return '/views/templates/hook/default/' . $filename;
        }

        return '/views/templates/hook/' . $hookName . '/' . $filename;
    }

    public function buildTemplateProduct($products)
    {
        $template_products = array();
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

    public function renderWidget($hookName, array $configuration)
    {
        $this->smarty->assign(
            $this->getWidgetVariables($hookName, $configuration)
        );

        return $this->display(__FILE__, $this->getHookTemplatesPath($hookName, 'hook.tpl'));
    }

    /**
     * @param       $hookName
     * @param array $configuration
     */
    public function getWidgetVariables($hookName, array $configuration)
    {
        $blocks = $this->repository->getHooks($hookName, true);

        foreach ($blocks as $id => $block) {
            if ($block['type'] == 2) {
                $blocks[$id]['tabs'] = $this->repository->getTabs($block['id_collection'], true);
                foreach ($blocks[$id]['tabs'] as $key => $tab) {
                    $products = array();
                    $blocks[$id]['tabs'][$key]['hotspots'] = $this->repository->getHotSpots($tab['id_tab']);
                    if (count($blocks[$id]['tabs'][$key]['hotspots']) > 0) {
                        foreach ($blocks[$id]['tabs'][$key]['hotspots'] as $hotspot_id => $hotspot) {
                            if ($hotspot['type'] == 1) {
                                $products = array_merge($products, $blocks[$id]['tabs'][$key]['hotspots'][$hotspot_id]['product'] = $this->buildTemplateProduct($this->getProductsById(array('0' => $hotspot['id_product']))));
                            }
                        }
                        $blocks[$id]['tabs'][$key]['products'] = $products;
                    }
                }
            }
        }

        return array(
            'collections' => $blocks,
            'jxlb_page_name' => $this->context->controller->php_self,
            'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__
        );
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        if (Tools::getValue('controller') == 'jxlookbook') {
            $id_collection = (int)Tools::getValue('collection');
            $lookbook = new Jxlookbook();

            $tabs = $lookbook->repository->getTabs($id_collection, true);
            foreach ($tabs as $key => $tab) {
                $products = array();
                $tabs[$key]['hotspots'] = $lookbook->repository->getHotSpots($tab['id_tab']);
                if (count($tabs[$key]['hotspots']) > 0) {
                    foreach ($tabs[$key]['hotspots'] as $hotspot_id => $hotspot) {
                        if ($hotspot['type'] == 1) {
                            $products = array_merge($products, $tabs[$key]['hotspots'][$hotspot_id]['product'] = $lookbook->buildTemplateProduct($lookbook->getProductsById(array('0' => $hotspot['id_product']))));
                        }
                    }
                }
                $tabs[$key]['products'] = $products;
            }

            $this->context->smarty->assign(array(
                'tabs' => $tabs
            ));
            return $this->display($this->_path, '/views/templates/hook/default/_templates/jxlookbook-page-script.tpl');
        } else {
            foreach ($this->hooks as $key => $tab) {
                $blocks = $this->repository->getHooks($tab['name'], true);
                foreach ($blocks as $id => $block) {
                    if ($block['type'] == 2) {
                        $blocks[$id]['tabs'] = $this->repository->getTabs($block['id_collection']);
                        foreach ($blocks[$id]['tabs'] as $key => $tab) {
                            $products = array();
                            $blocks[$id]['tabs'][$key]['hotspots'] = $this->repository->getHotSpots($tab['id_tab']);
                            if (count($blocks[$id]['tabs'][$key]['hotspots']) > 0) {
                                foreach ($blocks[$id]['tabs'][$key]['hotspots'] as $hotspot_id => $hotspot) {
                                    if ($hotspot['type'] == 1) {
                                        $products = array_merge($products, $blocks[$id]['tabs'][$key]['hotspots'][$hotspot_id]['product'] = $this->buildTemplateProduct($this->getProductsById(array('0' => $hotspot['id_product']))));
                                    }
                                }
                                $blocks[$id]['tabs'][$key]['products'] = $products;
                            }
                        }
                    }
                }
            }

            $this->context->smarty->assign(array(
                'collections' => $blocks
            ));

            return $this->display($this->_path, '/views/templates/hook/default/_templates/jxlookbook-hook-script.tpl');
        }
    }

    protected function getLookBooksByIdProduct($id_product)
    {
        $tabs = JXLookBookHotSpotEntity::getByProductId($id_product);

        $this->context->smarty->assign(array(
            'tabs' => $tabs
        ));

        return $this->display($this->_path, '/views/templates/front/product-page.tpl');
    }

    protected function addLang($id_lang)
    {
        $hooks = $this->repository->getCollections();
        foreach ($hooks as $hook) {
            JXLookBookCollectionEntity::addLang($id_lang, $hook['id_collection']);
            $tabs = $this->repository->getTabs($hook['id_collection']);
            foreach ($tabs as $tab) {
                JXLookBookTabEntity::addLang($id_lang, $tab['id_tab']);
                $spots = $this->repository->getHotSpots($tab['id_tab']);
                foreach ($spots as $spot) {
                    JXLookBookHotSpotEntity::addLang($id_lang, $spot['id_spot']);
                }
            }
        }
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        $this->addLang($params['object']->id);
    }

    public function hookDisplayProductButtons($config)
    {
        $product = $this->context->controller->getProduct();
        return $this->getLookBooksByIdProduct($product->id);
    }

    public function hookActionProductDelete($config)
    {
        JXLookBookHotSpotEntity::deleteByProductId($config['product']->id);
    }

    public function hookDisplayRightColumnProduct()
    {
        $product = $this->context->controller->getProduct();
        return $this->getLookBooksByIdProduct($product->id);
    }

    public function hookBackOfficeHeader()
    {
        if ($this->checkModulePage()) {
            $this->context->controller->addCSS($this->_path . 'views/css/jxlookbook_admin.css');
            $this->context->controller->addJQuery();
            Media::addJSDefL('jxml_theme_url', $this->context->link->getAdminLink('AdminJXLookBook'));
            $this->context->controller->addJQueryUI(array('ui.sortable', 'ui.draggable'));
            $this->context->controller->addJS($this->_path . 'views/js/jQuery.hotSpot.js');
            $this->context->controller->addJS($this->_path . 'views/js/jxlookbook_admin.js');
        }
    }

    public function hookHeader()
    {
        $this->context->controller->registerJavascript('module-jxlookbook-hotspot', 'modules/'. $this->name . '/views/js/jQuery.hotSpot.js');
        $this->context->controller->registerJavascript('module-jxlookbook', 'modules/'. $this->name . '/views/js/jxlookbook.js');
        $this->context->controller->registerStylesheet('module-jxlookbook', 'modules/'. $this->name . '/views/css/jxlookbook.css');
    }
}
