{*
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
*}

{extends file="helpers/form/form.tpl"}

{block name="script"}
	var ps_force_friendly_product = false;
{/block}

{block name="input"}
	{if $input.name == "link_rewrite"}
		<script type="text/javascript">
		{if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
			var PS_ALLOW_ACCENTED_CHARS_URL = 1;
		{else}
			var PS_ALLOW_ACCENTED_CHARS_URL = 0;
		{/if}
		</script>
		{$smarty.block.parent}
	{elseif $input.type == "cat_list"}
		<div id="category_block">
			<div class="panel">
				<ul id="associated-categories-tree" class="cattree tree">
					{foreach from=$input.categories item='category' name='categories_loop'}
						<li class="tree-folder">
							<span class="tree-folder-name">
								<input type="radio" {if $input.id_jxblog_category_default && $input.id_jxblog_category_default == $category.id_jxblog_category}checked="checked"{elseif $smarty.foreach.categories_loop.iteration == 1}checked="checked"{/if} name="id_jxblog_category_default" value="{$category.id_jxblog_category}" />
								<input id="jxcategoryBox{$category.id_jxblog_category}" {if $input.related_categories && $category.id_jxblog_category|in_array:$input.related_categories}checked="checked"{/if} type="checkbox" name="jxcategoryBox[]" value="{$category.id_jxblog_category}" />
								<label class="tree-toggler"> {$category.name}</label>
							</span>
						</li>
					{/foreach}
			</div>
		</div>
	{elseif $input.type == "autocomplete"}
		{if isset($input.url) && $input.url}
			<input type="hidden" name="ajax_url" value="{$input.url}" />
		{/if}
		{assign var="type" value="related_`$input.id`"}
		<input id="input{$input.id}" name="input{$input.id}" value="{if $fields_value[$type]}{foreach from=$fields_value[$type] item='item'}{$item.id}-{/foreach}{else}-{/if}" type="hidden">
		<input id="name{$input.id}" name="name{$input.id}" value="{if $fields_value[$type]}{foreach from=$fields_value[$type] item='item'}{$item.name}¤{/foreach}{else}¤{/if}" type="hidden">
		<div id="ajax_choose_{$input.id}">
			<div class="input-group">
				<input type="text" id="{$input.id}_autocomplete_input" name="{$input.id}_autocomplete_input"/>
				<span class="input-group-addon"><i class="icon-search"></i></span>
			</div>
		</div>
		<div id="div{$input.id}">
			{if $fields_value[$type]}
				{foreach from=$fields_value[$type] item='item'}
					<div class="form-control-static">
						<button type="button" class="btn btn-default del{$input.id}" name="{$item.id}">
							<i class="icon-remove text-danger"></i>
						</button>
						{$item.name}
					</div>
				{/foreach}
			{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
