<?php
/**
 * 2017-2018 Zemez
 *
 * JX Blog Post Products
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

include_once(_PS_MODULE_DIR_.'jxblogpostproducts/src/JXBlogPostProductsRepository.php');

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class Jxblogpostproducts extends Module
{
    public $repository;
    public $settingsTab = array();
    public $settingsSubTabs = array();
    public $languages;

    public function __construct()
    {
        $this->name = 'jxblogpostproducts';
        $this->tab = 'content_management';
        $this->version = '0.0.1';
        $this->author = 'Zemez';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('JX Blog Post Products');
        $this->description = $this->l('The module allows binding products to posts.');
        $this->confirmUninstall = $this->l(
            'Are you sure that you want to delete the module? All related data will be deleted forever!'
        );
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->languages = Language::getLanguages(true);
        $this->repository = new JxblogPostProductsRepository(
            Db::getInstance(),
            $this->context->shop
        );

        $this->settingsTab = array(
            'class_name' => 'AdminJXBlogSettings',
            'module'     => $this->name,
            'name'       => 'Settings'
        );
        $this->settingsSubTabs = array(
            array(
                'class_name' => 'AdminJXBlogPostProductsSettings',
                'module'     => $this->name,
                'name'       => '\'Products to post\' settings'
            )
        );
    }

    public function install()
    {
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();
        if (!$moduleManager->isInstalled('jxblog') || !$moduleManager->isEnabled('jxblog')) {
            /*TODO# add an error if JX Blog does'nt installed*/
            return false;
        }

        return parent::install() &&
        $this->registerHook('actionObjectShopAddAfter') &&
        $this->registerHook('displayJxblogPostExtra') &&
        $this->registerHook('actionJxblogPostAfterAdd') &&
        $this->registerHook('actionJxblogPostAfterUpdate') &&
        $this->registerHook('actionJxblogPostAfterDelete') &&
        $this->registerHook('header') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('displayJXBlogPostFooter') &&
        $this->registerHook('displayProductExtraContent') &&
        $this->repository->createTables() &&
        $this->setSettings() &&
        $this->addTabs();
    }

    /**
     * Set default module settings after installation or adding new store
     *
     * @return bool
     */
    public function setSettings()
    {
        Configuration::updateValue('JXBLOGPOSTPRODUCTS_ITEMS_TO_SHOW', 4);

        return true;
    }

    /**
     * Set default settings after new store creation
     *
     * @param $params
     */
    public function hookActionObjectShopAddAfter($params)
    {
        $this->setSettings();
    }

    public function uninstall()
    {
        return $this->repository->dropTables() &&
            $this->removeTabs() &&
            $this->removeSettings() &&
            parent::uninstall();
    }

    /**
     * Remove all module settings after module deletion
     *
     * @return bool
     */
    public function removeSettings()
    {
        Configuration::deleteByName('JXBLOGPOSTPRODUCTS_ITEMS_TO_SHOW');

        return true;
    }


    /**
     * Add related to module tabs to the main navigation menu
     *
     * @return bool
     */
    protected function addTabs()
    {
        $idSettingsTab = TabCore::getIdFromClassName($this->settingsTab['class_name']);

        foreach ($this->settingsSubTabs as $newSubTab) {
            $this->addTab($newSubTab, $idSettingsTab);
        }

        return true;
    }

    public function addTab($tab, $parent)
    {
        $t = new Tab();
        $t->class_name = $tab['class_name'];
        $t->id_parent = $parent;
        $t->module = $tab['module'];

        foreach ($this->languages as $lang) {
            $t->name[$lang['id_lang']] = $this->l($tab['name']);
        }

        if (!$t->save()) {
            return false;
        }

        return $t->id;
    }

    /**
     * Add related to module tabs to the main navigation menu
     *
     * @return bool
     */
    protected function removeTabs()
    {
        foreach ($this->settingsSubTabs as $t) {
            if ($t) {
                $t = new Tab(TabCore::getIdFromClassName($t['class_name']));
                if (!$t->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminJXBlogPosts' && (Tools::getIsset('updatejxblog_post') || Tools::getIsset('addjxblog_post'))) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUi('ui.widget');
            $this->context->controller->addJqueryPlugin(array('tagify', 'autocomplete'));
            $this->context->controller->addJS($this->_path.'views/js/jxblogpostproducts_admin.js');
        }
    }

    public function hookHeader()
    {
        if (isset($this->context->controller->pagename) && $this->context->controller->pagename == 'jxblogpost') {
            $this->context->controller->addJS($this->_path.'/views/js/jxblogpostproducts.js');
            $this->context->controller->addCSS($this->_path.'/views/css/jxblogpostproducts.css');
        }
    }

    /**
     * Add extra fields and data to the post form
     * in a format array('fields' => fields, 'values' => value)
     *
     * @param $params
     *
     * @return array
     */
    public function hookDisplayJxblogPostExtra($params)
    {
        $post = $params['post'];
        $fields = array();
        $fields[] = array(
            'type' => 'autocomplete',
            'name' => 'related_products',
            'label' => $this->trans('Related products', array(), 'Modules.Jxblogpostproducts.Admin'),
            'desc' => $this->trans('Related products.', array(), 'Modules.Jxblogpostproducts.Admin'),
            'col'=> 3,
            'id' => 'products'
        );

        return array(
            'fields' => $fields,
            'values' => array(
                'related_products' => $post ? $this->repository->getAdminPostProducts($post->id) : false
            )
        );
    }

    public function hookActionJxblogPostAfterAdd($params)
    {
        return $this->repository->associateProductsToPost($params['id_jxblog_post']);
    }

    public function hookActionJxblogPostAfterUpdate($params)
    {
        return $this->repository->associateProductsToPost($params['id_jxblog_post']);
    }

    public function hookActionJxblogPostAfterDelete($params)
    {
        return $this->repository->disassociateProductsToPost($params['id_jxblog_post']);
    }

    /**
     * Get all related products and adapt it to default template
     *
     * @param $id_jxblog_post
     * @param $limit
     *
     * @return array|bool
     */
    private function getRelatedProducts($id_jxblog_post, $limit)
    {
        $productsIds = $this->repository->getRelatedProducts($id_jxblog_post, $limit);
        if (!$productsIds) {
            return false;
        }
        $products = $this->buildTemplateProduct($productsIds);

        return $products;
    }

    /**
     * Build data for default template
     * @param $products
     *
     * @return array
     */
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

    public function presenter($id_post)
    {
        $result = array();
        $posts = $this->repository->getProductRelatedPosts($id_post, $this->context->language->id);
        if ($posts) {
            $blog = new Jxblog();
            foreach ($posts as $key => $post) {
                $result[$key] = $post;
                $result[$key]['url'] = $blog->getBlogLink('post', array('id_jxblog_post' => $post['id_jxblog_post'], 'rewrite' => $post['link_rewrite']));
            }
        }

        return $result;
    }

    public function hookDisplayJXBlogPostFooter($params)
    {
        $this->context->smarty->assign(
            'products',
            $this->getRelatedProducts(
                $params['post']['id_jxblog_post'],
                Configuration::get('JXBLOGPOSTPRODUCTS_ITEMS_TO_SHOW')
            )
        );

        return $this->display($this->local_path, 'views/templates/hook/jxblogpostproducts.tpl');
    }

    public function hookDisplayProductExtraContent($params)
    {
        if ($relatedPosts = $this->presenter($params['product']->id)) {
            $this->context->smarty->assign('related_posts', $relatedPosts);
            $extraContent = new \PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
            $extraContent->setTitle($this->trans('Related Blog Posts'));
            $extraContent->addAttr(array('id' => 'jxblogpostproducts', 'class' => 'jxblogpostproducts'));
            $extraContent->setContent($this->display($this->local_path, 'views/templates/hook/jxblogpostproducts-product.tpl'));

            return array($extraContent);
        }

        return array();
    }
}
