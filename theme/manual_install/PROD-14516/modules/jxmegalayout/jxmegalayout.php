<?php
/**
 * 2017-2018 Zemez
 *
 * JX Mega Layout
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
 *  @author    Zemez (Alexander Grosul & Alexander Pervakov)
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

include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/JXMegaLayoutItems.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/JXMegaLayoutLayouts.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/JXMegaLayoutExport.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/JXMegaLayoutImport.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/JXMegaLayoutOptimize.php';
// add extra content classes
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/extra/JXMegaLayoutExtraHtml.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/extra/JXMegaLayoutExtraBanner.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/extra/JXMegaLayoutExtraVideo.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/extra/JXMegaLayoutExtraSlider.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/extra/JXMegaLayoutExtraExport.php';
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/extra/JXMegaLayoutExtraImport.php';
// add theme builder classes
include_once _PS_MODULE_DIR_ . 'jxmegalayout/classes/themebuilder/JXMegaLayoutThemeBuilder.php';

class Jxmegalayout extends Module
{
    protected $id_shop;
    protected $html;
    protected $errors;
    public $warning;
    public $defLayoutHooks;
    protected $defLayoutPath;
    protected $defCleanFolders;
    protected $defaultOptions;
    public $style_path;
    public $js_layouts_path;
    public $css_layouts_path;
    protected $php_compatibility = true;
    public $productInfoLayouts = false;
    protected $theme_dir;

    public function __construct()
    {
        $this->name = 'jxmegalayout';
        $this->tab = 'front_office_features';
        $this->version = '1.5.1';
        $this->author = 'Zemez (Alexander Grosul & Alexander Pervakov)';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('JX Mega Layout');
        $this->description = $this->l('Module adds more functionality for hooks.');

        $this->ps_versions_compliancy = array('min' => '1.7.1', 'max' => _PS_VERSION_);
        $this->languages = Language::getLanguages(false);
        $this->default_language = Configuration::get('PS_LANG_DEFAULT');
        $this->id_shop = $this->context->shop->id;
        $this->theme_dir = _PS_ALL_THEMES_DIR_.$this->context->shop->theme_name.'/templates/catalog/';
        $this->productInfoLayouts = $this->getProductInfoLayouts();

        $this->defHookSections = array(
            'MainLayouts' => array(
                'lang' => $this->l('Main Layouts'),
            ),
            'ProductPage' => array(
                'lang' => $this->l('Product Page'),
            )
        );

        $this->defLayoutHooks = array(
            'displayHeader' => array(
                'lang' => $this->l('Header'),
                'section' => 'MainLayouts',
                'pages' => 'all',
                'hooks' => array(
                    'displayTop',
                    'displayNav',
                    'displayNav1',
                    'displayNav2'
                )
            ),
            'displayTopColumn' => array(
                'lang' => $this->l('Top Column'),
                'section' => 'MainLayouts',
                'pages' => 'all',
                'hooks' => array(
                    'displayTopColumn',
                    'displayNavFullWidth',
                )
            ),
            'displayHome' => array(
                'lang' => $this->l('Home'),
                'section' => 'MainLayouts',
                'pages' => 'index',
                'hooks' => array(
                    'displayHome',
                    'displayWrapperTop',
                    'displayWrapperBottom',
                    'displayContentWrapperTop',
                    'displayLeftColumn',
                    'displayRightColumn'
                )
            ),
            'displayFooter' => array(
                'lang' => $this->l('Footer'),
                'section' => 'MainLayouts',
                'pages' => 'all',
                'hooks' => array(
                    'displayFooterBefore',
                    'displayFooter',
                    'displayFooterAfter'
                )
            )
        );

        if ($this->productInfoLayouts) {
            $this->defLayoutHooks['displayProductInfo'] = array(
                'lang' => $this->l('Product Info'),
                'section' => 'ProductPage',
                'pages' => false,
                'hooks' => array()
            );
        }

        $this->defLayoutHooks['displayFooterProduct'] = array(
            'lang' => $this->l('Product Footer'),
            'section' => 'ProductPage',
            'pages' => 'product',
            'hooks' => array(
                'displayFooterProduct',
            )
        );

        $this->errors = '';
        $this->style_path = $this->local_path . 'views/css/items/';
        $this->js_layouts_path = $this->local_path . 'views/js/layouts/';
        $this->css_layouts_path = $this->local_path . 'views/css/layouts/';
        $this->defLayoutPath = $this->local_path . 'default/';

        $this->defCleanFolders = array(
            //css folder
            $this->style_path,
            //export folder
            $this->local_path . 'export/temp/',
            //import folder
            $this->local_path . 'import/temp/'
        );
        $this->defaultOptions = array(
            'JXMEGALAYOUT_OPTIMIZE' => false,
            'JXMEGALAYOUT_SHOW_MESSAGES' => false
        );
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->php_compatibility = false;
        }
    }

    public function localPath()
    {
        return $this->local_path;
    }

    public function install()
    {
        if (!$this->php_compatibility) {
            $this->_errors[] = $this->l('PHP version must be 5.3 or higher');
            return false;
        }
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
                $this->installDefLayouts() &&
                $this->setDefaultPageInfoLayout() &&
                $this->createAjaxController() &&
                $this->installOptions() &&
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') &&
                $this->registerHook('jxMegaLayoutHeader') &&
                $this->registerHook('displayHomeTabContent') &&
                $this->registerHook('displayHomeTab') &&
                $this->registerHook('jxMegaLayoutHeader') &&
                $this->registerHook('jxMegaLayoutTopColumn') &&
                $this->registerHook('jxMegaLayoutHome') &&
                $this->registerHook('jxMegaLayoutFooter') &&
                $this->registerHook('jxMegaLayoutProductFooter') &&
                $this->registerHook('actionObjectShopAddAfter');
    }

    public function uninstall()
    {
        $optimize = new JXMegaLayoutOptimize();
        $optimize->deoptimize();
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() &&
                $this->removeAjaxContoller() &&
                $this->uninstallOptions() &&
                $this->deleteDefaultPageInfoLayout() &&
                $this->cleanFolders($this->defCleanFolders) &&
                $this->removeExtraContentImages();
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);

        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'jxmegalayout';
            }
        }

        $tab->class_name = 'AdminJXMegaLayout';
        $tab->module = $this->name;
        $tab->id_parent = - 1;

        return (bool) $tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int) Tab::getIdFromClassName('AdminJXMegaLayout')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }

        return true;
    }

    private function removeExtraContentImages()
    {
        $path = $this->local_path.'extracontent/';
        $images = array_merge(
            Tools::scandir($path, 'jpg'),
            Tools::scandir($path, 'jpeg'),
            Tools::scandir($path, 'gif'),
            Tools::scandir($path, 'png')
        );
        if (count($images)) {
            foreach ($images as $image) {
                unlink($path.$image);
            }
        }

        return true;
    }

    /**
     * Install default layouts from 'default' folder
     *
     * @return bool true
     */
    public function installDefLayouts()
    {
        $path = $this->defLayoutPath;
        $files = scandir($path);

        foreach ($files as $file) {
            if (($file != '..') && ($file != '.') && (JXMegaLayoutImport::isZip($file))) {
                $import = new JXMegaLayoutImport();
                $import->importLayout($path, $file, true);
            }
        }

        return true;
    }

    protected function installOptions()
    {
        foreach ($this->defaultOptions as $name => $value) {
            Configuration::updateValue($name, $value);
        }

        return true;
    }

    public function setDefaultPageInfoLayout()
    {
        if (!$this->productInfoLayouts) {
            return true;
        }

        $default = $this->productInfoLayoutsDefault();

        return Configuration::updateValue('jxmegalayout_poduct_layout', $default);
    }

    public function deleteDefaultPageInfoLayout()
    {
        return Configuration::deleteByName('jxmegalayout_poduct_layout');
    }

    protected function uninstallOptions()
    {
        foreach (array_keys($this->defaultOptions) as $name) {
            Configuration::deleteByName($name);
        }

        return true;
    }
    public function getContent()
    {
        $this->html = '';

        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->errors .= $this->displayError($this->l('You cannot add/edit elements from \"All Shops\" or  \"Group Shop\" context'));
        } else {
            // 'tab_params' for create admin panel on load
            $this->context->smarty->assign(array(
                'templates_dir' => _PS_MODULE_DIR_ . 'jxmegalayout/views/templates/admin/',
                'theme_url' => $this->context->link->getAdminLink('AdminJXMegaLayout'),
                'tabs' => $this->getTabsConfig(),
                'productInfoThemes' => $this->productInfoLayouts,
                'cur_theme' => $this->context->shop->theme_name,
                'active_template' => Configuration::get('jxmegalayout_poduct_layout')
            ));

            $this->html .= $this->display(__FILE__, 'views/templates/admin/jxmegalayout.tpl');
        }

        return $this->errors . $this->warning . $this->html;
    }

    /**
     * Clean folders from array
     *
     * @param array $folders_array Array of folders to clean
     * @return true
     */
    protected function cleanFolders($folders_array)
    {
        // remove module extra css if it exists
        if (is_dir($this->style_path.'modules/')) {
            Tools::deleteDirectory($this->style_path.'modules/');
        }
        foreach ($folders_array as $folder) {
            if (is_dir($folder)) {
                Jxmegalayout::cleanFolder($folder);
            }
        }
        return true;
    }

    /**
     * Clean folder
     *
     * @param string $path Folder to clean
     * @return bool true
     */
    public static function cleanFolder($path)
    {
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != 'index.php') {
                Jxmegalayout::checkPerms($path . $file, 0777);

                if (is_dir($path . $file)) {
                    Jxmegalayout::cleanFolder($path . $file . '/');
                    if (count(scandir($path . $file)) == 2) {
                        rmdir($path . $file);
                    }
                } else {
                    unlink($path . $file);
                }
            }
        }

        return true;
    }

    /**
     * Get all available pages to set exceptions for layouts by pages
     * @return array
     */
    public function getAvailablePagesList($id_layout)
    {
        $front_controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
        $jxmegalayout = new JXMegaLayoutLayouts($id_layout);
        $assigned_page_list = $jxmegalayout->getAssignedPages();
        $pages_list = array('subpages');
        $filtered_list = array();
        foreach (array_keys($front_controllers) as $name) {
            array_push($pages_list, $name);
        }
        foreach ($pages_list as $page) {
            if (in_array($page, $assigned_page_list)) {
                $filtered_list[$page] = 'active';
            } else {
                $filtered_list[$page] = false;
            }
        }
        return $filtered_list;
    }

    /**
     * Check if this hook is displayable on all pages
     * @param $hook_name name of current hook
     * @return bool
     */
    public static function displayAllPagesHook($hook_name)
    {
        $jxmegalayout = new Jxmegalayout();

        if ($jxmegalayout->defLayoutHooks[$hook_name]['pages'] == 'all') {
            return true;
        }

        return false;
    }

    /**
     * Get array of modules in hook
     *
     * @param int $id_hook Hook id
     * @param int $id_layout Layout id
     * @return array List of modules
     */
    public function getHookModulesList($hook_name, $id_layout)
    {
        $list = array();
        $modules_list = array();

        foreach ($this->defLayoutHooks[$hook_name]['hooks'] as $hook) {
            $id_hook = Hook::getIdByName($hook);
            $modules = Hook::getModulesFromHook((int) $id_hook);
            foreach ($modules as $key => $module) {
                $modules[$key]['hook_name'] = Hook::getNameById($module['id_hook']);
            }
            $modules_list = array_merge($modules_list, $modules);
        }

        $used_modules_list = JXMegaLayoutItems::checkModuleInLayout($id_layout);
        $i = 0;

        foreach ($modules_list as $module) {
            $m = Module::getInstanceById($module['id_module']);
            if (!$m) {
                continue;
            }
            $name = $m->name;
            $display_name = $m->displayName .' ('. $module['hook_name'] .')';
            // check if module is active for this store and don\'t used yet
            if (!$this->checkModuleStatus($name)) {
                if (!count($used_modules_list) || (count($used_modules_list) && !in_array($name.'-'.$module['hook_name'], $used_modules_list))) {
                    $list[$i]['id'] = $module['id_module'];
                    $list[$i]['name'] = $name;
                    $list[$i]['public_name'] = $display_name;
                    $list[$i]['origin_hook'] = $module['hook_name'];
                }
                $i++;
            }
        }

        return $list;
    }

    /**
     * Get array of blocks list in hook
     *
     * @param int $id_hook Hook id
     * @param int $id_layout Layout id
     * @return array $blocks_list of modules list
     */

    protected function renderBlockList($hook_name, $id_layout)
    {
        $blocks_list = array();
        $used_blocks_list = JXMegaLayoutItems::checkModuleInLayout($id_layout);

        if ($hook_name == 'displayFooter') {
            if (!in_array('logo', $used_blocks_list)) {
                $blocks_list[] = array(
                    'name' => 'logo',
                    'public_name' => $this->l('Block logo'),
                    'origin_hook' => ''
                );
            }
            if (!in_array('copyright', $used_blocks_list)) {
                $blocks_list[] = array(
                    'name' => 'copyright',
                    'public_name' => $this->l('Block copyright'),
                    'origin_hook' => ''
                );
            }
        } elseif ($hook_name == 'displayHeader') {
            if (!in_array('logo', $used_blocks_list)) {
                $blocks_list[] = array(
                    'name' => 'logo',
                    'public_name' => $this->l('Block logo'),
                    'origin_hook' => ''
                );
            }
        } elseif ($hook_name == 'displayHome') {
            if (!in_array('tabs', $used_blocks_list)) {
                $blocks_list[] = array(
                    'name' => 'tabs',
                    'public_name' => $this->l('Homepage tabs'),
                    'origin_hook' => ''
                );
            }
        }

        return $blocks_list;
    }

    /**
     * @return string Html of tools list
     */
    protected function renderToolsList()
    {
        $tools = array(
            'Export',
            'Import',
            'Options',
            'Extra Content',
            'Theme Builder'
        );

        $this->context->smarty->assign('tools', $tools);

        return $this->display($this->_path, '/views/templates/admin/jxmegalayout_tools.tpl');
    }

    /**
     * @param string $tool_name
     * @return string Html of tool
     */
    public function renderToolContent($tool_name)
    {
        switch ($tool_name) {
            case 'Export':
                return $this->renderExportContent();
            case 'Import':
                return $this->renderImportContent();
            case 'Options':
                return $this->renderOptionsContent();
            case 'Extra Content':
                return $this->renderExtraContent();
            case 'Theme Builder':
                return $this->renderThemeBuilder();
        }
    }

    /**
     * @return string Html of export page
     */
    protected function renderExportContent()
    {
        $hooks = $this->getExportConfig();
        $this->context->smarty->assign('hooks', $hooks);

        return $this->display($this->_path, '/views/templates/admin/tools/export.tpl');
    }

    /**
     * @return string Html of import page
     */
    protected function renderImportContent()
    {
        $this->context->smarty->assign('max_file_size', Jxmegalayout::getMaxFileSize());

        return $this->display($this->_path, '/views/templates/admin/tools/import.tpl');
    }

    /**
    * @return string Html of options page
    */
    protected function renderOptionsContent()
    {
        return $this->display($this->_path, '/views/templates/admin/tools/options.tpl');
    }

    /**
     * @return string Html of options page
     */
    protected function renderExtraContent()
    {
        $this->context->smarty->assign(array(
            'extra_content_html' => JXMegaLayoutExtraHtml::getList($this->context->language->id),
            'extra_content_banner' => JXMegaLayoutExtraBanner::getList($this->context->language->id),
            'extra_content_video' => JXMegaLayoutExtraVideo::getList($this->context->language->id),
            'extra_content_slider' => JXMegaLayoutExtraSlider::getList($this->context->language->id),
            'image_baseurl' => $this->_path.'extracontent/'
        ));
        return $this->display($this->_path, '/views/templates/admin/tools/extra/extra-content.tpl');
    }

    protected function renderThemeBuilder()
    {
        $themeBuilder = new JXMegalayoutThemeBuilder();
        $this->context->smarty->assign('compatible_parent_themes', $themeBuilder->getCompatibleParentThemes());
        return $this->display($this->_path, '/views/templates/admin/tools/themebuilder/theme-builder.tpl');
    }

    public function validateThemeNames($name, $publicName) {
        $errors = array();
        if (!Validate::isThemeName($name) || in_array($name, $this->themeBuilder->getAllThemesNames())) {
            $errors[] = $this->l('Theme name is invalid or has been already used. Please use different name.');
        }

        if (!Validate::isGenericName($publicName)) {
            $errors[] = $this->l('Theme public name is invalid. Please use different name');
        }

        if (!count($errors)) {
            return false;
        }

        return $this->displayError($errors);
    }

    public function renderToolExtraContent($content_type, $id_content = false)
    {
        switch ($content_type) {
            case 'all':
                $content = $this->display($this->local_path, 'views/templates/admin/tools/extra/extra-content-buttons.tpl');
                break;
            case 'html':
                $content = $this->getExtraContentHtml($id_content);
                break;
            case 'banner':
                $content = $this->getExtraContentBanner($id_content);
                break;
            case 'video':
                $content = $this->getExtraContentVideo($id_content);
                break;
            case 'slider':
                $content = $this->getExtraContentSlider($id_content);
                break;
        }

        return $content;
    }

    protected function getExtraContentHtml($id_html = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' =>  $id_html ? $this->l('Update Html block') : $this->l('Add Html block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter HTML item name'),
                        'name' => 'name',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('HTML content'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Specific Class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_extra_html'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'extra_content_type'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateHtml',
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_extra_banner';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.
            '&tab_module='.$this->tab.
            '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getExtraHtmlFieldsValues($id_html),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getExtraContentBanner($id_banner = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' =>  $id_banner ? $this->l('Update Banner block') : $this->l('Add Banner block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Banner item name'),
                        'name' => 'name',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Banner item link'),
                        'name' => 'link',
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'multilang_file',
                        'label' => $this->l('Add banner image'),
                        'name' => 'img',
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Banner content'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Specific Class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_extra_banner'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'extra_content_type'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateBanner',
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = true;
        $helper->identifier = 'id_extra_banner';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.
            '&tab_module='.$this->tab.
            '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getExtraBannerFieldsValues($id_banner),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'image_baseurl' => $this->_path.'extracontent/'
        );

        $helper->override_folder = '_configure/';

        return $helper->generateForm(array($fields_form));
    }

    protected function getExtraContentVideo($id_video = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' =>  $id_video ? $this->l('Update Video block') : $this->l('Add Video block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Video item name'),
                        'name' => 'name',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'multilang_video',
                        'label' => $this->l('Preview'),
                        'name' => 'url',
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Video item URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3,
                        'desc' => $this->l('Video url must be like //www.youtube.com/embed/video_id')
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('HTML content'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Specific Class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_extra_video'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'extra_content_type'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateVideo',
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_extra_video';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.
            '&tab_module='.$this->tab.
            '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getExtraVideoFieldsValues($id_video),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        $helper->override_folder = '_configure/';

        return $helper->generateForm(array($fields_form));
    }

    protected function getExtraContentSlider($id_slider = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' =>  $id_slider ? $this->l('Update Slider block') : $this->l('Add Slider block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Slider item name'),
                        'name' => 'name',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Slider description'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Specific Class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'slides_wizard',
                        'label' => $this->l('Slides'),
                        'name' => 'slides'
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Visible items'),
                        'name'  => 'visible_items',
                        'lang'  => false,
                        'col'   => 3,
                        'required' => true
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Items scroll'),
                        'name'  => 'items_scroll',
                        'lang'  => false,
                        'col'   => 3,
                        'required' => true
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Slide margin'),
                        'name'  => 'margin',
                        'lang'  => false,
                        'col'   => 3,
                        'required' => true
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Slider speed'),
                        'name'  => 'speed',
                        'lang'  => false,
                        'col'   => 3,
                        'required' => true
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto scroll'),
                        'name' => 'auto_scroll',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Pause'),
                        'desc'  => $this->l('This setting impacts only with "Auto scroll"'),
                        'name'  => 'pause',
                        'lang'  => false,
                        'col'   => 3,
                        'required' => true
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Slider Loop'),
                        'name' => 'loop',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Pager'),
                        'name' => 'pager',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Controls'),
                        'name' => 'controls',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Auto height'),
                        'name' => 'auto_height',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_extra_slider'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'extra_content_type'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateSlider',
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_extra_slider';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.
            '&tab_module='.$this->tab.
            '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $content_types = array('html', 'banner', 'video', 'product');
        if (!$this->checkModuleStatus('jxblog')) {
            array_push($content_types, 'post');
        }
        $helper->tpl_vars = array(
            'fields_value' => $this->getExtraSliderFieldsValues($id_slider),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'content_types' => $content_types,
            'content_lists' => array(
                'html' => JXMegaLayoutExtraHtml::getList($this->context->language->id),
                'banner' => JXMegaLayoutExtraBanner::getList($this->context->language->id),
                'video' => JXMegaLayoutExtraVideo::getList($this->context->language->id),
                'product' => false,
                'post' => false
            ),
            'slides' => $id_slider ? JXMegaLayoutExtraSlider::getSlides($id_slider, $this->context->language->id, false) : false
        );

        $helper->override_folder = '_configure/';

        return $helper->generateForm(array($fields_form));
    }

    protected function getExtraHtmlFieldsValues($id_html = false)
    {
        if ($id_html) {
            $html = new JXMegaLayoutExtraHtml($id_html);
        } else {
            $html = new JXMegaLayoutExtraHtml($id_html);
        }

        $fields_values = array(
            'id_extra_html' => Tools::getValue('id_extra_html', $html->id),
            'specific_class' => Tools::getValue('specific_class', $html->specific_class),
            'extra_content_type' => 'html'
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['name'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $html->name[$lang['id_lang']]);
            $fields_values['content'][$lang['id_lang']] = Tools::getValue('content_'.(int)$lang['id_lang'], $html->content[$lang['id_lang']]);
        }

        return $fields_values;
    }

    protected function getExtraBannerFieldsValues($id_banner = false)
    {
        if ($id_banner) {
            $banner = new JXMegaLayoutExtraBanner($id_banner);
        } else {
            $banner = new JXMegaLayoutExtraBanner($id_banner);
        }

        $fields_values = array(
            'id_extra_banner' => Tools::getValue('id_extra_banner', $banner->id),
            'specific_class' => Tools::getValue('specific_class', $banner->specific_class),
            'extra_content_type' => 'banner'
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['name'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $banner->name[$lang['id_lang']]);
            $fields_values['link'][$lang['id_lang']] = Tools::getValue('link_'.(int)$lang['id_lang'], $banner->link[$lang['id_lang']]);
            $fields_values['img'][$lang['id_lang']] = Tools::getValue('img_'.(int)$lang['id_lang'], $banner->img[$lang['id_lang']]);
            $fields_values['content'][$lang['id_lang']] = Tools::getValue('content_'.(int)$lang['id_lang'], $banner->content[$lang['id_lang']]);
        }

        return $fields_values;
    }

    protected function getExtraVideoFieldsValues($id_video = false)
    {
        if ($id_video) {
            $video = new JXMegaLayoutExtraVideo($id_video);
        } else {
            $video = new JXMegaLayoutExtraVideo($id_video);
        }

        $fields_values = array(
            'id_extra_video' => Tools::getValue('id_extra_video', $video->id),
            'specific_class' => Tools::getValue('specific_class', $video->specific_class),
            'extra_content_type' => 'video'
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['name'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $video->name[$lang['id_lang']]);
            $fields_values['url'][$lang['id_lang']] = Tools::getValue('url_'.(int)$lang['id_lang'], $video->url[$lang['id_lang']]);
            $fields_values['content'][$lang['id_lang']] = Tools::getValue('content_'.(int)$lang['id_lang'], $video->content[$lang['id_lang']]);
        }

        return $fields_values;
    }

    protected function getExtraSliderFieldsValues($id_slider = false)
    {
        if ($id_slider) {
            $slider = new JXMegaLayoutExtraSlider($id_slider);
        } else {
            $slider = new JXMegaLayoutExtraSlider($id_slider);
        }

        $fields_values = array(
            'id_extra_slider' => Tools::getValue('id_extra_slider', $slider->id),
            'specific_class' => Tools::getValue('specific_class', $slider->specific_class),
            'visible_items' => Tools::getValue('visible_items', $slider->visible_items ? $slider->visible_items : 1),
            'items_scroll' => Tools::getValue('items_scroll', $slider->items_scroll ? $slider->items_scroll : 1),
            'margin' => Tools::getValue('margin', $slider->margin ? $slider->margin : 0),
            'speed' => Tools::getValue('speed', $slider->speed ? $slider->speed : 500),
            'auto_scroll' => Tools::getValue('auto_scroll', $slider->auto_scroll != '' ? $slider->auto_scroll : false),
            'pause' => Tools::getValue('pause', $slider->pause ? $slider->pause : 3000),
            'loop' => Tools::getValue('loop', $slider->loop != '' ? $slider->loop : true),
            'pager' => Tools::getValue('pager', $slider->pager != '' ? $slider->pager : false),
            'controls' => Tools::getValue('controls', $slider->controls != '' ? $slider->controls : true),
            'auto_height' => Tools::getValue('auto_height', $slider->auto_height != '' ? $slider->auto_height : true),
            'extra_content_type' => 'slider'
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['name'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $slider->name[$lang['id_lang']]);
            $fields_values['content'][$lang['id_lang']] = Tools::getValue('content_'.(int)$lang['id_lang'], $slider->content[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /**
     * Form fields validation
     *
     * @param $content
     *
     * @return bool|string
     */
    protected function validateExtraContentHtml($content)
    {
        $errors = array();

        if (Tools::isEmpty($content['name_'.$this->default_language]) || !Validate::isString($content['name_'.$this->default_language])) {
            $errors[] = $this->l('Field "Name" is empty or invalid');
        }

        if (!Validate::isString($content['specific_class'])) {
            $errors[] = $this->l('Field "Specific class" is invalid');
        }

        foreach ($this->languages as $language) {
            if (!Tools::isEmpty($content['name_'.$language['id_lang']]) && !Validate::isString($content['name_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "Name" is invalid in language - ').$language['iso_code'];
            }
            if (!Tools::isEmpty($content['content_'.$language['id_lang']]) && !Validate::isCleanHtml($content['content_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "HTML content" is invalid in language - ').$language['iso_code'];
            }
        }

        if (!count($errors)) {
            return false;
        }

        return implode($errors, '<br />');
    }

    /**
     * Form fields validation
     *
     * @param $content
     *
     * @return bool|string
     */
    protected function validateExtraContentBanner($content)
    {
        $errors = array();

        if (Tools::isEmpty($content['name_'.$this->default_language]) || !Validate::isString($content['name_'.$this->default_language])) {
            $errors[] = $this->l('Field "Name" is empty or invalid');
        }

        if (!Validate::isString($content['specific_class'])) {
            $errors[] = $this->l('Field "Specific class" is invalid');
        }

        foreach ($this->languages as $language) {
            if (!Tools::isEmpty($content['name_'.$language['id_lang']]) && !Validate::isString($content['name_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "Name" is invalid in language - ').$language['iso_code'];
            }
            if (!Tools::isEmpty($content['link_'.$language['id_lang']]) && !Validate::isString($content['link_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "Link" is invalid in language - ').$language['iso_code'];
            }
            if (isset($_FILES['img_'.$language['id_lang']]) && $_FILES['img_'.$language['id_lang']]['name']) {
                if ($err = ImageManager::validateUpload($_FILES['img_'.$language['id_lang']], Tools::getMaxUploadSize())) {
                    $errors[] = $err;
                }
            }
            if (!Tools::isEmpty($content['content_'.$language['id_lang']]) && !Validate::isCleanHtml($content['content_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "HTML content" is invalid in language - ').$language['iso_code'];
            }
        }

        if (!count($errors)) {
            return false;
        }

        return implode($errors, '<br />');
    }

    /**
     * Form fields validation
     *
     * @param $content
     *
     * @return bool|string
     */
    protected function validateExtraContentVideo($content)
    {
        $errors = array();

        if (Tools::isEmpty($content['name_'.$this->default_language]) || !Validate::isString($content['name_'.$this->default_language])) {
            $errors[] = $this->l('Field "Name" is empty or invalid');
        }

        if (Tools::isEmpty($content['url_'.$this->default_language]) || !Validate::isString($content['url_'.$this->default_language])) {
            $errors[] = $this->l('Field "Url" is empty or invalid');
        }

        if (!Validate::isString($content['specific_class'])) {
            $errors[] = $this->l('Field "Specific class" is invalid');
        }

        foreach ($this->languages as $language) {
            if (!Tools::isEmpty($content['name_'.$language['id_lang']]) && !Validate::isString($content['name_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "Name" is invalid in language - ').$language['iso_code'];
            }
            if (!Tools::isEmpty($content['url_'.$language['id_lang']]) && !Validate::isString($content['url_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "Url" is invalid in language - ').$language['iso_code'];
            }
            if (!Tools::isEmpty($content['content_'.$language['id_lang']]) && !Validate::isCleanHtml($content['content_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "HTML content" is invalid in language - ').$language['iso_code'];
            }
        }

        if (!count($errors)) {
            return false;
        }

        return implode($errors, '<br />');
    }

    /**
     * Form fields validation
     *
     * @param $content
     *
     * @return bool|string
     */
    protected function validateExtraContentSlider($content)
    {
        $errors = array();

        if (Tools::isEmpty($content['name_'.$this->default_language]) || !Validate::isString($content['name_'.$this->default_language])) {
            $errors[] = $this->l('Field "Name" is empty or invalid');
        }

        if (!Validate::isString($content['specific_class'])) {
            $errors[] = $this->l('Field "Specific class" is invalid');
        }

        foreach ($this->languages as $language) {
            if (!Tools::isEmpty($content['name_'.$language['id_lang']]) && !Validate::isString($content['name_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "Name" is invalid in language - ').$language['iso_code'];
            }
            if (!Tools::isEmpty($content['content_'.$language['id_lang']]) && !Validate::isCleanHtml($content['content_'.$language['id_lang']])) {
                $errors[] = $this->l('Field "HTML content" is invalid in language - ').$language['iso_code'];
            }
        }

        if (Tools::isEmpty(Tools::getValue('visible_items')) || !Validate::isInt(Tools::getValue('visible_items'))) {
            $errors[] = $this->l('Field "Visible items" is empty or invalid');
        }

        if (Tools::isEmpty(Tools::getValue('items_scroll')) || !Validate::isInt(Tools::getValue('items_scroll'))) {
            $errors[] = $this->l('Field "Items Scroll" is empty or invalid');
        }

        if (Tools::isEmpty(Tools::getValue('margin')) || !Validate::isInt(Tools::getValue('margin'))) {
            $errors[] = $this->l('Field "Slide margin" is empty or invalid');
        }

        if (Tools::isEmpty(Tools::getValue('speed')) || !Validate::isInt(Tools::getValue('speed'))) {
            $errors[] = $this->l('Field "Slider speed" is empty or invalid');
        }

        if (Tools::getValue('auto_scroll') && (Tools::isEmpty(Tools::getValue('pause')) || !Validate::isInt(Tools::getValue('pause')))) {
            $errors[] = $this->l('Field "Pause" is empty or invalid');
        }

        if (!count($errors)) {
            return false;
        }

        return implode($errors, '<br />');
    }

    /**
     * Parse content by type and determine which method should be used
     *
     * @param $type
     * @param $content all information sent from ajax and received within Tools::getAllValues()
     *
     * @return array
     */
    public function saveExtraContent($type, $content)
    {
        $this->context->smarty->assign('content_type', $type);
        // use this to determine using buttons in messages
        $this->context->smarty->assign('buttons', $type);
        switch ($type) {
            case 'html':
                if ($errors = $this->validateExtraContentHtml($content)) {
                    $this->context->smarty->assign('errors', $errors);
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-invalid-message.tpl');

                    return array('status' => 'invalid', 'report' => $tpl);
                }
                if (!$this->saveExtraContentHtml($content)) {
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-error-message.tpl');

                    return array('status' => 'error', 'report' => $tpl);
                }
                break;
            case 'banner':
                if ($errors = $this->validateExtraContentBanner($content)) {
                    $this->context->smarty->assign('errors', $errors);
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-invalid-message.tpl');

                    return array('status' => 'invalid', 'report' => $tpl);
                }
                if (!$this->saveExtraContentBanner($content)) {
                    return array('status' => 'error', 'report' => $this->l('An error occurred during the Banner saving'));
                }
                break;
            case 'video':
                if ($errors = $this->validateExtraContentVideo($content)) {
                    $this->context->smarty->assign('errors', $errors);
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-invalid-message.tpl');

                    return array('status' => 'invalid', 'report' => $tpl);
                }
                if (!$this->saveExtraContentVideo($content)) {
                    return array('status' => 'error', 'report' => $this->l('An error occurred during the Video saving'));
                }
                break;
            case 'slider':
                if ($errors = $this->validateExtraContentSlider($content)) {
                    $this->context->smarty->assign('errors', $errors);
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-invalid-message.tpl');

                    return array('status' => 'invalid', 'report' => $tpl);
                }
                if (!$this->saveExtraContentSlider($content)) {
                    return array('status' => 'error', 'report' => $this->l('An error occurred during the Video saving'));
                }
                break;
        }

        return array('status' => 'success', 'report' => $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-success-message.tpl'));
    }

    /**
     * Save HTML block to extra content
     *
     * @param $content all information sent from ajax and received within Tools::getAllValues()
     *
     * @return bool
     */
    protected function saveExtraContentHtml($content)
    {
        if ($content['id_extra_html']) {
            $html = new JXMegaLayoutExtraHtml($content['id_extra_html']);
        } else {
            $html = new JXMegaLayoutExtraHtml();
        }

        $html->specific_class = $content['specific_class'];
        foreach ($this->languages as $language) {
            if (Tools::isEmpty($content['name_'.$language['id_lang']])) {
                $html->name[$language['id_lang']] = $content['name_'.$this->default_language];
            } else {
                $html->name[$language['id_lang']] = $content['name_'.$language['id_lang']];
            }

            $html->content[$language['id_lang']] = $content['content_'.$language['id_lang']];
        }

        if (!$html->save()) {
            return false;
        }

        return true;
    }

    /**
     * Save video to extra content
     *
     * @param $content all information sent from ajax and received within Tools::getAllValues()
     *
     * @return bool
     */
    protected function saveExtraContentBanner($content)
    {
        if ($content['id_extra_banner']) {
            $banner = new JXMegaLayoutExtraBanner($content['id_extra_banner']);
        } else {
            $banner = new JXMegaLayoutExtraBanner();
        }

        $banner->specific_class = $content['specific_class'];
        foreach ($this->languages as $language) {
            if (Tools::isEmpty($content['name_'.$language['id_lang']])) {
                $banner->name[$language['id_lang']] = $content['name_'.$this->default_language];
            } else {
                $banner->name[$language['id_lang']] = $content['name_'.$language['id_lang']];
            }

            $banner->link[$language['id_lang']] = $content['link_'.$language['id_lang']];
            if ($image = $this->uploadImage($_FILES['img_'.$language['id_lang']])) {
                $banner->img[$language['id_lang']] = $image;
            }
            $banner->content[$language['id_lang']] = $content['content_'.$language['id_lang']];
        }

        if (!$banner->save()) {
            return false;
        }

        return true;
    }

    /**
     * Upload banner images to the serves
     *
     * @param $file
     *
     * @return bool|string
     */
    private function uploadImage($file)
    {
        $path = $this->local_path.'extracontent/';
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        if (isset($file['name']) && isset($file['tmp_name']) && !Tools::isEmpty($file['tmp_name'])) {
            $fileFormat = explode('.', $file['name']);
            $newName = Tools::passwdGen(16).'.'.$fileFormat[1];
            if (!move_uploaded_file($file['tmp_name'], $path.$newName)) {
                return false;
            }

            return $newName;
        }

        return false;
    }

    /**
     * Save video to extra content
     *
     * @param $content all information sent from ajax and received within Tools::getAllValues()
     *
     * @return bool
     */
    protected function saveExtraContentVideo($content)
    {
        if ($content['id_extra_video']) {
            $video = new JXMegaLayoutExtraVideo($content['id_extra_video']);
        } else {
            $video = new JXMegaLayoutExtraVideo();
        }

        $video->specific_class = $content['specific_class'];
        foreach ($this->languages as $language) {
            if (Tools::isEmpty($content['name_'.$language['id_lang']])) {
                $video->name[$language['id_lang']] = $content['name_'.$this->default_language];
            } else {
                $video->name[$language['id_lang']] = $content['name_'.$language['id_lang']];
            }
            if (Tools::isEmpty($content['url_'.$language['id_lang']])) {
                $video->url[$language['id_lang']] = $content['url_'.$this->default_language];
            } else {
                $video->url[$language['id_lang']] = $content['url_'.$language['id_lang']];
            }

            $video->content[$language['id_lang']] = $content['content_'.$language['id_lang']];
        }

        if (!$video->save()) {
            return false;
        }

        return true;
    }

    /**
     * Save slider to extra content
     *
     * @param $content all information sent from ajax and received within Tools::getAllValues()
     *
     * @return bool
     */
    protected function saveExtraContentSlider($content)
    {
        if ($content['id_extra_slider']) {
            $slider = new JXMegaLayoutExtraSlider($content['id_extra_slider']);
        } else {
            $slider = new JXMegaLayoutExtraSlider();
        }

        $slider->specific_class = $content['specific_class'];
        $slider->visible_items = $content['visible_items'];
        $slider->items_scroll = $content['items_scroll'];
        $slider->margin = $content['margin'];
        $slider->speed = $content['speed'];
        $slider->auto_scroll = $content['auto_scroll'];
        if ($slider->auto_scroll) {
            $slider->pause = $content['pause'];
        } else {
            $slider->pause = 3000;
        }
        $slider->loop = $content['loop'];
        $slider->pager = $content['pager'];
        $slider->controls = $content['controls'];
        $slider->auto_height = $content['auto_height'];
        foreach ($this->languages as $language) {
            if (Tools::isEmpty($content['name_'.$language['id_lang']])) {
                $slider->name[$language['id_lang']] = $content['name_'.$this->default_language];
            } else {
                $slider->name[$language['id_lang']] = $content['name_'.$language['id_lang']];
            }

            $slider->content[$language['id_lang']] = $content['content_'.$language['id_lang']];
        }

        if (!$slider->save()) {
            return false;
        }

        if (isset($content['slides']) && !$slider->updateSlides($content['slides'])) {
            return false;
        }

        return true;
    }

    /**
     * Remove extra content by type
     *
     * @param $type type of content (html, banner, video, slider)
     * @param $id_item a particular item of content by type
     *
     * @return array
     */
    public function removeExtraContent($type, $id_item)
    {
        // use this to determine using buttons in messages or not
        $this->context->smarty->assign('buttons', false);
        switch ($type) {
            case 'html':
                $item = new JXMegaLayoutExtraHtml($id_item);
                if (!$item->delete()) {
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-error-message.tpl');

                    return array('status' => 'error', 'report' => $tpl);
                }
                break;
            case 'banner':
                $path = $this->local_path.'extracontent/';
                $item = new JXMegaLayoutExtraBanner($id_item);
                foreach ($this->languages as $language) {
                    if ($item->img[$language['id_lang']] && file_exists($path.$item->img[$language['id_lang']])) {
                        unlink($path.$item->img[$language['id_lang']]);
                    }
                }
                if (!$item->delete()) {
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-error-message.tpl');

                    return array('status' => 'error', 'report' => $tpl);
                }
                break;
            case 'video':
                $item = new JXMegaLayoutExtraVideo($id_item);
                if (!$item->delete()) {
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-error-message.tpl');

                    return array('status' => 'error', 'report' => $tpl);
                }
                break;
            case 'slider':
                $item = new JXMegaLayoutExtraSlider($id_item);
                if (!$item->delete()) {
                    $tpl = $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-error-message.tpl');

                    return array('status' => 'error', 'report' => $tpl);
                }
                break;
        }

        return array('status' => 'success', 'report' => $this->display($this->local_path, 'views/templates/admin/tools/extra/message/extra-content-remove-message.tpl'));
    }

    private function getExtraContentItem($item, $front = false)
    {
        $info = explode('-', $item);
        switch ($info[0]) {
            case 'html':
                $data = JXMegaLayoutExtraHtml::getItem($info[1], $this->context->language->id);
                break;
            case 'banner':
                $data = JXMegaLayoutExtraBanner::getItem($info[1], $this->context->language->id);
                break;
            case 'video':
                $data = JXMegaLayoutExtraVideo::getItem($info[1], $this->context->language->id);
                break;
            case 'slider':
                $data = JXMegaLayoutExtraSlider::getItem($info[1], $this->context->language->id, $front);
                break;
            case 'product':
                if (!$front) {
                    $data['name'] = Product::getProductName($info[1]);
                } else {
                    $data = $this->assembleProduct($info[1]);
                }
                break;
            case 'post':
                if ($error = $this->checkModuleStatus('jxblog')) {
                    $data['name'] = $error;
                } else {
                    if (!$front) {
                        $post = new JXBlogPost($info[1], $this->context->language->id);
                        $data['name'] = $post->name;
                    } else {
                        $data = JXBlogPost::getPost(
                            $info[1],
                            $this->context->language->id,
                            $this->context->shop->id,
                            $this->context->customer->id_default_group
                        );
                    }
                }
                break;
        }

        return $data;
    }

    public function assembleProduct($id_product)
    {
        if (Validate::isLoadedObject(new Product($id_product))) {
            $product = (new ProductAssembler($this->context))
                ->assembleProduct(array('id_product' => $id_product));
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
            return  $presenter->present(
                $presentationSettings,
                $product,
                $this->context->language
            );
        } else {
            return false;
        }
    }
    /**
     * @return array of export configs
     */
    protected function getExportConfig()
    {
        $hooks = array();

        foreach ($this->defLayoutHooks as $hook_name => $hook) {
            if ($hook_name == 'displayProductInfo') {
                continue;
            }
            $status = 'on';

            if (!JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->id_shop)) {
                $status = 'off';
            }

            $hooks[] = array(
                'hook_name' => $hook['lang'],
                'layouts' => JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->id_shop),
                'status' => $status
            );
        }

        return $hooks;
    }


    /**
     * Sort array by field 'sort_order'
     *
     * @param array $array
     * @return array
     */
    protected function arraySort($array)
    {
        if (count($array) > 1) {
            usort($array, function ($a, $b) {
                return $a['sort_order'] - $b['sort_order'];
            });
        }

        return $array;
    }

    /**
     * Get layouts array from db
     *
     * @param int $id_layout
     * @return bool|array false|array of layouts
     */
    public function getLayoutItems($id_layout)
    {
        $items = null;
        if (!$result = JXMegaLayoutItems::getItems($id_layout)) {
            return false;
        }

        foreach ($result as $item) {
            $id_item = $item['id_item'];
            $items[$id_item] = $item;
        }

        return $items;
    }

    /**
     * Generate layout map
     *
     * @param array $layout_items
     * @return array layout map
     */
    public function generateLayoutMap($layout_items)
    {
        $map = array();

        if (is_array($layout_items)) {
            foreach ($layout_items as $id => $item) {
                $id_parent = $item['id_parent'];
                $level = $this->checkLayoutItemLevel($layout_items, $id);
                $map[$level][$id_parent][] = $item;
            }
        }

        return $map;
    }

    /**
     * Check level of item
     *
     * @param array $layout_items
     * @param int $id_item
     * @param int $level
     * @return int item level in array
     */
    protected function checkLayoutItemLevel($layout_items, $id_item, $level = 0)
    {
        if ($layout_items[$id_item]['id_parent'] != 0) {
            $id_parent = $layout_items[$id_item]['id_parent'];

            if (isset($layout_items[$id_parent]['id_parent'])) {
                $level++;
                $level = $this->checkLayoutItemLevel($layout_items, $id_parent, $level);
            }
        }

        return $level;
    }

    /**
     * Get module html
     *
     * @param string $hook_name
     * @param id $id_module
     * @param array $params
     * @return boolean|string False|Module html
     */
    protected function renderModuleContent($hook_name, $id_module, $params = array())
    {
        $this->context->smarty->assign(array('original_hook_name' => $hook_name));

        if (!$result = Hook::exec($hook_name, $params, $id_module)) {
            return false;
        }

        return $result;
    }

    /**
     * @param array $map
     * @param int $level
     * @param array $positions
     * @param array $params
     * @return bool|string False or Layout html
     */
    protected function renderLayoutFront($map, $level = null, $positions = array(), $params = array())
    {
        if (is_null($level)) {
            $level = count($map) - 1;

            if ($level < 0) {
                return false;
            }
        }

        foreach ($map[$level] as $id_parent => $items) {
            $positions[$id_parent] = '';
            $items = $this->arraySort($items);

            foreach ($items as $item) {
                if (!isset($positions[$item['id_item']])) {
                    $positions[$item['id_item']] = '';
                }

                switch ($item['type']) {
                    case 'module':
                        if (!$this->checkModuleStatus($item['module_name'])) {
                            $id_module = Module::getModuleIdByName($item['module_name']);
                            $this->context->smarty->assign(array(
                                'position' => $this->renderModuleContent($item['origin_hook'], $id_module, $params),
                                'jxml_class' => 'module '. $item['specific_class'],
                            ));
                            $positions[$id_parent] .= $this->display($this->_path, '/views/templates/hook/layouts/layout.tpl');
                        }
                        break;
                    case 'wrapper':
                        $this->context->smarty->assign(array(
                            'position' => $positions[$item['id_item']],
                            'jxml_class' => 'wrapper ' . $item['id_unique'] . ' ' . $item['specific_class']
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/hook/layouts/layout.tpl');
                        break;
                    case 'row':
                        $this->context->smarty->assign(array(
                            'position' => $positions[$item['id_item']],
                            'jxml_class' => 'row ' . $item['id_unique'] . ' ' . $item['specific_class']
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/hook/layouts/layout.tpl');
                        break;
                    case 'col':
                        $colXs = $item['col'];
                        if ($colXs) {
                            $colXs = $item['col_xs'];
                        }
                        $class = $item['id_unique'] . ' ' . $colXs . ' ' . $item['col_sm'] . ' ' . $item['col_md'] . ' ' . $item['col_lg'] . ' ' . $item['col_xl']. ' ' . $item['col_xxl']. ' ';
                        $this->context->smarty->assign(array(
                            'position' => $positions[$item['id_item']],
                            'jxml_class' => $class . $item['specific_class']
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/hook/layouts/layout.tpl');
                        break;
                    case 'block':
                        $this->context->smarty->assign(array(
                            'items' => $item
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/hook/layouts/block.tpl');
                        break;
                    case 'content':
                        $this->context->smarty->assign('img_path', $this->_path.'extracontent/');
                        $this->context->smarty->assign(array(
                            'item' => $item,
                            'content' => $this->getExtraContentItem($item['module_name'], true)
                        ));
                        $type = explode('-', $item['module_name']);
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/hook/layouts/extra/'.$type[0].'.tpl');
                        break;
                }
            }
        }

        $level--;

        if ($level >= 0) {
            $html = $this->renderLayoutFront($map, $level, $positions, $params);
        } else {
            $html = $positions[0];
        }

        return $html;
    }

    /**
     * Generate layout html for back-office
     *
     * @param array $map Map of layout
     * @param bool $preview Layout mod
     * @param int $level Level of layout item
     * @param array $positions Items positions in layout
     * @return string Html of layout
     */
    public function renderLayoutAdmin($map, $preview = false, $level = null, $positions = array())
    {
        if (count($map) <= 0) {
            return false;
        }

        if (is_null($level)) {
            $level = count($map) - 1;
        }

        foreach ($map[$level] as $id_parent => $items) {
            $positions[$id_parent] = '';
            $items = $this->arraySort($items);

            foreach ($items as $item) {
                if (!isset($positions[$item['id_item']])) {
                    $positions[$item['id_item']] = '';
                }

                switch ($item['type']) {
                    case 'module':
                        if ($warning = $this->checkModuleStatus($item['module_name'])) {
                            $item['warning'] = $warning;
                        }
                        $this->context->smarty->assign(array(
                            'elem' => $item,
                            'preview' => $preview
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/admin/layouts/module.tpl');
                        break;
                    case 'wrapper':
                        $this->context->smarty->assign(array(
                            'position' => $positions[$item['id_item']],
                            'elem' => $item,
                            'preview' => $preview
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/admin/layouts/wrapper.tpl');
                        break;
                    case 'row':
                        $this->context->smarty->assign(array(
                            'position' => $positions[$item['id_item']],
                            'elem' => $item,
                            'preview' => $preview
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/admin/layouts/row.tpl');
                        break;
                    case 'col':
                        $this->context->smarty->assign(array(
                            'class' => $item['col'] . ' ' .$item['col_xs'] . ' ' . $item['col_sm'] . ' ' . $item['col_md'] . ' ' . $item['col_lg']. ' ' . $item['col_xl']. ' ' . $item['col_xxl'],
                            'position' => $positions[$item['id_item']],
                            'elem' => $item,
                            'preview' => $preview
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/admin/layouts/col.tpl');
                        break;
                    case 'block':
                        $this->context->smarty->assign(array(
                            'elem' => $item,
                            'preview' => $preview
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/admin/layouts/module.tpl');
                        break;
                    case 'content':
                        if (!$info = $this->getExtraContentItem($item['module_name'])) {
                            $item['warning'] = $this->l('Item does not exist anymore. ').$item['module_name'];
                        }

                        $this->context->smarty->assign(array(
                            'elem' => $item,
                            'preview' => $preview,
                            'info' => $info
                        ));
                        $positions[$id_parent] .= $this->display($this->_path, '/views/templates/admin/layouts/content.tpl');
                        break;
                }
            }
        }

        $level--;

        if ($level >= 0) {
            $html = $this->renderLayoutAdmin($map, $preview, $level, $positions);
        } else {
            $html = $positions[0];
        }

        return $html;
    }

    /**
     * Get layout html for back-office
     *
     * @param int $id_layout
     * @param bool $preview Preview or not
     * @return string Html of layout
     */
    public function getLayoutAdmin($id_layout, $preview = false)
    {
        $layout_array = $this->getLayoutItems($id_layout);
        $map = $this->generateLayoutMap($layout_array);

        $result = $this->renderLayoutAdmin($map, $preview);
        if ($preview && !$result) {
            return $this->displayWarning($this->l('Add some items to layout.'));
        }
        return $result;
    }

    /**
     * Get tabs config
     *
     * @return string Tabs html
     */
    public function getTabsConfig()
    {
        $tabs = array();

        foreach ($this->defLayoutHooks as $hook_name => $hook) {
            $tabs = array_merge($tabs, $this->getLayoutTabConfig($hook_name, $hook));
        }

        $tabs = array_merge($tabs, array(
            'Tools' => array(
                'content' => $this->renderToolsList(),
                'type' => 'settings',
                'id' => 'jxml-tools_tab',
                'tab_name' => $this->l('Tools')
            ),
            'Sections' => array(
                'content' => '',
                'type' => 'sections',
                'id' => 'jxml-sections',
                'tab_name' => $this->l('Sections'),
                'sections' => $this->defHookSections
            )
        ));
        return $tabs;
    }

    /**
     * Return layout content or back-office
     *
     * @param string $id_layout
     * @return string Html of layout content
     */
    public function renderLayoutContent($id_layout)
    {
        $tab = new JXMegaLayoutLayouts($id_layout);
        $this->context->smarty->assign('content', array(
            'layout' => $this->getLayoutAdmin($id_layout),
            'id_layout' => $id_layout,
            'hook_name' => $tab->hook_name,
            'status' => $tab->status,
            'partly_use' => $tab->getAssignedPages(true),
            'layout_name' => $tab->layout_name,
            'pages_list' => $this->getAvailablePagesList($id_layout)
        ));

        $layout_content = $this->display($this->_path, '/views/templates/admin/jxmegalayout-layout-content.tpl');
        $layout_buttons = $this->display($this->_path, '/views/templates/admin/jxmegalayout-layout-buttons.tpl');

        return array($layout_content, $layout_buttons);
    }

    /**
     * Check if layout is active for any page
     * to mark this layout as partly in use
     * @param $id_layout
     * @return array of assigned pages
     */
    public static function hasAssignedPages($id_layout)
    {
        $jxmegalayoutlayouts = new JXMegaLayoutLayouts($id_layout);
        return $jxmegalayoutlayouts->getAssignedPages(true);
    }

    /**
     * Return config for layout tab
     *
     * @param string $hook_name
     * @return string Tab html
     */
    protected function getLayoutTabConfig($hook_name, $hook)
    {
        $tab_array = array();
        $id_layout = JXMegaLayoutLayouts::getActiveLayoutId($hook_name, $this->id_shop);
        $layouts_list = JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->id_shop);


        if (!$id_layout || !$layouts_list) {
            $layout = null;
        } else {
            $layout = $this->getLayoutAdmin($id_layout);
        }

        $tab = new JXMegaLayoutLayouts($id_layout);
        $tab_array[$hook_name] = array(
            'layouts_list' => $layouts_list,
            'pages_list' => $this->getAvailablePagesList($id_layout),
            'layouts_list_json' => Tools::jsonEncode($layouts_list),
            'layout' => $layout,
            'id_layout' => $id_layout,
            'hook_name' => $hook_name,
            'section_name' => $hook['section'],
            'tab_name' => $hook['lang'],
            'type' => 'layout',
            'status' => $tab->status,
            'id' => '',
            'layout_name' => $tab->layout_name
        );

        return $tab_array;
    }

    /**
     * Render layout tab
     *
     * @param string $hook_name
     * @return string Layout tab html
     */
    public function renderLayoutTab($hook_name)
    {
        if ($hook_name == 'displayProductInfo') {
            $this->context->smarty->assign(array(
                'themes' => $this->productInfoLayouts,
                'cur_theme' => $this->context->shop->theme_name,
                'active_template' => Configuration::get('jxmegalayout_poduct_layout')
            ));

            return $this->display($this->_path, '/views/templates/admin/jxmegalayout-tab-product-content.tpl');
        }

        $tab = $this->getLayoutTabConfig($hook_name, $this->defLayoutHooks[$hook_name]);

        $this->context->smarty->assign(array(
            'content' => $tab[$hook_name],
            'templates_dir' => _PS_MODULE_DIR_ . 'jxmegalayout/views/templates/admin/',
        ));
        
        return $this->display($this->_path, '/views/templates/admin/jxmegalayout-tab-content.tpl');
    }

    /**
     * Generate html of hook
     *
     * @param int $hook_name
     * @return html Layout html
     */
    public function getLayoutFront($hook_name, $params = array())
    {
        $page_name = $this->context->controller->php_self;
        if (!$id_active_layout = JXMegaLayoutLayouts::getPageActiveLayoutId($hook_name, $page_name, $this->id_shop)) {
            if ($page_name != 'index') {
                if (!$id_active_layout = JXMegaLayoutLayouts::getPageActiveLayoutId($hook_name, 'subpages', $this->id_shop)) {
                    if (!$id_active_layout = JXMegaLayoutLayouts::getActiveLayoutId($hook_name, $this->id_shop)) {
                        return false;
                    }
                }
            } else {
                if (!$id_active_layout = JXMegaLayoutLayouts::getActiveLayoutId($hook_name, $this->id_shop)) {
                    return false;
                }
            }
        }
        $layouts_array = $this->getLayoutItems($id_active_layout);
        $map = $this->generateLayoutMap($layouts_array);

        if (count($layouts_array) > 0) {
            return $this->renderLayoutFront($map, null, array(), $params);
        } else {
            return false;
        }
    }

    /**
     * Return id shop
     *
     * @return int Shop id
     */
    public function getIdShop()
    {
        return $this->id_shop;
    }

    /**
     * Return web path of module
     *
     * @return string Web path
     */
    public function getWebPath()
    {
        return $this->_path;
    }

    /**
     * Check module status
     *
     * @param string $module_name
     * @return bool or string False or Warning
     */
    public function checkModuleStatus($module_name)
    {
        if (!Module::isInstalled($module_name)) {
            return sprintf($this->l('Module "%s" is not installed'), $module_name);
        } elseif (Module::getInstanceByName($module_name)->active == 0) {
            return sprintf($this->l('Module "%s" is not active'), $module_name);
        }
        return false;
    }

    /**
     * Check for new layouts
     *
     * @param string $hook_name
     * @param array $old_layouts
     * @return array New layouts
     */
    public function checkNewLayouts($hook_name, $old_layouts)
    {
        $layouts = JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->id_shop);
        $new_layouts = array();
        if ($layouts) {
            foreach ($layouts as $layout) {
                $new = false;
                if ($old_layouts) {
                    foreach ($old_layouts as $old_layout) {
                        if ($old_layout['id_layout'] == $layout['id_layout']) {
                            $new = false;
                            break;
                        } else {
                            $new = true;
                        }
                    }
                } else {
                    return $layouts;
                }
                if ($new) {
                    $new_layouts[] = $layout;
                }
            }
        }
        return $new_layouts;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('ui.sortable');
            $this->context->controller->addJqueryPlugin('colorpicker');
            $this->context->controller->addJS($this->_path . 'views/js/bootstrap-multiselect.js');
            $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
            $this->context->controller->addJS($this->_path . 'views/js/jxmegalayout_admin.js');
            $this->context->controller->addCSS($this->_path . 'views/css/jxmegalayout_admin.css');
            $this->addOptionsToBack();
        }
    }

    /**
     * @param int $id_unique
     * @return html Form with item styles
     */
    public function getItemStyles($id_unique)
    {
        $this->context->smarty->assign('id_unique', $id_unique);

        if ($this->checkUniqueStylesExists($id_unique)) {
            if ($content = $this->getStylesContent($id_unique)) {
                $styles = $this->encodeStyles($content);
                $this->context->smarty->assign('styles', $styles);
            }
        }

        return $this->display($this->_path, 'views/templates/admin/tools/styles.tpl');
    }

    /**
     * @param int $id_unique
     * @return bool||string image url
     */
    public function getItemImageUrl($id_unique)
    {
        if ($this->checkUniqueStylesExists($id_unique)) {
            if ($content = $this->getStylesContent($id_unique)) {
                $bgImage = $this->encodeStyles($content);
                if (!isset($bgImage['background_image'])) {
                    return false;
                }

                return $bgImage['background_image'];
            }
        }

        return false;
    }

    /**
     * @param int $id_unique
     * @param string $style_path Path to styles
     * @return bool Styles exist
     */
    public function checkUniqueStylesExists($id_unique, $style_path = null)
    {
        if ($style_path == null) {
            $style_path = $this->style_path;
        }

        if (!file_exists($style_path . $id_unique . '.css')) {
            return false;
        }

        return true;
    }

    /**
     * @param int $id_unique
     * @param string $style_path
     * @return bool|string False or Styles
     */
    public function getStylesContent($id_unique, $style_path = null)
    {
        if ($style_path == null) {
            $style_path = $this->style_path;
        }
        if (!$content = Tools::file_get_contents($style_path . $id_unique . '.css')) {
            return false;
        }

        return $content;
    }

    /**
     * @param string $styles
     * @return array Array of styles
     */
    public function encodeStyles($styles)
    {
        $styles_content = $this->getStyleContent($styles);
        
        return $this->convertToStylesArray($styles_content);
    }

    /**
     * @param string $styles
     * @return string Item styles
     */
    protected function getStyleContent($styles)
    {
        $content = explode('{', str_replace('}', '', $styles));

        return trim($content[1]);
    }

    /**
     * @param string $data Styles
     * @return array Styles array
     */
    protected function convertToStylesArray($data)
    {
        $styles = array();
        $rows = explode(';', trim($data));

        foreach ($rows as $row) {
            $row = explode(':', $row);

            if ($row[0] && $row[1]) {
                $styles[str_replace('-', '_', trim($row[0]))] = trim($row[1]);
            }
        }

        return $styles;
    }

    public function getModuleExtraCss($unique_id)
    {

        if (is_dir($this->style_path.'modules/'.$unique_id)) {
            $css = Tools::scandir($this->style_path.'modules/'.$unique_id, 'css');
            if ($css) {
                return $css;
            }
        }
        return false;
    }

    /**
     * @param int $id_unique
     * @param string $styles
     * @param string $style_path Path to styles
     * @param bool $import Save mode
     * @return bool True if file saved
     */
    public function saveItemStyles($id_unique, $styles, $style_path = null, $import = false)
    {
        if ($style_path == null) {
            $style_path = $this->style_path;
        }
        if ($id_unique && $styles) {
            $content = $this->generateItemStyles($id_unique, $styles);
            $file = fopen($style_path . $id_unique . '.css', 'w');
            fwrite($file, $content);
            fclose($file);
            if (!$import) {
                $this->combineAllItemsStyles();
            }

            return true;
        }
    }

    /**
     * @param int $id_unique
     * @param string $styles string
     * @return string Styles
     */
    protected function generateItemStyles($id_unique, $styles)
    {
        $style = '';
        $style .= '.' . $id_unique . ' {';

        foreach ($styles as $key => $value) {
            if ($key && $value) {
                $key = str_replace('_', '-', $key);
                $style .= $key . ':' . $value . ';';
            }
        }

        $style .= '}';

        return $style;
    }

    /**
     * Delete item styles
     * @param int $id_unique
     * @return bool True if styles deleted
     */
    public function deleteItemStyles($id_unique, $all = false)
    {
        $res = true;

        if ($this->checkUniqueStylesExists($id_unique)) {
            $res &= @unlink($this->style_path . $id_unique . '.css');
            $res &= $this->combineAllItemsStyles();
        }

        if ($all && is_dir($this->style_path.'modules/'.$id_unique)) {
            $dirname = $this->style_path.'modules/'.$id_unique;
            $files = Tools::scandir($dirname, 'css');
            if (file_exists($this->style_path.'modules/'.$id_unique.'/index.php')) {
                $files[] = 'index.php';
            }
            if ($files) {
                foreach ($files as $file) {
                    $res &= @unlink($dirname.'/'.$file);
                }
            }
            $res &= rmdir($dirname);
        }

        return $res;
    }

    /**
     * Combine all active styles to main file
     *
     * @return bool True if styles combinated
     */
    public function combineAllItemsStyles()
    {
        $dir_files = Tools::scandir($this->style_path, 'css');
        $active_files = JXMegaLayoutItems::getShopItemsStyles();
        $combined_css = '';

        foreach ($dir_files as $dir_file) {
            if ($active_files) {
                if (file_exists($this->style_path . $dir_file) && in_array(str_replace('.css', '', $dir_file), $active_files)) {
                    $combined_css .= Tools::file_get_contents($this->style_path . $dir_file) . "\n";
                }
            }
        }

        if (!Tools::isEmpty($combined_css)) {
            // combine all custom style to one css file
            $file = fopen($this->style_path . 'combined_unique_styles_' . $this->context->shop->id . '.css', 'w');
            fwrite($file, $combined_css);
            fclose($file);
        } else {
            // remove combined css file if no custom style exists
            if (file_exists($this->style_path . 'combined_unique_styles_' . $this->context->shop->id . '.css')) {
                @unlink($this->style_path . 'combined_unique_styles_' . $this->context->shop->id . '.css');
            }
        }

        return true;
    }

    /**
     * Copy from $src to $dst
     * @param string $src Folder
     * @param string $dst Folder
     */
    public static function recurseCopy($src, $dst)
    {
        @mkdir($dst);

        if (file_exists($src)) {
            $files = scandir($src);
            foreach ($files as $file) {
                if (( $file != '.' ) && ( $file != '..' )) {
                    if (is_dir($src . '/' . $file)) {
                        Jxmegalayout::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
        }
    }

    /**
     * Get module icon if exists
     * @return bool|string path to image or false
     */
    public static function getModuleIcon($module_name)
    {
        if (file_exists(_PS_MODULE_DIR_.$module_name.'/logo.png')) {
            $image = _MODULE_DIR_.$module_name.'/logo.png';
        } elseif (file_exists(_PS_MODULE_DIR_.$module_name.'/logo.gif')) {
            $image = _MODULE_DIR_.$module_name.'/logo.gif';
        } else {
            $image = false;
        }

        return $image;
    }

    /**
     * @return int|string Max file size to upload
     */
    public static function getMaxFileSize()
    {
        $max_file_size = ini_get('post_max_size');
        $result = trim($max_file_size);
        $last = Tools::strtolower($result);

        switch ($last) {
            case 'g':
                $result *= 1024;
                break;
            case 'm':
                $result *= 1024;
                break;
            case 'k':
                $result *= 1024;
                break;
        }

        return $result;
    }

    /**
     * Check permission on file, and rewrite it
     *
     * @param string $file Path to file
     * @param string $new_perms New permissions
     * @return bool
     */
    public static function checkPerms($file, $new_perms)
    {
        $perms = fileperms($file);

        $info = '';
        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                        (($perms & 0x0800) ? 's' : 'x' ) :
                        (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                        (($perms & 0x0400) ? 's' : 'x' ) :
                        (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                        (($perms & 0x0200) ? 't' : 'x' ) :
                        (($perms & 0x0200) ? 'T' : '-'));

        if ($info != 'rwxrwxrwx') {
            chmod($file, $new_perms);
        }

        return true;
    }

    /**
     * Render layout form
     *
     * @param string $hook_name
     * @return string Html of form
     */
    public function addLayoutForm($hook_name)
    {
        $this->context->smarty->assign('hook_name', $hook_name);

        return $this->display(__FILE__, 'views/templates/admin/jxmegalayout_add-layout.tpl');
    }

    /**
     * Render module form
     *
     * @param string $hook_name
     * @param int $id_layout
     * @return string
     */
    public function addModuleForm($hook_name, $id_layout)
    {
        $this->context->smarty->assign(
            'modules_list',
            array_merge($this->getHookModulesList($hook_name, $id_layout), $this->renderBlockList($hook_name, $id_layout))
        );
        $this->context->smarty->assign('hook_name', $hook_name);

        return $this->display($this->_path, 'views/templates/admin/tools/modules-select.tpl');
    }

    /**
     * Render module form
     *
     * @return string
     */
    public function addExtraContentForm()
    {
        $html = JXMegaLayoutExtraHtml::getList($this->context->language->id);
        $banner = JXMegaLayoutExtraBanner::getList($this->context->language->id);
        $video = JXMegaLayoutExtraVideo::getList($this->context->language->id);
        $slider = JXMegaLayoutExtraSlider::getList($this->context->language->id);

        $content_list = array('html' => $html, 'banner' => $banner, 'video' => $video, 'slider' => $slider, 'product' => false);
        if (!$this->checkModuleStatus('jxblog')) {
            $content_list = array_merge($content_list, array('post' => false));
        }

        $this->context->smarty->assign('content_list', $content_list);

        return $this->display($this->_path, 'views/templates/admin/tools/extra-content-select.tpl');
    }

    /**
     * @param string $hook_name
     * @param string $layout_name
     * @return bool|int If layout added return id
     */
    public function addLayout($hook_name, $layout_name)
    {
        $layout = new JXMegaLayoutLayouts();
        $layout->hook_name = $hook_name;
        $layout->layout_name = $layout_name;
        $layout->id_shop = $this->id_shop;

        if (!$this->addFilesToLayout($layout_name)) {
            return fasle;
        }

        if (!$layout->save()) {
            return false;
        }

        return $layout->id;
    }

    /**
     * Add layout media files
     *
     * @param string $layout_name
     * @return bool
     */
    public function addFilesToLayout($layout_name)
    {
        $comment = '/* This comment is here to prevent an error during the including an empty file */';
        $result = true;
        $css_file = fopen($this->css_layouts_path.$layout_name . '.css', 'w');
        // add empty comment to prevent error when empty file included
        fwrite($css_file, $comment);
        $result &= fclose($css_file);
        $js_file = fopen($this->js_layouts_path.$layout_name . '.js', 'w');
        fwrite($js_file, $comment);
        $result &= fclose($js_file);

        return $result;
    }

    /**
     * Rename layout media files
     *
     * @param int $id_layout
     * @param string $layout_name
     * @return bool
     */
    public function renameFilesOfLayout($id_layout, $layout_name)
    {
        $old_layout_name = JXMegaLayoutLayouts::getLayoutName($id_layout);
        $result = true;

        if (file_exists($this->css_layouts_path.$old_layout_name.'.css')) {
            $result &= rename($this->css_layouts_path.$old_layout_name.'.css', $this->css_layouts_path.$layout_name.'.css');
        } else {
            $file = fopen($this->css_layouts_path.$layout_name.'.css', 'w');
            $result &= fclose($file);
        }

        if (file_exists($this->js_layouts_path.$old_layout_name.'.js')) {
            $result &= rename($this->js_layouts_path.$old_layout_name.'.js', $this->js_layouts_path.$layout_name.'.js');
        } else {
            $file = fopen($this->js_layouts_path.$layout_name.'.js', 'w');
            $result &= fclose($file);
        }

        return $result;
    }

    /**
     * Delete layout media files
     *
     * @param int $id_layout
     * @return bool
     */
    public function deleteFilesOfLayout($id_layout)
    {
        $old_layout_name = JXMegaLayoutLayouts::getLayoutName($id_layout);
        $result = true;
        $result &= @unlink($this->css_layouts_path.$old_layout_name.'.css');
        $result &= @unlink($this->js_layouts_path.$old_layout_name.'.js');

        return $result;
    }

    protected function addOptionsToBack()
    {
        $def = array();
        foreach (array_keys($this->defaultOptions) as $name) {
            $value =  ConfigurationCore::get($name);
            $def[$name] = $value;
        }

        Media::addJsDef($def);
    }

    /**
     *  Add media files of active layouts
     */
    public function addMediaToFront()
    {
        $active_layouts = JXMegaLayoutLayouts::getActiveLayouts();
        $current_page = $this->context->controller->php_self;

        foreach ($active_layouts as $file_name => $data) {
            if ($data['status']) {
                if (($data['hook_name'] == 'displayHome' && $current_page != 'index')
                    || ($data['hook_name'] == 'displayFooterProduct' && $current_page != 'product')) {
                    continue;
                }
                $this->includeMediaFiles($file_name);
            } elseif ($data['pages']) {
                foreach ($data['pages'] as $page) {
                    if ($current_page == $page) {
                        $this->includeMediaFiles($file_name);
                    } elseif ($page == 'subpages' && $current_page != 'index') {
                        $this->includeMediaFiles($file_name);
                    }
                }
            }
        }
    }

    public function addExtraCssFiles()
    {
        $active_layouts = JXMegaLayoutItems::getShopItemsStyles();
        if ($active_layouts) {
            foreach ($active_layouts as $id_item => $unique_id) {
                $item = new JXMegaLayoutItems($id_item);
                if ($item->extra_css) {
                    $this->context->controller->addCSS($this->_path . 'views/css/items/modules/'.$unique_id.'/'.$item->extra_css.'.css');
                }
            }
        }
    }

    /**
     * Include js and css file by layout name
     *
     * @param $file_name name of files
     */
    protected function includeMediaFiles($file_name)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/layouts/'.$file_name.'.css');
        $this->context->controller->addJs($this->_path . 'views/js/layouts/'.$file_name.'.js');
    }

    /**
     * Render message
     *
     * @param int $id_layout
     * @param string $action
     * @param string $text
     * @return string Html of message
     */
    public function showMessage($id_layout, $action, $text = '')
    {
        $this->context->smarty->assign('message', array(
            'type' => $action,
            'id_layout' => $id_layout,
            'text' => $text
        ));

        return $this->display($this->_path, 'views/templates/admin/tools/messages.tpl');
    }

    public function getProductInfoLayouts()
    {
        if (!$this->checkProductInfoLayoutConfig() || !$settings = $this->getProductInfoLayoutSettings()) {
            return false;
        }

        $templates = $this->filterProductInfoLayouts($settings);

        return $templates;
    }

    public function checkProductInfoLayoutConfig()
    {
        if (!file_exists($this->theme_dir.'product_pages/config.json')) {
            return false;
        }
        return true;
    }

    public function getProductInfoLayoutSettings()
    {
        $file = $this->theme_dir.'product_pages/config.json';

        if (!$content = Tools::file_get_contents($file)) {
            return false;
        }

        return Tools::jsonDecode($content, true);
    }

    public function filterProductInfoLayouts($templates)
    {
        if (!is_array($templates)) {
            return false;
        }

        foreach ($templates as $key => $data) {
            if (!Validate::isTplName($key) || !file_exists($this->theme_dir.'product_pages/'.$key.'.tpl')) {
                unset($templates[$key]);
            }
            if (!Validate::isFileName($data['preview']) || !file_exists($this->theme_dir.'product_pages/'.$data['preview'])) {
                $templates[$key]['preview'] = false;
            }
            if (!Validate::isGenericName($data['name'])) {
                $templates[$key]['name'] = false;
            }
        }

        if (!$templates) {
            return false;
        }

        return $templates;
    }

    public function productInfoLayoutsDefault()
    {
        foreach ($this->productInfoLayouts as $name => $data) {
            if ($data['default']) {
                return $name;
            }
        }

        return false;
    }

    public function hookActionObjectShopAddAfter($params)
    {
        return $params;
    }

    public function hookHeader()
    {
        $this->context->controller->registerJavascript('swiper', 'modules/' .$this->name. '/views/js/swiper.min.js', array('media' => 'all', 'priority' => 10));
        $this->context->controller->registerStylesheet('swiper', 'modules/' .$this->name. '/views/css/swiper.min.css', array('media' => 'all', 'priority' => 10));
        $this->context->controller->addJS($this->_path . '/views/js/jxmegalayout.js');
        $this->context->controller->addCSS($this->_path . '/views/css/jxmegalayout.css');
        $this->addMediaToFront();
        $this->addExtraCssFiles();
        $this->context->controller->addCSS($this->_path . '/views/css/items/combined_unique_styles_' . $this->context->shop->id . '.css');
        if ($this->context->controller->php_self == 'product') {
            $layout = Configuration::get('jxmegalayout_poduct_layout');
            if (!$layout) {
                $this->setDefaultPageInfoLayout();
            }
            if ($layout) {
                $this->context->controller->addCss($this->theme_dir . 'css/product_pages/' . $layout . '.css');
                $this->context->controller->addJs($this->theme_dir . 'js/product_pages/' . $layout . '.js');
                $this->context->smarty->assign('megalayoutProductInfoPage', $layout . '.tpl');
            }
        }
    }

    public function hookJxMegaLayoutHeader()
    {
        $this->context->smarty->assign('isMegaHeader', true);

        return $this->getLayoutFront('displayHeader');
    }

    public function hookJxMegaLayoutTopColumn()
    {
        $this->context->smarty->assign('isMegaTopColumn', true);
        return $this->getLayoutFront('displayTopColumn');
    }

    public function hookJxMegaLayoutHome()
    {
        $this->context->smarty->assign('isMegaHome', true);

        return $this->getLayoutFront('displayHome');
    }

    public function hookJxMegaLayoutFooter()
    {
        $this->context->smarty->assign('isMegaFooter', true);
        return $this->getLayoutFront('displayFooter');
    }

    public function hookJxMegaLayoutProductFooter($params)
    {
        $this->context->smarty->assign('isMegaProductFooter', true);
        return $this->getLayoutFront('displayFooterProduct', $params);
    }
}
