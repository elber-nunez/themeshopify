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

{if isset($tree) && $tree}
  {foreach from=$tree item='branch'}
    <li class="category{if isset($page.page_name) && $page.page_name == 'category' && $id_selected == $branch.id_category} sfHoverForce{/if}">
      {assign var='url' value=$link->getPageLink('index')}
      {if $branch.level_depth > 1}
        {assign var='url' value=$link->getCategoryLink($branch.id_category)}
      {/if}
      <a href="{$url}" title="{$branch.name}">{$branch.name}</a>
      {if isset($branch.children) && $branch.children}
        {include file='./categories-tree-branch.tpl' items=$branch.children id_selected=$id_selected}
      {/if}
    </li>
  {/foreach}
{/if}