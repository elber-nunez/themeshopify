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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'jxblogcomment/src/JXBlogCommentRepository.php');
include_once(_PS_MODULE_DIR_.'jxblogcomment/classes/JXBlogComments.php');

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class Jxblogcomment extends Module
{
    protected $config_form = false;
    public $repository;
    public $mainTab = array();
    public $tabs = array();
    public $languages;
    public $tabRepository;
    public $authors = array();
    public $attachmentsPath;
    public $attachmentsLink;

    public function __construct()
    {
        $this->name = 'jxblogcomment';
        $this->tab = 'content_management';
        $this->version = '0.0.1';
        $this->author = 'Zemez';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('JX Blog Comment');
        $this->description = $this->l('The module to improve Jx Blog with powerful comments system.');
        $this->confirmUninstall = $this->l(
            'Are you sure that you want to delete the module? All related data will be deleted forever!'
        );
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->languages = Language::getLanguages(true);
        $this->defaultLanguage = new Language(Configuration::get('PS_LANG_DEFAULT'));
        $this->repository = new JXBlogCommentRepository(
            Db::getInstance(),
            $this->context->shop
        );
        $this->attachmentsPath = $this->local_path.'attachments/';
        $this->attachmentsLink = $this->_path.'attachments/';
        $this->mainTab = array(
            'class_name' => 'AdminJXBlog',
            'module'     => $this->name,
            'name'       => 'JX Blog'
        );
        $this->tabs = array(
            array(
                'class_name' => 'AdminJXBlogComments',
                'module'     => $this->name,
                'name'       => 'Comments'
            )
        );
        $this->settingsTab = array(
            'class_name' => 'AdminJXBlogSettings',
            'module'     => $this->name,
            'name'       => 'Settings'
        );
        $this->settingsSubTabs = array(
            array(
                'class_name' => 'AdminJXBlogCommentsSettings',
                'module'     => $this->name,
                'name'       => 'Comments settings'
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
        $this->registerHook('displayJxblogImageManagerExtra') &&
        $this->registerHook('actionJxblogPostAfterDelete') &&
        $this->registerHook('actionUpdateJxblogImages') &&
        $this->registerHook('header') &&
        $this->registerHook('displayJXBlogPostFooter') &&
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
        Configuration::updateValue('JXBLOGCOMMENT_READ_PERMISSION', 1);
        Configuration::updateValue('JXBLOGCOMMENT_WRITE_PERMISSION', 2);
        Configuration::updateValue('JXBLOGCOMMENT_MODERATION', 1);
        Configuration::updateValue('JXBLOGCOMMENT_REPLYING', 2);
        Configuration::updateValue('JXBLOGCOMMENT_UPVOTING', 2);
        Configuration::updateValue('JXBLOGCOMMENT_EDITING', 2);
        Configuration::updateValue('JXBLOGCOMMENT_EDITING_CONFIRMATION', 1);
        Configuration::updateValue('JXBLOGCOMMENT_DELETING', 2);
        Configuration::updateValue('JXBLOGCOMMENT_DELETING_REPLIED', 0);
        Configuration::updateValue('JXBLOGCOMMENT_ATTACHMENTS', 2);
        Configuration::updateValue('JXBLOGCOMMENT_HASHTAGS', 2);
        Configuration::updateValue('JXBLOGCOMMENT_PINGING', 2);
        Configuration::updateValue('JXBLOGCOMMENT_NAVIGATION', 1);
        Configuration::updateValue('JXBLOGCOMMENT_ON_ENTER', 0);

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
            $this->removeAttachments() &&
            parent::uninstall();
    }

    /**
     * Remove all module settings after module deletion
     *
     * @return bool
     */
    public function removeSettings()
    {
        Configuration::deleteByName('JXBLOGCOMMENT_READ_PERMISSION');
        Configuration::deleteByName('JXBLOGCOMMENT_WRITE_PERMISSION');
        Configuration::deleteByName('JXBLOGCOMMENT_MODERATION');
        Configuration::deleteByName('JXBLOGCOMMENT_REPLYING');
        Configuration::deleteByName('JXBLOGCOMMENT_UPVOTING');
        Configuration::deleteByName('JXBLOGCOMMENT_EDITING');
        Configuration::deleteByName('JXBLOGCOMMENT_EDITING_CONFIRMATION');
        Configuration::deleteByName('JXBLOGCOMMENT_DELETING');
        Configuration::deleteByName('JXBLOGCOMMENT_DELETING_REPLIED');
        Configuration::deleteByName('JXBLOGCOMMENT_ATTACHMENTS');
        Configuration::deleteByName('JXBLOGCOMMENT_HASHTAGS');
        Configuration::deleteByName('JXBLOGCOMMENT_PINGING');
        Configuration::deleteByName('JXBLOGCOMMENT_NAVIGATION');
        Configuration::deleteByName('JXBLOGCOMMENT_ON_ENTERs');

        return true;
    }

    /**
     * Clear attachments folder before module uninstalling
     *
     * @return bool
     */
    public function removeAttachments()
    {
        $attachments = array_merge(
            Tools::scandir($this->attachmentsPath, 'jpg'),
            Tools::scandir($this->attachmentsPath, 'jpeg'),
            Tools::scandir($this->attachmentsPath, 'gif'),
            Tools::scandir($this->attachmentsPath, 'png')
        );

        if (count($attachments)) {
            foreach ($attachments as $attachment) {
                unlink($this->attachmentsPath.$attachment);
            }
        }

        return true;
    }

    /**
     * Add related to module tabs to the main navigation menu
     *
     * @return bool
     */
    protected function addTabs()
    {
        $tabId = TabCore::getIdFromClassName($this->mainTab['class_name']);

        foreach ($this->tabs as $item) {
            $this->addTab($item, $tabId);
        }

        $idSettingsTab = TabCore::getIdFromClassName($this->settingsTab['class_name']);
        if (!$idSettingsTab) {
            $idSettingsTab = $this->addTab($this->settingsTab, $tabId);
        }

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

    /**
     * Return message if attempt to menage in multi-store mode
     *
     * @return bool|string
     */
    public function getWarningMultishop()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return $this->displayError(
                $this->l('You cannot manage it from "All Shops" or "Group Shop" context, select the store you want to edit')
            );
        } else {
            return false;
        }
    }

    /**
     * Adapt comments array for comments plugin
     *
     * @param $rawArray
     *
     * @return array
     */
    public function prepareCommentsArray($rawArray)
    {
        $result = array();
        if (!$rawArray) {
            return array();
        }
        foreach ($rawArray as $key => $comment) {
            $result[$key]['id'] = $comment['id_jxblog_comment'];
            $result[$key]['parent'] = isset($comment['id_parent']) && $comment['id_parent'] ? $comment['id_parent'] : null;
            $result[$key]['created'] = $comment['date_add'];
            $result[$key]['upvote_count'] = $comment['upvote_count'];
            $result[$key]['profile_picture_url'] = $this->getUserIcon(false);
            // check if a comment has being updated
            if ($comment['date_update'] && $comment['date_update'] != '0000-00-00 00:00:00') {
                $result[$key]['modified'] = $comment['date_update'];
            }
            $result[$key]['content'] = $comment['content'];
            if ($comment['image_name'] && $comment['image_type']) {
                $result[$key]['file_url'] = $this->attachmentsLink.$comment['image_name'];
                $result[$key]['file_mime_type'] = $comment['image_type'];
            }
            $result[$key]['created_by_current_user'] = false;
            if ($comment['id_customer']) {
                $result[$key]['creator'] = $comment['id_customer'];
                $customer = new Customer($comment['id_customer']);
                if (!Validate::isLoadedObject($customer)) {
                    $result[$key]['fullname'] = $this->trans('Inactive customer', array(), 'Modules.Jxblogcomment.Admin');
                } else {
                    $result[$key]['fullname'] = $customer->firstname.' '.$customer->lastname;
                    // get this author to the authors list
                    $this->addAuthorToList($customer->id, $result[$key]['fullname'], $customer->email, $this->getUserIcon(false, $customer->id));
                    if ($customer->id == $this->context->customer->id) {
                        $result[$key]['created_by_current_user'] = true;
                    }
                    $result[$key]['profile_picture_url'] = $this->getUserIcon(false, $customer->id);
                }
            } else if ($comment['id_guest']) {
                $result[$key]['creator'] = (100*100)+$comment['id_guest'];
                if ($this->context->customer->id_guest == $comment['id_guest']) {
                    $result[$key]['fullname'] = $this->trans('You', array(), 'Modules.Jxblogcomment.Admin');
                    $result[$key]['created_by_current_user'] = true;
                } else {
                    $result[$key]['fullname'] = $this->trans(
                        'Guest ('.$comment['id_guest'].')',
                        array(),
                        'Modules.Jxblogcomment.Admin'
                    );
                    $this->addAuthorToList($result[$key]['creator'], $result[$key]['fullname'], '', $this->getUserIcon(false));
                }
            } else if ($comment['id_admin']) {
                $result[$key]['creator'] = (200*200)+$comment['id_admin'];
                $result[$key]['fullname'] = $this->trans(
                    'Administrator',
                    array(),
                    'Modules.Jxblogcomment.Admin'
                );
                $this->addAuthorToList($result[$key]['creator'], $result[$key]['fullname'], '', $this->getUserIcon(true));
                $result[$key]['profile_picture_url'] = $this->getUserIcon(true);
            }
            $result[$key]['is_new'] = (bool)$comment['is_new'];
            if ($comment['pings']) {
                $result[$key]['pings'] = explode(',', $comment['pings']);
            }
        }

        return $result;
    }

    public function addAuthorToList($id_author, $fullName, $email, $avatar = '')
    {
        if (!$email) {
            $email = '';
        }
        $this->authors[] = array(
            'id' => $id_author,
            'fullname' => $fullName,
            'email' => $email,
            'profile_picture_url' => $avatar
        );
    }

    public function hookHeader()
    {
        if (isset($this->context->controller->pagename) && $this->context->controller->pagename == 'jxblogpost') {
            Media::addJsDef(
                array(
                    'jxCurrentUserIcon' => $this->getUserIcon(false),
                    'jxTextareaPlaceholderText' => $this->trans('Add a comment', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxNewestText' => $this->trans('New', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxOldestText' => $this->trans('Old', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxPopularText' => $this->trans('Popular', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxAttachmentsText' => $this->trans('Attachments', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxSendText' => $this->trans('Send', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxReplyText' => $this->trans('Reply', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxEditText' => $this->trans('Edit', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxEditedText' => $this->trans('edited', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxYouText' => $this->trans('You', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxSaveText' => $this->trans('Save', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxDeleteText' => $this->trans('Delete', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxViewAllRepliesText' => $this->trans('Show all replies (__replyCount__)', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxHideRepliesText' => $this->trans('Hide', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxNoCommentsText' => $this->trans('No comments', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxNoAttachmentsText' => $this->trans('No attachments', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxAttachmentDropText' => $this->trans('Drop here', array(), 'Modules.Jxblogcomment.Admin'),
                    'jxReadOnly' => $this->checkPermission('JXBLOGCOMMENT_WRITE_PERMISSION'),
                    'jxModeration' => $this->checkAccess('JXBLOGCOMMENT_MODERATION'),
                    'jxReplying' => $this->checkAccess('JXBLOGCOMMENT_REPLYING'),
                    'jxVoting' => $this->checkAccess('JXBLOGCOMMENT_UPVOTING'),
                    'jxEditing' => $this->checkAccess('JXBLOGCOMMENT_EDITING'),
                    'jxEditingConfirmation' => $this->checkAccess('JXBLOGCOMMENT_EDITING_CONFIRMATION'),
                    'jxDeleting' => $this->checkAccess('JXBLOGCOMMENT_DELETING'),
                    'jxDeletingReplied' => $this->checkAccess('JXBLOGCOMMENT_DELETING_REPLIED'),
                    'jxAttachments' => $this->checkAccess('JXBLOGCOMMENT_ATTACHMENTS'),
                    'jxHashtags' => $this->checkAccess('JXBLOGCOMMENT_HASHTAGS'),
                    'jxPinging' => $this->checkAccess('JXBLOGCOMMENT_PINGING'),
                    'jxNavigation' => $this->checkAccess('JXBLOGCOMMENT_NAVIGATION'),
                    'jxPostOnEnter' => (bool)Configuration::get('JXBLOGCOMMENT_ON_ENTER'),
                    'ajaxPath' => $this->context->link->getModuleLink('jxblogcomment', 'ajax', array(), true),
                    'commentsArray' => $this->prepareCommentsArray(JXBlogComments::getAllComments(Tools::getValue('id_jxblog_post'))),
                    'usersArray' => $this->authors
                )
            );
            $this->context->controller->addJS($this->_path.'/views/js/jquery.textcomplete.min.js');
            $this->context->controller->addJS($this->_path.'/views/js/jquery-comments-modified.js');
            $this->context->controller->addJS($this->_path.'/views/js/jxblogcomment.js');
            $this->context->controller->addCss($this->_path.'/views/css/jquery-comments.css');
            $this->context->controller->addCSS($this->_path.'/views/css/jxblogcomment.css');
        }
    }

    public function checkPermission($param)
    {
        $user = $this->context->customer->isLogged();
        $readPermission = (int)Configuration::get($param);
        if ($readPermission === 0) {
            return true;
        }
        if ($readPermission === 1) {
            return false;
        }
        if ($user && $readPermission === 2) {
            return false;
        }

        return true;
    }

    public function checkAccess($param)
    {
        $user = $this->context->customer->isLogged();
        $varPermission = (int)Configuration::get($param);

        if ($varPermission == 0) {
            return false;
        }

        if ($varPermission == 1) {
            return true;
        }

        if ($varPermission == 2 && $user) {
            return true;
        }

        return false;
    }

    public function getUserIcon($is_admin, $id_user = false)
    {
        if ($is_admin) {
            return $this->_path.'images/admin-icon.jpg';
        }
        $logged = $this->context->customer->isLogged();
        if (!$id_user && !$logged) {
            return $this->_path.'images/guest-icon.jpg';
        }

        return $this->_path.'images/user-icon.jpg';
    }

    /**
     * Upload user ions(used by the main module)
     * @param $name name of new file
     * @param $file file data
     *
     * @return bool|mixed|string
     */
    public function uploadIcon($name, $file)
    {
        if (isset($file['name']) && isset($file['tmp_name']) && !Tools::isEmpty($file['tmp_name'])) {
            if ($error = ImageManager::validateUpload($file, Tools::getMaxUploadSize())) {
                return $error;
            } else {
                $path = $this->local_path.'images/';
                if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($file['tmp_name'], $tmp_name)) {
                    return Context::getContext()->getTranslator()->trans('Error while uploading image.', array($file['error']), 'Modules.Jxblogcomment.Admin');
                }
                if (!ImageManager::resize($tmp_name, $path.'/'.$name.'.jpg', 100, 100, 'png')) {
                    return Context::getContext()->getTranslator()->trans('Cannot resize an image.', array($file['error']), 'Modules.Jxblogcomment.Admin');
                }
                unlink($tmp_name);
            }
        }

        return false;
    }

    /**
     * Display icon fields in the Image Manager Page
     * The fields will complement the main images' form so they must be in format such as below
     *
     * @return array
     */
    public function hookDisplayJxblogImageManagerExtra()
    {
        $fields = array();
        $imageAdminIcon = '';
        $imageUserIcon = '';
        $imageGuestIcon = '';
        if (file_exists(str_replace('/modules/jxblog/', '/modules/jxblogcomment/', $this->local_path).'images/admin-icon.jpg')) {
            $imageAdminIcon = '<img src="'.str_replace('/modules/jxblog/', '/modules/jxblogcomment/', $this->_path).'images/admin-icon.jpg'.'" alt="" />';
        }
        if (file_exists(str_replace('/modules/jxblog/', '/modules/jxblogcomment/', $this->local_path).'images/user-icon.jpg')) {
            $imageUserIcon = '<img src="'.str_replace('/modules/jxblog/', '/modules/jxblogcomment/', $this->_path).'images/user-icon.jpg'.'" alt="" />';
        }
        if (file_exists(str_replace('/modules/jxblog/', '/modules/jxblogcomment/', $this->local_path).'images/guest-icon.jpg')) {
            $imageGuestIcon = '<img src="'.str_replace('/modules/jxblog/', '/modules/jxblogcomment/', $this->_path).'images/guest-icon.jpg'.'" alt="" />';
        }

        $fields[] = array(
            'label' => $this->trans('Administrator icon', array(), 'Modules.JXBlog.Admin'),
            'desc' => $this->trans('The icon which will be displayed in the comments', array(), 'Modules.JXBlog.Admin'),
            'type' => 'file',
            'name' => 'admin_icon',
            'image' => $imageAdminIcon
        );

        $fields[] = array(
            'label' => $this->trans('Registered users icon', array(), 'Modules.JXBlog.Admin'),
            'desc' => $this->trans('The icon which will be displayed in the comments', array(), 'Modules.JXBlog.Admin'),
            'type' => 'file',
            'name' => 'user_icon',
            'image' => $imageUserIcon
        );

        $fields[] = array(
            'label' => $this->trans('Unregistered users icon', array(), 'Modules.JXBlog.Admin'),
            'desc' => $this->trans('The icon which will be displayed in the comments', array(), 'Modules.JXBlog.Admin'),
            'type' => 'file',
            'name' => 'guest_icon',
            'image' => $imageGuestIcon
        );

        return $fields;
    }

    /**
     * Upload user icons
     * @param $params
     */
    public function hookActionUpdateJxblogImages($params)
    {
        $errors = array();
        if ($userIcon = $params['values']['admin_icon']) {
            if ($error = $this->uploadIcon('admin-icon', $_FILES['admin_icon'])) {
                $errors[] = $error;
            }
        }
        if ($userIcon = $params['values']['user_icon']) {
            if ($error = $this->uploadIcon('user-icon', $_FILES['user_icon'])) {
                $errors[] = $error;
            }
        }
        if ($guestIcon = $params['values']['guest_icon']) {
            if ($error = $this->uploadIcon('guest-icon', $_FILES['guest_icon'])) {
                $errors[] = $error;
            }
        }
    }

    public function hookActionJxblogPostAfterDelete($params)
    {
        $comment = new JXBlogComments();
        $comments = $comment->getAllPostCommentsByID($params['id_jxblog_post']);
        if ($comments) {
            foreach ($comments as $item) {
                $commentToDelete = new JXBlogComments($item['id_jxblog_comment']);
                $commentToDelete->delete();
            }
        }
    }

    public function hookDisplayJXBlogPostFooter()
    {
        $this->context->smarty->assign('isLogged', $this->context->customer->isLogged());
        $this->context->smarty->assign('readingDisabled', $this->checkPermission('JXBLOGCOMMENT_READ_PERMISSION'));
        $this->context->smarty->assign('commentingDisabled', $this->checkPermission('JXBLOGCOMMENT_WRITE_PERMISSION'));
        return $this->display($this->local_path, 'views/templates/hook/comments-loop.tpl');
    }
}
