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

<div id="jxmegalayout-product-info-pages" class="jxmegalayout-product-info-pages panel clearfix">
  <div class="bootstrap">
    <ul class="row">
      {foreach from=$themes key=name item='theme'}
        <li class="col-xs-12 col-sm-6 col-md-4 col-lg-2{if $active_template == $name} active{/if}{if !$active_template && $theme.default} active{/if}">
          <h6>{$theme.name|escape:'html':'UTF-8'}</h6>
          <a data-theme-name="{$name|escape:'html':'UTF-8'}" href="#">
            {if $theme.preview}
              <img class="img-responsive" src='../themes/{$cur_theme|escape:'html':'UTF-8'}/templates/catalog/product_pages/{$theme.preview|escape:'html':'UTF-8'}' alt="{$theme.name|escape:'html':'UTF-8'}">
            {else}
              <span class="no-prview-image">
                {l s='No preview image' mod='jxmegalayout'}
              </span>
            {/if}
          </a>
        </li>
      {/foreach}
    </ul>
  </div>
</div>