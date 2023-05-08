<?php
/**
 * 2017 Zemez
 *
 * JX Blog Comment
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
 *  @copyright 2017 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class AdminJXBlogCommentsController extends ModuleAdminController
{
    public $translator;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'jxblog_comment';
        $this->list_id = $this->table;
        $this->identifier = 'id_jxblog_comment';
        $this->className = 'JXBlogComments';
        $this->module = $this;
        $this->lang = false;
        $this->bootstrap = true;
        $this->languages = Language::getLanguages(false);
        $this->default_language = Configuration::get('PS_LANG_DEFAULT');
        $this->context = Context::getContext();
        $this->translator = $this->context->getTranslator();
        $this->_defaultOrderBy = 'a.id_jxblog_comment';
        $this->_defaultOrderWay = 'ASC';
        $this->_default_pagination = 10;
        $this->_pagination = array(10, 20, 50, 100);
        $this->_orderBy = Tools::getValue($this->table.'Orderby');
        $this->_orderWay = Tools::getValue($this->table.'Orderway');
        $this->imageDir = '../modules/jxblogcomment/attachments/';
        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->trans('Delete selected', array(), 'Modules.Jxblogcomment.Admin'),
                'icon'    => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(), 'Modules.Jxblogcomment.Admin')
            )
        );
        $this->fields_list = array(
            'id_jxblog_comment' => array(
                'title'   => $this->trans('ID Comment', array(), 'Modules.Jxblogcomment.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true
            ),
            'post' => array(
                'title'   => $this->trans('Post', array(), 'Modules.Jxblogcomment.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'filter_key' => 'bpl!name'
            ),
            'author'              => array(
                'title'   => $this->trans('Author', array(), 'Modules.Jxblogcomment.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true,
                'filter_key' => 'c!lastname'
            ),
            'content'           => array(
                'title'   => $this->trans('Content', array(), 'Modules.Jxblogcomment.Admin'),
                'width'   => 500,
                'type'    => 'text',
                'search'  => false,
                'orderby' => false,
                'lang'    => false
            ),
            'img'               => array(
                'title'    => $this->trans('Attachments'),
                'type'     => 'image_field',
                'img_path' => $this->imageDir,
                'search'   => false,
                'orderby'  => false
            ),
            'upvote_count'      => array(
                'title'   => $this->trans('Votes', array(), 'Modules.Jxblogcomment.Admin'),
                'type'    => 'text',
                'search'  => true,
                'orderby' => true,
                'lang'    => true,
                'class'   => 'text-center'
            ),
            'date_add'          => array(
                'title'   => $this->trans('Date added', array(), 'Modules.Jxblogcomment.Admin'),
                'width'   => 100,
                'type'    => 'datetime',
                'search'  => true,
                'orderby' => true
            ),
            'date_update'       => array(
                'title'   => $this->trans('Date update', array(), 'Modules.Jxblogcomment.Admin'),
                'width'   => 100,
                'type'    => 'datetime',
                'search'  => true,
                'orderby' => true
            ),
            'is_new'            => array(
                'title'   => $this->trans('Is new', array(), 'Modules.Jxblogcomment.Admin'),
                'active'  => 'new',
                'type'    => 'bool',
                'class'   => 'fixed-width-xs',
                'align'   => 'center',
                'ajax'    => true,
                'orderby' => false
            ),
            'active'            => array(
                'title'   => $this->trans('Active', array(), 'Modules.Jxblogcomment.Admin'),
                'active'  => 'status',
                'type'    => 'bool',
                'class'   => 'fixed-width-xs',
                'align'   => 'center',
                'ajax'    => true,
                'orderby' => false
            )
        );
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer c ON(a.`id_customer` = c.`id_customer`)';
        $this->_join .= 'LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang bpl ON(bpl.`id_jxblog_post` = a.`id_post` AND bpl.`id_lang` = '.$this->context->language->id.')';
        $this->_select = 'CONCAT(c.`firstname`," ", c.`lastname`) as `author`, bpl.`name` as `post`';
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    /**
     * Override to hide the actions panel
     */
    public function initToolbar()
    {
    }

    public function renderList()
    {
        $this->addRowAction('reply');
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function initContent()
    {
        // instantiation of a reply form
        if (Tools::getIsset('reply') && $id_jxblog_comment = Tools::getValue('id_jxblog_comment')) {
            $this->content = $this->renderReplyForm($id_jxblog_comment);
            $this->context->smarty->assign(
                array(
                    'content'                   => $this->content,
                    'url_post'                  => self::$currentIndex.'&token='.$this->token,
                    'show_page_header_toolbar'  => $this->show_page_header_toolbar,
                    'page_header_toolbar_title' => $this->page_header_toolbar_title,
                    'page_header_toolbar_btn'   => $this->page_header_toolbar_btn
                )
            );
        } else {
            parent::initContent();
        }
    }

    /**
     * Display default action link
     */
    public function displayReplyLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('list_action_reply.tpl');

        if (!array_key_exists('Reply', self::$cache_lang)) {
            self::$cache_lang['Reply'] = $this->context->getTranslator()->trans('Reply', array(), 'Modules.Jxblogcomment.Admin');
        }

        $tpl->assign(array(
            'href' => $this->context->link->getAdminLink('AdminJXBlogComments').'&'.$this->identifier.'='.(int)$id.'&reply',
            'action' => self::$cache_lang['Reply'],
            'name' => $name,
        ));

        return $tpl->fetch();
    }

    public function renderForm()
    {
        $attachment = false;
        if ($id_comment = Tools::getValue('id_jxblog_comment')) {
            $comment = new JXBlogComments($id_comment);
            if ($comment->image_name) {
                $attachment = $this->module->attachmentsLink.$comment->image_name;
            }
        }
        $this->fields_form = array(
            'input' => array(
                array(
                    'form_group_class' => $attachment ? 'hidden' : '',
                    'id'               => 'comment-content',
                    'type'             => 'textarea',
                    'label'            => $this->trans('Comment content', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'             => 'content',
                    'desc'             => $this->trans(
                        'You can change the comment content',
                        array(),
                        'Modules.Jxblogcomment.Admin'
                    ),
                    'lang'             => false,
                    'col'              => 4,
                    'autoload_rte'     => false
                ),
                array(
                    'label' => $this->trans('Attachment', array(), 'Modules.Jxblogcomment.Admin'),
                    'type' => 'attachment',
                    'name' => 'attachment',
                    'file' => $attachment
                ),
                array(
                    'type'   => 'switch',
                    'label'  => $this->trans('Status', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'   => 'active',
                    'values' => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Jxblogcomment.Admin'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Jxblogcomment.Admin'),
                        )
                    )
                ),
                array(
                    'type'   => 'switch',
                    'label'  => $this->trans('Is new', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'   => 'is_new',
                    'values' => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Jxblogcomment.Admin'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Jxblogcomment.Admin'),
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Modules.Jxblogcomment.Admin'),
                'class' => 'button pull-right btn btn-default'
            )
        );

        return parent::renderForm();
    }

    public function renderReplyForm($id_jxblog_comment)
    {
        $attachment = false;
        if (!Validate::isLoadedObject($comment = new JXBlogComments($id_jxblog_comment))) {
            return false;
        }
        if ($comment->image_name) {
            $attachment = $this->module->attachmentsLink.$comment->image_name;
        }
        $this->fields_form = array(
            'input' => array(
                array(
                    'form_group_class' => $attachment ? 'hidden' : '',
                    'id'               => 'comment-content',
                    'type'             => 'textarea',
                    'label'            => $this->trans('Comment content', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'             => 'content',
                    'desc'             => $this->trans(
                        'Content of the comment you want to answer to',
                        array(),
                        'Modules.Jxblogcomment.Admin'
                    ),
                    'lang'             => false,
                    'col'              => 6,
                    'autoload_rte'     => false,
                    'readonly'         => true
                ),
                array(
                    'form_group_class' => !$attachment ? 'hidden' : '',
                    'label' => $this->trans('Attachment', array(), 'Modules.Jxblogcomment.Admin'),
                    'type' => 'attachment',
                    'name' => 'attachment',
                    'file' => $attachment
                ),
                array(
                    'id'               => 'reply-content',
                    'type'             => 'textarea',
                    'label'            => $this->trans('Reply content', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'             => 'reply_content',
                    'desc'             => $this->trans(
                        'Write your reply. Only text or attachment is available in other case attachment has a precedence',
                        array(),
                        'Modules.Jxblogcomment.Admin'
                    ),
                    'lang'             => false,
                    'col'              => 6,
                    'autoload_rte'     => false
                ),
                array(
                    'label' => $this->trans('Attachment', array(), 'Modules.Jxblogcomment.Admin'),
                    'type' => 'file',
                    'name' => 'reply_attachment',
                    'file' => ''
                ),
                array(
                    'type'   => 'switch',
                    'label'  => $this->trans('Status', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'   => 'active',
                    'values' => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Jxblogcomment.Admin'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Jxblogcomment.Admin'),
                        )
                    )
                ),
                array(
                    'type'   => 'switch',
                    'label'  => $this->trans('Is new?', array(), 'Modules.Jxblogcomment.Admin'),
                    'name'   => 'is_new',
                    'values' => array(
                        array(
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Modules.Jxblogcomment.Admin'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Modules.Jxblogcomment.Admin'),
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->trans('Reply', array(), 'Modules.Jxblogcomment.Admin'),
                'class' => 'button pull-right btn btn-default',
                'icon' => 'process-icon-reply icon-reply',
                'name' => 'jxBlogCommentReply'
            )
        );

        $this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_jxblog_comment_hidden');
        $this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_post');

        $this->fields_value['content'] = $comment->content;
        $this->fields_value['id_jxblog_comment_hidden'] = $comment->id;
        $this->fields_value['id_post'] = $comment->id_post;
        $this->fields_value['active'] = 1;

        return parent::renderForm();
    }

    public function preValidateReply()
    {
        if (!Tools::getValue('id_jxblog_comment_hidden')) {
            $this->errors[] = $this->trans('Comment with such id doesn\'t found', array(), 'Modules.Jxblogcomment.Admin');
        }

        if (!Tools::getValue('reply_content') && !Tools::getValue('reply_attachment')) {
            $this->errors[] = $this->trans('You did not leave any comment. Please leave comment or attach any image', array(), 'Modules.Jxblogcomment.Admin');
        }

        if (Tools::getValue('reply_content') && !Validate::isString('reply_content')) {
            $this->errors[] = $this->trans('Content of the reply message is invalid', array(), 'Modules.Jxblogcomment.Admin');
        }

        if (Tools::getValue('reply_attachment') && $errors = ImageManager::validateUpload($_FILES['reply_attachment'])) {
            $this->errors[] = $errors;
        }

        if (count($this->errors)) {
            return false;
        }

        return true;
    }

    public function replyComment()
    {
        $reply = new JXBlogComments();
        $reply->id_post = (int)Tools::getValue('id_post');
        $reply->id_parent = (int)Tools::getValue('id_jxblog_comment_hidden');
        $reply->id_admin = $this->context->employee->id;
        $reply->content = Tools::getValue('reply_content');
        $reply->image_name = Tools::getValue('reply_attachment');
        $reply->is_new = Tools::getValue('is_new');
        $reply->active = Tools::getValue('active');
        if ($reply->image_name) {
            if (isset($_FILES['reply_attachment']['name']) && isset($_FILES['reply_attachment']['tmp_name']) && !Tools::isEmpty($_FILES['reply_attachment']['tmp_name'])) {
                $fileFormat = explode('.', $_FILES['reply_attachment']['name']);
                $newName = Tools::passwdGen(15).'.'.$fileFormat[1];
                if (!move_uploaded_file($_FILES['reply_attachment']['tmp_name'], $this->module->attachmentsPath.$newName)) {
                    $this->errors[] = $this->trans('Cannot upload an image', array(), 'Modules.Jxblogcomment.Admin');
                }
            } else {
                $this->errors[] = $this->trans('Cannot upload an image', array(), 'Modules.Jxblogcomment.Admin');
            }
            $reply->image_name = $newName;
            $reply->image_type = $_FILES['reply_attachment']['type'];
        }

        if (count($this->errors)) {
            return false;
        }

        if (!$reply->add()) {
            $this->errors[] = $this->trans('Cannot reply a comment', array(), 'Modules.Jxblogcomment.Admin');
        }

        if (count($this->errors)) {
            return false;
        }

        return true;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('jxBlogCommentReply')) {
            if (!$this->preValidateReply()) {
                $this->content = $this->renderReplyForm(Tools::getValue('id_jxblog_comment_hidden'));
            } else {
                $this->replyComment();
            }
        } else {
            parent::postProcess();
        }
    }

    public function ajaxProcessStatusjxblogComment()
    {
        if (!$id_comment = (int)Tools::getValue('id_jxblog_comment')) {
            die(
                json_encode(
                    array(
                        'success' => false,
                        'error' => true,
                        'text' => $this->trans('Failed to update the status', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        } else {
            $comment = new JXBlogComments((int)$id_comment);
            if (Validate::isLoadedObject($comment)) {
                $comment->active = $comment->active == 1 ? 0 : 1;
                $comment->save() ?
                die(
                    json_encode(
                        array(
                            'success' => true,
                            'text' => $this->trans('The status has been updated successfully', array(), 'Modules.Jxblogcomment.Admin')
                        )
                    )
                ) :
                die(
                    json_encode(
                        array(
                            'success' => false,
                            'error' => true,
                            'text' => $this->trans('Failed to update the status', array(), 'Modules.Jxblogcomment.Admin')
                        )
                    )
                );
            }
        }
    }

    public function ajaxProcessNewjxblogComment()
    {
        if (!$id_comment = (int)Tools::getValue('id_jxblog_comment')) {
            die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.JXBlogComments.Admin'))));
        } else {
            $comment = new JXBlogComments((int)$id_comment);
            if (Validate::isLoadedObject($comment)) {
                $comment->is_new = $comment->is_new == 1 ? 0 : 1;
                $comment->save() ?
                    die(json_encode(array('success' => true, 'text' => $this->trans('The status has been updated successfully', array(), 'Modules.Jxblogcomment.Admin')))) :
                    die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Modules.Jxblogcomment.Admin'))));
            }
        }
    }
}
