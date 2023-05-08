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
 * @author    Zemez (Alexander Grosul)
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JXBlogTag extends ObjectModel
{
    public $id_lang;
    public $tag;
    public static $definition = array(
        'table'     => 'jxblog_tag',
        'primary'   => 'id_jxblog_tag',
        'multilang' => false,
        'fields'    => array(
            'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'tag'     => array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'required' => true, 'size' => 64)
        )
    );

    /**
     * Check if tag is already exists to prevent duplicating
     *
     * @param $id_lang
     * @param $tag
     *
     * @return false|null|string
     */
    public static function checkTagExistence($id_lang, $tag)
    {
        return Db::getInstance()->getValue('
          SELECT `id_jxblog_tag`
          FROM '._DB_PREFIX_.'jxblog_tag
          WHERE `tag` = "'.pSql($tag).'"
          AND `id_lang` = '.(int)$id_lang);
    }

    /**
     * Get all post tags
     *
     * @param $id_jxblog_post
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPostTags($id_jxblog_post)
    {
        return Db::getInstance()->executeS('
          SELECT t.*
          FROM '._DB_PREFIX_.'jxblog_tag t
          LEFT JOIN '._DB_PREFIX_.'jxblog_tag_post tp
          ON(tp.`id_jxblog_tag`=t.`id_jxblog_tag`)
          WHERE tp.`id_jxblog_post` = '.(int)$id_jxblog_post);
    }
}
