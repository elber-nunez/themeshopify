{**
* 2017-2018 Zemez
*
* JX Accelerated Mobile Page
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

<div class="listing-product col-12 col-sm-6 col-lg-4 no-gutters">
  <div class="product-container">
    {if $quick_view}
      {include file='module:jxamp/views/templates/front/amp/_partials/product-miniature-quick-view.tpl' product=$product}
      <button class="quick-view-btn" on="tap:my-lightbox-{$product.id_product}" role="button" tabindex="0">
        <i class="fa fa-eye"></i>
      </button>
    {/if}
    <a href="{$product.link}">
      <amp-img src="{$product.cover.large.url}" height="{$product.cover.large.height}" width="{$product.cover.large.width}" layout="responsive"></amp-img>
    </a>
    {if $product.show_price}
      <div class="listing-product-price">
        <span class="price final-price">{$product.price}</span>
        {if $product.has_discount}
          <span class="price regular-price">{$product.regular_price}</span>
          {if $product.discount_type === 'percentage'}
            <span class="discount-percentage">{$product.discount_percentage}</span>
          {/if}
        {/if}
      </div>
    {/if}
    <h3 class="listing-product-name"><a href="{$product.link}">{$product.name}</a></h3>
    <a class="btn btn-more" href="{$product.link}" title="{$product.name}">{l s='Read more' mod='jxamp'}</a>
  </div>
</div>