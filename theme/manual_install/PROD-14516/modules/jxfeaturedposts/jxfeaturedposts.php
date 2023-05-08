<?php
/**
* 2017-2018 Zemez
*
* JX Featured Products
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
include_once(_PS_MODULE_DIR_.'jxblog/src/JXBlogTabManager.php');
include_once(_PS_MODULE_DIR_.'jxfeaturedposts/src/JXFeaturedPostsRepository.php');
include_once(_PS_MODULE_DIR_.'jxfeaturedposts/classes/JXFeaturedPost.php');

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class Jxfeaturedposts extends Module
{
    protected $repository;
    private $tabManager;
    protected $templateFile;
    public $settingsTab = array();
    public $settingsSubTabs = array();
    public $languages;

    public function __construct()
    {
        $this->name = 'jxfeaturedposts';
        $this->tab = 'front_office_features';
        $this->version = '0.0.3';
        $this->author = 'Zemez (Alexander Grosul)';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('JX Featured Posts');
        $this->description = $this->l('The module allows to show featured posts from JX Blog on your store homepage.');
        $this->confirmUninstall = $this->l('Are you sure want uninstall the module? All related data will be lost.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->languages = Language::getLanguages(true);
        $this->modulePath = $this->local_path;
        $this->repository = new JXFeaturedPostsRepository(
            Db::getInstance(),
            $this->context->shop
        );
        $this->tabs = array(
            array(
                'class_name' => 'AdminJXFeaturedPosts',
                'module'     => $this->name,
                'name'       => 'Featured posts'
            )
        );
        $this->settingsSubTabs = array(
            array(
                'class_name' => 'AdminJXFeaturedPostsSettings',
                'module'     => $this->name,
                'name'       => '\'Featured posts\' settings'
            )
        );
        $this->tabManager = new JXBlogTabManager($this, $this->tabs, $this->settingsSubTabs);
        $this->templateFile = 'module:jxfeaturedposts/views/templates/hook/jxfeaturedposts.tpl';
    }

    public function install()
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();
        if (!$moduleManager->isInstalled('jxblog') || !$moduleManager->isEnabled('jxblog')) {
            $this->_errors[] = $this->l('The module requires JX Blog module be installed and enabled. Install JX Blog module at first');
            return false;
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionJxblogPostAfterDelete') &&
            $this->registerHook('actionJxblogPostAfterUpdate') &&
            $this->registerHook('actionJxblogCategoryAfterUpdate') &&
            $this->registerHook('actionJxblogCategoryAfterDelete') &&
            $this->registerHook('actionJxblogImageAfterDelete') &&
            $this->registerHook('displayHome') &&
            $this->repository->createTables() &&
            $this->setSettings() &&
            $this->addTabs();
    }

    public function uninstall()
    {
        return $this->repository->dropTables() &&
            $this->removeTabs() &&
            parent::uninstall();
    }

    /**
     * Set default module settings after installation or adding new store
     *
     * @return bool
     */
    public function setSettings()
    {
        Configuration::updateValue('JXFEATUREDPOSTS_ITEMS_TO_SHOW', 4);
        Configuration::updateValue('JXFEATUREDPOSTS_ORDER', 3);

        return true;
    }

    /**
     * Add related to module tabs to the main navigation menu
     *
     * @return bool
     */
    protected function addTabs()
    {
        return $this->tabManager->addTabs();
    }

    /**
     * Add related to module tabs to the main navigation menu
     *
     * @return bool
     */
    protected function removeTabs()
    {
        return $this->tabManager->removeTabs();
    }

    public function hookActionJxblogPostAfterDelete($params)
    {
        $featuredPosts = JXFeaturedPost::getAllBlocksByPost($params['id_jxblog_post']);
        if ($featuredPosts) {
            foreach ($featuredPosts as $featuredPost) {
                $block = new JXFeaturedPost($featuredPost['id_featured_post']);
                $block->delete();
            }
        }

        $this->_clearCache('*');
    }

    public function hookActionJxblogPostAfterUpdate($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionJxblogCategoryAfterUpdate($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionJxblogCategoryAfterDelete($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionJxblogImageAfterDelete($params)
    {
        $this->_clearCache('*');
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/jxfeaturedposts.js');
        $this->context->controller->addCSS($this->_path.'/views/css/jxfeaturedposts.css');
    }

    public function hookDisplayHome()
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('jxfeaturedposts'))) {
            $posts = JXFeaturedPostsRepository::getShopFeaturedPosts(
                $this->context->shop->id,
                $this->context->language->id,
                $this->context->customer->id_default_group
            );

            $this->context->smarty->assign('featured_posts', $posts);
        }

        return $this->fetch($this->templateFile, $this->getCacheId('jxfeaturedposts'));
    }
}
