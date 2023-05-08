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

class AdminJXFeaturedPostsController extends ModuleAdminController
{
    public $translator;
    protected $position_identifier = 'id_post_to_move';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'jxfeaturedposts';
        $this->list_id = $this->table;
        $this->identifier = 'id_featured_post';
        $this->className = 'JXFeaturedPost';
        $this->module = $this;
        $this->context = Context::getContext();
        $this->translator = $this->context->getTranslator();
        $this->_defaultOrderBy = 'a.position';
        $this->_defaultOrderWay = 'ASC';
        $this->_default_pagination = 10;
        $this->_pagination = array(10, 20, 50, 100);
        $this->_orderBy = Tools::getValue($this->table.'Orderby');
        $this->_orderWay = Tools::getValue($this->table.'Orderway');
        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->trans('Delete selected', array(), 'Modules.Jxfeaturedposts.Admin'),
                'icon'    => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(), 'Modules.Jxfeaturedposts.Admin')
            )
        );
        $this->fields_list = array(
            'id_featured_post' => array(
                'title'   => $this->trans('ID Featured post', array(), 'Modules.Jxfeaturedposts.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true
            ),
            'post' => array(
                'title'   => $this->trans('Name', array(), 'Modules.Jxfeaturedposts.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'filter_key' => 'bpl!name'
            ),
            'position' => array(
                'title' => $this->trans('Position', array(), 'Modules.Jxfeaturedposts.Admin'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center'
            ),
            'active'            => array(
                'title'   => $this->trans('Status', array(), 'Modules.Jxfeaturedposts.Admin'),
                'active'  => 'status',
                'type'    => 'bool',
                'class'   => 'fixed-width-xs',
                'align'   => 'center',
                'ajax'    => true,
                'orderby' => false
            )
        );
        $this->_join .= 'LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang bpl ON(bpl.`id_jxblog_post` = a.`id_post` AND bpl.`id_lang` = '.$this->context->language->id.')';
        $this->_where = ' AND a.`id_shop` IN('.implode(',', Shop::getContextListShopID()).')';
        $this->_select = ' bpl.`name` as `post`';
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function initContent()
    {
        // show multi-shop error if module is run in a few shop context
        if ($message = $this->getWarningMultishop()) {
            $this->errors[] = $message;
        } else {
            parent::initContent();
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderForm()
    {
        //get all available categories with their posts
        $hiddenExtraClass = '';
        $posts = $this->getCategoriesWithPosts();
        if (!$posts) {
            $hiddenExtraClass = ' hidden';
        }
        $this->fields_form = array(
            'input' => array(
                array(
                    'type'  => 'posts_tree',
                    'name'  => 'id_post',
                    'posts' => $posts,
                    'selected_post' => false,
                    'label' => $this->trans('Select post', array(), 'Modules.Jxfeaturedposts.Admin')
                ),
                array(
                    'form_group_class' => $hiddenExtraClass,
                    'type'  => 'text',
                    'name'  => 'position',
                    'col'   => 3,
                    'label' => $this->trans('Set item position', array(), 'Modules.Jxfeaturedposts.Admin'),
                    'desc' => $this->trans('Set item position in a front-end. It works only if "Custom" ordering is chosen', array(), 'Modules.Jxfeaturedposts.Admin')
                ),
                array(
                    'form_group_class' => $hiddenExtraClass,
                    'type'             => 'switch',
                    'label'            => $this->trans('Status', array(), 'Modules.Jxfeaturedposts.Admin'),
                    'name'             => 'active',
                    'values'           => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Jxfeaturedposts.Admin'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Jxfeaturedposts.Admin'),
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.Jxfeaturedposts.Admin'),
                'class' => 'button pull-right btn btn-default'.$hiddenExtraClass
            )
        );
        $this->tpl_form_vars['id_shop'] = $this->context->shop->id;

        return parent::renderForm();
    }

    public function postProcess()
    {
        $this->module->_clearCache('*');
        return parent::postProcess();
    }

    /**
     * Get all available categories with posts included
     *
     * @return array
     */
    private function getCategoriesWithPosts()
    {
        $result = array();
        // get a list of categories
        $blogCategories = JXBlogCategory::getAllShopCategories($this->context->shop->id, $this->context->language->id);
        if ($blogCategories) {
            // populate categories with posts and its information
            foreach ($blogCategories as $blogCategory) {
                $result[$blogCategory['id_jxblog_category']]['name'] = $blogCategory['name'];
                $result[$blogCategory['id_jxblog_category']]['posts'] = JXFeaturedPostsRepository::getPostsByDefaultCategory($blogCategory['id_jxblog_category'], $this->context->language->id);
            }
        }

        return $result;
    }

    public function ajaxProcessStatusjxfeaturedposts()
    {
        if (!$id_featured_post = (int)Tools::getValue('id_featured_post')) {
            die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.Jxfeaturedposts.Admin'))));
        } else {
            $post = new JXFeaturedPost((int)$id_featured_post);
            if (Validate::isLoadedObject($post)) {
                $post->active = $post->active == 1 ? 0 : 1;
                if ($post->save()) {
                    die(json_encode(array('success' => true, 'text' => $this->trans('The status has been updated successfully', array(), 'Modules.Jxfeaturedposts.Admin'))));
                } else {
                    die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.Jxfeaturedposts.Admin'))));
                }
            }
        }
    }

    public function ajaxProcessUpdatePositions()
    {
        $id_post_to_move = (int)Tools::getValue('id');
        $way = (int)Tools::getValue('way');
        $positions = Tools::getValue('featured_post');
        if (is_array($positions)) {
            foreach ($positions as $key => $value) {
                $pos = explode('_', $value);
                if (isset($pos[2]) && $pos[2] == $id_post_to_move) {
                    $position = $key;
                    break;
                }
            }
        }
        $post = new JXFeaturedPost($id_post_to_move);
        if (Validate::isLoadedObject($post)) {
            if (isset($position) && $post->updatePosition($way, $position)) {
                die(true);
            } else {
                die('{"hasError" : true, errors : "Cannot update featured post position"}');
            }
        } else {
            die('{"hasError" : true, "errors" : "This featured post cannot be loaded"}');
        }
    }

    private function getWarningMultishop()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->trans('You cannot manage this module settings from "All Shops" or "Group Shop" context, select the store you want to edit', array(), 'Modules.Jxfeaturedposts.Admin');
        } else {
            return '';
        }
    }
}
