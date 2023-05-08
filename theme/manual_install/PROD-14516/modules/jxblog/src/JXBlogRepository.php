<?php
/**
 * 2017 Zemz
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
 *  @author    Zemez (Alexander Grosul)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JXBlogRepository
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
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_category`(
    			`id_jxblog_category` int(10) NOT NULL auto_increment,
    			`active` int(1) NOT NULL,
    			`position` int(10) NOT NULL,
    			`date_add` datetime NOT NULL,
    			`date_upd` datetime NOT NULL,
    			PRIMARY KEY (`id_jxblog_category`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_category_lang` (
    			`id_jxblog_category` int(10) NOT NULL,
    			`id_lang` int(10) NOT NULL,
    			`name` varchar(40) NOT NULL default '',
    			`description` text NOT NULL,
    			`short_description` text NOT NULL,
    			`link_rewrite` varchar(40) NOT NULL default '',
    			`meta_keyword` text NOT NULL,
    			`meta_description` text NOT NULL,
    			`badge` varchar(100) NOT NULL default '',
    			PRIMARY KEY (`id_jxblog_category`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_category_group` (
    			`id_jxblog_category` int(10) NOT NULL,
    			`id_group` int(10) NOT NULL,
    			PRIMARY KEY (`id_jxblog_category`, `id_group`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_category_shop` (
    			`id_jxblog_category` int(11) NOT NULL,
    			`id_shop` int(11) NOT NULL,
    			PRIMARY KEY (`id_jxblog_category`, `id_shop`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_post`(
    			`id_jxblog_post` int(10) NOT NULL auto_increment,
    			`id_jxblog_category_default` int(1) NOT NULL,
    			`author` int(1) NOT NULL,
    			`views` int(1) NOT NULL DEFAULT '0',
    			`active` int(1) NOT NULL,
    			`date_add` datetime NOT NULL,
    			`date_upd` datetime NOT NULL,
    			`date_start` datetime NOT NULL,
    			PRIMARY KEY (`id_jxblog_post`, `id_jxblog_category_default`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_post_lang`(
    			`id_jxblog_post` int(10) NOT NULL,
    			`id_lang` int(1) NOT NULL,
    			`name` varchar(100) NOT NULL default '',
    			`description` text NOT NULL,
    			`short_description` text NOT NULL,
    			`meta_keyword` text NOT NULL,
    			`meta_description` text NOT NULL,
    			`link_rewrite` varchar(100) NOT NULL default '',
    			PRIMARY KEY (`id_jxblog_post`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_post_category`(
    			`id_jxblog_post` int(10) NOT NULL,
    			`id_jxblog_category` int(1) NOT NULL,
    			PRIMARY KEY (`id_jxblog_post`, `id_jxblog_category`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_tag` (
    			`id_jxblog_tag` int(10) NOT NULL auto_increment,
    			`id_lang` int(10) NOT NULL,
    			`tag` varchar(100) NOT NULL,
    			PRIMARY KEY (`id_jxblog_tag`, `id_lang`, `tag`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_tag_post` (
    			`id_jxblog_tag` int(10) NOT NULL,
    			`id_jxblog_post` int(10) NOT NULL,
    			PRIMARY KEY (`id_jxblog_tag`, `id_jxblog_post`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxblog_image` (
    			`id_jxblog_image` int(10) NOT NULL auto_increment,
    			`name` varchar(40) NOT NULL default '',
    			`width` int(10) NOT NULL,
    			`height` int(10) NOT NULL,
    			`category` int(10) NOT NULL,
    			`category_thumb` int(10) NOT NULL,
    			`post` int(10) NOT NULL,
    			`post_thumb` int(10) NOT NULL,
    			`user` int(10) NOT NULL,
    			PRIMARY KEY (`id_jxblog_image`, `name`)
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
			`{$this->db_prefix}jxblog_category`,
			`{$this->db_prefix}jxblog_category_lang`,
			`{$this->db_prefix}jxblog_category_group`,
			`{$this->db_prefix}jxblog_category_shop`,
			`{$this->db_prefix}jxblog_post`,
			`{$this->db_prefix}jxblog_post_lang`,
			`{$this->db_prefix}jxblog_post_category`,
			`{$this->db_prefix}jxblog_tag`,
			`{$this->db_prefix}jxblog_tag_post`,
			`{$this->db_prefix}jxblog_image`";

        return $this->db->execute($sql);
    }
}
