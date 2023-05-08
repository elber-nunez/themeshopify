{*
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
*}

{extends file="helpers/form/form.tpl"}

{block name="field"}
  {if $input.type == 'posts_tree'}
    {if $input.posts}
      <div class="col-lg-9">
        <div class="panel">
          {*<div class="tree-panel-heading-controls clearfix">
            <div class="tree-actions pull-right">
              <a href="#" onclick="$('#categories-tree').tree('collapseAll');$('#collapse-all-categories-tree').hide();$('#expand-all-categories-tree').show(); return false;" id="collapse-all-categories-tree" class="btn btn-default">
                <i class="icon-collapse-alt"></i> {l s='Collapse All' mod='jxfeaturedposts'}
              </a>
              <a href="#" onclick="$('#categories-tree').tree('expandAll');$('#collapse-all-categories-tree').show();$('#expand-all-categories-tree').hide(); return false;" id="expand-all-categories-tree" class="btn btn-default">
                <i class="icon-expand-alt"></i> {l s='Expand All' mod='jxfeaturedposts'}
              </a>
            </div>
          </div>*}
          <ul id="categories-tree" class="cattree tree">
            {if !$fields_value.id_post}
              {assign var="selected_post" value=false}
            {else}
              {assign var="selected_post" value=$fields_value.id_post}
            {/if}
            {foreach from=$input.posts item='category'}
              <li class="tree-folder">
                  <span class="tree-folder-name">
                    <i class="icon-folder-open"></i>
                    <label class="tree-toggler">-{$category.name}</label>
                  </span>
                {if $category.posts}
                  <ul class="tree">
                    {foreach from=$category.posts item='post'}
                      {if !$selected_post}
                        {assign var="selected_post" value=$post.id_jxblog_post}
                      {/if}
                      <li class="tree-item">
                        <span class="tree-item-name">
                          <label class="tree-toggler"><input name="id_post" value="{$post.id_jxblog_post}" {if $post.id_jxblog_post == $selected_post}checked{/if} type="radio">{$post['name']}</label>
                        </span>
                      </li>
                    {/foreach}
                  </ul>
                {/if}
              </li>
            {/foreach}
          </ul>
          <input type="hidden" name="id_shop" value="{$id_shop}" />
        </div>
      </div>
    {else}
      <div class="col-lg-5">
        <div class="alert alert-warning">
          {l s='There are no posts to select' mod='jxfeaturedposts'}
        </div>
      </div>
    {/if}
  {/if}
  {$smarty.block.parent}
{/block}