<?php
/**
 * 2017 Zemez
 *
 * JX Blog Comment
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
 *  @copyright 2017 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */


class JXBlogCommentRepository
{
    private $db;
    private $shop;
    private $db_prefix;
  

    public function __construct(Db $db, Shop $shop)
    {
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
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_comment` (
    			`id_jxblog_comment` int(10) NOT NULL auto_increment,
    			`id_post` int(10) NOT NULL,
    			`id_parent` int(10) NOT NULL,
    			`id_customer` int(10) NOT NULL,
    			`id_guest` int(10) NOT NULL,
    			`id_admin` int(10) NOT NULL,
    			`active` int(10) NOT NULL,
    			`date_add` datetime NOT NULL,
    			`date_update` datetime NOT NULL,
    			`upvote_count` int(10) NOT NULL,
    			`is_new` int(10) NOT NULL,
    			`content` text NOT NULL,
    			`pings` text NOT NULL,
    			`image_name` text NOT NULL,
    			`image_type` text NOT NULL,
    			PRIMARY KEY (`id_jxblog_comment`, `id_post`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_comment_vote` (
    			`id_jxblog_comment` int(10) NOT NULL auto_increment,
    			`id_guest` int(10) NOT NULL,
    			`id_customer` int(10) NOT NULL,
    			PRIMARY KEY (`id_jxblog_comment`, `id_guest`, `id_customer`)
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
			`{$this->db_prefix}jxblog_comment`,
			`{$this->db_prefix}jxblog_comment_vote`";

        return $this->db->execute($sql);
    }
}
