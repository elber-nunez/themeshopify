{**
* 2017-2018 Zemez
*
* TМ Homepage Combinations
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
* @author    Zemez
* @copyright 2017-2018 Zemez
* @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
  {if $input.type == 'radio' and $input.name == 'JXHEADERACCOUNT_DISPLAY_STYLE'}
    <div class="col-lg-9 radio-image">
      {foreach $input.values as $value}
        <div class="radio {if isset($input.class)}{$input.class}{/if}">
          {strip}
            <label>
              <input type="radio"	name="{$input.name}" id="{$value.id}" value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
              <img width="50px" height="50px" src="{$value.img_link}" alt="">
              {$value.label}
            </label>
          {/strip}
        </div>
        {if isset($value.p) && $value.p}<p class="help-block">{$value.p}</p>{/if}
      {/foreach}
    </div>
  {else}
    {$smarty.block.parent}
  {/if}
{/block}