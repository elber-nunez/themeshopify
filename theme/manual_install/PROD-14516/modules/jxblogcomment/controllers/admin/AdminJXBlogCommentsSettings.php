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

class AdminJXBlogCommentsSettingsController extends AdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $options = array(
            array(
                'value' => 0,
                'name'  => $this->trans('Nobody', array(), 'Modules.Jxblogcomment.Admin')
            ),
            array(
                'value' => 1,
                'name'  => $this->trans('Everybody', array(), 'Modules.Jxblogcomment.Admin')
            ),
            array(
                'value' => 2,
                'name'  => $this->trans('Authorized users', array(), 'Modules.Jxblogcomment.Admin')
            )
        );
        $this->fields_options = array(
            'general' => array(
                'title'  => $this->trans('Comments settings ', array(), 'Modules.Jxblogcomment.Admin'),
                'fields' => array(
                    'JXBLOGCOMMENT_READ_PERMISSION'      => array(
                        'title'      => $this->trans('Who can read comments', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select which categories of the users will be able to read comments from your blog',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_WRITE_PERMISSION'     => array(
                        'title'      => $this->trans(
                            'Who can leave comments?',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'desc'       => $this->trans(
                            'Select which categories of the users will be able to leave comments in your blog',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_MODERATION'           => array(
                        'title'      => $this->trans('Moderation', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can leave comments without moderation',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_REPLYING'             => array(
                        'title'      => $this->trans('Replying', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can reply to other comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_UPVOTING'             => array(
                        'title'      => $this->trans('Voting', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can vote to other comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_EDITING'              => array(
                        'title'      => $this->trans('Editing', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can edit its own comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_EDITING_CONFIRMATION' => array(
                        'title'      => $this->trans('Editing confirmation', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can edit own comments without moderation',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_DELETING'             => array(
                        'title'      => $this->trans('Deleting', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can delete its own comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_DELETING_REPLIED'     => array(
                        'title'      => $this->trans(
                            'Deleting comments with replies',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'desc'       => $this->trans(
                            'Select who can delete its own comments which have replies',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_ATTACHMENTS'          => array(
                        'title'      => $this->trans('Attachments', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can add attachments to comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_HASHTAGS'             => array(
                        'title'      => $this->trans('Hashtags', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can use hashtags in comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_PINGING'              => array(
                        'title'      => $this->trans('Pinging', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can mention users in comments',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_NAVIGATION'           => array(
                        'title'      => $this->trans('Navigation', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Select who can see navigation above the comments block',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $options,
                        'identifier' => 'value'
                    ),
                    'JXBLOGCOMMENT_ON_ENTER'             => array(
                        'title'      => $this->trans('Post on enter', array(), 'Modules.Jxblogcomment.Admin'),
                        'desc'       => $this->trans(
                            'Allow to post a comment by pressing "Enter" button',
                            array(),
                            'Modules.Jxblogcomment.Admin'
                        ),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0'
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Modules.Jxblogcomment.Admin')
                )
            )
        );
    }
}
