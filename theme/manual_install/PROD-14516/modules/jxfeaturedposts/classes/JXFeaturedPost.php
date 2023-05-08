<?php
/**
* 2017-2018 Zemez
*
* JX Featured Products
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class JXFeaturedPost extends ObjectModel
{
    public $id_post;
    public $id_shop;
    public $position;
    public $active;
    public static $definition = array(
        'table'     => 'jxfeaturedposts',
        'primary'   => 'id_featured_post',
        'multilang' => false,
        'fields'    => array(
            'id_post'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'active'   => array('type' => self::TYPE_BOOL, 'validate' => 'isBool')
        )
    );

    public function add($auto_date = true, $null_values = false)
    {
        if ($this->position) {
            $this->position = $this->position - 1;
            $this->increaseOldPositions($this->position);
        } else {
            if ($max = $this->getMaxPosition() === null) {
                $this->position = 0;
            } else {
                $this->position = $max + 1;
            }
        }
        return parent::add($auto_date, $null_values);
    }

    /**
     * Get max position to add new one after this
     *
     * @return false|null|string
     */
    public function getMaxPosition()
    {
        return Db::getInstance()->getValue('SELECT MAX(`position`) FROM `'._DB_PREFIX_.'jxfeaturedposts`');
    }

    /**
     * Increase all old position if we put new item between old ones
     *
     * @param $position
     */
    public function increaseOldPositions($position)
    {
        if (Db::getInstance()->getValue('SELECT * FROM '._DB_PREFIX_.'jxfeaturedposts WHERE `position` = '.(int)$position)) {
            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'jxfeaturedposts SET `position` = `position` + 1 WHERE `position` >= '.(int)$position);
        }
    }

    /**
     * Update positions of all featured posts after any post position was changed
     *
     * @param $way      decrease || increase position
     * @param $position new position
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function updatePosition($way, $position)
    {
        // select all available blog featured products and their positions
        if (!$res = Db::getInstance()->executeS(
            'SELECT `id_featured_post`, `position`
            FROM `'._DB_PREFIX_.'jxfeaturedposts`
            ORDER BY `position` ASC'
        )) {
            return false;
        }
        // mark the featured post which is moving
        $postMoved = false;
        foreach ($res as $post) {
            if ((int)$post['id_featured_post'] == (int)$this->id) {
                $postMoved = $post;
            }
        }
        if ($postMoved === false) {
            return false;
        }
        // update all featured products positions, but not that one which is moving
        $result = Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'jxfeaturedposts
            SET `position` = `position` '.($way ? '-1' : '+1').'
            WHERE `position` '.($way
                ? '> '.(int)$postMoved['position'].' AND `position` <= '.(int)$position
                : '< '.(int)$postMoved['position'].' AND `position` >= '.(int)$position)
        );
        // update position of the moving post
        $result &= Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'jxfeaturedposts`
            SET `position` = '.(int)$position.'
            WHERE `id_featured_post` = '.(int)$postMoved['id_featured_post']
        );

        return $result;
    }

    /**
     * Get all featured posts blog by related post id
     *
     * @param $id_post
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllBlocksByPost($id_post)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'jxfeaturedposts WHERE `id_post` = '.(int)$id_post;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get a list of featured posts for current shop
     *
     * @param $id_shop
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getFeaturedPosts($id_shop)
    {
        $sql = 'SELECT `id_post` FROM '._DB_PREFIX_.'jxfeaturedposts WHERE `id_shop` = '.(int)$id_shop.' AND `active` = 1';

        return Db::getInstance()->executeS($sql);
    }
}
