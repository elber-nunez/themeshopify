<?php
/**
 * 2017-2018 Zemez
 *
 * JX Header Account
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class JXHeaderAccountRepository
{
    private $db;
    private $shop;
    private $lang;
    private $db_name = 'jxheaderaccount';
    private $db_prefix;
    private $engine;

    public function __construct(Db $db, Shop $shop, Language $lang)
    {
        $this->db = $db;
        $this->shop = $shop;
        $this->lang = $lang;
        $this->db_prefix = $this->db->getPrefix();
        $this->engine = _MYSQL_ENGINE_;
    }

    public function createTables()
    {
        $success = true;

        $this->dropTables();

        $queries = array(
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}customer_jxheaderaccount` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_customer` int(10) unsigned NOT NULL,
                `id_shop` int(11) NOT NULL DEFAULT '1',
                `social_id` varchar(100) NOT NULL,
                `social_type` varchar(50) NOT NULL,
                `avatar_url` varchar(128) NOT NULL,
                PRIMARY KEY  (`id`,`id_shop`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;"
        );

        foreach ($queries as $query) {
            $success &= $this->db->execute($query);
        }

        return $success;
    }

    public function dropTables()
    {
        $query = "DROP TABLE IF EXISTS `{$this->db_prefix}{$this->db_name}`";

        return $this->db->execute($query);
    }
}
