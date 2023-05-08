{*
* 2017-2018 Zemez
*
* JX Compare Product
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
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if isset($products) && $products}
  {foreach from=$products item=product name=product}
    <div class="compare-product-element" data-id-product="{$product.info.id_product}">
      <a href="#" class="js-compare-button" data-action="remove-product" data-id-product="{$product.info.id_product}"><span aria-hidden="true">&times;</span></a>
      <img class="img-fluid" src="{$product.info.cover.bySize.small_default.url}" alt="{$product.info.cover.legend}" />
    </div>
  {/foreach}
{else}
  <li class="no-products">{l s='No products to compare'}</li>
{/if}


