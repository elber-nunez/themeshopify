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
 *  @author    Zemez (Alexander Grosul)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */


class JXBlogPostPostsRepository
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
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_post_post` (
    			`id_jxblog_post` int(10) NOT NULL,
    			`id_related_post` int(10) NOT NULL,
    			PRIMARY KEY (`id_jxblog_post`, `id_related_post`)
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
			`{$this->db_prefix}jxblog_post_post`";

        return $this->db->execute($sql);
    }

    /**
     * Set new post associations
     *
     * @param $id_jxblog_post
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function associatePostToPost($id_jxblog_post)
    {
        if (!$this->disassociatePostToPost($id_jxblog_post)) {
            return false;
        }
        $relatedPosts = explode('-', Tools::getValue('inputposts'));
        $relatedPostsIds = array();
        if ($relatedPosts) {
            foreach ($relatedPosts as $item) {
                if ($item) {
                    $relatedPostsIds[] = $item;
                }
            }
        }

        if (!$relatedPostsIds) {
            return true;
        }

        foreach ($relatedPostsIds as $id) {
            if (!Db::getInstance()->insert('jxblog_post_post', array('id_jxblog_post' => (int)$id_jxblog_post, 'id_related_post' => (int)$id))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all posts associations
     *
     * @param $id_jxblog_post
     *
     * @return bool
     */
    public function disassociatePostToPost($id_jxblog_post)
    {
        if (!Db::getInstance()->delete('jxblog_post_post', '`id_jxblog_post` = '.$id_jxblog_post)) {
            return false;
        }

        return true;
    }

    /**
     * In case if we delete post we need to remove all relations which it has
     *
     * @param $id_jxblog_post
     *
     * @return bool
     */
    public function disassociatePostFromAllPosts($id_jxblog_post)
    {
        if (!Db::getInstance()->delete('jxblog_post_post', '`id_related_post` = '.$id_jxblog_post)) {
            return false;
        }

        return true;
    }

    /**
     * Get related post for admin-part
     *
     * @param $id_jxblog_post
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getAdminPostPosts($id_jxblog_post)
    {
        return Db::getInstance()->executeS("
                SELECT pl.`name`, pp.`id_related_post` as `id`
                FROM `{$this->db_prefix}jxblog_post_lang` pl
                LEFT JOIN `{$this->db_prefix}jxblog_post_post` pp
                ON(pl.`id_jxblog_post`=pp.`id_jxblog_post`)
                WHERE pl.`id_lang` = ".(int)$this->context->language->id."
                AND pp.`id_jxblog_post` = ".(int)$id_jxblog_post);
    }

    /**
     * Get related post and necessary information about them
     *
     * @param $id_jxblog_post
     * @param $id_lang
     * @param $id_shop
     * @param $id_user_group
     * @param $limit
     *
     * @return array|bool
     * @throws PrestaShopDatabaseException
     */
    public function getRelatedPosts($id_jxblog_post, $id_lang, $id_shop, $id_user_group, $limit)
    {
        $sql = "SELECT pp.`id_related_post` AS `id_jxblog_post`
                FROM `{$this->db_prefix}jxblog_post_post` pp
                WHERE pp.`id_jxblog_post` = ".(int)$id_jxblog_post."
                LIMIT ".(int)$limit;

        if (!$related = Db::getInstance()->executeS($sql)) {
            return false;
        }
        $result = array();
        foreach ($related as $product) {
            if ($relatedInfo = JXBlogPost::getPost($product['id_jxblog_post'], $id_lang, $id_shop, $id_user_group)) {
                array_push($result, $relatedInfo[0]);
            }
        }
        return $result;
    }
}
