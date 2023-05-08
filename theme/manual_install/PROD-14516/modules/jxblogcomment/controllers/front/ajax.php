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
 * @author    Zemez (Alexander Grosul)
 * @copyright 2017 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JXBlogCommentAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $action = Tools::getValue('action');
        $action = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
        if (!empty($action) && method_exists($this, 'ajax'.$action)) {
            $this->{'ajax'.$action}();
        } else {
            if (isset($_FILES) && $_FILES) {
                $this->uploadFile($_FILES['file']);
            }
            die(json_encode(array('error' => 'method doesn\'t exist')));
        }
    }

    /**
     * Upload attachment file
     *
     * @param $file
     */
    private function uploadFile($file)
    {
        $jxblogcomment = new Jxblogcomment();
        if (isset($file['name']) && isset($file['tmp_name']) && !Tools::isEmpty($file['tmp_name'])) {
            if ($errors = ImageManager::validateUpload($file, Tools::getMaxUploadSize())) {
                die(json_encode(array('status' => false, 'response' => $errors)));
            } else {
                $fileFormat = explode('.', $file['name']);
                $newName = Tools::passwdGen(15).'.'.$fileFormat[1];
                if (!move_uploaded_file($file['tmp_name'], $jxblogcomment->attachmentsPath.$newName)) {
                    die(json_encode(
                        array(
                            'status'   => false,
                            'response' => $this->trans(
                                'Error while uploading image.',
                                array($file['error']),
                                'Modules.Jxblogcomment.Admin'
                            )
                        )
                    ));
                }
                die(json_encode(
                    array(
                        'status'   => true,
                        'response' => $this->trans(
                            'The image is uploaded.',
                            array($file['error']),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'path'     => $jxblogcomment->attachmentsLink,
                        'name'     => $newName,
                        'type'     => $file['type']
                    )
                ));
            }
        }
    }

    /**
     * Add comment
     *
     * @throws PrestaShopException
     */
    public function ajaxAddComment()
    {
        $content = Tools::getValue('content');
        $parent = Tools::getValue('parent');
        if (Tools::isEmpty($content) || !Validate::isString($content)) {
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans(
                            'The content of the comment is invalid',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        )
                    )
                )
            );
        }
        $comment = new JXBlogComments();
        $comment->id_post = Tools::getValue('id_jxblog_post');
        $comment->id_parent = isset($parent) ? (int)$parent : 0;
        $comment->id_customer = $this->context->customer->id ? $this->context->customer->id : 0;
        $comment->id_guest = !$this->context->customer->id ? $this->context->customer->id_guest : 0;
        // check if pings are allowed and decode them if yes
        if ($this->module->checkAccess('JXBLOGCOMMENT_PINGING') && $pings = $this->getPings($content)) {
            if ($pings['ids']) {
                $content = $pings['content'];
                $comment->pings = implode(',', $pings['ids']);
            }
        }
        $comment->content = $content;
        $comment->upvote_count = 0;
        $comment->date_update = date('Y-m-d H:i:s');
        $comment->is_new = true;
        $comment->active = true;
        // if comment require a moderation set a status false
        if ($moderation = $this->module->checkPermission('JXBLOGCOMMENT_MODERATION')) {
            $comment->active = false;
        }
        if (!$comment->add()) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans(
                            'An error occurred during the comment saving',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        )
                    )
                )
            );
        }
        ob_end_clean();
        header('Content-Type: application/json');
        if ($moderation) {
            die(
                json_encode(
                    array(
                        'status' => true,
                        'response' => $this->trans('The comment successfully added and will display after moderation', array(), 'Modules.Jxblogcomment.Admin'),
                        'moderation' => $moderation,
                        'id_comment' => $comment->id
                    )
                )
            );
        }
        die(
            json_encode(
                array(
                    'status' => true,
                    'response' => $this->trans('The comment successfully added', array(), 'Modules.Jxblogcomment.Admin'),
                    'moderation' => $moderation,
                    'id_comment' => $comment->id
                )
            )
        );
    }

    /**
     * Update a comment
     *
     * @throws PrestaShopException
     */
    public function ajaxUpdateComment()
    {
        $content = Tools::getValue('content');
        if (!$id_comment = Tools::getValue('id_comment')) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('Cannot find the comment', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        if (Tools::isEmpty($content) || !Validate::isString($content)) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('The content of the comment is invalid', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        $comment = new JXBlogComments((int)$id_comment);
        $comment->content = $content;
        $comment->date_update = date('y-m-d H:i:s');
        // check if an edited comment require a moderation if yes set if disabled
        if ($moderation = $this->module->checkPermission('JXBLOGCOMMENT_EDITING_CONFIRMATION')) {
            $comment->active = false;
        }
        if (!$comment->update()) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('An error occurred during the comment updating', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        ob_end_clean();
        header('Content-Type: application/json');
        if ($moderation) {
            die(
                json_encode(
                    array(
                        'status' => true,
                        'response' => $this->trans('The comment successfully updated and will display after moderation', array(), 'Modules.Jxblogcomment.Admin'),
                        'moderation' => $moderation
                    )
                )
            );
        }
        die(
            json_encode(
                array(
                    'status' => true,
                    'response' => $this->trans('The comment data successfully updated', array(), 'Modules.Jxblogcomment.Admin'),
                    'moderation' => $moderation
                )
            )
        );
    }

    /**
     * Vote a comment
     */
    public function ajaxVoteComment()
    {
        if (!$id_comment = Tools::getValue('id_comment')) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('Cannot find the comment', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        $comment = new JXBlogComments((int)$id_comment);
        $guest = $this->context->customer->isLogged();
        $id_user = !$guest ? $this->context->customer->id_guest : $this->context->customer->id;
        $result = (int)$comment->updateCommentVotes($id_user, $guest);
        if ($result === 0 || $result) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(json_encode(array('status' => true, 'result' => (int)$result)));
        }
    }

    /**
     * Delete a comment
     */
    public function ajaxDeleteComment()
    {
        $id_comment = Tools::getValue('id_comment');
        if (!$id_comment) {
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('Cannot find the comment', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        $jxblogcomment = new Jxblogcomment();
        $comment = new JXBlogComments((int)$id_comment);
        if ($comment->image_name && file_exists($jxblogcomment->attachmentsPath.$comment->image_name)) {
            unlink($jxblogcomment->attachmentsPath.$comment->image_name);
        }
        if (!$comment->delete()) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('An error occurred during the comment deleting', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        ob_end_clean();
        header('Content-Type: application/json');
        die(
            json_encode(
                array(
                    'status' => true,
                    'response' => $this->trans('The comment data successfully updated', array(), 'Modules.Jxblogcomment.Admin')
                )
            )
        );
    }

    /**
     * Add an information about attached file and form a comment
     *
     * @throws PrestaShopException
     */
    public function ajaxUploadAttachment()
    {
        $parent = Tools::getValue('parent');
        $attachment = new JXBlogComments();
        $attachment->id_post = Tools::getValue('id_jxblog_post');
        $attachment->id_parent = isset($parent) ? (int)$parent : 0;
        $attachment->id_customer = $this->context->customer->id ? $this->context->customer->id : 0;
        $attachment->id_guest = !$this->context->customer->id ? $this->context->customer->id_guest : 0;
        $attachment->content = '';
        $attachment->upvote_count = 0;
        $attachment->date_update = date('Y-m-d H:i:s');
        $attachment->is_new = true;
        $attachment->active = true;
        $attachment->image_name = Tools::getValue('name');
        $attachment->image_type = Tools::getValue('type');
        // check if comment require a moderation
        if ($moderation = $this->module->checkPermission('JXBLOGCOMMENT_MODERATION')) {
            $attachment->active = false;
        }
        if (!$attachment->add()) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
                json_encode(
                    array(
                        'status' => false,
                        'response' => $this->trans('An error occurred during the attachment saving', array(), 'Modules.Jxblogcomment.Admin')
                    )
                )
            );
        }
        ob_end_clean();
        header('Content-Type: application/json');
        if ($moderation) {
            die(
                json_encode(
                    array(
                        'status' => true,
                        'response' => $this->trans('The attachment successfully added and will display after moderation', array(), 'Modules.Jxblogcomment.Admin'),
                        'moderation' => $moderation
                    )
                )
            );
        }
        die(
            json_encode(
                array(
                    'status' => true,
                    'response' => $this->trans('The attachment successfully added', array(), 'Modules.Jxblogcomment.Admin'),
                    'moderation' => $moderation
                )
            )
        );
    }

    /**
     * Parse a comment code to find pings
     * @param $content
     *
     * @return array
     */
    private function getPings($content)
    {
        $result = array();
        $ids = array();
        $content = explode(' ', $content);
        foreach ($content as $entry) {
            if (strpos($entry, '@') == 0 && Validate::isInt($id = str_replace('@', '', $entry))) {
                if (Validate::isLoadedObject($customer = new Customer($id))) {
                    array_push($ids, $id);
                    $result[] = '@'.$customer->firstname.' '.$customer->lastname;
                } else if (Validate::isLoadedObject($employee = new Employee(($id - (200*200))))) {
                    array_push($ids, $id);
                    $result[] = '@'.$this->trans('Administrator', array(), 'Modules.Jxblogcomment.Admin');
                } else {
                    array_push($ids, $id);
                    $result[] = '@'.$this->trans('Guest ('.($id - (100*100)).')', array(), 'Modules.Jxblogcomment.Admin');
                }
            } else {
                $result[] = $entry;
            }
        }

        return array('ids' => $ids, 'content' => implode(' ', $result));
    }
}
