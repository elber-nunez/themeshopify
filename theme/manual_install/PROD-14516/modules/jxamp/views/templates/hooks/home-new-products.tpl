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

{if isset($new_products) && $new_products.products}
  <div class="home-page-block">
    <h4 class="block-title">{l s='New Products' mod='jxamp'}</h4>
    {if Configuration::get('JXAMP_HOMEPAGE_NEW_PRODUCTS_CAROUSEL')}
      <amp-carousel id="home-carousel-new-products"
                    class="row no-gutters"
                    height="400"
                    layout="fixed-height"
                    type="carousel">
        {foreach from=$new_products.products item='product'}
          {include file='module:jxamp/views/templates/front/amp/_partials/product-miniature.tpl' product=$product quick_view=false}
        {/foreach}
      </amp-carousel>
    {else}
      <div class="row">
        {foreach from=$new_products.products item='product'}
          {include file='module:jxamp/views/templates/front/amp/_partials/product-miniature.tpl' product=$product quick_view=true}
        {/foreach}
      </div>
    {/if}
  </div>
{/if}
