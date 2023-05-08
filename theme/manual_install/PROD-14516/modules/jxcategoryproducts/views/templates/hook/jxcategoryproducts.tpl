{**
* 2017-2019 Zemez
*
* JX Category Products
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
*  @author    Zemez
*  @copyright 2017-2019 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if isset($blocks) && $blocks}
  {foreach from=$blocks item='block' name='block'}
    {assign var="block_identificator" value="{$block.id_tab}"}
    <section id="{$hook_class}-block-category-{$block_identificator}" class="category-block featured-products{if $block.use_carousel} swiper-container{/if}">
      <h1 class="h2 products-section-title text-uppercase">
        <a href="{$link->getCategoryLink($block.id)|escape:'html':'UTF-8'}">{$block.name}</a>
      </h1>
      {if isset($block.products) && $block.products}
        <div class="{if $block.use_carousel}swiper-wrapper{else}products{/if}">
          {assign var='products' value=$block.products}
          {foreach from=$products item='product'}
            {include file="catalog/_partials/miniatures/product.tpl" product=$product}
          {/foreach}
        </div>
        {if $block.use_carousel}
          {if isset($block.carousel_settings.carousel_pager) && $block.carousel_settings.carousel_pager}
            <div id="{$hook_class}-block-category-{$block_identificator}-swiper-pagination" class="swiper-pagination"></div>
          {/if}
          {if isset($block.carousel_settings.carousel_control) && $block.carousel_settings.carousel_control}
            <div id="{$hook_class}-block-category-{$block_identificator}-swiper-next" class="swiper-button-next{if isset($block.carousel_settings.carousel_hide_control) && $block.carousel_settings.carousel_hide_control} hideControlOnEnd{/if}"></div>
            <div id="{$hook_class}-block-category-{$block_identificator}-swiper-prev" class="swiper-button-prev{if isset($block.carousel_settings.carousel_hide_control) && $block.carousel_settings.carousel_hide_control} hideControlOnEnd{/if}"></div>
          {/if}
        {/if}
      {else}
        <p class="alert alert-warning">{l s='No products in this category.' mod='jxcategoryproducts'}</p>
      {/if}
    </section>
  {/foreach}
{/if}