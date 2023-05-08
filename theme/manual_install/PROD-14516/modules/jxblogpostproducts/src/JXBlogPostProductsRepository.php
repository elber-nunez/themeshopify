<?php
/**
 * 2017-2018 Zemez
 *
 * JX Blog Post Products
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


class JXBlogPostProductsRepository
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

    public function createTables()
    {
        $engine = _MYSQL_ENGINE_;
        $success = true;
        $this->dropTables();

        $queries = array(
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_product_post` (
    			`id_jxblog_post` int(10) NOT NULL,
    			`id_product` int(10) NOT NULL,
    			PRIMARY KEY (`id_jxblog_post`, `id_product`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8"
        );

        foreach ($queries as $query) {
            $success &= $this->db->execute($query);
        }

        return $success;
    }

    public function dropTables()
    {
        $sql = "DROP TABLE IF EXISTS
			`{$this->db_prefix}jxblog_product_post`";

        return $this->db->execute($sql);
    }

    /**
     * Associate all products to the post. Delete all old association before
     *
     * @param $id_jxblog_post
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function associateProductsToPost($id_jxblog_post)
    {
        if (!$this->disassociateProductsToPost($id_jxblog_post)) {
            return false;
        }
        $relatedProducts = explode('-', Tools::getValue('inputproducts'));
        $relatedProductsIds = array();
        if ($relatedProducts) {
            foreach ($relatedProducts as $item) {
                if ($item) {
                    $relatedProductsIds[] = $item;
                }
            }
        }

        if (!$relatedProductsIds) {
            return true;
        }

        foreach ($relatedProductsIds as $id) {
            if (!Db::getInstance()->insert('jxblog_product_post', array('id_jxblog_post' => (int)$id_jxblog_post, 'id_product' => (int)$id))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete products associations from post
     * @param $id_jxblog_post
     *
     * @return bool
     */
    public function disassociateProductsToPost($id_jxblog_post)
    {
        if (!Db::getInstance()->delete('jxblog_product_post', '`id_jxblog_post` = '.(int)$id_jxblog_post)) {
            return false;
        }

        return true;
    }

    /**
     * Get products associations for the back-office
     *
     * @param $id_jxblog_post
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getAdminPostProducts($id_jxblog_post)
    {
        return Db::getInstance()->executeS("
                  SELECT pl.`name`, pp.`id_product` as `id`
                  FROM `{$this->db_prefix}product_lang` pl
                  LEFT JOIN `{$this->db_prefix}jxblog_product_post` pp
                  ON(pl.`id_product` = pp.`id_product`)
                  WHERE pl.`id_lang` = ".(int)$this->context->language->id."
                  AND pp.`id_jxblog_post` = ".(int)$id_jxblog_post."
                  AND pl.`id_shop` = ".(int)$this->context->shop->id);
    }

    /**
     * Get products associations for the front-office
     *
     * @param $id_jxblog_post
     * @param $limit
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getRelatedProducts($id_jxblog_post, $limit)
    {
        $sql = "SELECT `id_product`
                FROM `{$this->db_prefix}jxblog_product_post`
                WHERE `id_jxblog_post` = ".(int)$id_jxblog_post."
                LIMIT ".(int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get all post that are related to product
     *
     * @param $id_product
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getProductRelatedPosts($id_product, $id_lang)
    {
        return Db::getInstance()->executeS("
            SELECT p.`id_jxblog_post`, pl.`name`, pl.`link_rewrite`
            FROM `{$this->db_prefix}jxblog_product_post` pp
            LEFT JOIN `{$this->db_prefix}jxblog_post` p
            ON(p.`id_jxblog_post` = pp.`id_jxblog_post`)
            LEFT JOIN `{$this->db_prefix}jxblog_post_lang` pl
            ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = ".(int)$id_lang.")
            WHERE pp.`id_product` = ".(int)$id_product."
        ");
    }
}
