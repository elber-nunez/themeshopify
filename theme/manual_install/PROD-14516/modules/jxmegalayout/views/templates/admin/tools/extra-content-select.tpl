{**
* 2017-2018 Zemez
*
* JX Mega Layout
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
*  @author    Zemez (Alexander Grosul & Alexander Pervakov)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if isset($content_list) && count($content_list) > 0}
  <h2 class="popup-heading">{l s='Add Extra content' mod='jxmegalayout'}</h2>
  <div class="form-group popup-content">
    <div class="form-group">
      <label>{l s='Specific class' mod='jxmegalayout'}</label>
      <input class="form-control" name="extra-content-classes" value=""/>
    </div>
    <div class="form-group">
      <label>{l s='Select content type' mod='jxmegalayout'}</label>
      <select name="extra_content_type">
        {foreach from=$content_list key='type' item='content'}
          {if $content}
            <option value="{$type}">{$type}</option>
          {/if}
          {if $type == 'product' || $type == 'post'}
            <option value="{$type}">{$type}</option>
          {/if}
        {/foreach}
      </select>
    </div>
    <div class="form-group">
      <label>{l s='Select/enter ID item' mod='jxmegalayout'}</label>
      {assign var='has_selected' value=false}
      {foreach from=$content_list key='type' name='loop' item='content'}
        {if $content}
          <select class="extra-content-type-selector{if $has_selected} hidden{/if}" name="{$type}">
            {foreach from=$content item='item'}
              <option value="{$item.id}">{$item.name}</option>
            {/foreach}
          </select>
          {assign var='has_selected' value=true}
        {/if}
        {if $type == 'product' || $type == 'post'}
          <input class="extra-content-type-selector {if $has_selected}hidden{else}{assign var='has_selected' value=true}{/if}" type="text" name="{$type}" />
        {/if}
      {/foreach}
    </div>
  </div>
  <div class="popup-btns">
    <a href="#" class="add-extra-content-confirm btn btn-success btn-lg">{l s='Confirm' mod='jxmegalayout'}</a>
  </div>
{else}
  <div class="form-group popup-content no-border">
    {l s='There are no extra content yet' mod='jxmegalayout'}
  </div>
{/if}
