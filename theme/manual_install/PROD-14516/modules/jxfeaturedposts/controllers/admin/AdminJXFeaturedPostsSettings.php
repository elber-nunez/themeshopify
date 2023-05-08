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

class AdminJXFeaturedPostsSettingsController extends AdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $orderBy = array(
            array(
                'value' => 0,
                'name'  => $this->trans('Name ascending', array(), 'Modules.Jxfeaturedposts.Admin')
            ),
            array(
                'value' => 1,
                'name'  => $this->trans('Name descending', array(), 'Modules.Jxfeaturedposts.Admin')
            ),
            array(
                'value' => 2,
                'name'  => $this->trans('Date ascending', array(), 'Modules.Jxfeaturedposts.Admin')
            ),
            array(
                'value' => 3,
                'name'  => $this->trans('Date descending', array(), 'Modules.Jxfeaturedposts.Admin')
            ),
            array(
                'value' => 4,
                'name'  => $this->trans('Views ascending', array(), 'Modules.Jxfeaturedposts.Admin')
            ),
            array(
                'value' => 5,
                'name'  => $this->trans('Views descending', array(), 'Modules.Jxfeaturedposts.Admin')
            ),
            array(
                'value' => 6,
                'name'  => $this->trans('Custom order', array(), 'Modules.Jxfeaturedposts.Admin')
            )
        );
        $this->fields_options = array(
            'general' => array(
                'title' => $this->trans('Featured posts settings ', array(), 'Modules.Jxfeaturedposts.Admin'),
                'fields' => array(
                    'JXFEATUREDPOSTS_ITEMS_TO_SHOW' => array(
                        'title'      => $this->trans('Number of visible featured posts', array(), 'Modules.Jxfeaturedposts.Admin'),
                        'suffix' => $this->trans('integer', array(), 'Modules.Jxfeaturedposts.Admin'),
                        'desc'       => $this->trans(
                            'How many featured posts display on the homepage?',
                            array(),
                            'Modules.Jxfeaturedposts.Admin'
                        ),
                        'validation' => 'isInt',
                        'type'       => 'text',
                        'default'    => '4'
                    ),
                    'JXFEATUREDPOSTS_ORDER' => array(
                        'title'      => $this->trans('Posts ordering type', array(), 'Modules.Jxfeaturedposts.Admin'),
                        'desc'       => $this->trans(
                            'Select which approach to use for posts ordering in the module',
                            array(),
                            'Modules.Jxfeaturedposts.Admin'
                        ),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $orderBy,
                        'identifier' => 'value'
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Modules.Jxfeaturedposts.Admin')
                )
            )
        );
    }
}
