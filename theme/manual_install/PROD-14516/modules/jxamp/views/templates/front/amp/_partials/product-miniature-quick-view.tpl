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

<amp-lightbox id="my-lightbox-{$product.id_product}" layout="nodisplay">
  <div class="lightbox" on="tap:my-lightbox-{$product.id_product}.close" role="button" tabindex="0">
    <div class="product-quick-view-container row">
      <div class="product-quick-view-image col-6">
        <amp-img src="{$product.cover.large.url}" height="{$product.cover.large.height}" width="{$product.cover.large.width}" layout="responsive"></amp-img>
      </div>
      <div class="product-quick-view-description-container col-6">
        <ul class="product-flags">
          {foreach from=$product.flags item=flag}
            <li class="product-flag {$flag.type}">{$flag.label}</li>
          {/foreach}
        </ul>
        <h3 class="product-quick-view-name"><a href="{$product.link}">{$product.name}</a></h3>
        {if $product.show_price}
          <div class="product-quick-view-price">
            <span class="price">{$product.price}</span>
            {if $product.has_discount}
              <span class="regular-price">{$product.regular_price}</span>
              {if $product.discount_type === 'percentage'}
                <span class="discount-percentage">{$product.discount_percentage}</span>
              {/if}
            {/if}
          </div>
        {/if}

        {if $product.available_now}
          <div class="product-quick-view-availability quick-view-title-row">
            <span class="availability-title quick-view-title">{l s='Availability: ' d='Shop.Theme.Catalog'}</span><span class="availability-value"> {$product.available_now}</span>
          </div>
        {/if}

        {if $product.available_later}
          <div class="product-quick-view-available-later quick-view-title-row">
            <span class="availability-title quick-view-title">{l s='Available later: ' d='Shop.Theme.Catalog'}</span><span class="availability-value"> {$product.available_later}</span>
          </div>
        {/if}

        <div class="product-quick-view-quantity quick-view-title-row">
          <span class="quantity-title quick-view-title">{l s='Quantity: ' d='Shop.Theme.Catalog'}</span><span class="quantity-value"> {$product.quantity}</span>
        </div>

        <div class="product-quick-view-reference quick-view-title-row">
          <span class="reference-title quick-view-title">{l s='Reference: ' d='Shop.Theme.Catalog'}</span><span class="reference-value"> {$product.reference}</span>
        </div>

        <div class="product-quick-view-category quick-view-title-row">
          <span class="category-title quick-view-title">{l s='Category: ' d='Shop.Theme.Catalog'}</span><span class="category-value"> {$product.category_name}</span>
        </div>

        <div class="product-quick-view-manufacturer quick-view-title-row">
          <span class="manufacturer-title quick-view-title">{l s='Brand: ' d='Shop.Theme.Catalog'}</span><span class="manufacturer-value"> {$product.manufacturer_name}</span>
        </div>
      </div>
      <div class="col-12 product-quick-view-description-container">
        {if $product.description}
          <div class="description">
            {$product.description nofilter}
          </div>
        {/if}

        {if $product.quantity_discounts}
          <h3 class="h6 product-discounts-title">{l s='Volume discounts' d='Shop.Theme.Catalog'}</h3>
          <table class="table-product-discounts">
            <thead>
            <tr>
              <th>{l s='Quantity' d='Shop.Theme.Catalog'}</th>
              <th>{$configuration.quantity_discount.label}</th>
              <th>{l s='You Save' d='Shop.Theme.Catalog'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$product.quantity_discounts item='quantity_discount' name='quantity_discounts'}
              <tr data-discount-type="{$quantity_discount.reduction_type}" data-discount="{$quantity_discount.real_value}" data-discount-quantity="{$quantity_discount.quantity}">
                <td>{$quantity_discount.quantity}</td>
                <td>{$quantity_discount.discount}</td>
                <td>{l s='Up to %discount%' d='Shop.Theme.Catalog' sprintf=['%discount%' => $quantity_discount.save]}</td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {/if}
        {if $product.features}
          <table class="table table-bordered product-data-sheet">
            <tbody>
            {foreach from=$product.features item='feature'}
              <tr>
                <td>{$feature.name}</td>
                <td>{$feature.value}</td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        {/if}
      </div>
    </div>
  </div>
</amp-lightbox>