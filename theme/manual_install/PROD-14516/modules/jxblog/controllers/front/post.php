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

class JxblogPostModuleFrontController extends ModuleFrontController
{
    public $pagename = 'jxblogpost';
    public $post;
    public function __construct()
    {
        parent::__construct();
        $this->post = new JXBlogPost(Tools::getValue('id_jxblog_post'), $this->context->language->id);
    }

    public function initContent()
    {
        parent::initContent();
        JXBlogPost::postViewed($this->post->id);
        $post = JXBlogPost::getPost($this->post->id, $this->context->language->id, $this->context->shop->id, $this->context->customer->id_default_group);
        if ($post) {
            $post = $post[0];
        }
        $this->context->smarty->assign(
            array(
                'post' => $post,
                'tags' => JXBlogPost::getPostTags($this->post->id, $this->context->language->id),
                'displayViews' => Configuration::get('JXBLOG_DISPLAY_POST_VIEWS'),
                'displayAuthor' => Configuration::get('JXBLOG_DISPLAY_POST_AUTHOR')
            )
        );
        $this->setTemplate('module:jxblog/views/templates/front/post.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $link = new Link();
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array('title' => $this->trans('Blog categories', array(), 'Modules.JXBlog.Admin'), 'url' => $link->getModuleLink('jxblog', 'categories'));
        $breadcrumb['links'][] = array('title' => $this->post->name, 'url' => $link->getModuleLink('jxblog', 'post', array('id_jxblog_post' => $this->post->id, 'rewrite' => $this->post->link_rewrite)));

        return $breadcrumb;
    }
}
