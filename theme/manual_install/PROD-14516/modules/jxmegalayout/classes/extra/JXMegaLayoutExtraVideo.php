<?php
/**
 * 2017-2018 Zemez
 *
 * JX Mega Layout
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
 *  @author    Zemez (Alexander Grosul & Alexander Pervakov)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class JXMegaLayoutExtraVideo extends ObjectModel
{
    public $name;
    public $url;
    public $content;
    public $specific_class;
    public static $definition = array(
        'table' => 'jxmegalayout_extra_video',
        'primary' => 'id_extra_video',
        'multilang' => true,
        'fields' => array(
            'name'           => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'url'            => array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'lang' => true),
            'content'        => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
            'specific_class' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 128)
        ),
    );

    /**
     * Get the list of all available video
     *
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getList($id_lang)
    {
        return Db::getInstance()->executeS('
            SELECT *, jev.`id_extra_video` as `id`
            FROM '._DB_PREFIX_.'jxmegalayout_extra_video jev
            LEFT JOIN '._DB_PREFIX_.'jxmegalayout_extra_video_lang jevl
            ON(jev.`id_extra_video` = jevl.`id_extra_video`)
            WHERE jevl.`id_lang` = '.(int)$id_lang);
    }

    public static function getItem($id_item, $id_lang)
    {
        return Db::getInstance()->getRow('
            SELECT jev.*, jevl.*
            FROM '._DB_PREFIX_.'jxmegalayout_extra_video jev
            LEFT JOIN '._DB_PREFIX_.'jxmegalayout_extra_video_lang jevl
            ON(jev.`id_extra_video` = jevl.`id_extra_video`)
            WHERE jev.`id_extra_video` = '.(int)$id_item.'
            AND jevl.`id_lang` = '.(int)$id_lang);
    }
}
