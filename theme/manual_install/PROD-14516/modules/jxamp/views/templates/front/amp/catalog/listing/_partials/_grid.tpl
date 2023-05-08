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

<div class="row">
  {literal}
  {{ #products }}
  <div class="listing-product grid col-12 col-sm-6 col-lg-4">
    <div class="product-container">
      {{ #show_image }}
      <a href="{{ link }}" title="{{ name }}">
        <amp-img
                class="product-image"
                src="{{ cover.large.url }}"
                width="{{ cover.large.width }}"
                height="{{ cover.large.height }}"
                layout="responsive">
        </amp-img>
      </a>
      {{ /show_image }}
      {{ #show_price }}
      <div class="listing-product-price">
        <span class="price final-price">{{ price }}</span>
        {{ #has_discount }}
        <span class="price regular-price">{{ regular_price }}</span>
        {{ #discount_type }}
        <span class="discount-percentage">{{ discount_percentage }}</span>
        {{ /discount_type }}
        {{ /has_discount }}
      </div>
      {{ /show_price }}
      <h3 class="listing-product-name">
        <a href="{{ link }}" title="{{ name }}">{{ name }}</a>
      </h3>
      {{ #view_more }}
      <a class="btn btn-more" href="{{ link }}" title="{{ name }}">
        {/literal}{l s='Read more' mod='jxamp'}{literal}
      </a>
      {{ /view_more }}
    </div>
  </div>
  {{ /products }}
  {/literal}
</div>