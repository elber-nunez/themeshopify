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
 * @author    Zemez
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class JXOneClickOrderCustomers
 */
class JXOneClickOrderCustomers extends ObjectModel
{
    /**
     * @var int
     */
    public $id_order;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $datetime;
    /**
     * @var string
     */
    public $number;
    /**
     * @var string
     */
    public $address;
    /**
     * @var string
     */
    public $email;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'jxoneclickorder_customers',
        'primary' => 'id_customer',
        'multilang' => false,
        'fields' => [
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isunsignedInt'
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isName'
            ],
            'number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isPhoneNumber'
            ],
            'address' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAddress'
            ],
            'message' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isMessage'
            ],
            'datetime' => [
                'type' => self::TYPE_STRING
            ],
            'email' => [
                'type' => self::TYPE_STRING
            ]
        ]
    ];
}
