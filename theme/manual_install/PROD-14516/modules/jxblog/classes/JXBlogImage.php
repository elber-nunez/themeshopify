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

class JXBlogImage extends ObjectModel
{
    public $name;
    public $width;
    public $height;
    public $category;
    public $category_thumb;
    public $post;
    public $post_thumb;
    public $user;
    public static $definition = array(
        'table'     => 'jxblog_image',
        'primary'   => 'id_jxblog_image',
        'multilang' => false,
        'fields'    => array(
            'name'           => array('type' => self::TYPE_STRING, 'validate' => 'isImageTypeName', 'required' => true, 'size' => 64),
            'width'          => array('type' => self::TYPE_INT, 'validate' => 'isImageSize', 'required' => true),
            'height'         => array('type' => self::TYPE_INT, 'validate' => 'isImageSize', 'required' => true),
            'category'       => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'category_thumb' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'post'           => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'post_thumb'     => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'user'           => array('type' => self::TYPE_BOOL, 'validate' => 'isBool')
        )
    );

    public function delete()
    {
        $result = true;
        $module = new Jxblog();
        if ($module->imagesAutoRegenerate) {
            $imageManager = new JXBlogImageManager($module);
            $result &= $imageManager->removeImageTypeImages($this->id);
        }
        $result &= parent::delete();

        Hook::exec('actionJxblogImageAfterDelete', array('id_jxblog_image' => $this->id));

        return $result;
    }

    /**
     * Check if such image type is already exists. Usage of name more than once is forbidden
     *
     * @param      $name
     * @param bool $id
     *
     * @return false|null|string
     */
    public static function checkIfNameExists($name, $id = false)
    {
        $condition = '';
        if ($id) {
            $condition = ' AND `id_jxblog_image` != '.$id;
        }
        return Db::getInstance()->getValue('SELECT * FROM '._DB_PREFIX_.'jxblog_image WHERE `name` = "'.pSQL($name).'"'.$condition);
    }

    /**
     * Get all image types related to images category
     *
     * @param bool $type
     * @param bool $name
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getImageTypesByCategory($type = false, $name = false)
    {
        $condition = '';
        if ($type && $type != 'default') {
            $condition = ' WHERE `'.$type.'` = 1';
        }
        if ($name && $type == 'default') {
            $condition .= ' WHERE `name` = "'.pSQL($name).'"';
        } elseif ($name) {
            $condition .= ' AND `name` = "'.pSQL($name).'"';
        }

        return Db::getInstance()->executeS('SELECT `name`, `width`, `height` FROM '._DB_PREFIX_.'jxblog_image'.$condition);
    }

    /**
     * Get information about image type such as width/height etc.
     *
     * @param $type
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getImageTypeInfo($type)
    {
        return Db::getInstance()->executeS('SELECT `name`, `width`, `height` FROM '._DB_PREFIX_.'jxblog_image WHERE `name` ="'.pSQL($type).'"');
    }
}
