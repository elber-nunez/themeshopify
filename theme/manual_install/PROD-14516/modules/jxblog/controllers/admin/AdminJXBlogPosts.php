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
 * @author    Zemez (Alexander Grosul)
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class AdminJXBlogPostsController extends ModuleAdminController
{
    public $translator;
    public $categories = false;

    public function __construct()
    {
        $this->table = 'jxblog_post';
        $this->list_id = $this->table;
        $this->identifier = 'id_jxblog_post';
        $this->className = 'JXBlogPost';
        $this->module = $this;
        $this->lang = true;
        $this->bootstrap = true;
        $this->languages = Language::getLanguages(false);
        $this->default_language = Configuration::get('PS_LANG_DEFAULT');
        $this->context = Context::getContext();
        $this->translator = $this->context->getTranslator();
        $this->_defaultOrderBy = 'a.id_jxblog_post';
        $this->_defaultOrderWay = 'ASC';
        $this->_default_pagination = 10;
        $this->_pagination = array(10, 20, 50, 100);
        $this->_orderBy = Tools::getValue($this->table.'Orderby');
        $this->_orderWay = Tools::getValue($this->table.'Orderway');
        $this->imageDir = '../modules/jxblog/img/p/';
        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->trans('Delete selected', array(), 'Modules.JXBlog.Admin'),
                'icon'    => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(), 'Modules.JXBlog.Admin')
            )
        );
        $this->fields_list = array(
            'id_jxblog_post' => array(
                'title'   => $this->trans('ID Post', array(), 'Modules.JXBlog.Admin'),
                'width'   => 50,
                'type'    => 'text',
                'search'  => true,
                'orderby' => true
            ),
            'image' => array(
                'title' => $this->trans('Image', array(), 'Modules.JXBlog.Admin'),
                'image' => $this->imageDir,
                'width' => 150,
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'name'           => array(
                'title'   => $this->trans('Name', array(), 'Modules.JXBlog.Admin'),
                'width'   => 300,
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true,
                'filter_key' => 'b!name'
            ),
            'category_name' => array(
                'title'   => $this->trans('Default category', array(), 'Modules.JXBlog.Admin'),
                'width'   => 300,
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true,
                'filter_key' => 'cl!name'
            ),
            'employee_last_name' => array(
                'title'   => $this->trans('Author', array(), 'Modules.JXBlog.Admin'),
                'width'   => 300,
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true,
                'filter_key' => 'e!firstname'
            ),
            'views'           => array(
                'title'   => $this->trans('Views', array(), 'Modules.JXBlog.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true
            ),
            'date_add' => array(
                'title'   => $this->trans('Date added', array(), 'Modules.JXBlog.Admin'),
                'width'   => 100,
                'type'    => 'datetime',
                'search'  => true,
                'orderby' => true
            ),
            'date_start' => array(
                'title'   => $this->trans('Posted date', array(), 'Modules.JXBlog.Admin'),
                'width'   => 100,
                'type'    => 'datetime',
                'search'  => true,
                'orderby' => true
            ),
            'active'         => array(
                'title'   => $this->trans('Active', array(), 'Modules.JXBlog.Admin'),
                'active'  => 'status',
                'type'    => 'bool',
                'class'   => 'fixed-width-xs',
                'align'   => 'center',
                'ajax'    => true,
                'orderby' => false
            )
        );
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'jxblog_category_lang` cl ON(a.`id_jxblog_category_default` = cl.`id_jxblog_category` AND cl.`id_lang` = '.$this->context->language->id.')';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'employee` e ON(a.`author` = e.`id_employee`)';
        $this->_select = 'cl.`name` as `category_name`, CONCAT(e.`lastname`," ",e.`firstname`)  as `employee_last_name`';
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.widget');
        $this->context->controller->addJqueryPlugin(array('tagify'));

        parent::setMedia($isNewTheme);
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function initContent()
    {
        if (!$this->categories = JXBlogCategory::getAllCategoriesWithInfo()) {
            return $this->errors[] = $this->trans(
                'There are no categories in the blog. To create a post you need to create at least one category before.',
                array(),
                'Modules.JXBlog.Admin'
            );
        }

        return parent::initContent();
    }

    public function renderForm()
    {
        $image = false;
        $thumb = false;
        if (Tools::getIsset('id_jxblog_post') && $id_jxblog_post = Tools::getValue('id_jxblog_post')) {
            if (file_exists($this->module->modulePath.'img/p/'.$id_jxblog_post.'.jpg')) {
                $image = '<img class="imgm img-thumbnail" src="'.$this->module->_link.'img/p/'.$id_jxblog_post.'.jpg" width="300" />';
            }
        }
        if (isset($id_jxblog_post)) {
            if (file_exists($this->module->modulePath.'img/pt/'.$id_jxblog_post.'.jpg')) {
                $thumb = '<img class="imgm img-thumbnail" src="'.$this->module->_link.'img/pt/'.$id_jxblog_post.'.jpg" width="300" />';
            }
        }

        if (Tools::getIsset('id_jxblog_post') && $id_jxblog_post = Tools::getValue('id_jxblog_post')) {
            $post = new JXBlogPost($id_jxblog_post);
        }

        $this->fields_form = array(
            'input'  => array(
                array(
                    'type'     => 'text',
                    'required' => true,
                    'class'    => 'copy2friendlyUrl',
                    'label'    => $this->trans('Post name', array(), 'Modules.JXBlog.Admin'),
                    'hint'     => $this->trans('Invalid characters: &lt;&gt;;=#{}', array(), 'Modules.JXBlog.Admin'),
                    'desc'     => $this->trans('Enter the blog post name', array(), 'Modules.JXBlog.Admin'),
                    'name'     => 'name',
                    'lang'     => true,
                    'col'      => 4
                ),
                array(
                    'type'     => 'text',
                    'hint'     => $this->trans(
                        'Only letters, numbers, underscore (_) and the minus (-) character are allowed.',
                        array(),
                        'Modules.JXBlog.Admin'
                    ),
                    'label'    => $this->trans('Friendly URL', array(), 'Modules.JXBlog.Admin'),
                    'name'     => 'link_rewrite',
                    'required' => true,
                    'desc'     => $this->trans(
                        'Enter the blog post friendly URL. Will be used as a link to the post in the "Friendly URL" mode',
                        array(),
                        'Modules.JXBlog.Admin'
                    ),
                    'lang'     => true,
                    'col'      => 3
                ),
                array(
                    'type'         => 'textarea',
                    'label'        => $this->trans('Short description', array(), 'Modules.JXBlog.Admin'),
                    'name'         => 'short_description',
                    'desc'         => $this->trans('Enter the post short description', array(), 'Modules.JXBlog.Admin'),
                    'lang'         => true,
                    'col'          => 6,
                    'autoload_rte' => true
                ),
                array(
                    'type'         => 'textarea',
                    'label'        => $this->trans('Full description', array(), 'Modules.JXBlog.Admin'),
                    'name'         => 'description',
                    'desc'         => $this->trans('Enter the post full description', array(), 'Modules.JXBlog.Admin'),
                    'lang'         => true,
                    'col'          => 6,
                    'autoload_rte' => true
                ),
                array(
                    'type'  => 'text',
                    'hint'  => $this->trans(
                        'Only letters, numbers, underscore (_) and the minus (-) character are allowed.',
                        array(),
                        'Modules.JXBlog.Admin'
                    ),
                    'label' => $this->trans('Meta Keywords', array(), 'Modules.JXBlog.Admin'),
                    'name'  => 'meta_keyword',
                    'desc'  => $this->trans(
                        'Enter Your Post Meta Keywords. Separated by comma(,) ',
                        array(),
                        'Modules.JXBlog.Admin'
                    ),
                    'lang'  => true,
                    'col'   => 6
                ),
                array(
                    'type'         => 'textarea',
                    'label'        => $this->trans('Meta Description', array(), 'Modules.JXBlog.Admin'),
                    'name'         => 'meta_description',
                    'desc'         => $this->trans('Enter the post meta description', array(), 'Modules.JXBlog.Admin'),
                    'lang'         => true,
                    'col'          => 6,
                    'autoload_rte' => false
                ),
                array(
                    'type'          => 'file',
                    'label'         => $this->trans('Post image', array(), 'Modules.JXBlog.Admin'),
                    'name'          => 'image',
                    'display_image' => true,
                    'image'         => $image ? $image : false,
                    'desc'          => $this->trans('Only .jpg images are allowed', array(), 'Modules.JXBlog.Admin')
                ),
                array(
                    'type'          => 'file',
                    'label'         => $this->trans('Post thumb', array(), 'Modules.JXBlog.Admin'),
                    'name'          => 'thumbnail',
                    'display_image' => true,
                    'image'         => $thumb ? $thumb : false,
                    'desc'          => $this->trans('Only .jpg images are allowed', array(), 'Modules.JXBlog.Admin')
                ),
                array(
                    'type'   => 'switch',
                    'label'  => $this->trans('Status', array(), 'Modules.JXBlog.Admin'),
                    'name'   => 'active',
                    'values' => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.JXBlog.Admin')
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.JXBlog.Admin')
                        )
                    )
                ),
                array(
                    'type' => 'cat_list',
                    'label' => $this->trans('Associated categories', array(), 'Modules.JXBlog.Admin'),
                    'categories' => $this->categories,
                    'name' => 'cat_list',
                    'required' => true,
                    'hint' => $this->trans('Select default category(radio) and all related(checkboxes)', array(), 'Modules.JXBlog.Admin'),
                    'id_jxblog_category_default' => isset($post) ? $post->id_jxblog_category_default : false,
                    'related_categories' => isset($post) ? $post->getAssociatedCategories() : false
                ),
                array(
                    'type' => 'tags',
                    'name' => 'tags',
                    'label' => $this->trans('Post tags', array(), 'Modules.JXBlog.Admin'),
                    'lang' => true,
                    'hint' => $this->trans('To add "tags," click in the field, write something, and then press "Enter."', array(), 'Modules.JXBlog.Admin').'&nbsp;'.$this->trans('Forbidden characters:', array(), 'Modules.JXBlog.Admin').' <>;=#{}'
                ),
                array(
                    'type' => 'datetime',
                    'name' => 'date_start',
                    'label' => $this->trans('Publishing date', array(), 'Modules.JXBlog.Admin'),
                    'desc' => $this->trans('Set the date if you want to delay the article publishing.', array(), 'Modules.JXBlog.Admin')
                )
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.JXBlog.Admin'),
                'class' => 'button pull-right btn btn-default'
            )
        );

        $extraData = array_values(Hook::exec('displayJxblogPostExtra', array('post' => isset($post) ? $post : false), null, true));
        // add all necessary data from related modules
        if ($extraData) {
            foreach ($extraData as $extra) {
                $extraFields = $extra['fields'];
                $extraValues = $extra['values'];
                foreach ($extraFields as $filed) {
                    $this->fields_form['input'][] = $filed;
                }
                foreach ($extraValues as $key => $filed) {
                    $this->fields_value[$key] = $filed;
                }
            }
        }

        $this->fields_value['tags'] = isset($post) ? $post->getAdminPostTags() : false;

        if (!($JXBlogPost = $this->loadObject(true))) {
            return;
        }
        return parent::renderForm();
    }

    public function ajaxProcessSearchPosts()
    {
        $excludeIds = array();
        $exclude = explode(',', Tools::getValue('excludeIds'));
        foreach ($exclude as $item) {
            if ($item) {
                $excludeIds[] = $item;
            }
        }

        $posts = JXBlogPost::searchPostsLive(Tools::getValue('q'), $this->context->language->id, Tools::getValue('limit'), $excludeIds);
        if ($posts) {
            die(implode("\n", $posts));
        }
    }

    public function ajaxProcessStatusjxblogPost()
    {
        if (!$id_post = (int)Tools::getValue('id_jxblog_post')) {
            die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.JXBlog.Admin'))));
        } else {
            $post = new JXBlogPost((int)$id_post);
            if (Validate::isLoadedObject($post)) {
                $post->active = $post->active == 1 ? 0 : 1;
                $post->save() ?
                    die(json_encode(array('success' => true, 'text' => $this->trans('The status has been updated successfully', array(), 'Modules.JXBlog.Admin')))) :
                    die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.JXBlog.Admin'))));
            }
        }
    }
}
