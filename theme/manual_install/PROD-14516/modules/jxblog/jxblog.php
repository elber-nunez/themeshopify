<?php
/**
 * 2017-2018 Zemez
 *
 * JX Blog
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

include_once(_PS_MODULE_DIR_.'jxblog/src/JXBlogRepository.php');
include_once(_PS_MODULE_DIR_.'jxblog/classes/JXBlogCategory.php');
include_once(_PS_MODULE_DIR_.'jxblog/classes/JXBlogPost.php');
include_once(_PS_MODULE_DIR_.'jxblog/classes/JXBlogTag.php');
include_once(_PS_MODULE_DIR_.'jxblog/classes/JXBlogImage.php');
include_once(_PS_MODULE_DIR_.'jxblog/classes/JXBlogImageManager.php');

class Jxblog extends Module
{
    public $repository;
    public $mainTab = array();
    public $tabs = array();
    public $languages;
    public $modulePath;
    public $_link;
    public $imagesAutoRegenerate;
    public $imageTypes;

    public function __construct()
    {
        $this->name = 'jxblog';
        $this->tab = 'content_management';
        $this->version = '0.0.2';
        $this->author = 'Zemez (Alexander Grosul)';
        $this->need_instance = 1;
        $this->controllers = array('author', 'categories', 'category', 'post', 'search', 'tag');
        $this->bootstrap = true;
        parent::__construct();
        $this->modulePath = $this->local_path;
        $this->_link = $this->_path;

        $this->displayName = $this->l('JX Blog');
        $this->description = $this->l('The best blog-extension for Prestashop platform.');
        $this->confirmUninstall = $this->l('Are you sure that you want to delete the module? All related data will be deleted forever!');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->languages = Language::getLanguages(true);
        $this->defaultLanguage = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $this->repository = new JXBlogRepository(Db::getInstance(), $this->context->shop);
        $this->mainTab = array(
            'class_name' => 'AdminJXBlog',
            'module' => $this->name,
            'name' => 'JX Blog'
        );

        $this->tabs = array(
            array(
                'class_name' => 'AdminJXBlogCategories',
                'module'     => $this->name,
                'name'       => 'Categories'
            ),
            array(
                'class_name' => 'AdminJXBlogPosts',
                'module'     => $this->name,
                'name'       => 'Posts'
            ),
            array(
                'class_name' => 'AdminJXBlogImages',
                'module'     => $this->name,
                'name'       => 'Images Manager'
            )
        );

        $this->settingsTab = array(
            'class_name' => 'AdminJXBlogSettings',
            'module'     => $this->name,
            'name'       => 'Settings'
        );

        $this->settingsSubTabs = array(
            array(
                'class_name' => 'AdminJXBlogMainSettings',
                'module'     => $this->name,
                'name'       => 'Main settings'
            )
        );
        $this->imageTypes = array(
            'default'        => array('default'),
            'category'       => array('c/', 'category'),
            'category_thumb' => array('ct/', 'category_thumb'),
            'post'           => array('p/', 'post'),
            'post_thumb'     => array('pt/', 'post_thumb'),
            'user'           => array('u/', 'user')
        );

        $this->imagesAutoRegenerate = Configuration::get('JXBLOG_IMAGES_AUTO_REGENERATION');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('moduleRoutes') &&
            $this->repository->createTables() &&
            $this->setSettings() &&
            $this->addTabs();
    }

    public function uninstall()
    {
        // use this hook to delete all related module which are not useful after this module deleting
        Hook::exec('actionJXBlogBeforeModuleDelete');

        return $this->repository->dropTables() &&
            $this->removeTabs() &&
            $this->removeSettings() &&
            $this->clearAllImages() &&
            parent::uninstall();
    }

    protected function addTabs()
    {
        $idMainTab = $this->addTab($this->mainTab, 0);

        foreach ($this->tabs as $item) {
            $this->addTab($item, $idMainTab);
        }

        $idSettingsTab = $this->addTab($this->settingsTab, $idMainTab);
        foreach ($this->settingsSubTabs as $item) {
            $this->addTab($item, $idSettingsTab);
        }

        return true;
    }

    public function addTab($tab, $parent)
    {
        $t = new Tab();
        $t->class_name = $tab['class_name'];
        $t->id_parent = (int)$parent;
        $t->module = $tab['module'];

        foreach ($this->languages as $lang) {
            $t->name[$lang['id_lang']] = $this->l($tab['name']);
        }

        if (!$t->save()) {
            return false;
        }

        return $t->id;
    }

    public function setSettings()
    {
        Configuration::updateValue('JXBLOG_IMAGES_AUTO_REGENERATION', 1);
        Configuration::updateValue('JXBLOG_DISPLAY_POST_AUTHOR', 1);
        Configuration::updateValue('JXBLOG_DISPLAY_POST_VIEWS', 1);
        Configuration::updateValue('JXBLOG_POSTS_PER_PAGE', 6);

        return true;
    }

    protected function removeTabs()
    {
        $tabId = TabCore::getIdFromClassName($this->mainTab['class_name']);
        $tab = new Tab($tabId);
        if (!$tab->delete()) {
            return false;
        }

        foreach (array_merge($this->tabs, $this->settingsSubTabs) as $t) {
            if ($t) {
                $t = new Tab(TabCore::getIdFromClassName($t['class_name']));
                if (!$t->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function removeSettings()
    {
        Configuration::deleteByName('JXBLOG_IMAGES_AUTO_REGENERATION');
        Configuration::deleteByName('JXBLOG_DISPLAY_POST_AUTHOR');
        Configuration::deleteByName('JXBLOG_DISPLAY_POST_VIEWS');
        Configuration::deleteByName('JXBLOG_POSTS_PER_PAGE');

        return true;
    }

    protected function clearAllImages()
    {
        $imagesList = array();
        $path = $this->modulePath.'/img/';
        foreach ($this->imageTypes as $type => $imageType) {
            if ($type != 'default') {
                $path = $this->modulePath.'/img/'.$imageType[0];
            }
            if ($images = Tools::scandir($path, 'jpg')) {
                foreach ($images as $image) {
                    array_push($imagesList, $path.$image);
                }
            }
        }

        if ($imagesList) {
            $imageManager = new JXBlogImageManager($this);
            return $imageManager->removeImagesByList($imagesList);
        }

        return true;
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-jxblog-categories'  => array(
                'controller' => 'categories',
                'rule'       => 'blog/categories',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-cpagination' => array(
                'controller' => 'categories',
                'rule'       => 'blog/categories/page/{page}',
                'keywords'   => array(
                    'page' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'page'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-category'    => array(
                'controller' => 'category',
                'rule'       => 'blog/category/{id_jxblog_category}/{rewrite}',
                'keywords'   => array(
                    'id_jxblog_category' => array('regexp' => '[0-9]+', 'param' => 'id_jxblog_category'),
                    'rewrite'            => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*')
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-tag'         => array(
                'controller' => 'tag',
                'rule'       => 'blog/tag/{id_jxblog_tag}',
                'keywords'   => array(
                    'id_jxblog_tag' => array('regexp' => '[0-9]+', 'param' => 'id_jxblog_tag')
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-tpagination' => array(
                'controller' => 'tag',
                'rule'       => 'blog/tag/{id_jxblog_tag}/page/{page}',
                'keywords'   => array(
                    'id_jxblog_tag' => array('regexp' => '[0-9]+', 'param' => 'id_jxblog_tag'),
                    'page'          => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'page'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-author'      => array(
                'controller' => 'author',
                'rule'       => 'blog/author/{author}',
                'keywords'   => array(
                    'author' => array('regexp' => '[0-9]+', 'param' => 'author')
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-apagination' => array(
                'controller' => 'author',
                'rule'       => 'blog/author/{author}/page/{page}',
                'keywords'   => array(
                    'author' => array('regexp' => '[0-9]+', 'param' => 'author'),
                    'page'   => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'page'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-pagination'  => array(
                'controller' => 'category',
                'rule'       => 'blog/category/{id_jxblog_category}/{rewrite}/page/{page}',
                'keywords'   => array(
                    'id_jxblog_category' => array('regexp' => '[0-9]+', 'param' => 'id_jxblog_category'),
                    'rewrite'            => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'page'               => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'page'),
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-post'        => array(
                'controller' => 'post',
                'rule'       => 'blog/post/{id_jxblog_post}/{rewrite}',
                'keywords'   => array(
                    'id_jxblog_post' => array('regexp' => '[0-9]+', 'param' => 'id_jxblog_post'),
                    'rewrite'        => array('regexp' => '[_a-zA-Z0-9\pL\pS-]*')
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
            'module-jxblog-search'      => array(
                'controller' => 'search',
                'rule'       => 'blog/search/',
                'keywords'   => array(
                ),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxblog',
                )
            ),
        );
    }

    /**
     * Used to access blog links from external modules
     *
     * @param $page
     * @param $params
     *
     * @return string
     */
    public function getBlogLink($page, $params)
    {
        $link = new Link();
        return $link->getModuleLink($this->name, $page, $params);
    }

    public function buildPagination($controller, $totalItems, $page, $itemsPerPage, $id = false, $rewrite = '')
    {
        if (!$totalItems) {
            return false;
        }
        $link = new Link();
        $pagination = array();
        $itemShowFrom = 1;
        $itemShowTo = $page * $itemsPerPage;
        if ($page > 1) {
            $itemShowFrom = ($itemsPerPage * ($page - 1)) + 1;
        }
        if ($itemShowTo > $totalItems) {
            $itemShowTo = $totalItems;
        }
        $params = array();
        if ($id) {
            $params = array('id_jxblog_category' => $id, 'rewrite' => $rewrite);
        }
        if ($controller == 'tpagination') {
            $params = array('id_jxblog_tag' => $id);
        }
        if ($controller == 'apagination') {
            $params = array('author' => $id);
        }
        $i = 0;
        $totalPages = ceil($totalItems/$itemsPerPage);
        $pagination['total'] = $totalItems;
        $pagination['from'] = $itemShowFrom;
        $pagination['to'] = $itemShowTo;
        if ($totalItems > $itemsPerPage) {
            $pagination['steps'][$i] = array(
                'type'   => 'previous',
                'url'    => $link->getModuleLink(
                    'jxblog',
                    $controller,
                    array_merge(array('page' => $page > 1 ? $page - 1 : $page), $params)
                ),
                'name'   => $this->trans('Previous', array(), 'Modules.JXBlog.Admin'),
                'active' => ($page == 1) ? true : false
            );
            for ($i = &$i; $i < $totalPages; $i++) {
                $pagination['steps'][$i + 1]['type'] = 'page';
                $pagination['steps'][$i + 1]['url'] = $link->getModuleLink('jxblog', $controller, array_merge(array('page' => $i + 1), $params));
                $pagination['steps'][$i + 1]['name'] = $i + 1;
                $pagination['steps'][$i + 1]['active'] = ($page == $i + 1) || (!$page && $i == 0) ? true : false;
            }
            $pagination['steps'][$i + 1] = array(
                'type'   => 'next',
                'url'    => $link->getModuleLink(
                    'jxblog',
                    $controller,
                    array_merge(array('page' => $page < $totalPages ? $page + 1 : $page), $params)
                ),
                'name'   => $this->trans('Next', array(), 'Modules.JXBlog.Admin'),
                'active' => ($page == $totalPages) ? true : false
            );
        }

        return $pagination;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/jxblog_admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/jxblog_admin.css');
        }
    }

    public function hookHeader()
    {
        $this->context->controller->requireAssets(array('font-awesome'));
        $this->context->controller->addJS($this->_path.'/views/js/jxblog.js');
        $this->context->controller->addCSS($this->_path.'/views/css/jxblog.css');
    }
}
