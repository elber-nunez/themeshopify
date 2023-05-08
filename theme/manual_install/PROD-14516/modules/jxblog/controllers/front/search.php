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

class JxblogSearchModuleFrontController extends ModuleFrontController
{
    public $pagename = 'search';
    public $query;
    public $category;
    public $page = 1;
    public $itemPerPage = 6;

    public function __construct()
    {
        if (Tools::getIsset('page') && $page = Tools::getValue('page')) {
            $this->page = $page;
        }
        parent::__construct();
        $this->itemPerPage = Configuration::get('JXBLOG_POSTS_PER_PAGE');
        $this->query = Tools::getValue('blog_search_query');
        $this->category = Tools::getValue('search_blog_categories');
    }

    public function initContent()
    {
        $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('q')));
        if (Tools::getValue('ajax')) {
            $this->getAjaxSearch($query);
        } else {
            parent::initContent();
            $pagination = false;
            $posts = false;
            if ($this->query) {
                $posts = JXBlogPost::search(
                    $this->query,
                    $this->category,
                    $this->context->language->id,
                    $this->context->shop->id,
                    $this->context->customer->id_default_group,
                    $this->page,
                    $this->itemPerPage
                );
                $pagination = $this->module->buildPagination(
                    'spagination',
                    JXBlogPost::countPostsBySearch(
                        $this->query,
                        $this->category,
                        $this->context->language->id,
                        $this->context->shop->id,
                        $this->context->customer->id_default_group
                    ),
                    $this->page,
                    $this->itemPerPage,
                    '',
                    ''
                );
                $currentCategoryName = '';
                if ($this->category) {
                    $currentCategory = new JXBlogCategory($this->category, $this->context->language->id);
                    $currentCategoryName = $currentCategory->name;
                }
            }
            $this->context->smarty->assign(
                array(
                    'blog_search_query' => $this->query,
                    'posts' => $posts,
                    'pagination' => $pagination,
                    'active_blog_category' => $currentCategoryName,
                    'displayViews' => Configuration::get('JXBLOG_DISPLAY_POST_VIEWS'),
                    'displayAuthor' => Configuration::get('JXBLOG_DISPLAY_POST_AUTHOR')
                )
            );

            $this->setTemplate('module:jxblog/views/templates/front/search.tpl');
        }
    }

    public function getBreadcrumbLinks()
    {
        $link = new Link();
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array('title' => $this->trans('Blog categories', array(), 'Modules.JXBlog.Admin'), 'url' => $link->getModuleLink('jxblog', 'categories'));

        return $breadcrumb;
    }

    protected function getAjaxSearch($query)
    {
        $id_category = (int)Tools::getValue('category');
        $posts = JXBlogPost::search(
            $query,
            $id_category,
            $this->context->language->id,
            $this->context->shop->id,
            $this->context->customer->id_default_group,
            1,
            1000
        );
        $output = array();
        if (count($posts)) {
            $jxsearch = new Jxsearch();
            $link = new Link();
            foreach ($posts as $post) {
                $this->context->smarty->assign('post', array(
                    'info' => $post,
                    'url' => $link->getModuleLink('jxblog', 'post', array('id_jxblog_post' => $post['id_jxblog_post'], 'rewrite' => $post['link_rewrite'])),
                ));
                $output[] = $jxsearch->display($jxsearch->getLocalPath(), '/views/templates/hook/_items/row-blog.tpl');
            }
        }
        if (!count($output)) {
            die(json_encode(array('empty' => $this->trans('No post found'))));
        }

        $total = count($output);

        die(json_encode(array('result' => $output, 'total' => $total)));
    }
}
