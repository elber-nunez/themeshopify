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

<div
        class="content sortable {$elem.id_unique|escape:'html':'UTF-8'} {if isset($elem.warning)}not-active{/if}"
        {if $preview == false}
          data-type="content"
          data-id="{$elem.id_item|escape:'html':'UTF-8'}"
          data-parent-id="{$elem.id_parent|escape:'html':'UTF-8'}"
          data-extra-content="{$elem.module_name|escape:'html':'UTF-8'}"
          data-sort-order="{$elem.sort_order|escape:'html':'UTF-8'}"
          data-specific-class="{$elem.specific_class|escape:'html':'UTF-8'}"
          data-id-unique="{$elem.id_unique|escape:'html':'UTF-8'}"
        {/if}>
  <article class="content-inner clearfix inner">
    {if isset($elem.warning)}<p class="alert alert-warning">{$elem.warning|escape:'quotes':'UTF-8'}</p>{/if}
    <div class="button-container clearfix">
      <span {if $preview == false}data-toggle="tooltip" data-placement="right" title="{$elem.module_name|escape:'htmlall':'UTF-8'}" class="content-name"{/if}>
        <span class="content-text">
          {l s='Extra content:' mod='jxmegalayout'} {if $info && isset($info.name)}{$info.name}{else}{$elem.module_name|escape:'htmlall':'UTF-8'}{/if}
        </span>
        <span class="identificator">{if $elem.specific_class}({$elem.specific_class|escape:'htmlall':'UTF-8'|replace:' ':' | '}){/if}</span>
      </span>
      {if $preview == false}
        <div class="dropdown button-container pull-right">
          <a href="#" id="dropdownMenu-{$elem.id_unique|escape:'html':'UTF-8'}" class="dropdown-toggle" aria-expanded="true" aria-haspopup="true" data-toggle="dropdown" type="button"></a>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenu-{$elem.id_unique|escape:'htmlall':'UTF-8'}">
            {if !isset($elem.warning)}
              <li><a href="#" class="edit-extra-content">{l s='Edit settings' mod='jxmegalayout'}</a></li>
            {/if}
            <li><a href="#" class="remove-item">{l s='Delete' mod='jxmegalayout'}</a></li>
          </ul>
        </div>
      {/if}
    </div>
  </article>
</div>
