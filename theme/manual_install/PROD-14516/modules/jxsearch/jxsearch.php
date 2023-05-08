<?php
/*
* 2017-2018 Zemez
*
* JX Search
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

require_once(dirname(__FILE__).'/classes/JXSearchSearch.php');
require_once(dirname(__FILE__).'/classes/JxSearchProvider.php');

class Jxsearch extends Module implements WidgetInterface
{
    protected $config_form = false;
    private $categories_list = array();
    private $spacer_size = '1';
    private $blog = false;

    public function __construct()
    {
        $this->name = 'jxsearch';
        $this->tab = 'front_office_features';
        $this->version = '1.3.1';
        $this->author = 'Zemez (Alexander Grosul)';
        $this->need_instance = 0;
        $this->controllers = array('jxsearch');
        $this->bootstrap = true;
        $this->module_key = '3c1e3abe05cc92554a08725fd7d91a8c';
        parent::__construct();

        $this->controllers = array('search');
        $this->displayName = $this->l('JX Search');
        $this->description = $this->l('Adds a quick search field to your website.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        // check if jx blog is active
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();
        if ($moduleManager->isInstalled('jxblog') && $moduleManager->isEnabled('jxblog')) {
            $this->blog = true;
        }
    }

    public function install()
    {
        $settings = $this->moduleSettings();

        foreach ($settings as $name => $value) {
            Configuration::updateValue($name, $value);
        }

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('displayTop');
    }

    public function uninstall()
    {
        $settings = $this->moduleSettings();

        foreach (array_keys($settings) as $name) {
            if ($name != 'PS_SEARCH_MINWORDLEN') {
                Configuration::deleteByName($name);
            }
        }

        return parent::uninstall();
    }

    /**
     * Array with all settings and default values
     * @return array $setting
     */
    protected function moduleSettings()
    {
        $settings = array(
            'PS_JXSEARCH_AJAX' => true,
            'PS_SEARCH_MINWORDLEN' => 3,
            'PS_SEARCH_CHILDREN' => false,
            'PS_JXSEARCH_ITEMS_SHOW' => 3,
            'PS_JXSEARCH_SHOWALL' => true,
            'PS_JXSEARCH_PAGER' => true,
            'PS_JXSEARCH_NAVIGATION' => true,
            'PS_JXSEARCH_NAVIGATION_POSITION' => 'bottom',
            'PS_JXSEARCH_HIGHLIGHT' => false,
            'PS_JXSEARCH_AJAX_IMAGE' => true,
            'PS_JXSEARCH_AJAX_DESCRIPTION' => true,
            'PS_JXSEARCH_AJAX_PRICE' => true,
            'PS_JXSEARCH_AJAX_REFERENCE' => true,
            'PS_JXSEARCH_AJAX_MANUFACTURER' => true,
            'PS_JXSEARCH_AJAX_SUPPLIERS' => true,
            'PS_JXSEARCH_BLOG' => false
        );

        return $settings;
    }

    public function getContent()
    {
        $output = '';
        if ((bool)Tools::isSubmit('submitJxsearchModule') == true) {
            if (!$erros = $this->preValidate()) {
                $this->postProcess();
                $output .= $this->displayConfirmation($this->l('Settings successfully saved'));
            } else {
                $output .=  $erros;
            }
        }

        return $output.$this->renderForm();
    }

    private function preValidate()
    {
        $minquery = Tools::getValue('PS_SEARCH_MINWORDLEN');
        $shownumber = Tools::getValue('PS_JXSEARCH_ITEMS_SHOW');
        $errors = array();
        if (Tools::isEmpty($minquery) || !Validate::isInt($minquery) || $minquery < 1) {
            $errors[] = $this->l('\"Minimum query length\" is invalid. Must be an integer number > 1');
        }
        if (Tools::isEmpty($shownumber) || !Validate::isInt($shownumber) || $shownumber < 1) {
            $errors[] = $this->l('\"Number of shown results\" is invalid. Must be an integer number > 1');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
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
        $helper->submit_action = 'submitJxsearchModule';
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
        $form = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Ajax Search'),
                        'name' => 'PS_JXSEARCH_AJAX',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block instant-block',
                        'type' => 'text',
                        'label' => $this->l('Minimum query length'),
                        'name' => 'PS_SEARCH_MINWORDLEN',
                        'col' => 2,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Search in children category'),
                        'name' => 'PS_SEARCH_CHILDREN',
                        'is_bool' => false,
                        'desc' => $this->l('Do you want to allow searching in children categories?'),
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
                        'form_group_class' => 'ajax-block',
                        'type' => 'text',
                        'label' => $this->l('Number of shown results'),
                        'name' => 'PS_JXSEARCH_ITEMS_SHOW',
                        'col' => 2,
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display "Show All" button'),
                        'name' => 'PS_JXSEARCH_SHOWALL',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display pager'),
                        'name' => 'PS_JXSEARCH_PAGER',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display navigation'),
                        'name' => 'PS_JXSEARCH_NAVIGATION',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block navigation-block',
                        'type' => 'select',
                        'label' => $this->l('Position of navigation'),
                        'name' => 'PS_JXSEARCH_NAVIGATION_POSITION',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'top',
                                    'name' => $this->l('top')),
                                array(
                                    'id' => 'bottom',
                                    'name' => $this->l('bottom')),
                                array(
                                    'id' => 'both',
                                    'name' => $this->l('both')),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Highlight query result'),
                        'name' => 'PS_JXSEARCH_HIGHLIGHT',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display image in Ajax search'),
                        'name' => 'PS_JXSEARCH_AJAX_IMAGE',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display description in Ajax search'),
                        'name' => 'PS_JXSEARCH_AJAX_DESCRIPTION',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display prices in Ajax search'),
                        'name' => 'PS_JXSEARCH_AJAX_PRICE',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display reference in Ajax search'),
                        'name' => 'PS_JXSEARCH_AJAX_REFERENCE',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display manufacturer in Ajax search'),
                        'name' => 'PS_JXSEARCH_AJAX_MANUFACTURER',
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
                        ),
                    ),
                    array(
                        'form_group_class' => 'ajax-block',
                        'type' => 'switch',
                        'label' => $this->l('Display suppliers in Ajax search'),
                        'name' => 'PS_JXSEARCH_AJAX_SUPPLIERS',
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
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        // check if JX Blog is active to add extra field in the main Jxsearch form
        if ($this->blog) {
            $form['form']['input'][] = array(
                'type'    => 'switch',
                'label'   => $this->l('Blog search'),
                'name'    => 'PS_JXSEARCH_BLOG',
                'is_bool' => false,
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
            );
        }

        return $form;
    }

    protected function getConfigFormValues()
    {
        $filled_settings = array();
        $settings = $this->moduleSettings();

        foreach (array_keys($settings) as $name) {
            $filled_settings[$name] = Configuration::get($name);
        }

        return $filled_settings;
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Get name category for form add category
     * @return array $this->categories_list
     */
    public function getCategoriesList()
    {
        $category = new Category();
        $this->generateCategoriesOption($category->getNestedCategories((int)Configuration::get('PS_HOME_CATEGORY'), $this->context->language->id), true);

        return $this->categories_list;
    }

    public function getLocalPath()
    {
        return $this->_path;
    }

    /**
     * Categories option for generation list category
     * @param $categories
     */
    protected function generateCategoriesOption($categories, $disable_spacer = false)
    {
        $spacer = $this->spacer_size;

        if ($disable_spacer) {
            $spacer = 0;
        }

        foreach ($categories as $category) {
            array_push(
                $this->categories_list,
                array(
                    'id' => (int)$category['id_category'],
                    'name' => str_repeat('-', $spacer * (int)$category['level_depth']) . $category['name']
                )
            );
            if (isset($category['children']) && !empty($category['children'])) {
                $this->generateCategoriesOption($category['children']);
            }
        }
    }

    /******************************************************************************************************************/
                    // ------ This part is useful only in combination with a Jx blog ------ //
    /******************************************************************************************************************/
    public function getBlogCategoriesList()
    {
        $blogCategories = JXBlogCategory::getAllFrontCategories(
            $this->context->language->id,
            $this->context->shop->id,
            $this->context->customer->id_default_group,
            1,
            1000
        );

        return $blogCategories;
    }

    public static function getJXBlogSearchLink()
    {
        $bloglink = new Jxblog();

        return $bloglink->getBlogLink('search', array());
    }
    /******************************************************************************************************************/
                                            // ------ end ------ //
    /******************************************************************************************************************/
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/jxsearch_admin.js');
        }
    }

    public function hookModuleRoutes($params)
    {
        $my_link = array(
            'jxsearch' => array(
                'controller'    => 'jxsearch',
                'rule'          => 'jxsearch',
                'keywords'      => array(),
                'params'        => array(
                    'fc'            => 'module',
                    'module'        => 'jxsearch',
                ),
            )
        );

        return $my_link;
    }

    public static function getJXSearchUrl()
    {
        $ssl_enable = Configuration::get('PS_SSL_ENABLED');
        $id_lang = (int)Context::getContext()->language->id;
        $id_shop = (int)Context::getContext()->shop->id;
        $rewrite_set = (int)Configuration::get('PS_REWRITING_SETTINGS');
        $ssl = null;
        static $force_ssl = null;

        if ($ssl === null) {
            if ($force_ssl === null) {
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            }
            $ssl = $force_ssl;
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }

        if ($ssl && $ssl_enable) {
            $base = 'https://'.$shop->domain_ssl;
        } else {
            $base = 'http://'.$shop->domain;
        }
        $langUrl = Language::getIsoById($id_lang).'/';

        if ((!$rewrite_set && in_array($id_shop, array((int)Context::getContext()->shop->id,  null)))
            || !Language::isMultiLanguageActivated($id_shop)
            || !(int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $id_shop)) {
            $langUrl = '';
        }

        return $base.$shop->getBaseURI().$langUrl;
    }

    public static function getJXSearchLink($rewrite = 'jxsearch', $params = null, $id_shop = null, $id_lang = null)
    {
        $url = Jxsearch::getJXSearchUrl();
        $dispatcher = Dispatcher::getInstance();

        if ($params != null) {
            return $url . $dispatcher->createUrl($rewrite, $id_lang, $params);
        }

        return $url.$dispatcher->createUrl($rewrite);
    }

    public function hookHeader()
    {
        $this->getCategoriesList();

        $this->smarty->assign('search_categories', $this->categories_list);
        if (Configuration::get('PS_JXSEARCH_BLOG') && $this->blog) {
            $this->smarty->assign('search_blog_categories', $this->getBlogCategoriesList());
        }
        $this->context->controller->registerStylesheet('module-jxsearch', 'modules/'. $this->name .'/views/css/jxsearch.css');

        Media::addJsDef(array('use_jx_ajax_search' => false));
        Media::addJsDef(array('use_blog_search' => false));

        if (Configuration::get('PS_JXSEARCH_AJAX')) {
            Media::addJsDef(array('search_url_local' => $this->context->link->getModuleLink('jxsearch', 'ajaxsearch', array())));
            Media::addJsDef(array('jxsearch_showall_text' => $this->l('Display all results(%s more)')));
            Media::addJsDef(array('jxsearch_minlength' => Configuration::get('PS_SEARCH_MINWORDLEN')));
            Media::addJsDef(array('jxsearch_itemstoshow' => Configuration::get('PS_JXSEARCH_ITEMS_SHOW')));
            Media::addJsDef(array('jxsearch_showallresults' => Configuration::get('PS_JXSEARCH_SHOWALL')));
            Media::addJsDef(array('jxsearch_pager' => Configuration::get('PS_JXSEARCH_PAGER')));
            Media::addJsDef(array('jxsearch_navigation' => Configuration::get('PS_JXSEARCH_NAVIGATION')));
            Media::addJsDef(array('jxsearch_navigation_position' => Configuration::get('PS_JXSEARCH_NAVIGATION_POSITION')));
            Media::addJsDef(array('jxsearch_highlight' => Configuration::get('PS_JXSEARCH_HIGHLIGHT')));
        }

        if (Configuration::get('PS_JXSEARCH_AJAX')) {
            Media::addJsDef(array('use_jx_ajax_search' => true));
        }

        if (Configuration::get('PS_JXSEARCH_BLOG')) {
            Media::addJsDef(array('use_blog_search' => true));
            Media::addJsDef(array('blog_search_url' => $this->context->link->getModuleLink('jxblog', 'search', array('ajax' => true))));
        }


        if (Configuration::get('PS_JXSEARCH_AJAX')) {
            $this->context->controller->addJqueryPlugin('autocomplete');
            $this->context->controller->registerJavascript('module-jxsearch', 'modules/'. $this->name .'/views/js/jxsearch.js');
        }
    }

    private function calculHookCommon()
    {
        $this->smarty->assign(array(
            'ENT_QUOTES' =>        ENT_QUOTES,
            'search_ssl' =>        Tools::usingSecureMode(),
            'ajaxsearch' =>        Configuration::get('PS_JXSEARCH_AJAX'),
            'self' =>            dirname(__FILE__),
        ));

        return true;
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $this->calculHookCommon();
        $this->smarty->assign(
            array(
                'search_query' => (string)Tools::getValue('search_query'),
                'active_category' => (int)Tools::getValue('search_categories')
            )
        );
        if ($this->blog && Configuration::get('PS_JXSEARCH_BLOG')) {
            $this->smarty->assign(
                array(
                    'blog_search_query' => (string)Tools::getValue('blog_search_query'),
                    'active_blog_category' => (int)Tools::getValue('search_blog_categories')
                )
            );
        }
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached('jxsearch.tpl', $this->getCacheId())) {
            $this->getWidgetVariables($hookName, $configuration);
        }

        return $this->display($this->_path, '/views/templates/hook/jxsearch.tpl', $this->getCacheId());
    }
}
