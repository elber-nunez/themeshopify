{*
* 2017-2018 Zemez
*
* JX Deal of Day
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
*  @author    Zemez (Sergiy Sakun)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file="helpers/form/form.tpl"}

{block name="field"}
  {if $input.type == 'block_specific'}
    {addJsDefL name=jxdd_msg}{l s='This product has specific price' mod='jxdaydeal'}{/addJsDefL}
    {addJsDefL name=jxdd_msg_period}{l s='Period:' mod='jxdaydeal'}{/addJsDefL}
    {addJsDefL name=jxdd_msg_sale}{l s='Sale:' mod='jxdaydeal'}{/addJsDefL}
    {addJsDefL name=jxdd_msg_use}{l s='use' mod='jxdaydeal'}{/addJsDefL}
    {addJsDefL name=jxdd_msg_included}{l s='tax included' mod='jxdaydeal'}{/addJsDefL}
    {addJsDefL name=jxdd_msg_excluded}{l s='tax excluded' mod='jxdaydeal'}{/addJsDefL}
    <div class="daydeal-alert-container">
      {foreach from=$specific_prices_data item=specific_prices name=specific_prices}
        <div class="daydeal-prices alert alert-warning">
          <p>{l s='This product has specific price' mod='jxdaydeal'}</p>
          <p>{l s='Period:' mod='jxdaydeal'}&nbsp;{$specific_prices['from']|escape:'htmlall':'UTF-8'} - {$specific_prices['to']|escape:'htmlall':'UTF-8'}</p>
          <p>{l s='Sale:' mod='jxdaydeal'}&nbsp;{$specific_prices['reduction']|escape:'htmlall':'UTF-8'}&nbsp;{$specific_prices['reduction_type']|escape:'htmlall':'UTF-8'}
          {if $specific_prices['reduction_type'] == 'amount'},&nbsp;
            {if $specific_prices['reduction_tax'] == 1}
              {l s='tax included' mod='jxdaydeal'}
            {else}
              {l s='tax excluded' mod='jxdaydeal'}
            {/if}
          {/if}
          </p>
          {if !$specific_prices['status']}
          <label>
            <input type="checkbox" class="daydeal-checkbox" value="{$specific_prices['id_specific_price']|escape:'htmlall':'UTF-8'}" name="specific_price_old" />
            {l s='use' mod='jxdaydeal'}
          </label>
        {/if}
        </div>
      {/foreach}
    </div>
  {/if}
  {if $input.type == 'custom_autocomplete'}
    <div class="col-lg-3">
      {assign var="type" value="related_`$input.id`"}
      <input id="input{$input.id}" name="{$input.name}" value="{if $fields_value['id_product']}{$fields_value['id_product']}{else}-{/if}" type="hidden">
      <input id="name{$input.id}" name="name{$input.id}" value="{if $fields_value['id_product']}{Product::getProductName($fields_value['id_product'])}{else}¤{/if}" type="hidden">
      <div id="ajax_choose_{$input.id}" {if $fields_value['id_product']}style="display: none;"{/if}>
        <div class="input-group">
          <input type="text" id="{$input.id}_autocomplete_input" name="{$input.id}_autocomplete_input"/>
          <span class="input-group-addon"><i class="icon-search"></i></span>
        </div>
      </div>
      <div id="div{$input.id}">
        {if $fields_value['id_product']}
          <div class="form-control-static">
            <button type="button" class="btn btn-default del{$input.id}" name="{$fields_value['id_product']}">
              <i class="icon-remove text-danger"></i>
            </button>
            {Product::getProductName($fields_value['id_product'])}
          </div>
        {/if}
      </div>
    </div>
  {/if}
{$smarty.block.parent}
{/block}
