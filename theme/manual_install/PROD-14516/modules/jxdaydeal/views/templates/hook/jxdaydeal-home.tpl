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

<section id="products" class="daydeal-products clearfix">
  <h1 class="h1 products-section-title text-uppercase ">{l s='Deal of the day' mod='jxdaydeal'}</h1>
  <div class="products">
    {if isset($daydeal_products) && $daydeal_products}
      {foreach from=$daydeal_products item=product name=product}
        <article class="product-miniature js-product-miniature" data-id-product="{$product.info.id_product}" data-id-product-attribute="{$product.info.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
          <div class="thumbnail-container">
            {block name='product_thumbnail'}
              <a class="thumbnail product-thumbnail" href="{$product.info.url}">
                <img class="replace-2x img-responsive" src="{$product.info.cover.bySize.home_default.url}" alt = "{$product.info.cover.legend}" data-full-size-image-url = "{$product.info.cover.large.url}" />
                {if isset($daydeal_products_extra[$product.info.id_product]["label"]) && $daydeal_products_extra[$product.info.id_product]["label"]}
                  <span class="label-daydeal">{$daydeal_products_extra[$product.info.id_product]["label"]|escape:'htmlall':'UTF-8'}</span>
                {/if}
              </a>
            {/block}
            <div class="product-description">
              {block name='product_name'}
                <h1 class="h3 product-title" itemprop="name"><a href="{$product.info.url}">{$product.info.name|truncate:30:'...'}</a></h1>
              {/block}
              {block name='product_price_and_shipping'}
                {if $product.info.show_price}
                  <div class="product-price-and-shipping">
                    {if $product.info.has_discount}
                      {hook h='displayProductPriceBlock' product=$product.info type="old_price"}
                      <span class="regular-price">{$product.info.regular_price}</span>
                      {if $product.info.discount_type === 'percentage'}
                        <span class="discount-percentage">{$product.info.discount_percentage}</span>
                      {/if}
                    {/if}
                    {hook h='displayProductPriceBlock' product=$product.info type="before_price"}
                    <span itemprop="price" class="price">{$product.info.price}</span>
                    {hook h='displayProductPriceBlock' product=$product.info type='unit_price'}
                    {hook h='displayProductPriceBlock' product=$product.info type='weight'}
                  </div>
                {/if}
              {/block}
            </div>
            {block name='product_flags'}
              <ul class="product-flags">
                {foreach from=$product.info.flags item=flag}
                  <li class="{$flag.type}">{$flag.label}</li>
                {/foreach}
              </ul>
            {/block}
            <div class="highlighted-informations{if !$product.info.main_variants} no-variants{/if} hidden-sm-down">
              <a href="#" class="quick-view" data-link-action="quickview">
                <i class="material-icons search">&#xE8B6;</i> {l s='Quick view' mod='jxdaydeal'}
              </a>
              {block name='product_variants'}
                {if $product.info.main_variants}
                  {include file='catalog/_partials/variant-links.tpl' variants=$product.info.main_variants}
                {/if}
              {/block}
            </div>
           </div>
        </article>
      {/foreach}
    {else}
      <p class="alert alert-info">{l s='No special products at this time.' mod='jxdaydeal'}</p>
    {/if}
  </div>
</section>