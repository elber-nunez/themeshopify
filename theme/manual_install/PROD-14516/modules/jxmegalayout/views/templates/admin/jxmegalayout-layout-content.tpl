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

<div {if isset($content.layout)}data-layout-id="{$content.id_layout|escape:'htmlall':'UTF-8'}"{/if} class="jxmegalayout-admin container">
  {if isset($content.layout)}
    <div class="jxlayout-row">
      <span class="jxmlmegalayout-layout-name">{$content.layout_name|escape:'htmlall':'UTF-8'}</span>
      <a data-layout-id="{$content.id_layout|escape:'htmlall':'UTF-8'}" href="#" class="edit-layout"></a>
      <a data-layout-id="{$content.id_layout|escape:'htmlall':'UTF-8'}" href="#" class="remove-layout"></a>
    </div>
    <article class="inner">
      {$content.layout|escape:'quotes':'UTF-8'}
      <p class="add-buttons">
        <span class="col-xs-12 col-sm-6 add-but">
          <a href="#" class="btn add-wrapper min-level">+ {l s='Add wrapper' mod='jxmegalayout'}</a>
        </span>
        <span class="col-xs-12 col-sm-6 add-but">
          <a href="#" class="btn add-row  min-level">+ {l s='Add row' mod='jxmegalayout'}</a>
        </span>
      </p>
    </article>
    <input type="hidden" name="jxml_id_layout" value="{$content.id_layout|escape:'htmlall':'UTF-8'}"/>
  {else}
    {if $content.layouts_list}
      <p class="alert alert-info">{l s='Select a layout' mod='jxmegalayout'}</p>
    {else}
      <p class="alert alert-info">{l s='Add a layout' mod='jxmegalayout'}</p>
    {/if}
  {/if}
</div>