<?php
/**
 * 2017-2018 Zemez
 *
 * JX Mega Menu
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
 *  @author    Zemez
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class JXMegaMenuRepository
{
    private $db;
    private $shop;
    private $language;
    private $db_prefix;

    public function __construct(Db $db, Shop $shop, Language $language)
    {
        $this->db = $db;
        $this->shop = $shop;
        $this->language = $language;
        $this->db_prefix = $db->getPrefix();
    }

    public function createTables()
    {
        $engine = _MYSQL_ENGINE_;
        $success = true;
        $this->dropTables();

        $queries = array(
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL DEFAULT '1',
                `sort_order` int(11) NOT NULL DEFAULT '0',
                `specific_class` VARCHAR(100),
                `hook_name` VARCHAR(100),
                `is_mega` int(11) NOT NULL DEFAULT '0',
                `is_simple` int(11) NOT NULL DEFAULT '0',
                `is_custom_url` int(11) NOT NULL DEFAULT '0',
                `active` int(11) NOT NULL DEFAULT '1',
                `unique_code` VARCHAR(100),
            PRIMARY KEY  (`id_item`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_lang` (
                `id_item` int(10) unsigned NOT NULL,
                `id_lang` int(11) NOT NULL,
                `title` VARCHAR(100),
                `badge` VARCHAR(100),
                `url` VARCHAR(100),
            PRIMARY KEY  (`id_item`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_tab` int(11) NOT NULL,
                `row` int(11) NOT NULL DEFAULT '1',
                `col` int(11) NOT NULL DEFAULT '1',
                `width` int(11),
                `class` VARCHAR(100),
                `type` int(11) NOT NULL DEFAULT '0',
                `is_mega` int(11) NOT NULL DEFAULT '0',
                `settings` VARCHAR(10000),
            PRIMARY KEY  (`id`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_html` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL DEFAULT '1',
                `specific_class` VARCHAR(100),
            PRIMARY KEY  (`id_item`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_html_lang` (
                `id_item` int(10) unsigned NOT NULL,
                `id_lang` int(11) NOT NULL,
                `title` VARCHAR(100),
                `content` text NOT NULL,
            PRIMARY KEY (`id_item`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_link` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL DEFAULT '1',
                `specific_class` VARCHAR(100),
                `blank` int(11) NOT NULL,
            PRIMARY KEY  (`id_item`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_link_lang` (
                `id_item` int(10) unsigned NOT NULL,
                `id_lang` int(11) NOT NULL,
                `title` VARCHAR(100),
                `url` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id_item`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_banner` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL DEFAULT '1',
                `specific_class` VARCHAR(100),
                `blank` int(11) NOT NULL,
            PRIMARY KEY (`id_item`, `id_shop`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_banner_lang` (
                `id_item` int(10) unsigned NOT NULL,
                `id_lang` int(11) NOT NULL,
                `title` VARCHAR(100),
                `url` VARCHAR(100) NOT NULL,
                `image` VARCHAR(100) NOT NULL,
                `public_title` VARCHAR(100),
                `description` text NOT NULL,
            PRIMARY KEY (`id_item`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_video` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL DEFAULT '1',
            PRIMARY KEY  (`id_item`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_video_lang` (
                `id_item` int(10) unsigned NOT NULL,
                `id_lang` int(11) NOT NULL,
                `title` VARCHAR(100),
                `url` VARCHAR(100) NOT NULL,
                `type` VARCHAR(100),
            PRIMARY KEY (`id_item`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_map` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL DEFAULT '1',
                `latitude` VARCHAR(100),
                `longitude` VARCHAR(100),
                `scale` int(11) NOT NULL,
                `marker` VARCHAR(100) NOT NULL,
            PRIMARY KEY  (`id_item`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_map_lang` (
                `id_item` int(10) unsigned NOT NULL,
                `id_lang` int(11) NOT NULL,
                `title` VARCHAR(100),
                `description` text NOT NULL,
            PRIMARY KEY (`id_item`, `id_lang`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxmegamenu_settings` (
                `id_item` int(11) NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) NOT NULL,
                `hook_name`  VARCHAR(100),
                `googleapi`  VARCHAR(100),
                PRIMARY KEY  (`id_item`, `id_shop`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8;"
        );

        foreach ($queries as $query) {
            $success &= $this->db->execute($query);
        }

        return $success;
    }

    public function dropTables()
    {
        $sql = "DROP TABLE IF EXISTS
			`{$this->db_prefix}jxmegamenu`,
			`{$this->db_prefix}jxmegamenu_lang`,
			`{$this->db_prefix}jxmegamenu_items`,
			`{$this->db_prefix}jxmegamenu_html`,
			`{$this->db_prefix}jxmegamenu_html_lang`,
			`{$this->db_prefix}jxmegamenu_link`,
			`{$this->db_prefix}jxmegamenu_link_lang`,
			`{$this->db_prefix}jxmegamenu_banner`,
			`{$this->db_prefix}jxmegamenu_banner_lang`,
			`{$this->db_prefix}jxmegamenu_video`,
			`{$this->db_prefix}jxmegamenu_video_lang`,
			`{$this->db_prefix}jxmegamenu_map`,
			`{$this->db_prefix}jxmegamenu_map_lang`,
			`{$this->db_prefix}jxmegamenu_settings`";

        return $this->db->execute($sql);
    }

    public function getAllModuleHooks($moduleName)
    {
        $sql = "SELECT DISTINCT h.id_hook as id, h.name as name
                FROM {$this->db_prefix}hook h
                LEFT JOIN {$this->db_prefix}hook_module hm
                ON(h.id_hook = hm.`id_hook`)
                INNER JOIN {$this->db_prefix}module m
                ON(hm.`id_module` = m.`id_module`)
                WHERE (lower(h.`name`) LIKE 'display%')
                AND m.`name` = '{$moduleName}'
                ORDER BY h.name ASC
            ";
        $hooks = $this->db->executeS($sql);
        foreach ($hooks as $key => $hook) {
            if (preg_match('/admin/i', $hook['name'])
                || preg_match('/backoffice/i', $hook['name'])
                || $hook['name'] == 'displayBeforeBodyClosingTag'
            ) {
                unset($hooks[$key]);
            }
        }

        return $hooks;
    }

    /*
        Get categories tree
    */
    public function getTree($result_parents, $result_ids, $id_category = null)
    {
        if (is_null($id_category)) {
            $id_category = $this->shop->id_category;
        }

        $children = array();
        if (isset($result_parents[$id_category]) && count($result_parents[$id_category])) {
            foreach ($result_parents[$id_category] as $subcat) {
                $children[] = $this->getTree($result_parents, $result_ids, $subcat['id_category']);
            }
        }

        if (!isset($result_ids[$id_category])) {
            return false;
        }

        $return = array(
            'id' => $id_category,
            'name' =>  $result_ids[$id_category]['name'],
            'level_depth' =>  $result_ids[$id_category]['level_depth'],
            'children' => $children
        );

        return $return;
    }

    /*
        Get all Cms categories with pages
    */
    public function getCMSCategories($recursive = false, $parent = 0, $id_shop = false, $depth = 1, $spacer = 0)
    {
        $id_shop = $this->shop->id;
        $join_shop = '';
        $where_shop = '';
        $categories = array();

        if (Tools::version_compare(_PS_VERSION_, '1.6.0.12', '>=') == true) {
            $join_shop = " INNER JOIN `{$this->db_prefix}cms_category_shop` cs
            ON (bcp.`id_cms_category` = cs.`id_cms_category`)";
            $where_shop = " AND cs.`id_shop` = {$id_shop} AND cl.`id_shop` = {$id_shop}";
        }

        if ($recursive === false) {
            $sql = "SELECT bcp.`id_cms_category`, bcp.`id_parent`,
                           bcp.`level_depth`, bcp.`active`, bcp.`position`,
                           cl.`name`, cl.`link_rewrite`
                    FROM `{$this->db_prefix}cms_category` bcp{$join_shop}
                    INNER JOIN `{$this->db_prefix}cms_category_lang` cl
                    ON (bcp.`id_cms_category` = cl.`id_cms_category`)
                    WHERE cl.`id_lang` = {$this->language->id}{$where_shop}";
            if ($parent) {
                $sql .= " AND bcp.`id_parent` = {$parent}";
            }

            return $this->db->executeS($sql);
        } else {
            $sql = "SELECT bcp.`id_cms_category`, bcp.`id_parent`,
                           bcp.`level_depth`, bcp.`active`, bcp.`position`,
                           cl.`name`, cl.`link_rewrite`
                    FROM `{$this->db_prefix}cms_category` bcp{$join_shop}
                    INNER JOIN `{$this->db_prefix}cms_category_lang` cl
                    ON (bcp.`id_cms_category` = cl.`id_cms_category`)
                    WHERE cl.`id_lang` = {$this->language->id}{$where_shop}";
            if ($parent) {
                $sql .= " AND bcp.`id_parent` = {$parent}";
            }
            $sql .= ' AND bcp.`level_depth` = '.$depth;
            $results = $this->db->executeS($sql);
            foreach ($results as $result) {
                $sub_categories = $this->getCMSCategories(true, $result['id_cms_category'], false, 1, $spacer);
                if ($sub_categories && count($sub_categories) > 0) {
                    $result['children'] = $sub_categories;
                }

                $result['id'] = $result['id_cms_category'];
                $result['is_cms'] = '1';
                $result['pages'] = $this->getCMSPages(
                    (int)$result['id_cms_category'],
                    false,
                    false,
                    true,
                    $depth,
                    $spacer
                );
                $categories[] = $result;
            }

            return isset($categories) ? $categories : false;
        }
    }

    /*
        Get all cms pages for cms category
    */
    public function getCMSPages($id_cms_category, $id_lang = false, $id_shop = false, $is_list = false, $depth = 0, $spacer = 0)
    {
        $id_shop = $this->shop->id;
        $id_lang = $this->language->id;

        $where_shop = '';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.12', '>=') == true) {
            $where_shop = " AND cl.`id_shop` = {$id_shop}";
        }

        $sql = "SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
            FROM `{$this->db_prefix}cms` c
            INNER JOIN `{$this->db_prefix}cms_shop` cs
            ON (c.`id_cms` = cs.`id_cms`)
            INNER JOIN `{$this->db_prefix}cms_lang` cl
            ON (c.`id_cms` = cl.`id_cms`)
            WHERE c.`id_cms_category` = {$id_cms_category}
            AND cs.`id_shop` = {$id_shop}
            AND cl.`id_lang` = {$id_lang}{$where_shop}
            AND c.`active` = 1
            ORDER BY `position`";

        if ($is_list) {
            if (!$result = $this->db->executeS($sql)) {
                return false;
            }

            $data = array();
            $i = 0;
            $spacer = str_repeat('&nbsp;', $spacer * (int)$depth);
            foreach ($result as $res) {
                $data[$i]['id'] = $res['id_cms'];
                $data[$i]['name'] = $spacer.$res['meta_title'];
                $data[$i]['is_cms_page'] = '1';
                $i++;
            }
            return $data;
        }
        return $this->db->executeS($sql);
    }

    public function customGetNestedCategories(
        $shop_id,
        $root_category = null,
        $id_lang = false,
        $active = true,
        $groups = null,
        $use_shop_restriction = true,
        $sql_filter = '',
        $sql_sort = '',
        $sql_limit = ''
    ) {
        if (isset($root_category) && !Validate::isInt($root_category)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array)$groups;
        }

        $cache_id = 'Category::getNestedCategories_'.md5((int)$shop_id.(int)$root_category.(int)$id_lang.(int)$active.(int)$active.(isset($groups) && Group::isFeatureActive() ? implode('', $groups) : ''));

        if (!Cache::isStored($cache_id)) {
            $result = $this->db->executeS(
                'SELECT c.*, cl.*
                FROM `'._DB_PREFIX_.'category` c
                INNER JOIN `'._DB_PREFIX_.'category_shop` category_shop
                ON (category_shop.`id_category` = c.`id_category`
                AND category_shop.`id_shop` = "'.(int)$shop_id.'")
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
                ON (c.`id_category` = cl.`id_category`
                AND cl.`id_shop` = "'.(int)$shop_id.'")
                WHERE 1 '.$sql_filter.' '.($id_lang ? 'AND cl.`id_lang` = '.(int)$id_lang : '').'
                '.($active ? ' AND (c.`active` = 1 OR c.`is_root_category` = 1)' : '').'
                '.(isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('.implode(',', $groups).')' : '').'
                '.(!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '').'
                '.($sql_sort != '' ? $sql_sort : ' ORDER BY c.`level_depth` ASC').'
                '.($sql_sort == '' && $use_shop_restriction ? ', category_shop.`position` ASC' : '').'
                '.($sql_limit != '' ? $sql_limit : '')
            );

            $categories = array();
            $buff = array();

            foreach ($result as $row) {
                $current = &$buff[$row['id_category']];
                $current = $row;

                if ($row['id_parent'] == 0) {
                    $categories[$row['id_category']] = &$current;
                } else {
                    $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
                }
            }
            Cache::store($cache_id, $categories);
        }
        return Cache::retrieve($cache_id);
    }
}
