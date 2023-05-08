{**
* 2017-2018 Zemez
*
* JX Accelerated Mobile Page
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
*}

<section>
  <h4 class="depth-{$depth} menu-item">
    <a href="{$link->getCategoryLink($category.id_category, $category.link_rewrite)}">{$category.name}</a>
    {if isset($category.children) && $category.children|count > 0}
      <i class="fa fa-chevron-down show"></i>
      <i class="fa fa-chevron-up hide"></i>
    {/if}
  </h4>
  <div>
    {if isset($category.children) && $category.children|count > 0}
      <amp-accordion disable-session-states>
        {foreach from=$category.children item='children'}
          {include file='module:jxamp/views/templates/front/amp/blocks/_partials/category_tree_branch.tpl' category=$children depth=$depth + 1}
        {/foreach}
      </amp-accordion>
    {/if}
  </div>
</section>