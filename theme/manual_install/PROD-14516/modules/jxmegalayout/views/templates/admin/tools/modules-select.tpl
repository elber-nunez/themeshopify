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

{if isset($modules_list) && count($modules_list) > 0}
  <h2 class="popup-heading">{l s='Add module' mod='jxmegalayout'}</h2>
  <div class="form-group popup-content">
    <div class="form-group">
      <label>{l s='Specific class' mod='jxmegalayout'}</label>
      <input class="form-control" name="module-classes" value=""/>
    </div>
    <div class="form-group">
      <label>{l s='Select a module' mod='jxmegalayout'}</label>
      <select data-select-id="{$hook_name|escape:'htmlall':'UTF-8'}" name="jxml_module_{$hook_name|escape:'htmlall':'UTF-8'}">
        {foreach from=$modules_list item='module'}
          <option data-origin-hook="{$module.origin_hook|escape:'htmlall':'UTF-8'}" value="{$module.name|escape:'htmlall':'UTF-8'}">{$module.public_name|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
      </select>
    </div>
  </div>
  <div class="popup-btns">
    <a href="#" class="add-module-confirm btn btn-success btn-lg">{l s='Confirm' mod='jxmegalayout'}</a>
  </div>
{else}
  <div class="form-group popup-content no-border">
    {l s='There are no more modules for this position in this layout' mod='jxmegalayout'}
  </div>
{/if}