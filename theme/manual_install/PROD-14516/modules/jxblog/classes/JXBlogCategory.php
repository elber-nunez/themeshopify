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

class JXBlogCategory extends ObjectModel
{
    public $module;
    public $id;
    public $id_jxblog_category;
    public $active;
    public $position;
    public $date_add;
    public $date_upd;
    public $name;
    public $description;
    public $short_description;
    public $link_rewrite;
    public $meta_keyword;
    public $meta_description;
    public $badge;
    public static $definition = array(
        'table'     => 'jxblog_category',
        'primary'   => 'id_jxblog_category',
        'multilang' => true,
        'fields'    => array(
            'active'            => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'position'          => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add'          => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd'          => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'name'              => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
            'description'       => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'short_description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'link_rewrite'      => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true),
            'meta_keyword'      => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
            'meta_description'  => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
            'badge'             => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString')
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->module = new Jxblog();
        Shop::addTableAssociation('jxblog_category', array('type' => 'shop'));
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function add($auto_date = true, $null_values = false)
    {
        Hook::exec('actionJxblogCategoryBeforeAdd', array('id_jxblog_category' => $this->id));

        $res = parent::add($auto_date, $null_values);
        $res &= $this->associateToGroup();

        Hook::exec('actionJxblogCategoryAfterAdd', array('id_jxblog_category' => $this->id));

        return $res;
    }

    public function update($null_values = false)
    {
        Hook::exec('actionJxblogCategoryBeforeUpdate', array('id_jxblog_category' => $this->id));

        $res = parent::update($null_values);
        $res &= $this->associateToGroup();

        Hook::exec('actionJxblogCategoryAfterUpdate', array('id_jxblog_category' => $this->id));

        return $res;
    }

    public function delete()
    {
        Hook::exec('actionJxblogCategoryBeforeDelete', array('id_jxblog_category' => $this->id));

        $imageManager = new JXBlogImageManager($this->module);
        $result = parent::delete();
        $result &= $this->removeAssociationGroup();
        $result &= $imageManager->removeImages($this->id, 'category');
        $result &= $imageManager->removeImages($this->id, 'category_thumb');

        Hook::exec('actionJxblogCategoryAfterDelete', array('id_jxblog_category' => $this->id));

        return $result;
    }

    /**
     * Check a category name existence to avoid name duplication
     *
     * @param $name
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function checkCategoryNameExistence($id_jxblog_category, $name, $id_lang)
    {
        $extend = '';
        if ($id_jxblog_category) {
            $extend = ' AND `id_jxblog_category` != '.(int)$id_jxblog_category;
        }
        $sql = 'SELECT `id_jxblog_category`
                FROM '._DB_PREFIX_.'jxblog_category_lang
                WHERE `name` = "'.pSql($name).'"
                AND `id_lang` = '.(int)$id_lang.$extend;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Check a category friendly URL existence to avoid duplication
     *
     * @param $url
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function checkFriendlyUrlNameExistence($id_jxblog_category, $url, $id_lang)
    {
        $extend = '';
        if ($id_jxblog_category) {
            $extend = ' AND `id_jxblog_category` != '.(int)$id_jxblog_category;
        }
        $sql = 'SELECT `id_jxblog_category`
                FROM '._DB_PREFIX_.'jxblog_category_lang
                WHERE `link_rewrite` = "'.pSql($url).'"
                AND `id_lang` = '.(int)$id_lang.$extend;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Associate current category to all selected groups
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function associateToGroup()
    {
        $groups = Tools::getValue('groupBox');
        if (!$this->removeAssociationGroup()) {
            return false;
        }
        if ($groups && count($groups) > 0) {
            foreach ($groups as $group) {
                if (!Db::getInstance()->getValue(
                    'SELECT * FROM '._DB_PREFIX_.'jxblog_category_group WHERE `id_jxblog_category` = '.$this->id.' AND `id_group` = '.$group
                )
                ) {
                    if (!Db::getInstance()->insert(
                        'jxblog_category_group',
                        array('id_jxblog_category' => (int)$this->id, 'id_group' => (int)$group)
                    )
                    ) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Remove current category from groups associations
     *
     * @return bool
     */
    public function removeAssociationGroup()
    {
        return Db::getInstance()->delete('jxblog_category_group', '`id_jxblog_category` = '.(int)$this->id.'');
    }

    /**
     * Get all groups to which current category is related to
     *
     * @return array|null
     * @throws PrestaShopDatabaseException
     */
    public function getGroups()
    {
        $cacheId = 'JXBlogCategory::getGroups_'.(int)$this->id;
        if (!Cache::isStored($cacheId)) {
            $sql = new DbQuery();
            $sql->select('jxbcg.`id_group`');
            $sql->from('jxblog_category_group', 'jxbcg');
            $sql->where('jxbcg.`id_jxblog_category` = '.(int)$this->id);
            $result = Db::getInstance()->executeS($sql);
            $groups = array();
            foreach ($result as $group) {
                $groups[] = $group['id_group'];
            }
            Cache::store($cacheId, $groups);

            return $groups;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Update positions of all categories after any category position was changed
     *
     * @param $way      decrease || increase position of changing category
     * @param $position new position of changing category
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function updatePosition($way, $position)
    {
        // select all available blog categories and their positions
        if (!$res = Db::getInstance()->executeS(
            '
            SELECT `id_jxblog_category`, `position`
            FROM `'._DB_PREFIX_.'jxblog_category`
            ORDER BY `position` ASC'
        )
        ) {
            return false;
        }
        // mark the category which is moving
        $categoryMoved = false;
        foreach ($res as $category) {
            if ((int)$category['id_jxblog_category'] == (int)$this->id) {
                $categoryMoved = $category;
            }
        }
        if ($categoryMoved === false) {
            return false;
        }
        // update all categories' positions, but not that one which is moving
        $result = Db::getInstance()->execute(
            '
            UPDATE '._DB_PREFIX_.'jxblog_category
            SET `position` = `position` '.($way ? '-1' : '+1').', `date_upd` = "'.date('Y-m-d H:i:s').'"
            WHERE `position` '.($way
                ? '> '.(int)$categoryMoved['position'].' AND `position` <= '.(int)$position
                : '< '.(int)$categoryMoved['position'].' AND `position` >= '.(int)$position)
        );
        // update position of the moving category
        $result &= Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'jxblog_category`
            SET `position` = '.(int)$position.',
            `date_upd` = "'.date('Y-m-d H:i:s').'"
            WHERE `id_jxblog_category` = '.(int)$categoryMoved['id_jxblog_category']
        );

        return $result;
    }

    /**
     * Get the maximum position of already existing categories to set it + 1 to a new category
     *
     * @return false|int|null|string
     */
    public function getNewPosition()
    {
        $sql = 'SELECT MAX(`position`)
                FROM '._DB_PREFIX_.'jxblog_category';
        $max = Db::getInstance()->getValue($sql);
        if ($max === null) {
            return 0;
        }

        return $max + 1;
    }

    public static function getAllCategories()
    {
        return Db::getInstance()->executeS('SELECT `id_jxblog_category` AS `id` FROM '._DB_PREFIX_.'jxblog_category');
    }

    public static function getAllCategoriesWithInfo()
    {
        return Db::getInstance()->executeS('
          SELECT c.*, cl.* FROM '._DB_PREFIX_.'jxblog_category c
          LEFT JOIN '._DB_PREFIX_.'jxblog_category_lang cl
          ON(cl.`id_jxblog_category`=c.`id_jxblog_category`)
          WHERE cl.`id_lang` = '.Context::getContext()->language->id);
    }

    public static function getAllFrontCategories($id_lang, $id_shop, $id_group, $page = 0, $limit = 10)
    {
        return Db::getInstance()->executeS('
                SELECT c.*, cl.*
                FROM '._DB_PREFIX_.'jxblog_category c
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_lang cl
                ON(c.`id_jxblog_category` = cl.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop cs
                ON(c.`id_jxblog_category` = cs.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group cg
                ON(c.`id_jxblog_category` = cg.`id_jxblog_category`)
                WHERE c.`active` = 1
                AND cs.`id_shop` = '.(int)$id_shop.'
                AND cg.`id_group` = '.(int)$id_group.'
                AND cl.`id_lang` = '.(int)$id_lang.'
                ORDER BY c.`position`
                LIMIT '.(int)($page - 1)*$limit.','.(int)$limit);
    }

    public static function getCategory($id_category, $id_lang, $id_shop, $id_group)
    {
        return Db::getInstance()->executeS('
                SELECT c.*, cl.*
                FROM '._DB_PREFIX_.'jxblog_category c
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_lang cl
                ON(c.`id_jxblog_category` = cl.`id_jxblog_category` AND cl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop cs
                ON(c.`id_jxblog_category` = cs.`id_jxblog_category` AND cs.`id_shop` = '.(int)$id_shop.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group cg
                ON(c.`id_jxblog_category` = cg.`id_jxblog_category` AND cg.`id_group` = '.(int)$id_group.')
                WHERE c.id_jxblog_category = '.(int)$id_category);
    }

    public static function countFrontCategories($id_shop, $id_group)
    {
        $sql = 'SELECT count(*)
                FROM '._DB_PREFIX_.'jxblog_category c
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop cs
                ON(c.`id_jxblog_category` = cs.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group cg
                ON(c.`id_jxblog_category` = cg.`id_jxblog_category`)
                WHERE cs.`id_shop` = '.(int)$id_shop.'
                AND cg.`id_group` = '.(int)$id_group;

        return (int)Db::getInstance()->getValue($sql);
    }

    public static function getAllShopCategories($id_shop, $id_lang)
    {
        $sql = 'SELECT jc.`id_jxblog_category`, jcl.`name`, jcl.`id_lang`, jcs.`id_shop`
                FROM '._DB_PREFIX_.'jxblog_category jc
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop jcs
                ON(jc.`id_jxblog_category` = jcs.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_lang jcl
                ON(jc.`id_jxblog_category` = jcl.`id_jxblog_category` AND `id_lang` = '.(int)$id_lang.')
                WHERE jc.`active` = 1
                AND jcs.`id_shop` = '.(int)$id_shop;

        return Db::getInstance()->executeS($sql);
    }
}
