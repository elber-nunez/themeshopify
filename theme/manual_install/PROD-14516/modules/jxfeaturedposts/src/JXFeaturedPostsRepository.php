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


class JXFeaturedPostsRepository
{
    private $db;
    private $shop;
    private $db_prefix;

    public function __construct(Db $db, Shop $shop)
    {
        $this->context = Context::getContext();
        $this->db = $db;
        $this->shop = $shop;
        $this->db_prefix = $db->getPrefix();
        
    }

    /**
     * Create necessary tables
     *
     * @return bool
     */
    public function createTables()
    {
        $engine = _MYSQL_ENGINE_;
        $success = true;
        $this->dropTables();

        $queries = array(
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxfeaturedposts` (
    			`id_featured_post` int(10) NOT NULL AUTO_INCREMENT,
    			`id_post` int(10) NOT NULL,
    			`id_shop` int(10) NOT NULL,
    			`position` int(10) NOT NULL DEFAULT '1',
    			`active` int(10) NOT NULL,
    			PRIMARY KEY (`id_featured_post`, `id_post`, `id_shop`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8"
        );

        foreach ($queries as $query) {
            $success &= $this->db->execute($query);
        }

        return $success;
    }

    /**
     * Drop all related tables
     *
     * @return bool
     */
    public function dropTables()
    {
        $sql = "DROP TABLE IF EXISTS
			`{$this->db_prefix}jxfeaturedposts`";

        return $this->db->execute($sql);
    }

    /**
     * Get posts that have this category as a default
     *
     * @param $id_jxblog_category_default
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPostsByDefaultCategory($id_jxblog_category_default, $id_lang)
    {
        $sql = 'SELECT p.*, pl.*
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                WHERE p.`id_jxblog_category_default` = '.(int)$id_jxblog_category_default;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get shop featured posts with posts information and correct sorting and numbers to show
     *
     * @param $id_shop
     * @param $id_lang
     * @param $id_group
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getShopFeaturedPosts($id_shop, $id_lang, $id_group)
    {
        $sql = 'SELECT p.*, pl.*
                FROM '._DB_PREFIX_.'jxfeaturedposts pf
                LEFT JOIN '._DB_PREFIX_.'jxblog_post p
                ON(pf.`id_post` = p.`id_jxblog_post`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop pcs
                ON(p.`id_jxblog_category_default` = pcs.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group pcg
                ON(p.`id_jxblog_category_default` = pcg.`id_jxblog_category`)
                WHERE pf.`id_shop` = '.(int)$id_shop.'
                AND p.`active` = 1
                AND pf.`active` = 1
                AND pcs.`id_shop` = '.(int)$id_shop.'
                AND pcg.`id_group` = '.(int)$id_group.'
                ORDER BY '.self::getFeaturedPostsOrdering().'
                LIMIT '.(int)self::getFeaturedPostsLimit();

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get current sorting approach
     *
     * @return string
     */
    protected static function getFeaturedPostsOrdering()
    {
        $orderType = Configuration::get('JXFEATUREDPOSTS_ORDER');
        switch ($orderType) {
            case '0':
                $ordering = 'pl.`name` ASC';
                break;
            case '1':
                $ordering = 'pl.`name` DESC';
                break;
            case '2':
                $ordering = 'p.`date_add` ASC';
                break;
            case '3':
                $ordering = 'p.`date_add` DESC';
                break;
            case '4':
                $ordering = 'p.`views` DESC';
                break;
            case '5':
                $ordering = 'p.`views` DESC';
                break;
            case '6':
                $ordering = 'pf.`position` ASC';
                break;
        }

        return $ordering;
    }

    /**
     * Get limit of featured products
     * @return int|string
     */
    protected static function getFeaturedPostsLimit()
    {
        if (!$limit = Configuration::get('JXFEATUREDPOSTS_ITEMS_TO_SHOW')) {
            return 1;
        } else {
            return $limit;
        }
    }
}
