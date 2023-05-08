<?php
/**
 * 2017-2018 Zemez
 *
 * JX Look Book
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
 *  @author    Zemez
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class JXLookBookCollections
 */
class JXLookBookHookEntity extends ObjectModel
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $id_shop;
    /**
     * @var int
     */
    public $sort_order;
    /**
     * @var bool
     */
    public $active;
    /**
     * @var string
     */
    public $hook_name;
    public $type;
    public $id_collection;
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'jxlookbook_hook',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'sort_order' => array(
                'type' => self::TYPE_INT,
                'required' => true,
                'validate' => 'isUnsignedInt'
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'required' => true,
                'validate' => 'isBool'
            ),
            'hook_name' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 200
            ),
            'type' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isCleanHtml',
                'size' => 200
            ),
            'id_collection' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            )
        )
    );
}
