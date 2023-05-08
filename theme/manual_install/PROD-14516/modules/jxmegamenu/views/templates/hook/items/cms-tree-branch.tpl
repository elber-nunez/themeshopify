{*
* 2017-2018 Zemez
*
* JX Mega Menu
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
*}

{if isset($items) && $items}
  <ul>
    {foreach from=$items item='item'}
      <li class="cms-category{if isset($page.page_name) && $page.page_name == 'cms' && $id_selected == $item.id_cms_category} sfHoverForce{/if}">
        <a href="{$link->getCMSCategoryLink($item.id_cms_category)}" title="{$item.title}">{$item.title}</a>
        {if isset($item.children) && $item.children}
          {include file='./cms-tree-branch.tpl' items=$item.children id_selected=$id_selected id_selected_page=id_selected_page}
        {/if}
      </li>
      {if isset($item.pages) && $item.pages}
        {foreach from=$item.pages item='page'}
          <li class="cms-page{if isset($page.page_name) && $page.page_name == 'cms' && $id_selected_page == $page.id} sfHoverForce{/if}">
            <a href="{$link->getCMSLink($page.id)}" title="{$page.name}">{$page.name}</a>
          </li>
        {/foreach}
      {/if}
    {/foreach}
  </ul>
{/if}