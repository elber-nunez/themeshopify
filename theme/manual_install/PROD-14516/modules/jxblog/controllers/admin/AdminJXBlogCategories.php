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

class AdminJXBlogCategoriesController extends ModuleAdminController
{
    public $translator;
    protected $position_identifier = 'id_jxblog_category_to_move';
    public $imageManager;
    public function __construct()
    {
        $this->translator = Context::getContext()->getTranslator();
        $this->table = 'jxblog_category';
        $this->list_id = $this->table;
        $this->identifier = 'id_jxblog_category';
        $this->className = 'JXBlogCategory';
        $this->module = $this;
        $this->lang = true;
        $this->bootstrap = true;
        $this->languages = Language::getLanguages(false);
        $this->default_language = Configuration::get('PS_LANG_DEFAULT');
        $this->context = Context::getContext();
        if (Shop::isFeatureActive()) {
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        }
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.$this->table.'_shop jxs ON a.id_jxblog_category=jxs.id_jxblog_category && jxs.id_shop IN('.implode(',', Shop::getContextListShopID()).')';
        $this->_select = 'jxs.id_shop';
        $this->_defaultOrderBy = 'a.position';
        $this->_defaultOrderWay = 'ASC';
        $this->_default_pagination = 10;
        $this->_pagination = array(10, 20, 50, 100);
        $this->_orderBy = Tools::getValue($this->table.'Orderby');
        $this->_orderWay = Tools::getValue($this->table.'Orderway');
        $this->orderBy = 'position';
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected', array(), 'Admin.Actions'),
                'icon' => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(), 'Admin.Notifications.Warning')
            )
        );
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->_group = 'GROUP BY a.id_jxblog_category';
        }
        $this->fields_list = array(
            'id_jxblog_category' => array(
                'title'   => $this->trans('ID Category', array(), 'Modules.JXBlog.Admin'),
                'width'   => 100,
                'type'    => 'text',
                'search'  => true,
                'orderby' => true
            ),
            'name'        => array(
                'title'   => $this->trans('Name', array(), 'Modules.JXBlog.Admin'),
                'width'   => 440,
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true
            ),
            'position' => array(
                'title' => $this->trans('Position', array(), 'Admin.Global'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center'
            ),
            'active' => array(
                'title' => $this->trans('Displayed', array(), 'Admin.Global'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false
            )
        );
        parent::__construct();
        $this->imageManager = new JXBlogImageManager($this->module);
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function initContent()
    {
        if ($this->errors) {
            $this->content = $this->renderForm();
            $this->context->smarty->assign('content', $this->content);
        } elseif (Tools::getIsset('delete'.$this->table)) {
            $this->content = $this->confirmDeleteForm();
            parent::initContent();
        } else {
            parent::initContent();
        }
    }

    public function confirmDeleteForm()
    {
        $availableCategories = JXBlogCategory::getAllCategoriesWithInfo();
        $options = array();
        foreach ($availableCategories as $key => $category) {
            if ($category['id_jxblog_category'] != Tools::getValue('id_jxblog_category')) {
                $options[$key]['id'] = $category['id_jxblog_category'];
                $options[$key]['type'] = $category['name'];
            }
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->trans('What do you want to do with products that are related to the category and have it as the main?', array(), 'Module.JXBlog.Admin')
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_jxblog_category',
                    'value' => Tools::getValue('id_jxblog_category')
                ),
                array(
                    'type' => 'radio',
                    'name' => 'deleteAction',
                    'label' => '',
                    'col'  => 12,
                    'values' => array(
                        array(
                            'id' => 'deleteAction',
                            'value' => 1,
                            'label' => $this->trans('Leave post without main category', array(), 'Module.JXBlog.Admin')
                        ),
                        array(
                            'id' => 'deleteAction',
                            'value' => 2,
                            'label' => $this->trans('Remove post for which the category is the main', array(), 'Module.JXBlog.Admin')
                        ),
                        array(
                            'id' => 'deleteAction',
                            'value' => 3,
                            'label' => $this->trans('Select new main category from the list below', array(), 'Module.JXBlog.Admin')
                        )
                    )
                ),
                array(
                    'type' => 'select',
                    'name' => 'newDefaultCategory',
                    'label' => '',
                    'col' => 12,
                    'options' => array(
                        'query' => $options,
                        'id'    => 'id',
                        'name'  => 'type'
                    )
                )
            ),
            'buttons' => array(
                array(
                    'title' => $this->trans('Delete', array(), 'Modules.JXBlog.Admin'),
                    'class' => 'button pull-right btn btn-danger',
                    'name' => 'confirmDelete',
                    'type' => 'submit'
                )
            )
        );

        $this->fields_value['deleteAction'] = 1;
        $this->submit_action = 'confirmDelete';
        return parent::renderForm();
    }

    public function renderForm()
    {
        $id_category = Tools::getValue('id_jxblog_category');
        $unidentified = new Group(Configuration::get('PS_UNIDENTIFIED_GROUP'));
        $guest = new Group(Configuration::get('PS_GUEST_GROUP'));
        $default = new Group(Configuration::get('PS_CUSTOMER_GROUP'));
        $unidentified_group_information = sprintf($this->trans('%s - All people without a valid customer account.', array(), 'Modules.JXBlog.Admin'), '<b>'.$unidentified->name[$this->context->language->id].'</b>');
        $guest_group_information = sprintf($this->trans('%s - Customer who placed an order with the guest checkout.', array(), 'Modules.JXBlog.Admin'), '<b>'.$guest->name[$this->context->language->id].'</b>');
        $default_group_information = sprintf($this->trans('%s - All people who have created an account on this site.', array(), 'Modules.JXBlog.Admin'), '<b>'.$default->name[$this->context->language->id].'</b>');
        $image = false;
        $thumb = false;
        if (Tools::getIsset('id_jxblog_category') && Tools::getValue('id_jxblog_category')) {
            if (file_exists($this->module->modulePath.'img/c/'.Tools::getValue('id_jxblog_category').'.jpg')) {
                $image = '<img class="imgm img-thumbnail" src="'.$this->module->_link.'img/c/'.Tools::getValue('id_jxblog_category').'.jpg" width="300" />';
            }
            if (file_exists($this->module->modulePath.'img/ct/'.Tools::getValue('id_jxblog_category').'.jpg')) {
                $thumb = '<img class="imgm img-thumbnail" src="'.$this->module->_link.'img/ct/'.Tools::getValue('id_jxblog_category').'.jpg" width="150" />';
            }
        }
        $this->fields_form = array(
            'input'  => array(
                array(
                    'type'     => 'text',
                    'class'    => 'copy2friendlyUrl',
                    'hint'     => $this->trans('Invalid characters: <>;=#{}', array(), 'Modules.JXBlog.Admin'),
                    'label'    => $this->trans('Name', array(), 'Modules.JXBlog.Admin'),
                    'name'     => 'name',
                    'required' => true,
                    'desc'     => $this->trans('Enter the blog category name', array(), 'Modules.JXBlog.Admin'),
                    'lang'     => true,
                    'col'      => 3
                ),
                array(
                    'type'     => 'text',
                    'hint'     => $this->trans('Only letters, numbers, underscore (_) and the minus (-) character are allowed.', array(), 'Modules.JXBlog.Admin'),
                    'label'    => $this->trans('Friendly URL', array(), 'Modules.JXBlog.Admin'),
                    'name'     => 'link_rewrite',
                    'required' => true,
                    'desc'     => $this->trans(
                        'Enter the blog category friendly URL. Will be used as a link to the category in the "Friendly URL" mode',
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
                    'desc'         => $this->trans('Enter the category short description', array(), 'Modules.JXBlog.Admin'),
                    'lang'         => true,
                    'col'          => 6,
                    'autoload_rte' => true
                ),
                array(
                    'type'         => 'textarea',
                    'label'        => $this->trans('Full description', array(), 'Modules.JXBlog.Admin'),
                    'name'         => 'description',
                    'desc'         => $this->trans('Enter the category full description', array(), 'Modules.JXBlog.Admin'),
                    'lang'         => true,
                    'col'          => 6,
                    'autoload_rte' => true
                ),
                array(
                    'type'     => 'text',
                    'hint'     => $this->trans('Only letters, numbers, underscore (_) and the minus (-) character are allowed.', array(), 'Modules.JXBlog.Admin'),
                    'label'    => $this->trans('Meta Keywords', array(), 'Modules.JXBlog.Admin'),
                    'name'     => 'meta_keyword',
                    'desc'     => $this->trans(
                        'Enter Your Category Meta Keywords. Separated by comma(,) ',
                        array(),
                        'Modules.JXBlog.Admin'
                    ),
                    'lang'     => true,
                    'col'      => 6
                ),
                array(
                    'type'         => 'textarea',
                    'label'        => $this->trans('Meta Description', array(), 'Modules.JXBlog.Admin'),
                    'name'         => 'meta_description',
                    'desc'         => $this->trans('Enter the category meta description', array(), 'Modules.JXBlog.Admin'),
                    'lang'         => true,
                    'col'          => 6,
                    'autoload_rte' => false
                ),
                array(
                    'type' => 'file',
                    'label' => $this->trans('Image', array(), 'Modules.JXBlog.Admin'),
                    'name' => 'image',
                    'value' => true,
                    'display_image' => false,
                    'image' => $image
                ),
                array(
                    'type' => 'file',
                    'label' => $this->trans('Image thumbnail', array(), 'Modules.JXBlog.Admin'),
                    'name' => 'thumbnail',
                    'value' => true,
                    'display_image' => false,
                    'image' => $thumb
                ),
                array(
                    'type' => 'group',
                    'label' => $this->trans('Group access', array(), 'Modules.JXBlog.Admin'),
                    'name' => 'groupBox',
                    'values' => Group::getGroups(Context::getContext()->language->id),
                    'info_introduction' => $this->trans('You now have three default customer groups.', array(), 'Modules.JXBlog.Admin'),
                    'unidentified' => $unidentified_group_information,
                    'guest' => $guest_group_information,
                    'customer' => $default_group_information,
                    'hint' => $this->trans('Mark all of the customer groups which you would like to have access to this category.', array(), 'Modules.JXBlog.Admin')

                ),
                array(
                    'type'     => 'text',
                    'hint'     => $this->trans('Only letters, numbers, underscore (_) and the minus (-) character are allowed.', array(), 'Modules.JXBlog.Admin'),
                    'label'    => $this->trans('Badge', array(), 'Modules.JXBlog.Admin'),
                    'name'     => 'badge',
                    'desc'     => $this->trans(
                        'Enter the badge which will unify  the category on the list',
                        array(),
                        'Modules.JXBlog.Admin'
                    ),
                    'lang'     => true,
                    'col'      => 6
                ),
                array(
                    'type'             => 'switch',
                    'label'            => $this->trans('Status', array(), 'Modules.JXBlog.Admin'),
                    'name'             => 'active',
                    'values'           => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.JXBlog.Admin'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.JXBlog.Admin'),
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.JXBlog.Admin'),
                'class' => 'button pull-right btn btn-default'
            )
        );

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->trans('Shop association:', array(), 'Modules.JXBlog.Admin'),
                'name' => 'checkBoxShopAsso'
            );
        }

        $category_groups_ids = array();
        if ($id_category) {
            $category = new JXBlogCategory($id_category);
            $category_groups_ids = $category->getGroups();
        }

        $groups = Group::getGroups($this->context->language->id);
        if (!count($category_groups_ids)) {
            $preselected = array(Configuration::get('PS_UNIDENTIFIED_GROUP'), Configuration::get('PS_GUEST_GROUP'), Configuration::get('PS_CUSTOMER_GROUP'));
            $category_groups_ids = array_merge($category_groups_ids, $preselected);
        }
        foreach ($groups as $group) {
            $this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue('groupBox_'.$group['id_group'], (in_array($group['id_group'], $category_groups_ids)));
        }

        if (!($JXBlogCategory = $this->loadObject(true))) {
            return;
        }

        return parent::renderForm();
    }

    public function validateRules($class_name = false)
    {
        parent::validateRules();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddjxblog_category') && !Tools::getIsset('confirmDelete')) {
            $this->validateRules();
            if (count($this->errors)) {
                return false;
            }
            $id_category = (int)Tools::getValue('id_jxblog_category');
            if (!$id_category) {
                $category = new JXBlogCategory();
            } else {
                $category = new JXBlogCategory($id_category);
            }
            if (!$id_category) {
                $category->date_add = date('y-m-d H:i:s');
                $category->position = (int)$category->getNewPosition();
            } else {
                $category->date_upd = date('y-m-d H:i:s');
            }
            $category->active = Tools::getValue('active');
            foreach ($this->languages as $lang) {
                $category->name[$lang['id_lang']] = Tools::getValue('name_'.$lang['id_lang']);
                $category->link_rewrite[$lang['id_lang']] = Tools::getValue('link_rewrite_'.$lang['id_lang']);
                if (!$category->link_rewrite[$lang['id_lang']]) {
                    $category->link_rewrite[$lang['id_lang']] = Tools::getValue(
                        'link_rewrite_'.$this->default_language
                    );
                }
                if (!$category->link_rewrite[$lang['id_lang']]) {
                    $category->link_rewrite[$lang['id_lang']] = Tools::getValue(
                        'link_rewrite_'.$this->default_language
                    );
                }
                if ($category->checkCategoryNameExistence(
                    $category->id,
                    $category->name[$lang['id_lang']],
                    $lang['id_lang']
                )
                ) {
                    $this->errors[] = sprintf(
                        $this->trans('The category with such name already exists!. Name: %s, Language: %s'),
                        $category->name[$lang['id_lang']],
                        $lang['iso_code']
                    );
                }
                if ($category->checkFriendlyUrlNameExistence(
                    $category->id,
                    $category->link_rewrite[$lang['id_lang']],
                    $lang['id_lang']
                )
                ) {
                    $this->errors[] = sprintf(
                        $this->trans('The category with such Friendly Url already exists!. Name: %s, Language: %s'),
                        $category->link_rewrite[$lang['id_lang']],
                        $lang['iso_code']
                    );
                }
                $category->description[$lang['id_lang']] = Tools::getValue('description_'.$lang['id_lang']);
                $category->short_description[$lang['id_lang']] = Tools::getValue('short_description_'.$lang['id_lang']);
                $category->meta_keyword[$lang['id_lang']] = Tools::getValue('meta_keyword_'.$lang['id_lang']);
                $category->meta_description[$lang['id_lang']] = Tools::getValue('meta_description_'.$lang['id_lang']);
                $category->badge[$lang['id_lang']] = Tools::getValue('badge_'.$lang['id_lang']);
            }
            if ($this->errors) {
                return false;
            }
            if (!$category->save()) {
                $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
            }
            // upload category images after successful saving
            $imageManger = new JXBlogImageManager($this->module);
            if (!Tools::isEmpty(Tools::getValue('image')) && Tools::getValue('image')) {
                if ($error = $imageManger->uploadImage($category->id, $_FILES['image'], 'category')) {
                    $this->errors[] = $error;
                }
            }
            if (!Tools::isEmpty(Tools::getValue('thumbnail')) && Tools::getValue('thumbnail')) {
                if ($error = $imageManger->uploadImage($category->id, $_FILES['thumbnail'], 'category_thumb')) {
                    $this->errors[] = $error;
                }
            }
            // redirect to the categories list page if no errors occurred
            if (!$this->errors) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminJXBlogCategories').'&conf=4');
            }
        } elseif (Tools::isSubmit('confirmDelete') && $id_jxblog_category = Tools::getValue('id_jxblog_category')) {
            $post = new JXBlogPost();
            $category = new JXBlogCategory($id_jxblog_category);
            switch (Tools::getValue('deleteAction')) {
                case 1:
                    $post->resetDefaultCategory($id_jxblog_category);
                    break;
                case 2:
                    $posts = $post->getPostsByDefaultCategory($id_jxblog_category);
                    if ($posts) {
                        foreach ($posts as $post) {
                            $post = new JXBlogPost($post['id_jxblog_post']);
                            $post->delete();
                        }
                    }
                    break;
                case 3:
                    $post->resetDefaultCategory($id_jxblog_category, Tools::getValue('newDefaultCategory'));
                    break;
            }
            $category->delete();
        } elseif (!Tools::getIsset('delete'.$this->table)) {
            parent::postProcess();
        }
    }

    public function ajaxProcessStatusjxblogCategory()
    {
        if (!$id_category = (int)Tools::getValue('id_jxblog_category')) {
            die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.JXBlog.Admin'))));
        } else {
            $category = new JXBlogCategory((int)$id_category);
            if (Validate::isLoadedObject($category)) {
                $category->active = $category->active == 1 ? 0 : 1;
                $category->save() ?
                    die(json_encode(array('success' => true, 'text' => $this->trans('The status has been updated successfully', array(), 'Modules.JXBlog.Admin')))) :
                    die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.JXBlog.Admin'))));
            }
        }
    }

    public function ajaxProcessUpdatePositions()
    {
        $id_category_to_move = (int)Tools::getValue('id');
        $way = (int)Tools::getValue('way');
        $positions = Tools::getValue('jxblog_category');
        if (is_array($positions)) {
            foreach ($positions as $key => $value) {
                $pos = explode('_', $value);
                if (isset($pos[2]) && $pos[2] == $id_category_to_move) {
                    $position = $key;
                    break;
                }
            }
        }

        $category = new JXBlogCategory($id_category_to_move);
        if (Validate::isLoadedObject($category)) {
            if (isset($position) && $category->updatePosition($way, $position)) {
                die(true);
            } else {
                die('{"hasError" : true, errors : "Cannot update categories position"}');
            }
        } else {
            die('{"hasError" : true, "errors" : "This category cannot be loaded"}');
        }
    }
}
