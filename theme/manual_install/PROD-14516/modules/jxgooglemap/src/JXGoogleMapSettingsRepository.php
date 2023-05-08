<?php
/**
 * 2017-2018 Zemez
 *
 * JX Google Map
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

class JXGoogleMapSettingsRepository
{
    private $db;
    private $shop;
    private $language;
    private $db_prefix;
    private $table = 'jxgooglemap_settings';
    private $multiLangAlias = 's';
    private $multiLangJoin = '';
    public $settingsList = array(
        'JXGOOGLE_API_KEY' => '',
        'JXGOOGLE_STYLE' => 'shift_worker',
        'JXGOOGLE_TYPE' => 'roadmap',
        'JXGOOGLE_ZOOM' => 9,
        'JXGOOGLE_SCROLL' => 0,
        'JXGOOGLE_TYPE_CONTROL' => 0,
        'JXGOOGLE_STREET_VIEW' => 1,
        'JXGOOGLE_ANIMATION' => 0,
        'JXGOOGLE_POPUP' => 1,
    );

    public function __construct(Db $db, Shop $shop, Language $language, $multilangual = false)
    {
        $this->db = $db;
        $this->shop = $shop;
        $this->language = $language;
        $this->db_prefix = $db->getPrefix();
        if ($multilangual) {
            $this->multiLangAlias = 'sl';
            $this->multiLangJoin = 'LEFT JOIN '.$this->db_prefix.'store_lang sl
            ON(s.`id_store` = sl.`id_store` AND sl.`id_lang` = '.(int)$this->language->id.')';
        }
    }

    public function createTables()
    {
        $engine = _MYSQL_ENGINE_;
        $success = true;
        $this->dropTables();

        $queries = [
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}{$this->table}`(
    			`id_setting` int(10) unsigned NOT NULL auto_increment,
    			`hook_name` VARCHAR (100),
    			`id_shop` int(10) unsigned,
    			`setting_name` VARCHAR (100),
    			`value` VARCHAR (100),
    			PRIMARY KEY (`id_setting`, `hook_name`, `id_shop`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxgooglemap`(
    			`id_tab` int(11) NOT NULL AUTO_INCREMENT,
                `hook_name` VARCHAR(100) NOT NULL,
                `default` int(11) NOT NULL,
                `id_store` int(11) NOT NULL,
                `id_shop` int(11) NOT NULL,
                `status` int(11) NOT NULL,
                `marker` VARCHAR(100) NOT NULL,
                 PRIMARY KEY  (`id_tab`)
            ) ENGINE=$engine DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}jxgooglemap_lang`(
    			`id_tab` int(11) NOT NULL,
                `id_lang` int(11) NOT NULL,
                `content` text NOT NULL
            ) ENGINE=$engine DEFAULT CHARSET=utf8"
        ];

        foreach ($queries as $query) {
            $success &= $this->db->execute($query);
        }

        return $success;
    }

    public function dropTables()
    {
        $sql = "DROP TABLE IF EXISTS
			`{$this->db_prefix}{$this->table}`,
			`{$this->db_prefix}jxgooglemap`,
			`{$this->db_prefix}jxgooglemap_lang`";

        return Db::getInstance()->execute($sql);
    }

    public function setDefaultSettings($hookName) {
        $result = true;

        foreach ($this->settingsList as $name => $value) {
            $result &= $this->db->insert($this->table, array('hook_name' => pSQL($hookName), 'id_shop' => (int)$this->shop->id, 'setting_name' => pSQL($name), 'value' => pSQL($value)));
        }

        return $result;
    }

    public function getSetting($hookName, $settingName)
    {
        return $this->db->getValue(
            "SELECT `value`
            FROM `{$this->db_prefix}{$this->table}`
            WHERE `hook_name` = '".pSQL($hookName)."'
            AND `setting_name` = '".pSQL($settingName)."'
            AND `id_shop` = ".(int)$this->shop->id
        );
    }

    public function getSettings($hookName, array $result = [])
    {
        $settings = $this->db->executeS(
            "SELECT `setting_name`, `value`
            FROM `{$this->db_prefix}{$this->table}`
            WHERE `hook_name` = '".pSQL($hookName)."'
            AND `id_shop` = ".(int)$this->shop->id
        );

        if ($settings) {
            foreach ($settings as $setting) {
                $result[$setting['setting_name']] = $setting['value'];
            }
        }

        return $result;
    }

    public function checkSetting($hookName, $settingName)
    {
        return $this->db->getValue(
            "SELECT `id_setting`
            FROM `{$this->db_prefix}{$this->table}`
            WHERE `hook_name` = '".pSQL($hookName)."'
            AND `setting_name` = '".pSQL($settingName)."'
            AND `id_shop` = ".(int)$this->shop->id
        );
    }

    public function insertSetting($hookName, $settingName, $value)
    {
        return $this->db->insert($this->table, array('hook_name' => pSQL($hookName), 'id_shop' => (int)$this->shop->id, 'setting_name' => pSQL($settingName), 'value' => pSQL($value)));
    }

    public function updateSetting($hookName, $settingName, $value)
    {
        if ($this->checkSetting($hookName, $settingName)) {
            return $this->db->update(
                $this->table, array('value' => $value),
                '`hook_name` = "'.pSQL($hookName).'" AND `setting_name` = "'.pSQL($settingName).'"'
            );
        } else {
            return $this->insertSetting($hookName, $settingName, $value);
        }
    }

    public function deleteHookSettings($hookName)
    {
        return $this->db->delete($this->table, '`hook_name` = "'.pSQL($hookName).'" AND `id_shop` = '.(int)$this->shop->id);
    }

    public function getStyles()
    {
        return $this->db->executeS(
            "SELECT `value`
            FROM `{$this->db_prefix}{$this->table}`
            WHERE `id_shop` = ".(int)$this->shop->id."
            AND `setting_name` = 'JXGOOGLE_STYLE'"
        );
    }

    /**
     * Get list with store id
     * return bool $result if invalid or false
     */
    public function getStoresListIds()
    {
        return $this->db->executeS("SELECT s.`id_store`, {$this->multiLangAlias}.`name`
				FROM {$this->db_prefix}store s
				LEFT JOIN {$this->db_prefix}store_shop ss
				ON(s.`id_store` = ss.`id_store`)
				{$this->multiLangJoin}
				WHERE ss.`id_shop` = ".(int)$this->shop->id);
    }

    /**
     * Get all active store data for list table
     *
     * @return array $result
     */
    public function getTabList($hookName)
    {
        return $this->db->executeS("SELECT jxg.*, {$this->multiLangAlias}.`name`
            FROM {$this->db_prefix}jxgooglemap jxg
			LEFT JOIN {$this->db_prefix}store s
			ON(jxg.`id_store` = s.`id_store`)
			{$this->multiLangJoin}
			WHERE jxg.`id_shop` = ".(int)$this->shop->id."
			AND jxg.`hook_name` = '".pSQL($hookName)."'
			ORDER BY jxg.`id_tab`");
    }

    /**
     * Get all active store data
     *
     * @return array $result
     */
    public function getStoreContactsData($hookName)
    {
        return $this->db->executeS("SELECT jxg.*, {$this->multiLangAlias}.`name` , jxgl.`content`
            FROM {$this->db_prefix}jxgooglemap jxg
			LEFT JOIN {$this->db_prefix}store s
			ON(jxg.`id_store` = s.`id_store`)
			{$this->multiLangJoin}
			LEFT JOIN {$this->db_prefix}jxgooglemap_lang jxgl
			ON(jxg.`id_tab` = jxgl.`id_tab`)
			WHERE jxg.`id_shop` = ".(int)$this->shop->id."
			AND jxg.`hook_name` = '".pSQL($hookName)."'
			AND jxgl.`id_lang` = ".(int)$this->language->id."
			AND jxg.`status` = 1
			AND s.`active` = 1
			ORDER BY jxg.`id_tab`");
    }

    /**
     * Get shop by id store,
     * check if store already exists with same id store
     *
     * @param int $id_store
     * @return bool|array id store info or false
     */

    public function getShopByIdStore($hookName, $id_store)
    {
        return $this->db->executeS("SELECT *
                FROM {$this->db_prefix}jxgooglemap
                WHERE `id_store` = '".pSql($id_store)."'
                AND `hook_name` = '".pSQL($hookName)."'");
    }
}
