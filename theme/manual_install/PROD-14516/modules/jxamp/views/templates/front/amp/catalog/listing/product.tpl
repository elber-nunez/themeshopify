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

{extends file="$ampFilesPath./index.tpl"}
{if isset($amp_canonical) && $amp_canonical}
  {block canonical_url}{$amp_canonical}{/block}
{/if}
{block name='amp_blocks' append}
  {if Configuration::get('JXAMP_PRODUCT_SHARE_BTNS')}
    <script async custom-element="amp-social-share" src="https://cdn.ampproject.org/v0/amp-social-share-0.1.js"></script>
  {/if}
  <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
  <script async custom-element="amp-list" src="https://cdn.ampproject.org/v0/amp-list-0.1.js"></script>
  <script async custom-element="amp-selector" src="https://cdn.ampproject.org/v0/amp-selector-0.1.js"></script>
  <script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.1.js"></script>
  <script async custom-element="amp-image-lightbox" src="https://cdn.ampproject.org/v0/amp-image-lightbox-0.1.js"></script>
{/block}
{block name='head_extra'}
  <meta property="og:type" content="product">
  {if $amp_canonical}
    <meta property="og:url" content="{$amp_canonical}">
    <meta property="og:title" content="{$page.meta.title}">
    <meta property="og:site_name" content="{$shop.name}">
    <meta property="og:description" content="{$page.meta.description}">
    <meta property="og:image" content="{$product.cover.large.url}">
  {/if}
{/block}
{block main_title}
  <h1 class="main-title">{$product.name}</h1>
{/block}
{block main_content}
  <amp-state id="product" src="{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}ajax" [src]="'{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}ajax{$jsonUrl}"></amp-state>
  {assign var='cover_image' value=false}
  <div class="row">
    <div class="col-12">
      <div class="product-images-gallery">
        <amp-image-lightbox id="lightbox1" layout="nodisplay"></amp-image-lightbox>
        <amp-image-lightbox id="image-lightbox1" layout="nodisplay" data-close-button-aria-label="{l s='Close' mod='jxamp'}"></amp-image-lightbox>
        <amp-carousel id="product-gallery" width="300" height="300" layout="responsive" type="slides">
          {foreach from=$product_images item='item'}
            {if $item.cover}
              {assign var='cover_image' value=$item.images.bySize.medium_default.url}
            {/if}
            <amp-img on="tap:lightbox1" layout="responsive" tabindex="0" role="button" src="{$item.images.bySize.medium_default.url}" width="{$item.images.bySize.medium_default.width}" height="{$item.images.bySize.medium_default.height}"></amp-img>
          {/foreach}
        </amp-carousel>
        {if Configuration::get('JXAMP_PRODUCT_EXTRA_IMAGES')}
          <ul class="carousel-preview">
            {foreach from=$product_images item='item' name='loop'}
              <li>
                <button on="tap:product-gallery.goToSlide(index={$smarty.foreach.loop.index})">
                  <amp-img src="{$item.images.bySize.small_default.url}" width="{$item.images.bySize.small_default.width}" height="{$item.images.bySize.small_default.height}"></amp-img>
                </button>
              </li>
            {/foreach}
          </ul>
        {/if}
      </div>
      {if Configuration::get('JXAMP_PRODUCT_SHARE_BTNS')}
        <div class="social-sharing">
          {if Configuration::get('JXAMP_SHARE_BTN_FACEBOOK')}
            <amp-social-share
                    type="facebook"
                    data-param-app_id="{Configuration::get('JXAMP_SHARE_BTN_FACEBOOK_KEY')}"
                    data-param-href="{if isset($amp_canonical) && $amp_canonical}{$amp_canonical}{/if}"
            ></amp-social-share>
          {/if}
          {if Configuration::get('JXAMP_SHARE_BTN_GPLUS')}
            <amp-social-share
                    type="gplus"
                    data-param-url="{if isset($amp_canonical) && $amp_canonical}{$amp_canonical}{/if}"
            ></amp-social-share>
          {/if}
          {if Configuration::get('JXAMP_SHARE_BTN_PINTEREST')}
            <amp-social-share
                    type="pinterest"
                    {if $cover_image}data-param-media="{$cover_image}"{/if}></amp-social-share>
          {/if}
          {if Configuration::get('JXAMP_SHARE_BTN_TWITTER')}
            <amp-social-share type="twitter"></amp-social-share>
          {/if}
        </div>
      {/if}
      <div class="product-short-description">
        {$product.description_short nofilter}
      </div>
      <div class="product-prices">
        <div [text]="product.defaults.price || '{$product.price}'" class="product-price">
          {$product.price}
        </div>
        <div class="product-discount" {if !$product.has_discount}hidden [hidden]="!product.defaults.has_discount"{else}[hidden]="!product.defaults.has_discount"{/if}>
          <span class="regular-price">{$product.regular_price}</span>
        </div>
      </div>
      <div class="product-quantity">
        <span class="quantity-title">{l s='Quantity:' mod='jxamp'}</span>
        <span class="quantity-value" [text]="product.defaults.quantity || '{$product.quantity}'">{$product.quantity}</span>
      </div>
      <form method="post" action-xhr="{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}addtocart" target="_top">
        <fieldset>
          <div id="attributes-collection" class="row">
            {foreach from=$filter key=id item='elements'}
              <div class="col-12 col-sm-6">
                <label>{$elements.public_group_name}</label>
                {if $id == $top_filter}
                  <amp-selector name="top" on="select: AMP.setState({ {$id}: event.targetOption })">
                    <ul class="list-attribute">
                      {foreach from=$elements.attributes key=id_element item='element'}
                        <li
                                class="{if $elements.is_color_group}attribute-color-{$id_element} {/if} {if isset($defaults[$id]) && $defaults[$id] == $id_element}selected{/if}"
                                [class]="product.defaults.{$id} == {$id_element} ? 'selected {if $elements.is_color_group}attribute-color-{$id_element} {/if}' : '{if $elements.is_color_group}attribute-color-{$id_element} {/if}'" option="{$id_element}">
                          {if !$elements.is_color_group}{$element.attribute_name}{/if}
                        </li>
                      {/foreach}
                    </ul>
                  </amp-selector>
                {else}
                  <amp-selector name="{$id}" on="select:AMP.setState({literal}{{/literal}{$id}{literal}: event.targetOption}{/literal})">
                    <ul class="list-attribute">
                      {foreach from=$elements.attributes key=id_element item='element'}
                        <li class="{if $elements.is_color_group}attribute-color-{$id_element} {/if}{if isset($defaults[$id]) && $defaults[$id] == $id_element}selected{/if}"
                            [class]="(product.{$id}.e{$id_element} && product.defaults.{$id} == {$id_element}) ? 'available selected {if $elements.is_color_group}attribute-color-{$id_element} {/if}' : (product.{$id}.e{$id_element}) ? 'available {if $elements.is_color_group}attribute-color-{$id_element} {/if}' : 'unavailable {if $elements.is_color_group}attribute-color-{$id_element} {/if}'"
                            option="{$id_element}">
                          {if !$elements.is_color_group}{$element.attribute_name}{/if}
                        </li>
                      {/foreach}
                    </ul>
                  </amp-selector>
                {/if}
              </div>
            {/foreach}
          </div>
          <div class="quantity-wanted">
            <input
                    type="number"
                    name="quantity"
                    id="product-quantity"
                    [min]="product.defaults.minimal_quantity || {$product.minimal_quantity}"
                    [max]="product.defaults.quantity || {$product.quantity}"
                    [value]="product.defaults.minimal_quantity || {$product.minimal_quantity}"
                    required />
            <div class="add-to-cart-button">
              <button
                      id="add-to-cart-button"
                      type="submit"
                      {if !$product.available_for_order || ($product.quantity < $product.minimal_quantity)}disabled="disabled"{/if}
                      [disabled] = "product.defaults.available_for_order < 1 || product.defaults.quantity < product.defaults.minimal_quantity"
                      class="btn btn-primary"
                      name="add-to-cart"
              >
                {l s='Add to cart' mod='jxamp'}
              </button>
              {*<input
                      {if !$product.available_for_order || ($product.quantity < $product.minimal_quantity)}disabled="disabled"{/if}
                      [disabled] = "product.defaults.available_for_order < 1 || product.defaults.quantity < product.defaults.minimal_quantity"
                      type="submit"
                      class="btn btn-primary"
                      name="add-to-cart"
                      value="{l s='Add to cart' mod='jxamp'}"
              />*}
            </div>
          </div>
          <input type="hidden" name="id_product" value="{$product.id_product}" />
          <input type="hidden" name="id_product_attribute" value="{$default_id_product_attribute}" [value]="product.id_attribute || {$default_id_product_attribute}" />
        </fieldset>
        <div submit-success>
          <template type="amp-mustache">
            {literal}
              {{ #status }}
                <div class="alert alert-success">
              {{ /status }}
              {{ ^status }}
                <div class="alert alert-danger">
              {{ /status }}
              {{ result }}
              </div>
            {/literal}
          </template>
        </div>
        <div submit-error>
          <template type="amp-mustache">
            {l s='Unknown error occurred' mod='jxamp'}
          </template>
        </div>
      </form>
      <div class="extra-info">
        <amp-selector role="listbox" layout="container">
          {if $product.description}
            <div class="tab-pane tab-button" option="1" selected>{l s='More info' mod='jxamp'}</div>
            <div class="tab-content">{$product.description nofilter}</div>
          {/if}
          {if $product.features}
            <div class="tab-pane tab-button" option="2">{l s='Data sheet' mod='jxamp'}</div>
            <div class="tab-content">
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
            </div>
          {/if}
        </amp-selector>
      </div>
    </div>
  </div>
{/block}