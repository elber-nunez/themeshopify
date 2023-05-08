<?php
/**
 * 2017-2018 Zemez
 *
 * JX Look Book
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

class JXLookBookRepository
{
    private $db;
    private $shop;
    private $language;
    private $db_name = 'jxlookbook';
    private $db_prefix;
    private $engine;

    public function __construct(Db $db, Shop $shop, Language $language)
    {
        $this->db = $db;
        $this->shop = $shop;
        $this->language = $language;
        $this->db_prefix = $db->getPrefix();
        $this->engine = _MYSQL_ENGINE_;
    }

    public function createTables()
    {
        $success = true;

        $this->dropTables();

        $queries = array(
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}` (
                `id_collection` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL,
                `image` text NOT NULL,
                `sort_order` int(11) NOT NULL,
                `template` text NOT NULL,
                `active` int(11) NOT NULL,
                PRIMARY KEY  (`id_collection`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}_lang` (
                `id_entity` int(11) NOT NULL AUTO_INCREMENT,
                `id_collection` int(11) NOT NULL,
                `id_lang` int(11) NOT NULL,
                `name` text NOT NULL,
                `description` text NOT NULL,
                PRIMARY KEY  (`id_entity`,`id_collection`, `id_lang`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}_tab` (
                `id_tab` int(11) NOT NULL AUTO_INCREMENT,
                `id_collection` int(11) NOT NULL,
                `sort_order` int(11) NOT NULL,
                `active` int(11) NOT NULL,
                `image` varchar(450) NOT NULL,
                PRIMARY KEY  (`id_tab`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}_tab_lang` (
                `id_entity` int(11) NOT NULL AUTO_INCREMENT,
                `id_tab` int(11) NOT NULL,
                `id_lang` int(11) NOT NULL,
                `name` text NOT NULL,
                `description` text NOT NULL,
                PRIMARY KEY  (`id_entity`, `id_tab`, `id_lang`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}_hotspot` (
                `id_spot` int(11) NOT NULL AUTO_INCREMENT,
                `id_tab` int(11) NOT NULL,
                `type` int(11) NOT NULL,
                `coordinates` varchar(450) NOT NULL,
                `id_product` int(11) NOT NULL,
                PRIMARY KEY  (`id_spot`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}_hotspot_lang` (
                `id_entity` int(11) NOT NULL AUTO_INCREMENT,
                `id_spot` int(11) NOT NULL,
                `id_lang` int(11) NOT NULL,
                `name` text NOT NULL,
                `description` text NOT NULL,
                PRIMARY KEY  (`id_entity`, `id_spot`, `id_lang`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->db_name}_hook` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL,
                `sort_order` int(11) NOT NULL,
                `active` int(11) NOT NULL,
                `hook_name` text NOT NULL,
                `type` text NOT NULL, 
                `id_collection` int(11) NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE={$this->engine} DEFAULT CHARSET=utf8;"
        );

        foreach ($queries as $query) {
            $success &= $this->db->execute($query);
        }

        return $success;
    }

    public function dropTables()
    {
        $query = "DROP TABLE IF EXISTS 
                `{$this->db_prefix}{$this->db_name}`,
                `{$this->db_prefix}{$this->db_name}_lang`,
                `{$this->db_prefix}{$this->db_name}_tab`,
                `{$this->db_prefix}{$this->db_name}_tab_lang`,
                `{$this->db_prefix}{$this->db_name}_hotspot`,
                `{$this->db_prefix}{$this->db_name}_hotspot_lang`,
                `{$this->db_prefix}{$this->db_name}_hook`;";

        return $this->db->execute($query);
    }

    public function getFrontHooksList($moduleName)
    {
        $query = "SELECT DISTINCT h.id_hook as id, h.name as name
                FROM {$this->db_prefix}hook h
                LEFT JOIN {$this->db_prefix}hook_module hm
                ON(h.id_hook = hm.`id_hook`)
                INNER JOIN {$this->db_prefix}module m
                ON(hm.`id_module` = m.`id_module`)
                WHERE (lower(h.`name`) LIKE 'display%')
                AND m.`name` = '{$moduleName}'
                ORDER BY h.name ASC
            ";

        $hooks = $this->db->executeS($query);
        foreach ($hooks as $key => $hook) {
            if (preg_match('/admin/i', $hook['name']) || preg_match('/backoffice/i', $hook['name']) || $hook['name'] == 'displayProductButtons' || $hook['name'] == 'displayRightColumnProduct' || $hook['name'] == 'displayBeforeBodyClosingTag'
            ) {
                unset($hooks[$key]);
            }
        }
        if (count($hooks) > 1) {
            return $hooks;
        } else {
            return false;
        }
    }

    public function getCollections($active = false, $field = '*')
    {
        $query = "SELECT {$field}
                  FROM {$this->db_prefix}{$this->db_name} jxlb
                  JOIN {$this->db_prefix}{$this->db_name}_lang jxlbl
                  ON jxlb.id_collection = jxlbl.id_collection
                  AND jxlbl.id_lang = {$this->language->id}
                  AND jxlb.id_shop = {$this->shop->id}";

        if ($active) {
            $query .= " AND jxlb.active = 1";
        }

        $query .= " ORDER BY `sort_order`";

        return $this->db->executeS($query);
    }

    public function getTabs($id_collection, $active = false, $filed = '*')
    {
        $query = "SELECT {$filed}
                FROM {$this->db_prefix}{$this->db_name}_tab jxlt
                JOIN {$this->db_prefix}{$this->db_name}_tab_lang jxltl
                ON jxlt.id_tab = jxltl.id_tab
                AND jxltl.id_lang = {$this->language->id}
                AND jxlt.id_collection = {$id_collection}";

        if ($active) {
            $query .= ' AND `active` = 1';
        }
        $query .= ' ORDER BY `sort_order`';

        return $this->db->executeS($query);
    }

    public function getHotSpots($id_tab)
    {
        $query = "SELECT *
                FROM {$this->db_prefix}{$this->db_name}_hotspot tlhs
                JOIN {$this->db_prefix}{$this->db_name}_hotspot_lang tlhsl
                ON tlhs.id_spot = tlhsl.id_spot
                AND tlhsl.id_lang = {$this->language->id}
                AND tlhs.id_tab = {$id_tab}";

        return $this->db->executeS($query);
    }

    public function getHooks($hookName, $active = false)
    {
        $query = "SELECT tlhp.id, tlhp.active, tlhp.sort_order, tlhp.type, tlhp.id_collection, tlhp.hook_name, tlkl.name, tlkl.description, tlk.image, tlk.template
                  FROM {$this->db_prefix}{$this->db_name}_hook tlhp                 
                  INNER JOIN {$this->db_prefix}{$this->db_name} tlk   
                  ON tlhp.id_collection = tlk.id_collection
                  AND tlhp.hook_name = '{$hookName}'
                  JOIN {$this->db_prefix}{$this->db_name}_lang tlkl
                  ON tlk.id_collection = tlkl.id_collection                 
                  AND tlkl.id_lang = {$this->language->id}
                  AND tlk.id_shop = {$this->shop->id}";
        
        if ($active) {
            $query .= ' AND tlhp.`active` = 1';
        }
        
        $query .= ' ORDER BY tlhp.`sort_order`';

        return $this->db->executeS($query);
    }

    public function getMaxSortOrder($suffix = '', $where = false)
    {
        $query = "SELECT MAX(sort_order)
                AS sort_order
                FROM `{$this->db_prefix}{$this->db_name}{$suffix}`";

        if ($where) {
            $query .= " WHERE `{$where['key']}` = {$where['value']}";
        }

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query)) {
            return 0;
        }

        return $result;
    }

    public function getHookMaxSortOrder($hookName)
    {
        $query = "SELECT MAX(sort_order)
                AS sort_order
                FROM `{$this->db_prefix}{$this->db_name}_hook`
                WHERE id_shop = {$this->shop->id}
                AND hook_name = '{$hookName}'";

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query)) {
            return 0;
        }

        return $result;
    }
}
