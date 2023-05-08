<?php
/**
 * 2017-2018 Zemez
 *
 * JX Google Map
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
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

include_once(__DIR__.'/src/JXGoogleMapSettingsRepository.php');
include_once(__DIR__.'/src/JXGoogleMapPresenter.php');
include_once(__DIR__.'/src/StoreContacts.php');

class JxGoogleMap extends Module implements WidgetInterface
{
    public  $jxStoreContacts;
    public  $settings;
    private $defaultHook;
    private $hooks = array();

    public function __construct()
    {
        $this->name = 'jxgooglemap';
        $this->tab = 'front_office_features';
        $this->version = '1.2.5';
        $this->bootstrap = true;
        $this->author = 'Zemez';
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->module_key = '7c1c78e44cfd25b6f5e35a74c59f5bac';
        parent::__construct();
        $this->displayName = $this->l('JX Google Map');
        $this->description = $this->l('Module for displaying your stores on Google map.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->style_path = _PS_MODULE_DIR_.$this->name.'/views/js/styles';
        $this->settings = new JXGoogleMapSettingsRepository(
            Db::getInstance(),
            $this->context->shop,
            $this->context->language,
            Tools::version_compare(_PS_VERSION_, '1.7.3', '>=')
        );
        $this->presenter = new JXGoogleMapPresenter(
            Db::getInstance(),
            $this->context->shop,
            $this->name,
            Tools::version_compare(_PS_VERSION_, '1.7.3', '>=')
        );
        $this->hooks = $this->presenter->getAllModuleHooks();
        $this->defaultHook = $this->setDefaultHook();
    }

    public function install()
    {
        $this->clearCache();

        return parent::install()
        && $this->settings->createTables()
        && $this->registerHook('displayBackOfficeHeader')
        && $this->registerHook('displayHeader')
        && $this->registerHook('actionModuleUnRegisterHookBefore')
        && $this->registerHook('actionModuleRegisterHookAfter')
        && $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        $this->clearCache();

        return parent::uninstall() && $this->settings->dropTables() && $this->clearMarkersFolder();
    }

    protected function setDefaultHook()
    {
        if ($this->hooks && !$this->checkHook('hookHome')) {
            return $this->hooks[0]['name'];
        } else {
            return 'hookHome';
        }
    }

    protected function checkHook($hookName)
    {
        foreach ($this->hooks as $hook) {
            return in_array($hookName, $hook);
        }
    }

    private function clearMarkersFolder()
    {
        $res = true;
        $markers = Tools::scandir(__DIR__.'/img/markers', 'jpg');
        if ($markers) {
            foreach ($markers as $marker) {
                $res &= @unlink(__DIR__.'/img/markers/'.$marker);
            }
        }

        return $res;
    }

    public function hookActionModuleRegisterHookAfter($params)
    {
        if ($params['object']->name != 'jxgooglemap') {
            return;
        }
        // hack for hooks because in prestashop vision it is the same hook but has different names
        if ($params['hook_name'] == 'home') {
            $params['hook_name'] = 'displayHome';
        }
        if ($params['hook_name'] == 'footer') {
            $params['hook_name'] = 'displayFooter';
        }
        if ($params['hook_name'] == 'top') {
            $params['hook_name'] = 'displayTop';
        }
        // avoid setting of wrong hooks during the module reset
        if (!in_array(
            $params['hook_name'],
            array(
                'header',
                'displayHeader',
                'displayBackOfficeHeader',
                'backofficeheader',
                'Header',
                'actionModuleUnRegisterHookBefore',
                'actionModuleRegisterHookAfter'
            )
        )) {
            $this->settings->setDefaultSettings($params['hook_name']);
        }
    }

    public function hookActionModuleUnRegisterHookBefore($params)
    {
        $this->settings->deleteHookSettings($params['hook_name']);
    }

    public function getContent()
    {
        if ($currentHook = Tools::getValue('hookName')) {
            $this->defaultHook = $currentHook;
        }

        $output = '';
        $checker = false;
        if (((bool)Tools::isSubmit('submitJxgooglemapSettingModule')) == true) {
            if (!$errors = $this->validateSettings()) {
                $this->postProcess();
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Settings updated successful.'));
            } else {
                $output .= $errors;
            }
        }
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->displayError($this->l('You cannot add/edit elements from \"All Shops\" or \"Group Shop\".'));
        } else {
            if ((bool)Tools::isSubmit('submitJxgooglemapModule')) {
                if (!$result = $this->preValidateForm()) {
                    $this->clearCache();
                    $output .= $this->addTab();
                } else {
                    $checker = true;
                    $output = $result;
                    $output .= $this->renderForm();
                }
            }
            if ((bool)Tools::isSubmit('statusjxgooglemap')) {
                $output .= $this->updateStatusTab();
            }
            if ((bool)Tools::isSubmit('deletejxgooglemap')) {
                $output .= $this->deleteTab();
            }
            if ((bool)Tools::isSubmit('defaultjxgooglemap')) {
                $output .= $this->changeDefault();
            }
            if (Tools::getIsset('updatejxgooglemap') || Tools::getValue('updatejxgooglemap')) {
                if ($this->context->shop->id != Tools::getValue('id_shop')) {
                    $link_redirect = $this->context->link->getAdminLink(
                            'AdminModules', true
                        ).'&configure='.$this->name.($this->hooks ? '&hookName='.$this->defaultHook : '');
                    Tools::redirectAdmin($link_redirect);
                } else {
                    $output .= $this->renderForm();
                }
            } elseif ((bool)Tools::isSubmit('addjxgooglemap')) {
                $output .= $this->renderForm();
            } elseif (!$checker) {
                $output .= $this->renderFormSettings();
                $output .= $this->renderTabList();
            }
        }

        return $output;
    }

    /**
     * Add tab
     */
    protected function addTab()
    {
        $errors = array();
        if ((int)Tools::getValue('id_tab') > 0) {
            $tab = new StoreContacts((int)Tools::getValue('id_tab'));
        } else {
            $tab = new StoreContacts();
        }
        if ($default = Tools::getValue('default')) {
            if (!$tab->resetDefault($this->defaultHook, (int)$this->context->shop->id)) {
                return $this->displayError($this->l('Can\'t unlink previous default item.'));
            }
        }
        $tab->hook_name = $this->defaultHook;
        $tab->default = (int)Tools::getValue('default');
        $tab->id_store = (int)Tools::getValue('id_store');
        $tab->id_shop = (int)$this->context->shop->id;
        $tab->status = (int)Tools::getValue('status');
        $tab->content = pSql(trim(Tools::getValue('content')));
        if (Tools::isEmpty(Tools::getValue('old_marker'))) {
            $tab->marker = '';
        }
        if (isset($_FILES['marker']) && isset($_FILES['marker']['tmp_name']) && !empty($_FILES['marker']['tmp_name'])) {
            $random_name = Tools::passwdGen();
            if ($error = ImageManager::validateUpload($_FILES['marker'])) {
                $errors[] = $error;
            } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file(
                    $_FILES['marker']['tmp_name'], $tmp_name
                )
            ) {
                return false;
            } elseif (!ImageManager::resize(
                $tmp_name, dirname(__FILE__).'/img/markers/marker-'.$random_name.'-'.(int)$tab->id_shop.'.jpg', 64, 64,
                'png'
            )
            ) {
                $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
            }
            unlink($tmp_name);
            $tab->marker = 'marker-'.$random_name.'-'.(int)$tab->id_shop.'.jpg';
        }
        foreach (Language::getLanguages(false) as $lang) {
            $tab->content[$lang['id_lang']] = Tools::getValue('content_'.$lang['id_lang']);
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
     * Update status tab
     */
    protected function updateStatusTab()
    {
        $tab = new StoreContacts(Tools::getValue('id_tab'));
        if ($tab->status == 1) {
            $tab->status = 0;
        } else {
            $tab->status = 1;
        }
        if (!$tab->update()) {
            return $this->displayError($this->l('The tab status could not be updated.'));
        }
        $this->clearCache();

        return $this->displayConfirmation($this->l('The tab status is successfully updated.'));
    }

    /**
     * Delete tab
     */
    protected function deleteTab()
    {
        $tab = new StoreContacts(Tools::getValue('id_tab'));
        $res = $tab->delete();
        if (!$res) {
            return $this->displayError($this->l('Error occurred when deleting the tab'));
        }
        $this->clearCache();

        return $this->displayConfirmation($this->l('The tab is successfully deleted'));
    }

    /**
     *
     */
    protected function changeDefault()
    {
        $tab = new StoreContacts(Tools::getValue('id_tab'));
        if (!$tab->resetDefault($this->defaultHook, $this->context->shop->id)) {
            return $this->displayError($this->l('Can\'t reset default store'));
        }
        if ($tab->hook_name == $this->defaultHook && $tab->default) {
            $tab->default = 0;
        } else {
            $tab->default = 1;
        }
        if (!$tab->update()) {
            return $this->displayError($this->l('Can\'t save changes'));
        }
        $this->clearCache();

        return $this->displayConfirmation($this->l('Changes are successfully saved'));
    }

    /**
     * Check for item fields validity
     * @return array $errors if invalid or false
     */
    protected function preValidateForm()
    {
        $errors = array();
        $id_store = Tools::getValue('id_store');
        if (!(int)Tools::getValue('id_tab')) {
            if ((bool)$this->settings->getShopByIdStore($this->defaultHook, $id_store)) {
                $errors[] = $this->l('You have this store in google map');
            }
        }
        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
     * Validate filed values
     * @return array|bool errors or false if no errors
     */
    protected function validateSettings()
    {
        $errors = array();
        if (Tools::isEmpty(Tools::getValue('JXGOOGLE_API_KEY'))) {
            $errors[] = $this->l('Enter your Google Map API Key');
        }
        if (!Tools::isEmpty(Tools::getValue('JXGOOGLE_ZOOM'))
            && (!Validate::isInt(Tools::getValue('JXGOOGLE_ZOOM'))
                || Tools::getValue('JXGOOGLE_ZOOM') < 1)
        ) {
            $errors[] = $this->l('"Zoom Level" value error. Only integer numbers are allowed.');
        }
        if (Tools::getValue('JXGOOGLE_ZOOM') < 1 || Tools::getValue('JXGOOGLE_ZOOM') > 17) {
            $errors[] = $this->l('"Zoom Level" value error. Specify initial map zoom level (1 to 17).');
        }
        if ($errors) {
            return $this->displayError(implode('<br />', $errors));
        } else {
            return false;
        }
    }

    /**
     * Create the structure of your form.
     *
     * @param bool $tab
     *
     * @return array $tabs and $fields_list
     */
    public function renderTabList($tab = false)
    {
        if (!$tabs = $this->settings->getTabList($this->defaultHook)) {
            $tabs = array();
        }
        $fields_list = array(
            'id_tab'  => array(
                'title'   => $this->l('Id'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'name'    => array(
                'title'   => $this->l('Store name'),
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
            ),
            'status'  => array(
                'title'   => $this->l('Status'),
                'type'    => 'bool',
                'active'  => 'status',
                'search'  => false,
                'orderby' => false,
            ),
            'default' => array(
                'title'   => $this->l('Default'),
                'type'    => 'bool',
                'active'  => 'default',
                'search'  => false,
                'orderby' => false,
            )
        );
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_tab';
        $helper->table = 'jxgooglemap';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->title = $this->displayName;
        $helper->listTotal = count($tabs);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex
                .'&configure='.$this->name.'&add'.$this->name
                .'&token='.Tools::getAdminTokenLite(
                    'AdminModules'
                ).($this->hooks ? '&hookName='.$this->defaultHook : ''),
            'desc' => $this->l('Add new item')
        );
        $helper->currentIndex = AdminController::$currentIndex
            .'&configure='.$this->name.'&id_shop='.(int)$this->context->shop->id.($this->hooks ? '&hookName='.$this->defaultHook : '');
        $link_store = 2;

        return $helper->generateList($tabs, $fields_list, $link_store);
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Add item'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Select a store'),
                        'class'   => 'id_store',
                        'name'    => 'id_store',
                        'class'   => 'fixed-width-xs',
                        'options' => array(
                            'query' => $this->getStoreList(),
                            'id'    => 'id',
                            'name'  => 'name'
                        )
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Status'),
                        'name'    => 'status',
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
                        )
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Default'),
                        'name'    => 'default',
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
                        )
                    ),
                    array(
                        'type' => 'marker_prev',
                        'name' => 'marker_prev',
                    ),
                    array(
                        'type'  => 'file',
                        'label' => $this->l('Marker'),
                        'name'  => 'marker',
                        'value' => true,
                        'desc'  => $this->l('64px * 64px')
                    ),
                    array(
                        'type'         => 'textarea',
                        'autoload_rte' => true,
                        'label'        => $this->l('Custom text'),
                        'name'         => 'content',
                        'lang'         => true
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'href'  => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite(
                                'AdminModules'
                            ).($this->hooks ? '&hookName='.$this->defaultHook : ''),
                        'title' => $this->l('Back to list'),
                        'icon'  => 'process-icon-back'
                    )
                )
            ),
        );
        if ((bool)Tools::getIsset('updatejxgooglemap') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new StoreContacts((int)Tools::getValue('id_tab'));
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_tab', 'value' => (int)$tab->id);
            $fields_form['form']['marker'] = $tab->marker;
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'old_marker', 'value' => $tab->marker);
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJxgooglemapModule';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.($this->hooks ? '&hookName='.$this->defaultHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
            'marker_url'   => $this->_path.'img/markers/'
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * Set values for the tabs.
     * @return array $fields_values
     */
    protected function getConfigFormValues()
    {
        if ((bool)Tools::getIsset('updatejxgooglemap') && (int)Tools::getValue('id_tab') > 0) {
            $tab = new StoreContacts((int)Tools::getValue('id_tab'));
        } else {
            $tab = new StoreContacts();
        }
        $fields_values = array(
            'id_tab'     => Tools::getValue('id_tab'),
            'hook_name'  => $this->defaultHook,
            'default'    => Tools::getValue('default', $tab->default),
            'id_store'   => Tools::getValue('id_store', $tab->id_store),
            'status'     => Tools::getValue('status', $tab->status),
            'content'    => Tools::getValue('content', $tab->content),
            'marker'     => Tools::getValue('marker', $tab->marker),
            'old_marker' => Tools::getValue('old_marker', $tab->marker)
        );

        return $fields_values;
    }

    /**
     * Get array with id and name store
     * @return array $result
     */
    private function getStoreList()
    {
        $result = array();
        $stores = $this->settings->getStoresListIds();
        if (is_array($stores)) {
            foreach ($stores as $store) {
                array_push($result, array('id' => $store['id_store'], 'name' => $store['name']));
            }
        }

        return $result;
    }

    /**
     * Build the module form
     * @return mixed
     */
    protected function renderFormSettings()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJxgooglemapSettingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.($this->hooks ? '&hookName='.$this->defaultHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'image_path'   => $this->_path.'views/img',
            'fields_value' => $this->getConfigFormValuesSettings(), /* Add values for your inputs */
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Draw the module form
     * @return array
     */
    protected function getConfigForm(array $hooks = [], array $options = [])
    {
        if ($this->hooks) {
            foreach ($this->hooks as $hook) {
                $hooks[] = ['id' => $hook['name'], 'name' => $hook['name']];
            }
        }
        $styles_list = $this->renderFileNames($this->style_path, 'js');
        foreach ($styles_list as $style_type) {
            $options[] = array('id' => str_replace('.js', '', $style_type), 'name' => str_replace(
                '.js', '', str_replace('_', ' ', $style_type)
            ));
        }

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'form_group_class' => count($hooks) == 1 || !$hooks ? 'hidden' : '',
                        'type'             => 'select',
                        'label'            => $this->l('Select the hook to set up'),
                        'name'             => 'hookName',
                        'options'          => array(
                            'query' => $hooks,
                            'id'    => 'id',
                            'name'  => 'name'
                        )
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Google Map API Key'),
                        'name'     => 'JXGOOGLE_API_KEY',
                        'required' => true,
                        'col'      => 4,
                        'desc'     => $this->l('Enter your Google Map API Key.'),
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Map Style'),
                        'name'    => 'JXGOOGLE_STYLE',
                        'class'   => 'fixed-width-xs',
                        'options' => array(
                            'query' => $options,
                            'id'    => 'id',
                            'name'  => 'name'
                        )
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Map Type'),
                        'name'    => 'JXGOOGLE_TYPE',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id'   => 'roadmap',
                                    'name' => $this->l('Roadmap')),
                                array(
                                    'id'   => 'satellite',
                                    'name' => $this->l('Satellite')),
                            ),
                            'id'    => 'id',
                            'name'  => 'name'
                        )
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Zoom Level'),
                        'name'     => 'JXGOOGLE_ZOOM',
                        'required' => false,
                        'col'      => 2,
                        'class'    => 'fixed-width-xs',
                        'desc'     => $this->l('Specify initial map zoom level (1 to 17).'),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Zoom on scroll'),
                        'name'    => 'JXGOOGLE_SCROLL',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc'    => $this->l('Enable map zoom on mouse wheel scroll.'),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Map controls'),
                        'name'    => 'JXGOOGLE_TYPE_CONTROL',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc'    => $this->l('Enable map interface control elements.'),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Street view'),
                        'name'    => 'JXGOOGLE_STREET_VIEW',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc'    => $this->l('Enable street view option.'),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Animation marker'),
                        'name'    => 'JXGOOGLE_ANIMATION',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc'    => $this->l('Enable animation marker.'),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Popup'),
                        'name'    => 'JXGOOGLE_POPUP',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc'    => $this->l('Enable info windows after click marker.'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /*****
     ******    Get files from directory by extension
     ******    @param $path = path to files directory
     ******    @param $extensions = files extensions
     ******    @return files list(array)
     ******/
    protected function renderFileNames($path, $extensions = null)
    {
        if (!is_dir($path)) {
            return false;
        }
        if ($extensions) {
            $extensions = (array)$extensions;
            $list = implode('|', $extensions);
        }
        $results = scandir($path);
        $files = array();
        foreach ($results as $result) {
            if ('.' == $result[0]) {
                continue;
            }
            if (!$extensions || preg_match('~\.('.$list.')$~', $result)) {
                $files[] = $result;
            }
        }

        return $files;
    }

    /**
     * Fill the module form values
     * @return array
     */
    protected function getConfigFormValuesSettings(array $filled_settings = [])
    {
        $settings = $this->settings->getSettings($this->defaultHook);
        if (!$settings) {
            $settings = $this->settings->settingsList;
        }
        $filled_settings['hookName'] = $this->defaultHook;
        foreach ($settings as $name => $value) {
            $filled_settings[$name] = $value;
        }

        return $filled_settings;
    }

    /**
     * Get configuration field data type, because return only string
     *
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

    /**
     * Update Configuration values
     */
    protected function postProcess()
    {
        foreach (array_keys($this->settings->settingsList) as $name) {
            $this->settings->updateSetting($this->defaultHook, $name, Tools::getValue($name));
        }
    }

    protected function getGoogleSettings($hookName = false)
    {
        if (!$hook = $hookName) {
            $hook = $this->defaultHook;
        }
        $settings = $this->settings->getSettings($hook);
        $get_settings = array();
        foreach ($settings as $name => $value) {
            $get_settings[$name] = array('value' => $value, 'type' => $this->getStringValueType($value));
        }

        return $get_settings;
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

    public function hookDisplayHeader()
    {
        $styles = $this->settings->getStyles();
        $default_country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $google_script_status = true;
        $google_script = 'http'.((Configuration::get('PS_SSL_ENABLED')
                && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'))
                ? 's'
                : '').'://maps.google.com/maps/api/js?sensor=true&region='.Tools::substr(
                $default_country->iso_code, 0, 2
            );
        $google_script_alter = 'http'.((Configuration::get('PS_SSL_ENABLED')
                && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'))
                ? 's'
                : '').'://maps.google.com/maps/api/js?sensor=true&amp;region='.Tools::substr(
                $default_country->iso_code, 0, 2
            );
        if (!in_array($google_script, $this->context->controller->js_files) && !in_array(
                $google_script_alter, $this->context->controller->js_files
            )
        ) {
            $google_script_status = false;
        }
        Media::addJsDef(array('googleScriptStatus' => $google_script_status));
        $this->context->controller->registerStylesheet('module-jxgooglemap', 'modules/' .$this->name. '/views/css/jxgooglemap.css');
        foreach ($styles as $key=>$style) {
            $this->context->controller->registerJavascript('module-jxgooglemap-style-'.$key, 'modules/' .$this->name. '/views/js/styles/'.$style['value'].'.js');
        }
        $this->context->controller->registerJavascript('module-jxgooglemap', 'modules/' .$this->name. '/views/js/jxgooglemap.js');
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
            $this->context->controller->addJS($this->_path.'views/js/jxgooglemap_admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/jxgooglemap_admin.css');
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $this->context->smarty->assign('hookName', Tools::strtolower($hookName));
        $jxdefaultLat = 25.76500500;
        $jxdefaultLong = -80.24379700;
        $id_shop = $this->context->shop->id;
        $store_data = $this->settings->getStoreContactsData($hookName);
        if (is_array($store_data)) {
            foreach ($store_data as $store) {
                if ($store['default']) {
                    $s = new Store((int)$store['id_store'], true, $this->context->language->id, $id_shop);
                    $jxdefaultLat = $s->latitude;
                    $jxdefaultLong = $s->longitude;
                }
            }
        }
        $this->context->smarty->assign('jx_stores', $this->presenter->present($store_data));
        $this->context->smarty->assign('googleAPIKey', $this->settings->getSetting($hookName, 'JXGOOGLE_API_KEY'));
        $this->context->smarty->assign('marker_path', $this->_path.'img/markers/');
        $this->context->smarty->assign('jxdefaultLat', $jxdefaultLat);
        $this->context->smarty->assign('jxdefaultLong', $jxdefaultLong);
        $this->context->smarty->assign('img_store_dir', _PS_STORE_IMG_DIR_);
        $this->context->smarty->assign('settings', $this->getGoogleSettings($hookName));
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
        $templatePath = 'views/templates/hook/'.$this->name.'.tpl';
        if ($this->getTemplatePath('views/templates/hook/'.Tools::strtolower($hookName).'/'.$this->name.'.tpl')) {
            $templatePath = 'views/templates/hook/'.Tools::strtolower($hookName).'/'.$this->name.'.tpl';
        }
        $cacheName = $this->name.'_'.Tools::strtolower($hookName);
        if (!$this->isCached($this->name.'.tpl', $this->getCacheId($cacheName))) {
            $this->getWidgetVariables($hookName, $configuration);
        }

        return $this->display(__FILE__, $templatePath, $this->getCacheId($cacheName));
    }
}
