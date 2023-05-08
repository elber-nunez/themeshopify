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

class AdminJXBlogMainSettingsController extends AdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->fields_options = array(
            'general' => array(
                'title'  => $this->trans('Comments settings ', array(), 'Modules.JXBlog.Admin'),
                'fields' => array(
                    'JXBLOG_IMAGES_AUTO_REGENERATION'             => array(
                        'title'      => $this->trans('Image regeneration', array(), 'Modules.JXBlog.Admin'),
                        'desc'       => $this->trans(
                            'Regenerate images automatically after each changing?',
                            array(),
                            'Modules.JXBlog.Admin'
                        ),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0'
                    ),
                    'JXBLOG_DISPLAY_POST_AUTHOR'             => array(
                        'title'      => $this->trans('Display author', array(), 'Modules.JXBlog.Admin'),
                        'desc'       => $this->trans(
                            'Display an author of the post on the front-end?',
                            array(),
                            'Modules.JXBlog.Admin'
                        ),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0'
                    ),
                    'JXBLOG_DISPLAY_POST_VIEWS'             => array(
                        'title'      => $this->trans('Display views', array(), 'Modules.JXBlog.Admin'),
                        'desc'       => $this->trans(
                            'Display on the front-end how many times the post has been viewed?',
                            array(),
                            'Modules.JXBlog.Admin'
                        ),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0'
                    ),
                    'JXBLOG_POSTS_PER_PAGE'             => array(
                        'title'      => $this->trans('Items per page', array(), 'Modules.JXBlog.Admin'),
                        'desc'       => $this->trans(
                            'How many items will be displayed in listings on the front page?',
                            array(),
                            'Modules.JXBlog.Admin'
                        ),
                        'validation' => 'isInt',
                        'type'       => 'text',
                        'default'    => '6'
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Modules.JXBlog.Admin')
                )
            )
        );
    }
}
