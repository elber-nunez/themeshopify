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

{if isset($menu) && $menu}
  {if $hook == 'left_column' || $hook == 'right_column'}
    <section class="block">
    <h4 class="title_block">{l s='Menu' mod='jxmegamenu'}</h4>
    <div class="block_content {$hook}_menu column_menu top-level jxmegamenu_item">
  {else}
    <div class="{$hook}_menu top-level jxmegamenu_item">
    <div class="menu-title jxmegamenu_item">{l s='Menu' mod='jxmegamenu'}</div>
  {/if}
  <ul class="menu clearfix top-level-menu jxmegamenu_item">
    {foreach from=$menu key=id item='item'}
      <li class="{$item.specific_class}{if $item.is_simple} simple{/if} top-level-menu-li jxmegamenu_item {$item.unique_code} {if isset($item.selected) && $item.selected}{$item.selected}{/if}">
        {if $item.url}
          <a class="{$item.unique_code} top-level-menu-li-a jxmegamenu_item" href="{$item.url}">
        {else}
          <span class="{$item.unique_code} top-level-menu-li-span jxmegamenu_item">
        {/if}
          {if $item.title}{$item.title}{/if}
            {if $item.badge}
              <span class="menu_badge {$item.unique_code} top-level-badge jxmegamenu_item">{$item.badge}</span>
            {/if}
        {if $item.url}
          </a>
        {else}
          </span>
        {/if}
        {if $item.is_simple}
          <ul class="is-simplemenu jxmegamenu_item first-level-menu {$item.unique_code}">
            {if isset($item.submenu)}
              {$item.submenu nofilter}
            {/if}
          </ul>
        {/if}
        {if $item.is_mega}
          <div class="is-megamenu jxmegamenu_item first-level-menu {$item.unique_code}">
            {if isset($item.submenu)}
              {foreach from=$item.submenu key='id_row' item='row'}
                <div id="megamenu-row-{$id}-{$id_row}" class="megamenu-row row megamenu-row-{$id_row}">
                  {if isset($row)}
                    {foreach from=$row item='col'}
                      <div id="column-{$id}-{$id_row}-{$col.col}" class="megamenu-col megamenu-col-{$id_row}-{$col.col} col-sm-{$col.width} {$col.class}">
                        <ul class="content">
                          {$col.content nofilter}
                        </ul>
                      </div>
                    {/foreach}
                  {/if}
                </div>
              {/foreach}
            {/if}
          </div>
        {/if}
      </li>
    {/foreach}
  </ul>
  {if $hook == 'left_column' || $hook == 'right_column'}
    </div>
  </section>
  {else}
    </div>
  {/if}
{/if}