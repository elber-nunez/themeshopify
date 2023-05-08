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
{assign var='page_view' value=Configuration::get('JXAMP_LISTING_VIEW')}
{* calculate initial container height to prevent exceed height if there are a few products *}
{assign var='itemHeight' value=430}
{if $page_view == 'list'}
  {assign var='itemHeight' value=200}
{/if}
{assign var='itemsPerRow' value=3}
{assign var='numRows' value=1}
{if $products.totalProducts > $itemsPerRow}
  {assign var='numRows' value=$products.resultPerPage/$itemsPerRow}
{/if}
{assign var='listHeight' value=$itemHeight * $numRows|ceil}
{if $page_view == 'list'}
  {if $products.totalProducts > $products.resultPerPage}
    {assign var='numRows' value=$products.resultPerPage}
  {else}
    {assign var='numRows' value=$products.totalProducts}
  {/if}
  {assign var='listHeight' value=$itemHeight * $numRows|ceil}
{/if}
<amp-list id="paged-amp-list"
          layout="responsive"
          height="{$listHeight}"
          width="920"
          heights="(max-width: 400px) 150vw, (max-width: 755px) 90vw, (max-width: 991px) 80vw, {$listHeight}px"
          src="{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}ajax"
          [src]="'{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}ajax&page=' + pageNumber + '&orderWay=' + pageOrderWay + '&orderBy=' + pageOrderBy"
          single-item>
  <template type="amp-mustache">
    <p class="info">{l s='Page' mod='jxamp'} {literal}{{currentPage}}{/literal} {l s='of' mod='jxamp'} {literal}{{totalPages}}{/literal}</p>
    {if $page_view == 'grid'}
      {include file="module:jxamp/views/templates/front/amp/catalog/listing/_partials/_grid.tpl"}
    {else}
      {include file="module:jxamp/views/templates/front/amp/catalog/listing/_partials/_list.tpl"}
    {/if}
  </template>
  <div fallback>{l s='There are no products' mod='jxamp'}</div>
  <div overflow
       class="list-overflow">
    <button class="btn btn-more" type="button">
      {l s='See more' mod='jxamp'}
    </button>
  </div>
</amp-list>