<?php
/**
* 2017-2018 Zemez
*
* JX Mega Menu
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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

require_once(dirname(__FILE__).'/src/MegaMenu.php');
require_once(dirname(__FILE__).'/src/MegaMenuHtml.php');
require_once(dirname(__FILE__).'/src/MegaMenuLink.php');
require_once(dirname(__FILE__).'/src/MegaMenuBanner.php');
require_once(dirname(__FILE__).'/src/MegaMenuVideo.php');
require_once(dirname(__FILE__).'/src/MegaMenuMap.php');
require_once(dirname(__FILE__).'/src/JXMegaMenuRepository.php');
require_once(dirname(__FILE__).'/src/MegaMenuSettings.php');

class Jxmegamenu extends Module implements WidgetInterface
{
    private $menu = '';
    private $pattern = '/^([A-Z_]*)[0-9]+/';
    private $spacer_size = '5';
    private $page_name = '';
    private $megamenu_items = '';
    private $user_groups;
    protected $config_form = false;
    public $jxmegamenu;
    public $megamenu;
    public $megamenuhtml;
    public $megamenulink;
    public $megamenubanner;
    public $megamenuvideo;
    public $megamenumap;
    private $defaultHook = 'displayTop';
    private $currentHook;
    private $hooks = array();
    private $blog = false;

    public function __construct()
    {
        $this->name = 'jxmegamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.7.10';
        $this->author = 'Zemez (Alexander Grosul)';
        $this->default_language = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->languages = Language::getLanguages(true);

        $this->bootstrap = true;
        $this->secure_key = Tools::hash($this->name);
        $this->module_key = '832c9a16e83ca35e673a68c268c4607e';

        parent::__construct();

        $this->displayName = $this->l('JX Mega Menu');
        $this->description = $this->l('Mega Menu by Zemez');

        $this->confirmUninstall = $this->l('Are you sure that you want to delete all your info?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->repository = new JXMegaMenuRepository(
            Db::getInstance(),
            $this->context->shop,
            $this->context->language
        );
        $this->hooks = $this->repository->getAllModuleHooks($this->name);
        $this->megamenu = new MegaMenu();
        $this->megamenuhtml = new MegaMenuHtml();
        $this->megamenulink = new MegaMenuLink();
        $this->megamenubanner = new MegaMenuBanner();
        $this->megamenuvideo = new MegaMenuVideo();
        $this->megamenumap = new MegaMenuMap();
        if (count($this->hooks) > 1) {
            $this->currentHook = $this->hooks[key($this->hooks)]['name'];
        } else {
            $this->currentHook = $this->defaultHook;
        }

        // check if jx blog is active
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();
        if ($moduleManager->isInstalled('jxblog') && $moduleManager->isEnabled('jxblog')) {
            $this->blog = true;
        }
    }

    public function install()
    {
        $this->clearCache();
        return parent::install()
            && $this->repository->createTables()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader')
            && $this->registerHook($this->defaultHook)
            && $this->registerHook('actionObjectCategoryUpdateAfter')
            && $this->registerHook('actionObjectCategoryDeleteAfter')
            && $this->registerHook('actionObjectCategoryAddAfter')
            && $this->registerHook('actionObjectCmsUpdateAfter')
            && $this->registerHook('actionObjectCmsDeleteAfter')
            && $this->registerHook('actionObjectCmsAddAfter')
            && $this->registerHook('actionObjectSupplierUpdateAfter')
            && $this->registerHook('actionObjectSupplierDeleteAfter')
            && $this->registerHook('actionObjectSupplierAddAfter')
            && $this->registerHook('actionObjectManufacturerUpdateAfter')
            && $this->registerHook('actionObjectManufacturerDeleteAfter')
            && $this->registerHook('actionObjectManufacturerAddAfter')
            && $this->registerHook('actionObjectProductUpdateAfter')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionObjectProductAddAfter')
            && $this->registerHook('actionJXBlogBeforeModuleDelete')
            && $this->registerHook('actionJxblogCategoryAfterAdd')
            && $this->registerHook('actionJxblogCategoryAfterUpdate')
            && $this->registerHook('actionJxblogCategoryAfterDelete')
            && $this->registerHook('actionJxblogPostAfterUpdate')
            && $this->registerHook('actionJxblogPostAfterDelete')
            && $this->registerHook('categoryUpdate')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->createAjaxController();
    }

    public function uninstall()
    {
        $this->clearCache();
        return parent::uninstall()
            && $this->repository->dropTables()
            && $this->removeAjaxContoller()
            && $this->refreshCustomCssFolder();
    }

    public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(true);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'jxmegamenu';
            }
        }
        $tab->class_name = 'AdminJXMegaMenu';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        return (bool)$tab->add();
    }

    private function removeAjaxContoller()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminJXMegaMenu')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    protected function refreshCustomCssFolder()
    {
        $dir_files = Tools::scandir(Jxmegamenu::stylePath(), 'css');

        foreach ($dir_files as $file) {
            @unlink(Jxmegamenu::stylePath().$file);
        }

        return true;
    }

    public function getContent()
    {
        $output = '';
        $check_item_fields = '';
        $check_html_fields = '';
        $check_link_fields = '';
        $check_banner_fields = '';
        $check_video_fields = '';
        $check_map_fields = '';

        if ($currentHook = Tools::getValue('hookName')) {
            $this->currentHook = $currentHook;
        }

        if ($message = $this->getWarningMultishop()) {
            return $message;
        }

        $this->setTemplateVariables(); // set loaded template variables

        // update main items
        if (Tools::isSubmit('updateItem') || Tools::isSubmit('updateItemStay')) {
            if (!$check_item_fields = $this->preUpdateItem()) {
                $item_id = $this->updateItem();
                $this->context->smarty->assign('item', $this->megamenu->getItem($item_id));
                $this->parseStyle($this->megamenu->getItemUniqueCode($item_id));
                $this->clearCache();
                if (!Tools::isSubmit('updateItemStay')) {
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink(
                            'AdminModules',
                            true
                        ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.($this->hooks > 1 ? '&hookName='.$this->currentHook : '')
                    );
                } else {
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink(
                            'AdminModules',
                            true
                        ).'&configure='.$this->name.'&tab_module='
                        .$this->tab.'&module_name='.$this->name.'&editItem&id_item='.$item_id.($this->hooks > 1 ? '&hookName='.$this->currentHook : '')
                    );
                }
            } else {
                if ($item = Tools::getValue('id_item')) {
                    $this->context->smarty->assign('item', $this->megamenu->getItem($item));
                    $this->parseStyle($this->megamenu->getItemUniqueCode($item));
                    $this->clearCache();
                }
                $output .= $check_item_fields;
                $output .= $this->display($this->local_path, 'views/templates/admin/additem.tpl');
            }
        } elseif (Tools::getIsset('updateItemStatus')) {// update item status from table
            if (!$this->megamenu->changeItemStatus()) {
                $output .= $this->displayError($this->l('Can\'t update item status.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Status successfully updated.'));
            }
        } elseif (Tools::getIsset('deleteItem')) {
            if (!$this->megamenu->deleteItem()) {
                $output .= $this->displayError($this->l('Can\'t delete item.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Item successfully deleted.'));
            }
        } elseif (Tools::getIsset('editItem')) {
            if (!$this->megamenu->getItem()) {
                $output .= $this->displayError($this->l('Can\'t load item.'));
            } else {
                $this->context->smarty->assign('item', $this->megamenu->getItem());
                $this->parseStyle($this->megamenu->getItemUniqueCode());
                $this->clearCache();
            }
        }

        // Custom HTML manager
        if (Tools::isSubmit('updateHtml') || Tools::isSubmit('updateHtmlStay')) {
            if (!$check_html_fields = $this->preUpdateHTML()) {
                if ($html_id = $this->addHTML()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateHtmlStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='
                            .$this->tab.'&module_name='.$this->name.'&editHtml&id_item='.$html_id.($this->hooks > 1 ? '&hookName='.$this->currentHook : '')
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The HTML can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_html_fields;
                $output .= $this->renderAddHtml((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteHtml')) {
            $html = new MegaMenuHTML((int)Tools::getValue('id_item'));
            if (!$html->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete HTML item.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('HTML item successfully deleted.'));
            }
        }

        // Custom Links manager
        if (Tools::isSubmit('updateLink') || Tools::isSubmit('updateLinkStay')) {
            if (!$check_link_fields = $this->preUpdateLink()) {
                if ($link_id = $this->addLink()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateLinkStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editLink&id_item='.$link_id.($this->hooks > 1 ? '&hookName='.$this->currentHook : '')
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Link can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_link_fields;
                $output .= $this->renderAddLink((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteLink')) {
            $link = new MegaMenuLink((int)Tools::getValue('id_item'));
            if (!$link->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Link.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Link successfully deleted.'));
            }
        }

        // Banners manager
        if (Tools::isSubmit('updateBanner') || Tools::isSubmit('updateBannerStay')) {
            if (!$check_banner_fields = $this->preUpdateBanner()) {
                if ($id_banner = $this->addBanner()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateBannerStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editBanner&id_item='.$id_banner.($this->hooks ? '&hookName='.$this->currentHook : '')
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Banner can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_banner_fields;
                $output .= $this->renderAddBanner((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteBanner')) {
            $banner = new MegaMenuBanner((int)Tools::getValue('id_item'));
            if (!$banner->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Banner.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Banner successfully deleted.'));
            }
        }

        // Video manager
        if (Tools::isSubmit('updateVideo') || Tools::isSubmit('updateVideoStay')) {
            if (!$check_video_fields = $this->preUpdateVideo()) {
                if ($video_id = $this->addVideo()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateVideoStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editVideo&id_item='.$video_id.($this->hooks ? '&hookName='.$this->currentHook : '')
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Video can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_video_fields;
                $output .= $this->renderAddVideo((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteVideo')) {
            $video = new MegaMenuVideo((int)Tools::getValue('id_item'));
            if (!$video->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Video.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Video successfully deleted.'));
            }
        }

        // Map manager
        if (Tools::isSubmit('updateMap') || Tools::isSubmit('updateMapStay')) {
            if (!$check_map_fields = $this->preUpdateMap()) {
                if ($map_id = $this->addMap()) {
                    $this->clearCache();
                    if (Tools::isSubmit('updateMapStay')) {
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink(
                                'AdminModules',
                                true
                            ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='
                            .$this->name.'&editMap&id_item='.$map_id.($this->hooks ? '&hookName='.$this->currentHook : '')
                        );
                    }
                } else {
                    $output .= $this->displayError($this->l('The Map can\'t be saved.'));
                }
            } else {
                $this->clearCache();
                $output .= $check_map_fields;
                $output .= $this->renderAddMap((int)Tools::getValue('id_item'));
            }
        } elseif (Tools::getIsset('deleteMap')) {
            $map = new MegaMenuMap((int)Tools::getValue('id_item'));
            if (!$map->delete()) {
                $output .= $this->displayError($this->l('Can\'t delete Map.'));
            } else {
                $this->clearCache();
                $output .= $this->displayConfirmation($this->l('Map successfully deleted.'));
            }
        }

        $this->setTemplateVariables(); // refresh template  variables after changing

        if ((Tools::getIsset('addItem') || Tools::getIsset('editItem')) && !$check_item_fields) {
            $output .= $this->display($this->local_path, 'views/templates/admin/additem.tpl');
        } elseif ((Tools::getIsset('addHtml') || Tools::getIsset('editHtml')) && !$check_html_fields) {
            $output .= $this->renderAddHtml();
        } elseif ((Tools::getIsset('addLink') || Tools::getIsset('editLink')) && !$check_link_fields) {
            $output .= $this->renderAddLink();
        } elseif ((Tools::getIsset('addBanner') || Tools::getIsset('editBanner')) && !$check_banner_fields) {
            $output .= $this->renderAddBanner();
        } elseif ((Tools::getIsset('addVideo') || Tools::getIsset('editVideo')) && !$check_video_fields) {
            $output .= $this->renderAddVideo();
        } elseif ((Tools::getIsset('addMap') || Tools::getIsset('editMap')) && !$check_map_fields) {
            $output .= $this->renderAddMap();
        } else {
            if (!$check_item_fields && !$check_html_fields && !$check_link_fields
                && !$check_banner_fields && !$check_video_fields && !$check_map_fields) {
                $this->parseStyle($this->currentHook.'_megamenu_custom_styles');
                $this->clearCache();
                $output .= $this->display($this->local_path, 'views/templates/admin/list.tpl');
            }
        }

        return $output;
    }

    /*****
    ******    Set/refresh all necessary variables for templates
    *****/
    protected function setTemplateVariables()
    {
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('languages', $this->languages);
        $this->context->smarty->assign('default_language', $this->default_language);
        $this->context->smarty->assign('categTree', $this->initCategoriesQuery());
        $this->context->smarty->assign('cmsCatTree', $this->repository->getCMSCategories(true));
        if ($this->blog) {
            $this->context->smarty->assign('blogCatTree', $this->blogCatTree(true));
        }
        $this->context->smarty->assign('tabs', $this->megamenu->getList($this->currentHook));
        $this->context->smarty->assign('html_items', $this->megamenuhtml->getHtmlList());
        $this->context->smarty->assign('links', $this->megamenulink->getLinksList());
        $this->context->smarty->assign('banners', $this->megamenubanner->getBannersList());
        $this->context->smarty->assign('videos', $this->megamenuvideo->getVideosList());
        $this->context->smarty->assign('maps', $this->megamenumap->getMapsList());
        $this->context->smarty->assign('megamenu', $this->getMegamenuItems());
        $this->context->smarty->assign('image_baseurl', $this->_path.'images/');
        $this->context->smarty->assign('theme_url', $this->context->link->getAdminLink('AdminJXMegaMenu'));
        $this->context->smarty->assign('hooks', $this->renderHooksForm());
        if ($this->hooks) {
            $this->context->smarty->assign('currentHook', $this->currentHook);
        }
        $this->context->smarty->assign('option_select', $this->minifyHTML($this->renderChoicesSelect()));
        $this->context->smarty->assign('option_selected', $this->makeMenuOption());

        // buttons url
        $this->context->smarty->assign(
            'url_enable',
            $this->context->link->getAdminLink(
                'AdminModules',
                true
            )
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
        );

        $this->context->smarty->assign('branche_tpl_path', $this->local_path.'views/templates/admin/tree-branch.tpl');
    }

    protected function minifyHTML($content)
    {
        return  preg_replace(array('/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'), array(' ', ''), $content);
    }

    /**
    *    Check if all fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateItem()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('name_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('Item name is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all HTML fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateHTML()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('HTML item name is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all link fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateLink()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Link name is required.');
        }
        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Link URL is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all banner fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateBanner()
    {
        $errors = array();
        $banner = new MegaMenuBanner(Tools::getValue('id_item'));
        $imageexists = @getimagesize($_FILES['image_'.$this->default_language['id_lang']]['tmp_name']);
        $old_image = $banner->image;

        if (!$old_image && !$imageexists) {
            $errors[] = $this->l('The Banner image is required.');
        }
        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Banner URL is required.');
        }
        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Banner name is required.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all video fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateVideo()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Video name is required.');
        }

        if (Tools::isEmpty(Tools::getValue('url_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Video URL is required.');
        }

        foreach ($this->languages as $lang) {
            if (!Tools::isEmpty(Tools::getValue('url_'.$lang['id_lang'])) && !$this->getVideoType(Tools::getValue('url_'.$lang['id_lang']))) {
                $errors[] = sprintf($this->l('%s - the Video URL is unknown.'), $lang['iso_code']);
            }
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    /**
    *    Check if all map fields filled before saving
    *    @return string if error and false if no errors
    */
    protected function preUpdateMap()
    {
        $errors = array();

        if (Tools::isEmpty(Tools::getValue('title_'.$this->default_language['id_lang']))) {
            $errors[] = $this->l('The Map name is required.');
        }

        if (Tools::isEmpty(Tools::getValue('latitude'))) {
            $errors[] = $this->l('The Map latitude is required.');
        } elseif (!Validate::isCoordinate(Tools::getValue('latitude'))) {
            $errors[] = $this->l('Bad Map latitude.');
        }

        if (Tools::isEmpty(Tools::getValue('longitude'))) {
            $errors[] = $this->l('The Map longitude is required.');
        } elseif (!Validate::isCoordinate(Tools::getValue('longitude'))) {
            $errors[] = $this->l('Bad Map longitude.');
        }

        if (count($errors)) {
            return $this->displayError(implode('<br />', $errors));
        }

        return false;
    }

    public function updateItem()
    {
        if (!$id_item = Tools::getValue('id_item')) {
            $menuitem = new MegaMenu();
        } else {
            $menuitem = new MegaMenu($id_item);
        }

        $menuitem->sort_order = (int)Tools::getValue('sort_order');
        $menuitem->hook_name = $this->currentHook;
        $menuitem->id_shop = (int)$this->context->shop->id;
        $menuitem->is_mega = Tools::getValue('addnewmega');
        $menuitem->is_simple = Tools::getValue('issimplemenu');
        $menuitem->unique_code = Tools::getValue('unique_code');
        if (!$menuitem->unique_code) {
            $menuitem->unique_code = 'it_'.Tools::passwdGen(8, 'NUMERIC');
        }
        $menuitem->specific_class = Tools::getValue('specific_class');
        $menuitem->is_custom_url = Tools::getValue('tab_url_type');
        $menuitem->active = Tools::getValue('addnewactive');

        foreach (Language::getLanguages(true) as $lang) {
            if ($menuitem->is_custom_url) {
                $menuitem->url[$lang['id_lang']] = Tools::getValue('tab_url_'.$lang['id_lang']);
            } else {
                $menuitem->url[$lang['id_lang']] = Tools::getValue('tab_url');
            }
            $menuitem->title[$lang['id_lang']] = Tools::getValue('name_'.$lang['id_lang']);
            $menuitem->badge[$lang['id_lang']] = Tools::getValue('badge_'.$lang['id_lang']);
            if (!$menuitem->title[$lang['id_lang']]) {
                $menuitem->title[$lang['id_lang']] = Tools::getValue('name_'.(int)Configuration::get('PS_LANG_DEFAULT'));
            }
        }

        if (!$menuitem->id_item) {
            if (!$menuitem->add()) {
                return $this->displayError($this->l('The item could not be added.'));
            }
        } else {
            if (!$menuitem->update()) {
                return $this->displayError($this->l('The item could not be updated.'));
            }
        }

        $menuitem->addMenuItem();

        return $menuitem->id;
    }

    /*****
    ******    Add new html
    ******    @return html id if true or false
    *****/
    protected function addHTML()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $html = new MegaMenuHTML((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($html)) {
                $errors[] .= $this->displayError($this->l('Invalid HTML ID'));
                return false;
            }
        } else {
            $html = $this->megamenuhtml;
        }

        $html->id_shop = (int)$this->context->shop->id;
        $html->specific_class = Tools::getValue('specific_class');

        /* Sets each langue fields */
        $languages = Language::getLanguages(true);

        foreach ($languages as $language) {
            $html->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $html->content[$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$html->add()) {
                    $errors[] = $this->displayError($this->l('The HTML could not be added.'));
                }
            } elseif (!$html->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The HTML could not be updated.'));
            }
            $this->clearCache();

            if (!$errors) {
                return (int)$html->id;
            }
            return false;
        }
    }

    /*****
    ******    Add new custom link
    ******    @return link id if true or false
    *****/
    protected function addLink()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $link = new MegaMenuLink((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($link)) {
                $errors[] .= $this->displayError($this->l('Invalid link ID'));
                return false;
            }
        } else {
            $link = $this->megamenulink;
        }

        $link->id_shop = (int)$this->context->shop->id;
        $link->specific_class = Tools::getValue('specific_class');
        $link->blank = (bool)Tools::getValue('blank');

        /* Sets each langue fields */
        $languages = Language::getLanguages(true);

        foreach ($languages as $language) {
            $link->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $link->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$link->add()) {
                    $errors[] = $this->displayError($this->l('The link could not be added.'));
                }
            } elseif (!$link->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The link could not be updated.'));
            }
            $this->clearCache();
            if (!$errors) {
                return (int)$link->id;
            }
            return false;
        }
    }

    /*****
    ******    Add new banner whith images
    ******    @return banner id if true or false
    *****/
    protected function addBanner()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $banner = new MegaMenuBanner((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($banner)) {
                $errors[] .= $this->displayError($this->l('Invalid banner ID'));
                return false;
            }
        } else {
            $banner = $this->megamenubanner;
        }

        $banner->id_shop = (int)$this->context->shop->id;
        $banner->active = (int)Tools::getValue('active_slide');
        $banner->blank = (int)Tools::getValue('blank');
        $banner->specific_class = Tools::getValue('specific_class');

        /* Sets each langue fields */
        $languages = Language::getLanguages(true);

        foreach ($languages as $language) {
            $banner->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $banner->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
            $banner->public_title[$language['id_lang']] = Tools::getValue('public_title_'.$language['id_lang']);
            $banner->description[$language['id_lang']] = Tools::getValue('description_'.$language['id_lang']);

            /* Uploads image and sets banner */
            $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_'.$language['id_lang']]['name'], '.'), 1));
            $imagesize = @getimagesize($_FILES['image_'.$language['id_lang']]['tmp_name']);
            if (isset($_FILES['image_'.$language['id_lang']])
                && isset($_FILES['image_'.$language['id_lang']]['tmp_name'])
                && !empty($_FILES['image_'.$language['id_lang']]['tmp_name'])
                && !empty($imagesize)
                && in_array(
                    Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)),
                    array('jpg', 'gif', 'jpeg', 'png')
                )
                && in_array($type, array('jpg', 'gif', 'jpeg', 'png'))) {
                $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $salt = sha1(microtime());
                if ($error = ImageManager::validateUpload($_FILES['image_'.$language['id_lang']])) {
                    $errors[] = $error;
                } elseif (!$temp_name || !move_uploaded_file($_FILES['image_'.$language['id_lang']]['tmp_name'], $temp_name)) {
                    return false;
                } elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/images/'.$salt.'_'.$_FILES['image_'.$language['id_lang']]['name'], null, null, $type)) {
                    $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
                }

                if (isset($temp_name)) {
                    @unlink($temp_name);
                }
                $banner->image[$language['id_lang']] = $salt.'_'.$_FILES['image_'.$language['id_lang']]['name'];
            } elseif (Tools::getValue('image_old_'.$language['id_lang']) != '') {
                $banner->image[$language['id_lang']] = Tools::getValue('image_old_'.$language['id_lang']);
            }
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$banner->add()) {
                    $errors[] = $this->displayError($this->l('The slide could not be added.'));
                }
            } elseif (!$banner->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The slide could not be updated.'));
            }

            $this->clearCache();

            if (!$errors) {
                return $banner->id;
            }
            return false;
        }
    }

    /*****
    ******    Add new map whith marker
    ******    @return map id if true or false
    *****/
    protected function addMap()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $map = new MegaMenuMap((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($map)) {
                $errors[] .= $this->displayError($this->l('Invalid map ID'));
                return false;
            }
        } else {
            $map = $this->megamenumap;
        }

        $map->id_shop = (int)$this->context->shop->id;
        $map->latitude = Tools::getValue('latitude');
        $map->longitude = Tools::getValue('longitude');
        $map->scale = (int)Tools::getValue('scale');

        if (Tools::isEmpty(Tools::getValue('old_marker'))) {
            $map->marker = '';
        }

        if (isset($_FILES['marker']) && isset($_FILES['marker']['tmp_name']) && !empty($_FILES['marker']['tmp_name'])) {
            $random_name = Tools::passwdGen();
            if ($error = ImageManager::validateUpload($_FILES['marker'])) {
                $errors[] = $error;
            } elseif (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['marker']['tmp_name'], $tmp_name)) {
                return false;
            } elseif (!ImageManager::resize($tmp_name, dirname(__FILE__).'/img/markers/marker-'.$random_name.'-'.(int)$map->id_shop.'.jpg', 64, 64, 'png')) {
                $errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
            }
            unlink($tmp_name);
            $map->marker = 'marker-'.$random_name.'-'.(int)$map->id_shop.'.jpg';
        }

        /* Sets each langue fields */
        $languages = Language::getLanguages(true);

        foreach ($languages as $language) {
            $map->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $map->description[$language['id_lang']] = Tools::getValue('description_'.$language['id_lang']);
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$map->add()) {
                    $errors[] = $this->displayError($this->l('The map could not be added.'));
                }
            } elseif (!$map->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The map could not be updated.'));
            }

            $this->clearCache();

            if (!$errors) {
                return (int)$map->id;
            }
            return false;
        }
    }

    /*****
    ******    Add Video
    ******    @return Video id if true or false
    *****/
    protected function addVideo()
    {
        $errors = array();
        /* Sets ID if needed */
        if (Tools::getValue('id_item')) {
            $video = new MegaMenuVideo((int)Tools::getValue('id_item'));
            if (!Validate::isLoadedObject($video)) {
                $errors[] .= $this->displayError($this->l('Invalid video ID'));
                return false;
            }
        } else {
            $video = $this->megamenuvideo;
        }

        $video->id_shop = (int)$this->context->shop->id;

        /* Sets each langue fields */
        $languages = Language::getLanguages(true);

        foreach ($languages as $language) {
            $video->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
            $video->url[$language['id_lang']] = Tools::getValue('url_'.$language['id_lang']);
            $video->type[$language['id_lang']] = $this->getVideoType(Tools::getValue('url_'.$language['id_lang']));
        }

        /* Processes if no errors  */
        if (!$errors) {
            /* Adds */
            if (!Tools::getValue('id_item')) {
                if (!$video->add()) {
                    $errors[] = $this->displayError($this->l('The link could not be added.'));
                }
            } elseif (!$video->update()) {
                /* Update */
                $errors[] = $this->displayError($this->l('The link could not be updated.'));
            }

            $this->clearCache();

            if (!$errors) {
                return (int)$video->id;
            }
            return false;
        }
    }

    /*****
    ******    Get category tree
    *****/
    protected function initCategoriesQuery($id_category = false)
    {
        if (!$id_category) {
            $from_category = Configuration::get('PS_HOME_CATEGORY');
        } else {
            $from_category = $id_category;
        }
        $category = new Category($from_category, $this->context->language->id);

        $result_ids = array();
        $result_parents = array();

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT c.id_parent, c.id_category, c.level_depth, cl.name
            FROM `'._DB_PREFIX_.'category` c
            INNER JOIN `'._DB_PREFIX_.'category_lang` cl
            ON (c.`id_category` = cl.`id_category`
            AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
            INNER JOIN `'._DB_PREFIX_.'category_shop` cs
            ON (cs.`id_category` = c.`id_category`
            AND cs.`id_shop` = '.(int)$this->context->shop->id.')
            WHERE c.`active` = 1
            ORDER BY `level_depth` ASC');

        foreach ($result as &$row) {
            $result_parents[$row['id_parent']][] = &$row;
            $result_ids[$row['id_category']] = &$row;
        }

        return $this->repository->getTree($result_parents, $result_ids, ($category ? $category->id : null));
    }

    public function renderChoicesSelect()
    {
        $spacer = str_repeat('&nbsp;', $this->spacer_size);
        $groups = array();
        if (!Tools::isEmpty(Tools::getValue('id_item'))) {
            $items = $this->megamenu->getMenuItem((int)Tools::getValue('id_item'));
        } else {
            $items = array();
        }

        $groups['cms']['title'] = $this->l('CMS');
        $groups['cms']['items'] = $this->getCMSOptions(0, 1, $this->context->language->id);
        $groups['sup']['title'] = $this->l('Supplier');
        $groups['sup']['items']['ALLSUP0'] = $this->l('All suppliers');
        $suppliers = Supplier::getSuppliers(false, $this->context->language->id);
        foreach ($suppliers as $supplier) {
            $groups['sup']['items']['SUP'.$supplier['id_supplier']] = $spacer.$supplier['name'];
        }
        $groups['man']['title'] = $this->l('Manufacturer');
        $groups['man']['items']['ALLMAN0'] = $this->l('All manufacturers');
        $manufacturers = Manufacturer::getManufacturers(false, $this->context->language->id);
        foreach ($manufacturers as $manufacturer) {
            $groups['man']['items']['MAN'.$manufacturer['id_manufacturer']] = $spacer.$manufacturer['name'];
        }
        $groups['cat']['title'] = $this->l('Categories');
        $shops_to_get = Shop::getContextListShopID();
        foreach ($shops_to_get as $shop_id) {
            $groups['cat']['items'] = $this->generateCategoriesOption($this->repository->customGetNestedCategories($shop_id, null, (int)$this->context->language->id, true), $items);
        }
        if (Shop::isFeatureActive()) {
            $groups['shop']['title'] = $this->l('Shops');
            $shops = Shop::getShopsCollection();
            foreach ($shops as $shop) {
                if (!$shop->setUrl() && !$shop->getBaseURL()) {
                    continue;
                }
                $groups['shop']['items']['SHOP'.(int)$shop->id] = $spacer.$shop->name;
            }
        }
        $groups['html']['title'] = $this->l('HTML');
        $new_html = new MegaMenuHtml();
        foreach ($new_html = $new_html->getHtmlList() as $new) {
            $groups['html']['items']['HTML'.(int)$new['id_item']] = $spacer.$new['title'];
        }
        $groups['cl']['title'] = $this->l('Custom Links');
        $links = new MegaMenuLink();
        foreach ($links = $links->getLinksList() as $link) {
            $groups['cl']['items']['LNK'.(int)$link['id_item']] = $spacer.$link['title'];
        }
        $groups['ban']['title'] = $this->l('Banners');
        $links = new MegaMenuBanner();
        foreach ($links->getBannersList() as $banner) {
            $groups['ban']['items']['BNR'.(int)$banner['id_item']] = $spacer.$banner['title'];
        }
        $groups['vid']['title'] = $this->l('Videos');
        $videos = new MegaMenuVideo();
        foreach ($videos->getVideosList() as $video) {
            $groups['vid']['items']['VID'.(int)$video['id_item']] = $spacer.$video['title'];
        }
        $groups['map']['title'] = $this->l('Maps');
        $maps = new MegaMenuMap();
        foreach ($maps->getMapsList() as $map) {
            $groups['map']['items']['MAP'.(int)$map['id_item']] = $spacer.$map['title'];
        }
        $groups['prd']['title'] = $this->l('Products');
        $groups['prd']['items']['PRODUCT'] = $spacer.$this->l('Choose product ID (link)');
        $groups['prd']['items']['PRODUCTINFO'] = $spacer.$this->l('Choose product ID (info)');
        // these parts work only if jx block module is active
        if ($this->blog) {
            $blogCategories = $this->blogCatTree();
            if ($blogCategories) {
                $groups['blog_cat']['title'] = $this->l('Blog Categories');
                $groups['blog_cat']['items']['BLOG0'] = $spacer.$this->l('Blog Categories Listing');
                foreach ($blogCategories as $cat) {
                    $groups['blog_cat']['items']['BLOG'.$cat['id_jxblog_category']] = $spacer.$cat['name'];
                }
            }

            $blogPosts = JXBlogPost::getAllShopPosts($this->context->shop->id, $this->context->language->id);
            if ($blogPosts) {
                $groups['blog_post']['title'] = $this->l('Blog Posts');
                foreach ($blogPosts as $post) {
                    $groups['blog_post']['items']['BLOGP'.$post['id_jxblog_post']] = $spacer.$post['name'];
                }
            }
        }

        $this->context->smarty->assign('groups', $groups);

        return $this->display($this->local_path, 'views/templates/admin/_partials/available-select.tpl');
    }

    protected function makeMenuOption($megamenuitem = '')
    {
        $menu_array = array();
        if (!Tools::isEmpty($megamenuitem)) {
            $menu_item = $megamenuitem;
        } elseif (Tools::getValue('id_item')) {
            $menu_item = $this->megamenu->getMenuItem((int)Tools::getValue('id_item'));
        } else {
            $menu_item = array();
        }

        $id_lang = (int)$this->context->language->id;

        if (!Tools::isEmpty($megamenuitem)) {
            $menu_array['name'] = 'col-item-items';
            $menu_array['autocomplete'] = true;
        } else {
            $menu_array['name'] = 'simplemenu_items[]';
            $menu_array['id'] = 'simplemenu_items';
        }

        foreach ($menu_item as $item) {
            if (!$item) {
                continue;
            }

            preg_match($this->pattern, $item, $values);

            $id = (int)Tools::substr($item, Tools::strlen($values[1]), Tools::strlen($item));

            switch (Tools::substr($item, 0, Tools::strlen($values[1]))) {
                case 'CAT':
                    $category = new Category((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category)) {
                        $menu_array['options']['CAT'.$id] = $category->name;
                    }
                    break;
                case 'PRD':
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (Validate::isLoadedObject($product)) {
                        $menu_array['options']['PRD'.$id] = $product->name.' (product link)';
                    }
                    break;
                case 'PRDI':
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (Validate::isLoadedObject($product)) {
                        $menu_array['options']['PRDI'.$id] = $product->name.' (product info)';
                    }
                    break;
                case 'CMS':
                    $cms = new CMS((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($cms)) {
                        $menu_array['options']['CMS'.$id] = $cms->meta_title;
                    }
                    break;
                case 'CMS_CAT':
                    $category = new CMSCategory((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($category)) {
                        $menu_array['options']['CMS_CAT'.$id] = $category->name;
                    }
                    break;
                case 'ALLMAN':
                    $menu_array['options']['ALLMAN0'] = $this->l('All manufacturers');
                    break;
                case 'MAN':
                    $manufacturer = new Manufacturer((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($manufacturer)) {
                        $menu_array['options']['MAN'.$id] = $manufacturer->name;
                    }
                    break;
                case 'ALLSUP':
                    $menu_array['options']['ALLSUP0'] = $this->l('All suppliers');
                    break;
                case 'SUP':
                    $supplier = new Supplier((int)$id, (int)$id_lang);
                    if (Validate::isLoadedObject($supplier)) {
                        $menu_array['options']['SUP'.$id] = $supplier->name;
                    }
                    break;
                case 'SHOP':
                    $shop = new Shop((int)$id);
                    if (Validate::isLoadedObject($shop)) {
                        $menu_array['options']['SHOP'.(int)$id] = $shop->name;
                    }
                    break;
                case 'HTML':
                    $new_html = new MegaMenuHtml((int)$id);
                    if (Validate::isLoadedObject($new_html)) {
                        $menu_array['options']['HTML'.(int)$new_html->id] = $new_html->title[$id_lang];
                    }
                    break;
                case 'LNK':
                    $link = new MegaMenuLink((int)$id);
                    if (Validate::isLoadedObject($link)) {
                        $menu_array['options']['LNK'.(int)$link->id] = $link->title[$id_lang];
                    }
                    break;
                case 'BNR':
                    $banner = new MegaMenuBanner((int)$id);
                    if (Validate::isLoadedObject($banner)) {
                        $menu_array['options']['BNR'.(int)$banner->id] = $banner->title[$id_lang];
                    }
                    break;
                case 'VID':
                    $video = new MegaMenuVideo((int)$id);
                    if (Validate::isLoadedObject($video)) {
                        $menu_array['options']['VID'.(int)$video->id] = $video->title[$id_lang];
                    }
                    break;
                case 'MAP':
                    $map = new MegaMenuMap((int)$id);
                    if (Validate::isLoadedObject($map)) {
                        $menu_array['options']['MAP'.(int)$map->id] = $map->title[$id_lang];
                    }
                    break;
                // these parts work only if jx block module is active
                case 'BLOG':
                    if ($this->blog) {
                        if ($id == 0) {
                            $menu_array['options']['BLOG0'] = $this->l('Blog Categories Listing');
                        } else {
                            $blogCategory = new JXBlogCategory((int)$id);
                            if (Validate::isLoadedObject($blogCategory)) {
                                $menu_array['options']['BLOG'.(int)$blogCategory->id] = $blogCategory->name[$id_lang];
                            }
                        }
                    }
                    break;
                case 'BLOGP':
                    if ($this->blog) {
                        $blogPost = new JXBlogPost((int)$id);
                        if (Validate::isLoadedObject($blogPost)) {
                            $menu_array['options']['BLOGP'.(int)$blogPost->id] = $blogPost->name[$id_lang];
                        }
                    }
                    break;
            }
        }

        $this->context->smarty->assign('options', $menu_array);

        return $this->display($this->local_path, 'views/templates/admin/_partials/selected-select.tpl');
    }

    protected function getCMSOptions($parent = 0, $depth = 1, $id_lang = false, $id_shop = false)
    {
        $options = array();
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $id_shop = ($id_shop !== false) ? $id_shop : Context::getContext()->shop->id;
        $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$depth);
        $categories = $this->repository->getCMSCategories(true, (int)$parent, (int)$id_shop, $depth, $this->spacer_size);
        foreach ($categories as $category) {
            $options['CMS_CAT'.$category['id_cms_category']]['title'] = $spacer.$category['name'];
            $options['CMS_CAT'.$category['id_cms_category']]['items'] = $this->getCMSOptions($category['id_cms_category'], (int)$depth + 1, (int)$id_lang);
            $options['CMS_CAT'.$category['id_cms_category']]['pages'] = $category['pages'];
        }

        return $options;
    }

    protected function generateCategoriesOption($categories, $items_to_skip = null)
    {
        $cat = array();

        foreach ($categories as $category) {
            if (isset($items_to_skip)) {
                $shop = (object)Shop::getShop((int)$category['id_shop']);
                $cat['CAT'.(int)$category['id_category']]['title'] = str_repeat('&nbsp;', $this->spacer_size * (int)$category['level_depth']).$category['name'].' ('.$shop->name.')';
            }

            if (isset($category['children']) && !empty($category['children'])) {
                $cat['CAT'.(int)$category['id_category']]['items'] = $this->generateCategoriesOption($category['children'], $items_to_skip);
            }
        }

        return $cat;
    }

    protected function makeMenuTop($hookName)
    {
        $menu = array();
        if ($top_items = $this->megamenu->getTopItems($hookName)) {
            foreach ($top_items as $key => $top) {
                $item_num = $key + 1;
                $menu[$item_num] = $top;
                if (!$top['is_custom_url']) {
                    $top_item_url = $this->generateTopItemUrl($top['url']);
                } else {
                    $top_item_url = array('url' =>$top['url'], 'selected' => '');
                }
                $menu[$item_num]['selected'] = $top_item_url['selected'];
                if (!Tools::isEmpty($top_item_url['url'])) {
                    $menu[$item_num]['url'] = $top_item_url['url'];
                } else {
                    $menu[$item_num]['url'] = false;
                }

                if (!$top['is_mega']) {
                    $subitems = $this->megamenu->getMenuItem((int)$top['id_item'], 0, true);
                    if ($subitems) {
                        $menu[$item_num]['submenu'] = $this->makeMenu($hookName, $subitems);
                    }
                } else {
                    if ($rows = $this->megamenu->getMegamenuRow((int)$top['id_item'])) {
                        foreach ($rows as $row) {
                            if ($cols = $this->megamenu->getMegamenuRowCols((int)$top['id_item'], $row)) {
                                foreach ($cols as $col) {
                                    $menu[$item_num]['submenu'][$row][$col['id']] = $col;
                                    $menu[$item_num]['submenu'][$row][$col['id']]['content'] = $this->makeMenu($hookName, explode(',', $col['settings']));
                                }
                            }
                        }
                    }
                }
            }
        }

        return $menu;
    }

    protected function makeMenu($hookName, $subitems)
    {
        $id_lang = (int)$this->context->language->id;
        $submenu = '';
        foreach ($subitems as $item) {
            if (!$item) {
                continue;
            }

            preg_match($this->pattern, $item, $value);
            $id = (int)Tools::substr($item, Tools::strlen($value[1]), Tools::strlen($item));

            switch (Tools::substr($item, 0, Tools::strlen($value[1]))) {
                case 'CAT':
                    $this->context->smarty->assign('id_selected', (int)Tools::getValue('id_category'));
                    $this->context->smarty->assign('tree', Category::getNestedCategories($id, $id_lang, true, $this->user_groups));
                    $submenu .= $this->getItemTemplate($hookName, 'categories-tree');
                    break;
                case 'PRD':
                    $selected = ($this->context->controller->php_self == 'product' && (Tools::getValue('id_product') == $id)) ? ' class="sfHover product"' : ' class="product"';
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (!is_null($product->id)) {
                        $this->context->smarty->assign('name', $product->name);
                        $this->context->smarty->assign('lnk', Tools::HtmlEntitiesUTF8($product->getLink()));
                        $this->context->smarty->assign('selected', $selected);
                        $submenu .= $this->getItemTemplate($hookName, 'link');
                    }
                    break;
                case 'PRDI':
                    $selected = ($this->context->controller->php_self == 'product' && (Tools::getValue('id_product') == $id)) ?
                        ' class="sfHover product-info"' :
                        ' class="product-info"';
                    $product = new Product((int)$id, true, (int)$id_lang);
                    if (!is_null($product->id)) {
                        $submenu .= $this->generateProductInfo($hookName, $id, $selected);
                    }
                    break;
                case 'CMS':
                    $selected = ($this->page_name == 'cms' && (Tools::getValue('id_cms') == $id)) ? ' class="sfHover cms-page"' : ' class="cms-page"';
                    $cms = CMS::getLinks((int)$id_lang, array($id));
                    if (count($cms)) {
                        $this->context->smarty->assign('name', Tools::safeOutput($cms[0]['meta_title']));
                        $this->context->smarty->assign('lnk', Tools::HtmlEntitiesUTF8($cms[0]['link']));
                        $this->context->smarty->assign('selected', $selected);
                        $submenu .= $this->getItemTemplate($hookName, 'link');
                    }
                    break;
                case 'CMS_CAT':
                    $category = new CMSCategory((int)$id, (int)$id_lang);
                    $this->context->smarty->assign('id_selected', (int)Tools::getValue('id_cms_category'));
                    $this->context->smarty->assign('id_selected_page', (int)Tools::getValue('id_cms'));
                    $this->context->smarty->assign('tree', $this->getCMSMenuItems($category->id_parent, $category->level_depth));
                    $submenu .= $this->getItemTemplate($hookName, 'cms-tree');
                    break;
                case 'ALLMAN':
                    $link = new Link;
                    $list = array();
                    $list['class'] = 'all-manufacturers';
                    $list['url'] = $link->getPageLink('manufacturer');
                    $list['title'] = $this->l('All manufacturers');
                    $list['selected'] = ($this->context->controller->php_self == 'manufacturer' && !Tools::getValue('id_manufacturer')) ? ' sfHoverForce' : '';
                    $manufacturers = Manufacturer::getManufacturers();
                    if ($manufacturers) {
                        foreach ($manufacturers as $key => $manufacturer) {
                            $selected = ($this->context->controller->php_self == 'manufacturer' && (Tools::getValue('id_manufacturer') == (int)$manufacturer['id_manufacturer'])) ?
                                ' class="sfHoverForce manufacturer"' :
                                ' class="manufacturer"';
                            $list['items'][$key]['selected'] = $selected;
                            $list['items'][$key]['url'] = $link->getManufacturerLink((int)$manufacturer['id_manufacturer'], $manufacturer['link_rewrite']);
                            $list['items'][$key]['name'] = Tools::safeOutput($manufacturer['name']);
                        }
                    }
                    $this->context->smarty->assign('list', $list);
                    $submenu .= $this->getItemTemplate($hookName, 'link-list');
                    break;
                case 'MAN':
                    $selected = ($this->page_name == 'manufacturer' && (Tools::getValue('id_manufacturer') == $id)) ?
                        ' class="sfHover manufacturer"' :
                        ' class="manufacturer"';
                    $manufacturer = new Manufacturer((int)$id, (int)$id_lang);
                    if (!is_null($manufacturer->id)) {
                        if ((int)Configuration::get('PS_REWRITING_SETTINGS')) {
                            $manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
                        } else {
                            $manufacturer->link_rewrite = 0;
                        }
                        $link = new Link;
                        $this->context->smarty->assign('name', Tools::safeOutput($manufacturer->name));
                        $this->context->smarty->assign('lnk', Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int)$id, $manufacturer->link_rewrite)));
                        $this->context->smarty->assign('selected', $selected);
                        $submenu .= $this->getItemTemplate($hookName, 'link');
                    }
                    break;
                case 'ALLSUP':
                    $link = new Link;
                    $list = array();
                    $list['class'] = 'all-suppliers';
                    $list['url'] = $link->getPageLink('supplier');
                    $list['title'] = $this->l('All suppliers');
                    $list['selected'] = ($this->context->controller->php_self == 'supplier' && !Tools::getValue('id_supplier')) ? ' sfHoverForce' : '';
                    $suppliers = Supplier::getSuppliers();
                    if ($suppliers) {
                        foreach ($suppliers as $key => $supplier) {
                            $selected = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == (int)$supplier['id_supplier'])) ? ' class="sfHoverForce supplier"' : ' class="supplier"';
                            $list['items'][$key]['selected'] = $selected;
                            $list['items'][$key]['url'] = $link->getSupplierLink((int)$supplier['id_supplier'], $supplier['link_rewrite']);
                            $list['items'][$key]['name'] = Tools::safeOutput($supplier['name']);
                        }
                    }
                    $this->context->smarty->assign('list', $list);
                    $submenu .= $this->getItemTemplate($hookName, 'link-list');
                    break;
                case 'SUP':
                    $selected = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == $id)) ?
                        ' class="sfHover supplier"' :
                        ' class="supplier"';
                    $supplier = new Supplier((int)$id, (int)$id_lang);
                    if (!is_null($supplier->id)) {
                        $link = new Link;
                        $this->context->smarty->assign('name', $supplier->name);
                        $this->context->smarty->assign('lnk', Tools::HtmlEntitiesUTF8($link->getSupplierLink((int)$id, $supplier->link_rewrite)));
                        $this->context->smarty->assign('selected', $selected);
                        $submenu .= $this->getItemTemplate($hookName, 'link');
                    }
                    break;
                case 'SHOP':
                    $selected = ($this->page_name == 'index' && ($this->context->shop->id == $id)) ? ' class="sfHover shop"' : ' class="shop"';
                    $shop = new Shop((int)$id);
                    if (Validate::isLoadedObject($shop)) {
                        $this->context->smarty->assign('name', $shop->name);
                        $this->context->smarty->assign('lnk', Tools::HtmlEntitiesUTF8($shop->getBaseURL()));
                        $this->context->smarty->assign('selected', $selected);
                        $submenu .= $this->getItemTemplate($hookName, 'link');
                    }
                    break;
                case 'HTML':
                    $submenu .=  $this->generateCustomHtml($hookName, $id);
                    break;
                case 'LNK':
                    $submenu .=  $this->generateCustomLink($hookName, $id);
                    break;
                case 'BNR':
                    $submenu .=  $this->generateBanner($hookName, $id);
                    break;
                case 'VID':
                    $submenu .=  $this->generateVideo($hookName, $id);
                    break;
                case 'MAP':
                    $submenu .=  $this->generateMap($hookName, $id);
                    break;
                // these parts work only if jx block module is active
                case 'BLOG':
                    if ($this->blog) {
                        $page_name = isset($this->context->controller->pagename) ? $this->context->controller->pagename : '';
                        $link = new Jxblog();
                        if ($id === 0) {
                            $selected = $page_name == 'categories' ? ' class="sfHover blog-listing"' : ' class="blog-listing"';
                            $this->context->smarty->assign('name', Tools::safeOutput($this->l('Blog categories')));
                            $this->context->smarty->assign('lnk', Tools::HtmlEntitiesUTF8($link->getBlogLink('categories', array())));
                            $this->context->smarty->assign('selected', $selected);
                            $submenu .= $this->getItemTemplate($hookName, 'link');
                        } else {
                            $blogCategory = new JXBlogCategory((int)$id, (int)$id_lang);
                            if (!is_null($blogCategory->id) && $blogCategory->active) {
                                $selected = ($page_name == 'category' && $blogCategory->id == Tools::getValue('id_jxblog_category')) ? ' class="sfHover blog-listing-category"' : ' class="blog-listing-category"';
                                $this->context->smarty->assign('name', Tools::safeOutput($blogCategory->name));
                                $this->context->smarty->assign(
                                    'lnk',
                                    Tools::HtmlEntitiesUTF8(
                                        $link->getBlogLink(
                                            'category',
                                            array('id_jxblog_category' => (int)$blogCategory->id, 'rewrite' => $blogCategory->link_rewrite)
                                        )
                                    )
                                );

                                $this->context->smarty->assign('selected', $selected);
                                $submenu .= $this->getItemTemplate($hookName, 'link');
                            }
                        }
                    }
                    break;
                case 'BLOGP':
                    if ($this->blog) {
                        $lnk = new Jxblog();
                        $blogPost = new JXBlogPost((int)$id, (int)$id_lang);
                        if (!is_null($blogPost->id) && $blogPost->active) {
                            $this->context->smarty->assign(
                                'url',
                                $lnk->getBlogLink(
                                    'post',
                                    array('id_jxblog_post' => $blogPost->id, 'rewrite' => $blogPost->link_rewrite)
                                )
                            );
                            $this->context->smarty->assign('jxpost', $blogPost);
                            $submenu .= $this->getItemTemplate($hookName, 'post');
                        }
                    }
                    break;
            }
        }

        return $submenu;
    }

    protected function getItemTemplate($hook, $type)
    {
        $hook = Tools::strtolower($hook);
        $templatePath = 'views/templates/hook/items/'.$type.'.tpl';
        if ($this->getTemplatePath('views/templates/hook/'.$hook.'/items/'.$type.'.tpl')) {
            $templatePath = 'views/templates/hook/'.$hook.'/items/'.$type.'.tpl';
        }
        $output = $this->display($this->local_path, $templatePath);

        return $output;
    }

    /*****
    ******    Generate top item URL by element code (`url`)
    ******  return item url and active class if selected
    *****/
    public function generateTopItemUrl($url)
    {
        if (!$url) {
            return array('url' => '#', 'selected' => false);
        }
        $link = new Link();
        preg_match($this->pattern, $url, $value);
        $id = (int)Tools::substr($url, Tools::strlen($value[1]), Tools::strlen($url));
        $type = Tools::substr($url, 0, Tools::strlen($value[1]));
        $selected = '';

        switch ($type) {
            case 'CAT':
                $url = $link->getCategoryLink($id);
                if ($this->context->controller->php_self == 'category' && (int)Tools::getValue('id_category') == $id) {
                    $selected = ' sfHoverForce';
                }
                break;
            case 'CMS_CAT':
                $url = $link->getCMSCategoryLink($id);
                if ($this->context->controller->php_self == 'cms' && ((int)Tools::getValue('id_cms_category') == $id)) {
                    $selected = ' sfHoverForce';
                }
                break;
            case 'CMS':
                $url = $link->getCMSLink($id);
                if ($this->context->controller->php_self == 'cms' && Tools::getValue('id_cms') == $id) {
                    $selected = ' sfHoverForce';
                }
                break;
            case 'BLOG':
                if ($this->blog) {
                    $blogCategory = new JXBlogCategory($id, $this->context->language->id);
                    $link = new Jxblog();
                    $url = $link->getBlogLink(
                        'category',
                        array('id_jxblog_category' => $blogCategory->id, 'rewrite' => $blogCategory->link_rewrite)
                    );
                    if (isset($this->context->controller->pagename) && $this->context->controller->pagename == 'category' && Tools::getValue('id_jxblog_category') == $id) {
                        $selected = ' sfHoverForce';
                    }
                } else {
                    $url = '#';
                }
                break;
        }

        return array('url' => $url, 'selected' => $selected);
    }

    /*****
    ****** Get all cms items with nesting
    ****** $parent = paretn category id
    ****** $depth = depth level
    ****** $id_lang - current lang
    ****** return: nested list with all cms items
    *****/
    protected function getCMSMenuItems($parent, $depth = 1, $id_lang = false)
    {
        $options = array();
        $id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
        $id_shop = $this->context->shop->id;
        $categories = $this->repository->getCMSCategories(true, (int)$parent, (int)$id_shop, $depth, 0);

        foreach ($categories as $category) {
            $options[$category['id_cms_category']]['id_cms_category'] = $category['id_cms_category'];
            $options[$category['id_cms_category']]['title'] = $category['name'];
            $options[$category['id_cms_category']]['children'] = $this->getCMSMenuItems($category['id_cms_category'], (int)$depth + 1, (int)$id_lang);
            $options[$category['id_cms_category']]['pages'] = $category['pages'];
        }

        return $options;
    }

    /****
    *****    Generating megamenu content in admin part
    ****/
    protected function getMegamenuItems()
    {
        $id_item = (int)Tools::getValue('id_item');

        // get rows for this megamenu
        if (!$rows = $this->megamenu->getMegamenuRow($id_item)) {
            return false;
        }

        $layout = array();
        // parse each row
        foreach ($rows as $row) {
            // get columns for this megamenu row
            if (!$items = $this->megamenu->getMegamenuRowCols($id_item, $row)) {
                return false;
            }
            $row_data = '';

            // generate each column for current row
            foreach ($items as $item) {
                $layout[$row]['items'][$item['id']] = $item;
                $layout[$row]['items'][$item['id']]['class_select'] = $this->minifyHTML($this->classSelectGenerate((int)$item['width']));
                $layout[$row]['items'][$item['id']]['choices_select'] = $this->minifyHTML($this->renderChoicesSelect());
                $layout[$row]['items'][$item['id']]['menu_options'] = $this->minifyHTML($this->makeMenuOption(explode(',', $item['settings'])));
                $layout[$row]['items'][$item['id']]['col_data'] = '{col-'.$item['col'].'-'.$item['width'].'-('.$item['class'].')-'.$item['type'].'-['.$item['settings'].']}';
                // set hidden data for jquery (each row's column)
                $row_data .= '{col-'.$item['col'].'-'.$item['width'].'-('.$item['class'].')-'.$item['type'].'-['.$item['settings'].']}';
            }
            $layout[$row]['row_data'] = $row_data;
        }

        $this->context->smarty->assign('layout', $layout);

        return $this->display($this->local_path, 'views/templates/admin/_partials/layout.tpl');
    }

    /*****
    ****** Generate product info block by id
    ****** $id_product = product ID
    ****** return product info in html block
    *****/
    protected function generateProductInfo($hookName, $id_product, $selected = false)
    {
        $output = '';

        $product = (new ProductAssembler($this->context))
            ->assembleProduct(array('id_product' => $id_product))
        ;

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

        $products_for_template = $presenter->present(
            $presentationSettings,
            $product,
            $this->context->language
        );

        $this->context->smarty->assign(array(
            'product' => $products_for_template,
            'selected' => $selected
        ));

        $templatePath = 'views/templates/hook/items/product.tpl';
        if ($this->getTemplatePath('views/templates/hook/'.Tools::strtolower($hookName).'/items/product.tpl')) {
            $templatePath = 'views/templates/hook/'.Tools::strtolower($hookName).'/items/product.tpl';
        }
        $output .= $this->display($this->local_path, $templatePath);

        return $output;
    }

    /*****
    ****** Generate custom HTML block by id_item
    ****** $id_item = custom HTML ID
    ****** return custom HTML block
    *****/
    protected function generateCustomHtml($hookName, $id_item)
    {
        $html = new MegaMenuHtml((int)$id_item);
        if ($html) {
            $this->context->smarty->assign(array(
                'specific_class' => $html->specific_class,
                'title' => $html->title[(int)$this->context->language->id],
                'content' => $html->content[(int)$this->context->language->id]
            ));

            return $this->getItemTemplate($hookName, 'html');
        }

        return false;
    }

    /*****
    ****** Generate custom Link by id_item
    ****** $id_item = custom Link ID
    ****** return custom Link element
    *****/
    protected function generateCustomLink($hookName, $id_item)
    {
        $link = new MegaMenuLink((int)$id_item);
        if ($link) {
            $this->context->smarty->assign('name', $link->title[(int)$this->context->language->id]);
            $this->context->smarty->assign('lnk', $link->url[(int)$this->context->language->id]);
            $this->context->smarty->assign('selected', ($link->specific_class?'class="'.$link->specific_class.' custom-link"':'class="custom-link"'));
            $this->context->smarty->assign('target', $link->blank);

            return $this->getItemTemplate($hookName, 'link');
        }

        return false;
    }

    /*****
    ****** Generate Banner by id_item
    ****** $id_item = Banner ID
    ****** return custom Link element
    *****/
    protected function generateBanner($hookName, $id_item)
    {
        $output = '';
        $id_lang = (int)$this->context->language->id;
        $html = new MegaMenuBanner($id_item);

        if ($html) {
            $this->context->smarty->assign('image_baseurl', $this->_path.'images/');
            $this->context->smarty->assign('banner', array(
                                        'id' => $html->id,
                                        'specific_class' => $html->specific_class,
                                        'title' => $html->title[$id_lang],
                                        'url' => $html->url[$id_lang],
                                        'image' => $html->image[$id_lang],
                                        'blank' => $html->blank,
                                        'public_title' => $html->public_title[$id_lang],
                                        'description' => $html->description[$id_lang]
                                    ));

            $output .= $this->getItemTemplate($hookName, 'banner');
        }

        return $output;
    }

    /*****
    ****** Generate Video by id_item
    ****** $id_item = Video ID
    ****** return Video element
    *****/
    protected function generateVideo($hookName, $id_item)
    {
        $output = '';
        $id_lang = (int)$this->context->language->id;
        $video = new MegaMenuVideo($id_item);

        if ($video) {
            $this->context->smarty->assign('video', array(
                                        'id' => $video->id,
                                        'title' => $video->title[$id_lang],
                                        'url' => $video->url[$id_lang],
                                        'type' => $video->type[$id_lang]
                                    ));
            $output .= $this->getItemTemplate($hookName, 'video');
        }

        return $output;
    }

    /*****
    ****** Generate Map by id_item
    ****** $id_item = Map ID
    ****** return Map element
    *****/
    protected function generateMap($hookName, $id_item)
    {
        $output = '';
        $id_lang = (int)$this->context->language->id;
        $map = new MegaMenuMap($id_item);

        if ($map) {
            $this->context->smarty->assign('map', array(
                                        'unic_identificator' => Tools::passwdGen(),
                                        'id' => $map->id,
                                        'title' => $map->title[$id_lang],
                                        'description' => $map->description[$id_lang],
                                        'latitude' => $map->latitude,
                                        'longitude' => $map->longitude,
                                        'scale' => $map->scale,
                                        'icon' => $map->marker ? $this->_path.'img/markers/'.$map->marker : ''
                                    ));
            $output .= $this->getItemTemplate($hookName, 'map');
        }

        return $output;
    }

    /*****
    ****** Generate select for width checking
    ****** $width = current block width
    ****** return: select with all width types and current selected
    *****/
    private function classSelectGenerate($width)
    {
        $this->context->smarty->assign('selected', $width);

        return $this->display($this->local_path, 'views/templates/admin/_partials/class-select.tpl');
    }

    protected function renderHooksForm()
    {
        $hooks = array();
        if (count($this->hooks) <= 1) {
            return false;
        }
        foreach ($this->hooks as $hook) {
            $hooks[] = array('id' => $hook['name'], 'name' => $hook['name']);
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
            'firstCall'    => false
        );

        return $helper->generateForm(array($fields_form));
    }

    protected function getHooksFormValues()
    {
        return array('hookName' => $this->currentHook);
    }

    /*****
    ******    Generate form for Html blocks creating
    *****/
    private function renderAddHtml($id_html = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editHtml') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Html block'):$this->l('Add Html block'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter HTML item name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Specific class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('HTML content'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateHtml',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateHtmlStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editHtml') && (int)Tools::getValue('id_item') > 0) || $id_html) {
            if ($id_html) {
                $id = $id_html;
            } else {
                $id = Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => (int)$id);
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getHtmlFieldsValues($id_html),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Html blocks form fields
    *****/
    private function getHtmlFieldsValues($id)
    {
        if ($id) {
            $megamenuhtml  = new MegaMenuHtml((int)$id);
        } elseif (Tools::getIsset('editHtml') && (int)Tools::getValue('id_item') > 0) {
            $megamenuhtml  = new MegaMenuHtml((int)Tools::getValue('id_item'));
        } else {
            $megamenuhtml = $this->megamenuhtml;
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenuhtml->id),
            'specific_class' => Tools::getValue('specific_class', $megamenuhtml->specific_class),
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $megamenuhtml->title[$lang['id_lang']]);
            $fields_values['content'][$lang['id_lang']] = Tools::getValue('content_'.(int)$lang['id_lang'], $megamenuhtml->content[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Links creating
    *****/
    private function renderAddLink($id_link = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editLink') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Link'):$this->l('Add new Link'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Link name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Link URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Specific class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Open in new window'),
                        'name' => 'blank',
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
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateLink',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateLinkStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editLink') && (int)Tools::getValue('id_item') > 0) || $id_link > 0) {
            if ($id_link) {
                $id = $id_link;
            } else {
                $id = (int)Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => $id);
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getlinkFieldsValues($id_link),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Links form fields
    *****/
    private function getLinkFieldsValues($id)
    {
        if ($id) {
            $megamenulink  = new MegaMenuLink((int)$id);
        } elseif (Tools::getIsset('editLink') && (int)Tools::getValue('id_item') > 0) {
            $megamenulink  = new MegaMenuLink((int)Tools::getValue('id_item'));
        } else {
            $megamenulink = $this->megamenulink;
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenulink->id),
            'specific_class' => Tools::getValue('specific_class', $megamenulink->specific_class),
            'blank' => Tools::getValue('blank', $megamenulink->blank),
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $megamenulink->title[$lang['id_lang']]);
            $fields_values['url'][$lang['id_lang']] = Tools::getValue('url_'.(int)$lang['id_lang'], $megamenulink->url[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Banners creating
    *****/
    private function renderAddBanner($id_banner = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editBanner') && (int)Tools::getValue('id_item') > 0)
                        ?$this->l('Update Banner')
                        :$this->l('Add new Banner'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'files_lang',
                        'label' => $this->l('Select a file'),
                        'name' => 'image',
                        'required' => true,
                        'lang' => true,
                        'desc' => sprintf($this->l('Maximum image size: %s.'), ini_get('upload_max_filesize'))
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Banner name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Banner URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Specific class'),
                        'name' => 'specific_class',
                        'required' => false,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Public Title'),
                        'name' => 'public_title',
                        'required' => false,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Open in new window'),
                        'name' => 'blank',
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
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateBanner',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateBannerStay',
                    ),
                ),
            ),
        );
        if ((Tools::getIsset('editBanner') && (int)Tools::getValue('id_item') > 0) || $id_banner > 0) {
            if ($id_banner) {
                $id = $id_banner;
            } else {
                $id = (int)Tools::getValue('id_item');
            }

            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => $id);
            $banner = new MegaMenuBanner($id);
            $fields_form['form']['images'] = $banner->image;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $this->getBannerFieldsValues($id_banner),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'image_baseurl' => $this->_path.'images/'
        );

        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Banner form fields
    *****/
    private function getBannerFieldsValues($id = false)
    {
        if ($id) {
            $megamenubanner  = new MegaMenuBanner((int)$id);
        } elseif (Tools::getIsset('editBanner') && (int)Tools::getValue('id_item') > 0) {
            $megamenubanner  = new MegaMenuBanner((int)Tools::getValue('id_item'));
        } else {
            $megamenubanner = $this->megamenubanner;
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenubanner->id),
            'specific_class' => Tools::getValue('specific_class', $megamenubanner->specific_class),
            'blank' => Tools::getValue('blank', $megamenubanner->blank),
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue(
                'name_'.(int)$lang['id_lang'],
                $megamenubanner->title[$lang['id_lang']]
            );
            $fields_values['url'][$lang['id_lang']] = Tools::getValue(
                'url_'.(int)$lang['id_lang'],
                $megamenubanner->url[$lang['id_lang']]
            );
            $fields_values['image'][$lang['id_lang']] = Tools::getValue(
                'image_'.(int)$lang['id_lang'],
                $megamenubanner->image[$lang['id_lang']]
            );
            $fields_values['public_title'][$lang['id_lang']] = Tools::getValue(
                'public_title_'.(int)$lang['id_lang'],
                $megamenubanner->public_title[$lang['id_lang']]
            );
            $fields_values['description'][$lang['id_lang']] = Tools::getValue(
                'description_'.(int)$lang['id_lang'],
                $megamenubanner->description[$lang['id_lang']]
            );
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Video creating
    *****/
    private function renderAddVideo($id_video = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editVideo') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Video'):$this->l('Add new Video'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'videos_lang',
                        'name' => 'video',
                        'lang' => true,
                        'label' => $this->l('Video')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Video name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Video URL'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                        'col' => 3,
                        'desc' => $this->l('Video url should be like https://www.youtube.com/v/video_id or http://player.vimeo.com/video/video_id')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateVideo',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateVideoStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editVideo') && (int)Tools::getValue('id_item') > 0) || $id_video > 0) {
            if ($id_video) {
                $id = $id_video;
            } else {
                $id = (int)Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => $id);
            $video = new MegaMenuVideo($id);
            $fields_form['form']['videos'] = $video->url;
            $fields_form['form']['types'] = $video->type;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getVideoFieldsValues($id_video),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Video form fields
    *****/
    private function getVideoFieldsValues($id)
    {
        if ($id) {
            $megamenuvideo  = new MegaMenuVideo((int)$id);
        } elseif (Tools::getIsset('editVideo') && (int)Tools::getValue('id_item') > 0) {
            $megamenuvideo  = new MegaMenuVideo((int)Tools::getValue('id_item'));
        } else {
            $megamenuvideo = $this->megamenuvideo;
        }

        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenuvideo->id)
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('name_'.(int)$lang['id_lang'], $megamenuvideo->title[$lang['id_lang']]);
            $fields_values['url'][$lang['id_lang']] = Tools::getValue('url_'.(int)$lang['id_lang'], $megamenuvideo->url[$lang['id_lang']]);
            $fields_values['type'][$lang['id_lang']] = Tools::getValue('type_'.(int)$lang['id_lang'], $megamenuvideo->type[$lang['id_lang']]);
        }

        return $fields_values;
    }

    /*****
    ******    Generate form for Html blocks creating
    *****/
    private function renderAddMap($id_map = false)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => (Tools::getIsset('editMap') && (int)Tools::getValue('id_item') > 0)?$this->l('Update Map'):$this->l('Add Map'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'megamenu_map',
                        'name' => 'map',
                        'lang' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map item name'),
                        'name' => 'title',
                        'required' => true,
                        'lang' => true,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map latitude'),
                        'name' => 'latitude',
                        'required' => true,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map longitude'),
                        'name' => 'longitude',
                        'required' => true,
                        'lang' => false,
                        'col' => 3
                    ),
                    array(
                        'type' => 'marker_prev',
                        'name' => 'marker_prev',
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Marker'),
                        'name' => 'marker',
                        'value' => true,
                        'desc' => $this->l('64px * 64px')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Enter Map scale'),
                        'name' => 'scale',
                        'required' => false,
                        'lang' => false,
                        'col' => 3,
                        'desc' => $this->l('8 by default')
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Map description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'updateMap',
                ),
                'buttons' => array(
                    array(
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-save',
                        'title' => $this->l('Save & Stay'),
                        'type' => 'submit',
                        'name' => 'updateMapStay',
                    ),
                )
            ),
        );
        if ((Tools::getIsset('editMap') && (int)Tools::getValue('id_item') > 0) || $id_map) {
            if ($id_map) {
                $id = $id_map;
            } else {
                $id = Tools::getValue('id_item');
            }
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_item', 'value' => (int)$id);
            $map = new MegaMenuMap($id);
            $fields_form['form']['id'] = $id;
            $fields_form['form']['latitude'] = $map->latitude;
            $fields_form['form']['longitude'] = $map->longitude;
            $fields_form['form']['marker'] = $map->marker;
            $fields_form['form']['scale'] = $map->scale;
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'old_marker', 'value' => $map->marker);
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->show_cancel_button = true;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;

        $helper->identifier = 'id_item';
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
                                '&configure='.$this->name.
                                '&tab_module='.$this->tab.
                                '&module_name='.$this->name.($this->hooks ? '&hookName='.$this->currentHook : '');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getMapFieldsValues($id_map),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'marker_url' => $this->_path.'img/markers/'
        );

        return $helper->generateForm(array($fields_form));
    }

    /*****
    ******    Fill Html blocks form fields
    *****/
    private function getMapFieldsValues($id)
    {
        if ($id) {
            $megamenumap  = new MegaMenuMap((int)$id);
        } elseif (Tools::getIsset('editMap') && (int)Tools::getValue('id_item') > 0) {
            $megamenumap  = new MegaMenuMap((int)Tools::getValue('id_item'));
        } else {
            $megamenumap = $this->megamenumap;
        }
        $fields_values = array(
            'id_item' => Tools::getValue('id_item', $megamenumap->id),
            'latitude' => Tools::getValue('latitude', $megamenumap->latitude),
            'longitude' => Tools::getValue('longitude', $megamenumap->longitude),
            'scale' => Tools::getValue('scale', $megamenumap->scale ? $megamenumap->scale : 8),
            'marker' => Tools::getValue('marker', $megamenumap->marker),
            'old_marker' => Tools::getValue('old_marker', $megamenumap->marker)
        );

        $languages = Language::getLanguages(true);

        foreach ($languages as $lang) {
            $fields_values['title'][$lang['id_lang']] = Tools::getValue('title_'.(int)$lang['id_lang'], $megamenumap->title[$lang['id_lang']]);
            $fields_values['description'][$lang['id_lang']] = Tools::getValue('description_'.(int)$lang['id_lang'], $megamenumap->description[$lang['id_lang']]);
        }

        return $fields_values;
    }

    public function clearCache()
    {
        if ($this->hooks) {
            foreach ($this->hooks as $hook) {
                parent::_clearCache($this->name.'.tpl', $this->name.'_'.Tools::strtolower($hook['name']));
            }
        } else {
            parent::_clearCache($this->name.'.tpl', $this->name.'_'.Tools::strtolower($this->currentHook));
        }

        return true;
    }

    public function getVideoType($link)
    {
        if (strpos($link, 'youtube') > 0) {
            return 'youtube';
        } elseif (strpos($link, 'vimeo') > 0) {
            return 'vimeo';
        } else {
            return false;
        }
    }

    public static function stylePath()
    {
        return dirname(__FILE__).'/views/css/items/';
    }

    /*****
    ******     Parse styles fron tab's unique css file
    ******    $name = unique_code of tab
    ******    @return nothing
    ******    add smarty variables for each style row
    *****/
    protected function parseStyle($name = '')
    {
        if (!file_exists($this->stylePath().$name.'.css')) {
            return;
        }

        $file_content = Tools::file_get_contents($this->stylePath().$name.'.css');
        $styles = explode('}', $file_content);
        foreach ($styles as $style) {
            if (!Tools::isEmpty($style)) {
                $class = explode('{', $style);
                $class_data = array();
                $class_name = trim(str_replace($this->currentHook.'_menu', '', str_replace('jxmegamenu_item', '', str_replace(''.$name.'', '', str_replace('.', '', trim($class[0]))))));
                if (isset($class[1]) && !Tools::isEmpty($class[1])) {
                    $class_style = explode(';', $class[1]);
                    foreach ($class_style as $style_attr) {
                        if (!Tools::isEmpty($style_attr)) {
                            $style_el = explode(':', trim($style_attr));
                            $element_value = $style_el[1];
                            // replace url() if it's background-image field value
                            if ($style_el[0] == 'background-image') {
                                $element_value_replace = array('url(', ')');
                                $element_value = str_replace($element_value_replace, '', $style_el[1]);
                            }
                            $class_data[str_replace('-', '_', $style_el[0])] = $element_value;
                        }
                    }
                }
                $items_to_replace = array(':', '-');
                $this->context->smarty->assign(array(''.str_replace($items_to_replace, '_', $class_name).'' => $class_data));
            }
        }
    }

    public function generateUniqueStyles()
    {
        $dir_files = Tools::scandir(Jxmegamenu::stylePath(), 'css');
        $active_files = MegaMenu::getItemAllUniqueCodes();
        $combined_css = '';
        if ($this->hooks) {
            foreach ($this->hooks as $hook) {
                if (file_exists(Jxmegamenu::stylePath().$hook['name'].'_megamenu_custom_styles.css')) {
                    $combined_css .= Tools::file_get_contents(Jxmegamenu::stylePath().$hook['name'].'_megamenu_custom_styles.css');
                }
            }
        } else {
            if (file_exists(Jxmegamenu::stylePath().$this->currentHook.'_megamenu_custom_styles.css')) {
                $combined_css .= Tools::file_get_contents(Jxmegamenu::stylePath().$this->currentHook.'megamenu_custom_styles.css');
            }
        }

        foreach ($dir_files as $dir_file) {
            if (file_exists(Jxmegamenu::stylePath().$dir_file) && in_array(str_replace('.css', '', $dir_file), $active_files)) {
                $combined_css .= Tools::file_get_contents(Jxmegamenu::stylePath().$dir_file);
            }
        }

        if (!Tools::isEmpty($combined_css)) {
            // combine all custom style to one css file
            $file = fopen(Jxmegamenu::stylePath().'combined_unique_styles.css', 'w');
            fwrite($file, $combined_css);
            fclose($file);
        } else {
            // remove cobined css file if no custom style exists
            if (file_exists(Jxmegamenu::stylePath().'combined_unique_styles.css')) {
                @unlink(Jxmegamenu::stylePath().'combined_unique_styles.css');
            }
        }
        return true;
    }

    protected function getWarningMultishop()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->displayError($this->l('You cannot manage slides
                                                    items from "All Shops" or "Group Shop" context,
                                                    select the store you want to edit'));
        } else {
            return false;
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }

        $google_key = MegaMenuSettings::getFieldValueByIdShop($this->context->shop->id, $this->currentHook, 'googleapi');
        $key = '';
        if ($google_key) {
            $key = '&key='.$google_key;
        }

        $default_country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.sortable');
        $this->context->controller->addJqueryUi('ui.widget');
        $this->context->controller->addJqueryPlugin('tagify');
        $this->context->controller->addJqueryPlugin('colorpicker');
        $this->context->controller->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
        $this->context->controller->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
        $this->context->controller->addJS('http'.((Configuration::get('PS_SSL_ENABLED')
                                                && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'))
                                                ? 's'
                                                : '').'://maps.google.com/maps/api/js?sensor=true&amp;region='.Tools::substr($default_country->iso_code, 0, 2).$key);
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
    }

    protected function addGoogleScript()
    {
        if (!$this->checkMapInMenu()) {
            return;
        }
        $google_key = MegaMenuSettings::getFieldValueByIdShop($this->context->shop->id, $this->currentHook, 'googleapi');
        $key = '';
        if ($google_key) {
            $key = '&key='.$google_key;
        }
        $default_country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $google_part = '://maps.google.com/';
        $entry = strpos(implode(',', $this->context->controller->js_files), $google_part);
        if (!$entry) {
            $google_script = 'http'.((Configuration::get('PS_SSL_ENABLED')
                    && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'))
                    ? 's'
                    : '').'://maps.google.com/maps/api/js?region='.Tools::substr($default_country->iso_code, 0, 2).$key;
        } else {
            $google_script = '';
        }

        return $google_script;
    }

    /**
     * Check if map is used in the menu to include Google Script if used and no if don't
     * @return bool
     */
    protected function checkMapInMenu()
    {
        $megamenu = new MegaMenu();
        $items = $megamenu->getTopItems($this->currentHook);
        $list = array();
        foreach ($items as $item) {
            $subitem = $megamenu->getSubitemSettings((int)$item['id_item']);
            if ($subitem) {
                foreach ($subitem as $settings) {
                    $list[] = $settings['settings'];
                }
            }
        }

        if (strpos(implode(',', $list), 'MAP') !== false) {
            return true;
        }

        return false;
    }

    public function getSettingsModal($id_settings)
    {
        $settings = new MegaMenuSettings($id_settings);
        $this->context->smarty->assign('settings', $settings);

        return $this->display($this->local_path, 'views/templates/admin/_partials/settings-modal.tpl');
    }

    /******************************************************************************************************************/
                                        /*JX Blog functionality*/
    /******************************************************************************************************************/
    protected function blogCatTree()
    {
        $blogCategories = JXBlogCategory::getAllShopCategories($this->context->shop->id, $this->context->language->id);

        return $blogCategories;
    }
    /******************************************************************************************************************/
                                        /*And of JX Blog functionality*/
    /******************************************************************************************************************/

    public function hookActionObjectCategoryAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCmsUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCmsDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectCmsAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectSupplierUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectSupplierDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectSupplierAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectManufacturerUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectManufacturerDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectManufacturerAddAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        $this->clearCache();
    }

    public function hookActionObjectProductAddAfter($params)
    {
        $this->clearCache();
    }

    /**
     * Delete all JX Blog data from menus after the blog deleting
     */
    public function hookActionJXBlogBeforeModuleDelete()
    {
        $this->megamenu->removeJxBlogTopItems();
        $this->megamenu->removeJxBlogInnerItems();
        $this->clearCache();
    }

    public function hookActionJxblogCategoryAfterAdd()
    {
        $this->clearCache();
    }

    public function hookActionJxblogCategoryAfterUpdate()
    {
        $this->clearCache();
    }

    public function hookActionJxblogCategoryAfterDelete($params)
    {
        $id = $params['id_jxblog_category'];
        $this->megamenu->removeJxBlogTopItems($id);
        $this->megamenu->removeJxBlogInnerItems($id);
        $this->clearCache();
    }

    public function hookActionJxblogPostAfterUpdate()
    {
        $this->clearCache();
    }

    public function hookActionJxblogPostAfterDelete($params)
    {
        $id = $params['id_jxblog_post'];
        $this->megamenu->removeJxBlogInnerItems($id, true);
        $this->clearCache();
    }

    public function hookCategoryUpdate($params)
    {
        $this->clearCache();
    }

    public function hookHeader()
    {
        $this->context->controller->registerJavascript('module-jxmegamenu-hi', 'modules/'.$this->name.'/views/js/hoverIntent.js');
        $this->context->controller->registerJavascript('module-jxmegamenu-sf', 'modules/'.$this->name.'/views/js/superfish.js');
        $this->context->controller->registerJavascript('module-jxmegamenu', 'modules/'.$this->name.'/views/js/jxmegamenu.js');
        $this->context->controller->registerStylesheet('module-jxmegamenu', 'modules/'.$this->name.'/views/css/jxmegamenu.css');
        $this->context->controller->registerStylesheet('module-jxmegamenu-cs', 'modules/'.$this->name.'/views/css/items/combined_unique_styles.css');

        $this->context->smarty->assign(array(
            'google_script' => $this->addGoogleScript()
        ));

        return $this->display($this->_path, '/views/templates/hook/jxmegamenu-header.tpl');
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $this->smarty->assign('menu', $this->makeMenuTop($hookName));
        $this->smarty->assign('hook', $hookName);
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
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

    public function hookDisplayBeforeBodyClosingTag()
    {
        $maps = $this->megamenumap->getMapsList();

        foreach ($maps as $key => $map) {
            $maps[$key]['icon'] = $map['marker'] ? $this->_path.'img/markers/'.$map['marker'] : '';
        }
        $this->context->smarty->assign(array(
            'maps' => $maps
        ));

        return $this->display($this->_path, '/views/templates/hook/jxmegamenu-script.tpl');
    }
}
