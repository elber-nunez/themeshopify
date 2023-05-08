<?php
/**
 * 2017-2018 Zemez
 *
 * JX One Click Order
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
 * Class JXOneClickOrderOrders
 */
class JXOneClickOrderOrders extends ObjectModel
{
    /**
     * @var int
     */
    public $id_order;
    /**
     * @var int
     */
    public $id_shop;
    /**
     * @var string
     */
    public $status;
    /**
     * @var string
     */
    public $date_add;
    /**
     * @var string
     */
    public $date_upd;
    /**
     * @var bool
     */
    public $shown;
    /**
     * @var int
     */
    public $id_cart;
    /**
     * @var int
     */
    public $id_original_order;
    /**
     * @var int
     */
    public $id_employee;
    /**
     * @var string
     */
    public $description;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'jxoneclickorder',
        'primary' => 'id_order',
        'multilang' => false,
        'fields' => [
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt'
            ],
            'shown' => [
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt'
            ],
            'status' => [
                'type' => self::TYPE_STRING,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ],
            'id_cart' => [
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt'
            ],
            'id_original_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt'
            ],
            'id_employee' => [
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt'
            ],
            'description' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isCleanHtml'
            ]
        ]
    ];
}
