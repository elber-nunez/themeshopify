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

class JXBlogPost extends ObjectModel
{
    protected $module;
    protected $imageManager;
    protected $context;
    public $id_jxblog_category_default;
    public $author;
    public $views;
    public $active;
    public $date_add;
    public $date_upd;
    public $date_start;
    public $name;
    public $link_rewrite;
    public $description;
    public $short_description;
    public $meta_keyword;
    public $meta_description;
    public static $definition = array(
        'table'     => 'jxblog_post',
        'primary'   => 'id_jxblog_post',
        'multilang' => true,
        'fields'    => array(
            'id_jxblog_category_default' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'author'                     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'views'                      => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'active'                     => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add'                   => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd'                   => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_start'                 => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            // language fields
            'name'                       => array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'lang' => true, 'required' => true, 'size' => 64),
            'link_rewrite'               => array('type' => self::TYPE_STRING, 'validate' => 'isLinkRewrite', 'lang' => true, 'required' => true, 'size' => 64),
            'description'                => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true),
            'short_description'          => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true),
            'meta_keyword'               => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
            'meta_description'           => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString')
        )
    );

    public function __construct($id = null, $lang = null, $shop = null)
    {
        $this->module = new Jxblog();
        $this->context = Context::getContext();
        $this->imageManager = new JXBlogImageManager($this->module);
        parent::__construct($id, $lang, $shop);
    }

    public function add($auto_date = true, $null_values = false)
    {
        Hook::exec('actionJxblogPostBeforeAdd', array('id_jxblog_post' => $this->id));

        $result = true;
        if (!Tools::getValue('date_start')) {
            $this->date_start = date('Y-m-d H:i:s');
        }
        $this->author = $this->context->employee->id;
        $result &= parent::add($auto_date, $null_values);
        $result &= $this->updatePostImages();
        $result &= $this->associatePostToCategories();
        $result &= $this->associateTagsToPost();

        Hook::exec('actionJxblogPostAfterAdd', array('id_jxblog_post' => $this->id));

        return $result;
    }

    public function update($null_values = false)
    {
        Hook::exec('actionJxblogPostBeforeUpdate', array('id_jxblog_post' => $this->id));

        $result = true;
        $result &= parent::update($null_values);
        $result &= $this->updatePostImages();
        $result &= $this->associatePostToCategories();
        $result &= $this->associateTagsToPost();

        Hook::exec('actionJxblogPostAfterUpdate', array('id_jxblog_post' => $this->id));

        return $result;
    }

    public function delete()
    {
        Hook::exec('actionJxblogPostBeforeDelete', array('id_jxblog_post' => $this->id));

        $result = true;
        $result &= parent::delete();
        $result &= $this->disassociatePostToCategories();
        $result &= $this->disassociateTagsToPost();
        $result &= $this->imageManager->removeImages($this->id, 'post');
        $result &= $this->imageManager->removeImages($this->id, 'post_thumb');

        Hook::exec('actionJxblogPostAfterDelete', array('id_jxblog_post' => $this->id));

        return $result;
    }

    public function getPostsByDefaultCategory($id_jxblog_category)
    {
        return Db::getInstance()->executeS('SELECT `id_jxblog_post` FROM '._DB_PREFIX_.'jxblog_post WHERE `id_jxblog_category_default` = '.(int)$id_jxblog_category);
    }

    /**
     * Associate the product to all related categories
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    private function associatePostToCategories()
    {
        if (!$this->disassociatePostToCategories()) {
            return false;
        }
        $categories = Tools::getValue('jxcategoryBox');
        if (!$categories) {
            if (!Db::getInstance()->insert('jxblog_post_category', array('id_jxblog_post' => (int)$this->id, 'id_jxblog_category' => (int)$this->id_jxblog_category_default))) {
                return false;
            }
            return true;
        }
        foreach ($categories as $category) {
            if (!Db::getInstance()->insert('jxblog_post_category', array('id_jxblog_post' => (int)$this->id, 'id_jxblog_category' => (int)$category))) {
                return false;
            }
        }

        return true;
    }

    public function resetDefaultCategory($id_jxblog_category, $new_category_id = 0)
    {
        return Db::getInstance()->update('jxblog_post', array('id_jxblog_category_default' => (int)$new_category_id), '`id_jxblog_category_default` = '.(int)$id_jxblog_category);
    }

    private function disassociatePostToCategories()
    {
        if (!Db::getInstance()->delete('jxblog_post_category', '`id_jxblog_post` = '.(int)$this->id)) {
            return false;
        }

        return true;
    }

    /**
     * Get all categories to which the product is associated
     *
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function getAssociatedCategories()
    {
        $result = array();
        $categories = Db::getInstance()->executeS('SELECT `id_jxblog_category` FROM '._DB_PREFIX_.'jxblog_post_category WHERE `id_jxblog_post` = '.(int)$this->id);
        if (!$categories) {
            return array();
        } else {
            foreach ($categories as $category) {
                array_push($result, $category['id_jxblog_category']);
            }
        }

        return $result;
    }

    private function associateTagsToPost()
    {
        if (!$this->disassociateTagsToPost()) {
            return false;
        }

        foreach ($this->module->languages as $language) {
            $language_tags = Tools::getValue('tags_'.$language['id_lang']);
            if ($language_tags) {
                $tags = explode(',', $language_tags);
                foreach ($tags as $tag) {
                    if (!$id_tag = JXBlogTag::checkTagExistence($language['id_lang'], $tag)) {
                        $newTag = new JXBlogTag();
                        $newTag->id_lang = $language['id_lang'];
                        $newTag->tag = $tag;
                        if (!$newTag->add()) {
                            return false;
                        } else {
                            Db::getInstance()->insert('jxblog_tag_post', array('id_jxblog_tag' => (int)$newTag->id, 'id_jxblog_post' => (int)$this->id));
                        }
                    } else {
                        Db::getInstance()->insert('jxblog_tag_post', array('id_jxblog_tag' => (int)$id_tag, 'id_jxblog_post' => (int)$this->id));
                    }
                }
            }
        }

        return true;
    }

    private function disassociateTagsToPost()
    {
        if (!Db::getInstance()->delete('jxblog_tag_post', '`id_jxblog_post` = '.(int)$this->id)) {
            return false;
        }

        return true;
    }

    public function getAdminPostTags()
    {
        $rawTags = JXBlogTag::getPostTags($this->id);
        if (!$rawTags) {
            return false;
        }
        $result = array();
        foreach ($this->module->languages as $language) {
            $result[$language['id_lang']] = array();
            foreach ($rawTags as $tag) {
                if ($tag['id_lang'] == $language['id_lang']) {
                    $result[$language['id_lang']][] = $tag['tag'];
                }
            }
            $result[$language['id_lang']] = implode(',', $result[$language['id_lang']]);
        }

        return $result;
    }

    /**
     * Live search for the post to use in autocomplete
     *
     * @param       $name
     * @param       $id_lang
     * @param       $limit
     * @param array $excluded
     *
     * @return array|bool
     * @throws PrestaShopDatabaseException
     */
    public static function searchPostsLive($name, $id_lang, $limit, $excluded = array())
    {
        $result = array();
        $sql = 'SELECT p.`id_jxblog_post` as `id`, pl.`name` as `name`
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(pl.`id_jxblog_post`=p.`id_jxblog_post`)
                WHERE pl.`id_lang` = '.(int)$id_lang.'
                AND pl.`name` LIKE "%'.pSQL($name).'%"';
        if ($excluded) {
            $sql .= ' AND p.`id_jxblog_post` NOT IN ('.implode(',', $excluded).')';
        }

        $sql .= ' LIMIT '.(int)$limit;

        if (!$res = Db::getInstance()->executeS($sql)) {
            return false;
        }
        foreach ($res as $item) {
            $result[] = $item['name'].'|'.$item['id'];
        }

        return $result;
    }

    /**
     * Update post image if they were changed
     *
     * @return bool
     * @throws PrestaShopException
     */
    private function updatePostImages()
    {
        if (!Tools::isEmpty(Tools::getValue('image')) && Tools::getValue('image')) {
            if ($error = $this->imageManager->uploadImage($this->id, $_FILES['image'], 'post')) {
                die(Tools::displayError($error));
            }
        }
        if (!Tools::isEmpty(Tools::getValue('thumbnail')) && Tools::getValue('thumbnail')) {
            if ($error = $this->imageManager->uploadImage($this->id, $_FILES['thumbnail'], 'post_thumb')) {
                die(Tools::displayError($error));
            }
        }

        return true;
    }

    /**
     * Get full list of posts
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllPosts()
    {
        return Db::getInstance()->executeS('SELECT `id_jxblog_post` AS `id` FROM '._DB_PREFIX_.'jxblog_post');
    }

    /**
     * Get all post related to the certain category
     *
     * @param $id_category
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllPostsByCategory($id_category, $id_lang)
    {
        $sql = 'SELECT p.*, pl.*
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_category pc
                ON(p.`id_jxblog_post` = pc.`id_jxblog_post`)
                WHERE p.`date_start` <= NOW()
                AND pc.`id_jxblog_category` = '.(int)$id_category.'
                AND p.`active` = 1';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get post related to the certain category for listing
     *
     * @param     $id_category
     * @param     $id_lang
     * @param int $start
     * @param int $limit
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPostsByCategory($id_category, $id_lang, $start = 0, $limit = 10)
    {
        $sql = 'SELECT p.*, pl.*, CONCAT(e.`firstname`, " ", e.`lastname`) as `author`
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_category pc
                ON(p.`id_jxblog_post` = pc.`id_jxblog_post`)
                LEFT JOIN '._DB_PREFIX_.'employee e
                ON(p.`author` = e.`id_employee`)
                WHERE p.`date_start` <= NOW()
                AND pc.`id_jxblog_category` = '.(int)$id_category.'
                AND p.`active` = 1
                LIMIT '.(int)($start - 1)*$limit.','.(int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Count all post related to category
     *
     * @param $id_category
     *
     * @return false|null|string
     */
    public static function countPostsByCategory($id_category)
    {
        $sql = 'SELECT count(*)
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_category pc
                ON(p.`id_jxblog_post` = pc.`id_jxblog_post`)
                WHERE p.`date_start` <= NOW()
                AND pc.`id_jxblog_category` = '.(int)$id_category.'
                AND p.`active` = 1';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Check if current user has an access to the post
     *
     * @param $id_jxblog_post
     * @param $id_shop
     * @param $id_user_group
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    private static function checkAccess($id_jxblog_post, $id_shop, $id_user_group)
    {
        $sql = 'SELECT pc.`id_jxblog_category`
                FROM '._DB_PREFIX_.'jxblog_post_category pc
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop cs
                ON(pc.`id_jxblog_category` = cs.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group cg
                ON(pc.`id_jxblog_category` = cg.`id_jxblog_category`)
                WHERE pc.`id_jxblog_post` = '.(int)$id_jxblog_post.'
                AND cs.`id_shop` = '.(int)$id_shop.'
                AND cg.`id_group` = '.(int)$id_user_group;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get the post information
     *
     * @param $id_jxblog_post
     * @param $id_lang
     * @param $id_shop
     * @param $id_user_group
     *
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPost($id_jxblog_post, $id_lang, $id_shop, $id_user_group)
    {
        if (!self::checkAccess($id_jxblog_post, $id_shop, $id_user_group)) {
            return false;
        }
        $sql = 'SELECT p.*, pl.*, p.`author` as `id_author`, CONCAT(e.`firstname`," ", e.`lastname`) as `author`
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.id_jxblog_post AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'employee e
                ON(p.`author` = e.`id_employee`)
                WHERE p.`id_jxblog_post` = '.(int)$id_jxblog_post.'
                AND p.`active` = 1';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get the post tags
     *
     * @param $id_jxblog_post
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPostTags($id_jxblog_post, $id_lang)
    {
        return Db::getInstance()->executeS('
          SELECT t.*
          FROM '._DB_PREFIX_.'jxblog_tag t
          LEFT JOIN '._DB_PREFIX_.'jxblog_tag_post tp
          ON(tp.`id_jxblog_tag`=t.`id_jxblog_tag`)
          WHERE tp.`id_jxblog_post` = '.(int)$id_jxblog_post.'
          AND t.`id_lang` = '.(int)$id_lang);
    }

    /**
     * Get post by tag for listing by tag
     *
     * @param     $id_tag
     * @param     $id_lang
     * @param int $start
     * @param int $limit
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPostsByTag($id_tag, $id_lang, $start = 0, $limit = 10)
    {
        $sql = 'SELECT p.*, pl.*, CONCAT(e.`firstname`, " ", e.`lastname`) as `author`
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_tag_post ptp
                ON(p.`id_jxblog_post` = ptp.`id_jxblog_post`)
                LEFT JOIN '._DB_PREFIX_.'employee e
                ON(p.`author` = e.`id_employee`)
                WHERE p.`date_start` <= NOW()
                AND ptp.`id_jxblog_tag` = '.(int)$id_tag.'
                AND p.`active` = 1
                LIMIT '.(int)($start - 1)*$limit.','.(int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Count all posts related to the tag
     * @param $id_tag
     *
     * @return false|null|string
     */
    public static function countPostsByTag($id_tag)
    {
        $sql = 'SELECT count(*)
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_tag_post ptp
                ON(p.`id_jxblog_post` = ptp.`id_jxblog_post`)
                WHERE p.`date_start` <= NOW()
                AND ptp.`id_jxblog_tag` = '.(int)$id_tag.'
                AND p.`active` = 1';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Get posts by author
     * @param     $id_author
     * @param int $start
     * @param int $limit
     * @param     $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getPostsByAuthor($id_author, $id_lang, $start = 0, $limit = 10)
    {
        $sql = 'SELECT p.*, pl.*, CONCAT(e.`firstname`, " ", e.`lastname`) as `author`
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'employee e
                ON(p.`author` = e.`id_employee`)
                WHERE p.`date_start` <= NOW()
                AND p.`author` = '.(int)$id_author.'
                AND p.`active` = 1
                LIMIT '.(int)($start - 1)*$limit.','.(int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Count all author's posts
     *
     * @param $id_author
     *
     * @return false|null|string
     */
    public static function countPostsByAuthor($id_author)
    {
        $sql = 'SELECT count(*)
                FROM '._DB_PREFIX_.'jxblog_post p
                WHERE p.`date_start` <= NOW()
                AND p.`author` = '.(int)$id_author.'
                AND p.`active` = 1';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Update number of post views
     *
     * @param $id_post
     *
     * @return bool
     */
    public static function postViewed($id_post)
    {
        $sql = 'UPDATE '._DB_PREFIX_.'jxblog_post SET `views` = (views+1) where `id_jxblog_post` = '.(int)$id_post;

        return Db::getInstance()->execute($sql);
    }

    public static function getAllShopPosts($id_shop, $id_lang)
    {
        $sql = 'SELECT DISTINCT jp.`id_jxblog_post`, jpl.`name`
                FROM '._DB_PREFIX_.'jxblog_post jp
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang jpl
                ON(jp.`id_jxblog_post`=jpl.`id_jxblog_post` AND jpl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_category jpc
                ON(jp.`id_jxblog_post` = jpc.`id_jxblog_post`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category jc
                ON(jpc.`id_jxblog_category` = jc.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop jcs
                ON(jcs.`id_jxblog_category` = jc.`id_jxblog_category`)
                WHERE jp.`active` = 1
                AND jcs.`id_shop` = '.(int)$id_shop.'
                AND jc.`active` = 1';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Search for products by defined parameters. Check all accesses before showing
     * to prevent displaying products which are in private categories or in other stores
     *
     * @param     $query
     * @param     $id_category
     * @param     $id_lang
     * @param     $id_shop
     * @param     $id_customer_group
     * @param int $start
     * @param int $limit
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function search($query, $id_category, $id_lang, $id_shop, $id_customer_group, $start = 0, $limit = 10)
    {
        $sql = 'SELECT p.*, pl.*
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_category pc
                ON(p.`id_jxblog_post` = pc.`id_jxblog_post`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group cg
                ON(pc.`id_jxblog_category` = cg.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop cs
                ON(pc.`id_jxblog_category` = cs.`id_jxblog_category`)
                WHERE (pl.`name` LIKE "%'.pSQL($query).'%" OR pl.short_description LIKE "%'.pSQL($query).'%" OR pl.description LIKE "%'.pSQL($query).'%")
                AND p.`active` = 1';
        // add condition if category is defined
        if ($id_category) {
            $sql .= ' AND pc.`id_jxblog_category` = '.(int)$id_category;
        }
        $sql .= ' AND cg.`id_group` = '.(int)$id_customer_group.'
                 AND cs.`id_shop` = '.(int)$id_shop.'
                 LIMIT '.(int)($start - 1)*$limit.','.(int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    public static function countPostsBySearch($query, $id_category, $id_lang, $id_shop, $id_customer_group)
    {
        $sql = 'SELECT COUNT(*)
                FROM '._DB_PREFIX_.'jxblog_post p
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_lang pl
                ON(p.`id_jxblog_post` = pl.`id_jxblog_post` AND pl.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN '._DB_PREFIX_.'jxblog_post_category pc
                ON(p.`id_jxblog_post` = pc.`id_jxblog_post`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_group cg
                ON(pc.`id_jxblog_category` = cg.`id_jxblog_category`)
                LEFT JOIN '._DB_PREFIX_.'jxblog_category_shop cs
                ON(pc.`id_jxblog_category` = cs.`id_jxblog_category`)
                WHERE (pl.`name` LIKE "%'.pSQL($query).'%" OR pl.short_description LIKE "%'.pSQL($query).'%" OR pl.description LIKE "%'.pSQL($query).'%")
                AND p.`active` = 1';
        // add condition if category is defined
        if ($id_category) {
            $sql .= ' AND pc.`id_jxblog_category` = '.(int)$id_category;
        }
        $sql .= ' AND cg.`id_group` = '.(int)$id_customer_group.'
                 AND cs.`id_shop` = '.(int)$id_shop;

        return Db::getInstance()->getValue($sql);
    }
}
