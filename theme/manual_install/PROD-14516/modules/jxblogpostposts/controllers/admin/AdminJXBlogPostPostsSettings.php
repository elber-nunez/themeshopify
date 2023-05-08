<?php
/**
 * 2017-2018 Zemez
 *
 * JX Blog Post Posts
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

class AdminJXBlogPostPostsSettingsController extends AdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->fields_options = array(
            'general' => array(
                'title'  => $this->trans('Post products settings ', array(), 'Modules.Jxblogpostposts.Admin'),
                'fields' => array(
                    'JXBLOGPOSTPOSTS_ITEMS_TO_SHOW' => array(
                        'title'      => $this->trans('Number of visible related posts', array(), 'Modules.Jxblogpostposts.Admin'),
                        'desc'       => $this->trans(
                            'How many related posts display on the post page?',
                            array(),
                            'Modules.Jxblogpostposts.Admin'
                        ),
                        'validation' => 'isInt',
                        'type'       => 'text',
                        'default'    => '4'
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Modules.Jxblogpostposts.Admin')
                )
            )
        );
    }
}
