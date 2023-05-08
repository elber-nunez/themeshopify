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

class JXBlogComments extends ObjectModel
{
    public $id_post;
    public $id_parent;
    public $id_customer;
    public $id_guest;
    public $id_admin;
    public $active;
    public $date_add;
    public $date_update;
    public $upvote_count;
    public $content;
    public $is_new;
    public $pings;
    public $image_name;
    public $image_type;
    static $list = array();
    public static $definition = array(
        'table'     => 'jxblog_comment',
        'primary'   => 'id_jxblog_comment',
        'multilang' => false,
        'fields'    => array(
            'id_post'      => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_parent'    => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_guest'     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_admin'     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'active'       => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add'     => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_update'  => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'upvote_count' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'is_new'       => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'content'      => array('type' => self::TYPE_HTML, 'lang' => false, 'validate' => 'isCleanHTMl', 'size' => 4000),
            'pings'        => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isString'),
            'image_name'   => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isString'),
            'image_type'   => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isString')
        )
    );

    public function delete()
    {
        $jxblogcomment = new Jxblogcomment();
        $result = true;
        if ($this->image_name && file_exists($jxblogcomment->attachmentsPath.$this->image_name)) {
            unlink($jxblogcomment->attachmentsPath.$this->image_name);
        }
        $result &= $this->recursiveDelete($this->id);
        $result &= parent::delete();
        return $result;
    }

    /**
     * Get all comments related to the post.
     * Don't check the shop id because we suppose if post is shared between shops
     * a comment should do it too
     *
     * @param $id_post
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllComments($id_post)
    {
        $sql = 'SELECT *
                FROM '._DB_PREFIX_.'jxblog_comment
                WHERE `id_post` = '.(int)$id_post.'
                AND `active` = 1';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get all comment votes
     * @param $id_comment
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getCommentVotes($id_comment)
    {
        $sql = 'SELECT `id_jxblog_comment`
                FROM '._DB_PREFIX_.'jxblog_comment_vote
                WHERE `id_jxblog_comment` = '.(int)$id_comment;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Update comment votes in main table and in related table
     * where we keep information about users who have already voted for the comment
     * to avoid multiple commenting and implement a decreasing of voting if a user has changed his opinion.
     * Have two types of users
     *
     * @param $id_user
     * @param $guest
     *
     * @return mixed
     * @throws PrestaShopException
     */
    public function updateCommentVotes($id_user, $guest)
    {
        if (!$this->checkUserCommentVote($id_user, $guest)) {
            $this->addVote($id_user, $guest);
            $this->upvote_count = $this->upvote_count + 1;
            $this->update();
            return $this->upvote_count;
        } else {
            $this->removeVote($id_user, $guest);
            $this->upvote_count = $this->upvote_count - 1;
            $this->update();
            return $this->upvote_count;
        }
    }

    /**
     * Check whether user has voted or not
     * @param      $id_user
     * @param bool $guest
     *
     * @return false|null|string
     */
    public function checkUserCommentVote($id_user, $guest = false)
    {
        $condition = ' AND `id_customer` = '.(int)$id_user;
        if ($guest) {
            $condition = ' AND `id_guest` = '.(int)$id_user;
        }
        $sql = 'SELECT `id_jxblog_comment`
                FROM '._DB_PREFIX_.'jxblog_comment_vote
                WHERE `id_jxblog_comment` = '.(int)$this->id
            .$condition;

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Add entry if user is voting at the first time
     *
     * @param $id_user
     * @param $guest
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function addVote($id_user, $guest)
    {
        if ($guest) {
            return Db::getInstance()->insert(
                'jxblog_comment_vote',
                array('id_jxblog_comment' => (int)$this->id, 'id_guest' => (int)$id_user)
            );
        } else {
            return Db::getInstance()->insert(
                'jxblog_comment_vote',
                array('id_jxblog_comment' => (int)$this->id, 'id_customer' => (int)$id_user)
            );
        }
    }

    /**
     * Remove entry if user is voting not at the first time
     *
     * @param $id_user
     * @param $guest
     *
     * @return bool
     */
    public function removeVote($id_user, $guest)
    {
        if ($guest) {
            return Db::getInstance()->delete(
                'jxblog_comment_vote',
                '`id_guest` ='.(int)$id_user.' AND `id_jxblog_comment` = '.(int)$this->id
            );
        } else {
            return Db::getInstance()->delete(
                'jxblog_comment_vote',
                '`id_customer` ='.(int)$id_user.' AND `id_jxblog_comment` = '.(int)$this->id
            );
        }
    }

    /**
     * Delete all related posts in the all depth
     * @param $id_comment
     *
     * @return bool
     */
    public function recursiveDelete($id_comment)
    {
        if (!$id_comment) {
            return true;
        }
        $comments = $this->getRelatedComments($id_comment);
        foreach ($comments as $comment) {
            $commentToDelete = new JXBlogComments($comment['id_jxblog_comment']);
            $commentToDelete->delete();
            $this->recursiveDelete($comment['id_jxblog_comment']);
        }

        return true;
    }

    /**
     * Get posts related to current
     *
     * @param $id_comment
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getRelatedComments($id_comment)
    {
        $sql = 'SELECT `id_jxblog_comment`
                FROM '._DB_PREFIX_.'jxblog_comment
                WHERE `id_parent` = '.(int)$id_comment;

        return Db::getInstance()->executeS($sql);
    }

    public function getAllPostCommentsByID($id_post)
    {
        return Db::getInstance()->executeS('SELECT `id_jxblog_comment` FROM '._DB_PREFIX_.'jxblog_comment WHERE `id_post` = '.(int)$id_post);
    }
}
