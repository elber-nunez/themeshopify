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
 *  @author    Zemez (Alexander Grosul)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JxblogAuthorModuleFrontController extends ModuleFrontController
{
    public $pagename = 'author';
    public $author;
    public $page = 1;
    public $itemPerPage = 6;

    public function __construct()
    {
        if (Tools::getIsset('page') && $page = Tools::getValue('page')) {
            $this->page = $page;
        }
        parent::__construct();
        $this->itemPerPage = Configuration::get('JXBLOG_POSTS_PER_PAGE');
        $this->author = new Employee(Tools::getValue('author'));
    }

    public function initContent()
    {
        parent::initContent();
        $pagination = false;
        $posts = false;
        if ($this->author) {
            $posts = JXBlogPost::getPostsByAuthor($this->author->id, $this->context->language->id, $this->page, $this->itemPerPage);
            $pagination = $this->module->buildPagination(
                'apagination',
                JXBlogPost::countPostsByAuthor($this->author->id),
                $this->page,
                $this->itemPerPage,
                $this->author->id,
                ''
            );
        }
        $this->context->smarty->assign(
            array(
                'author' => $this->author,
                'posts' => $posts,
                'pagination' => $pagination,
                'displayViews' => Configuration::get('JXBLOG_DISPLAY_POST_VIEWS'),
                'displayAuthor' => Configuration::get('JXBLOG_DISPLAY_POST_AUTHOR')
            )
        );

        $this->setTemplate('module:jxblog/views/templates/front/author.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $link = new Link();
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array('title' => $this->trans('Blog categories', array(), 'Modules.JXBlog.Admin'), 'url' => $link->getModuleLink('jxblog', 'categories'));
        $breadcrumb['links'][] = array('title' => $this->author->firstname.' '.$this->author->lastname, 'url' => $link->getModuleLink('jxblog', 'author', array('author' => $this->author->id)));

        return $breadcrumb;
    }
}
