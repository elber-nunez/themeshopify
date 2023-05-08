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

use PrestaShop\PrestaShop\Core\Product\Search\Pagination;

class JxblogCategoriesModuleFrontController extends ModuleFrontController
{
    public $pagename = 'categories';
    public $itemPerPage = 6;
    public $page = 1;
    public function __construct()
    {
        if (Tools::getIsset('page') && $page = Tools::getValue('page')) {
            $this->page = $page;
        }
        parent::__construct();
        $this->itemPerPage = Configuration::get('JXBLOG_POSTS_PER_PAGE');
    }

    public function initContent()
    {
        parent::initContent();
        $pagination = false;
        $categories = JXBlogCategory::getAllFrontCategories($this->context->language->id, $this->context->shop->id, $this->context->customer->id_default_group, $this->page, $this->itemPerPage);
        if ($categories) {
            $pagination = $this->module->buildPagination(
                'cpagination',
                JXBlogCategory::countFrontCategories($this->context->shop->id, $this->context->customer->id_default_group),
                $this->page,
                $this->itemPerPage
            );
        }

        $this->context->smarty->assign(
            array(
                'categories' => $categories,
                'pagination' => $pagination
            )
        );
        $this->setTemplate('module:jxblog/views/templates/front/categories.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array('title' => $this->trans('Blog categories', array(), 'Modules.JXBlog.Admin'), 'url' => '');

        return $breadcrumb;
    }
}
