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

{if isset($content.layout)}
  <p {if isset($content.layout)}data-layout-id="{$content.id_layout|escape:'htmlall':'UTF-8'}"{/if} class="jxlist-layout-btns pull-left">
    <a data-layout-id="{$content.id_layout|escape:'htmlall':'UTF-8'}" href="#" class="{if $content.status || $content.partly_use}hidden{/if} layout-btn use-layout"><i class="process-icon-toggle-off"></i> {l s='Use as default' mod='jxmegalayout'}
    </a>
    <a data-layout-id="{$content.id_layout|escape:'htmlall':'UTF-8'}" href="#" class="{if !$content.status && !$content.partly_use}hidden{/if} layout-btn disable-layout"><i class="process-icon-toggle-on"></i> {l s='Use as default' mod='jxmegalayout'}
    </a>
  </p>
  {if Jxmegalayout::displayAllPagesHook($content.hook_name)}
    <select class="jxmegalayout-availible-pages" multiple="multiple" name="jxmegalayout-availible-pages">
      {foreach from=$content.pages_list key=page_name item=item_status}
        <option {if $item_status}selected="selected"{/if} value="{$page_name|escape:'htmlall':'UTF-8'}">{$page_name|escape:'htmlall':'UTF-8'}</option>
      {/foreach}
    </select>
  {/if}
{/if}