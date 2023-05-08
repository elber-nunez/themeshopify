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
  <script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
  <script async custom-element="amp-list" src="https://cdn.ampproject.org/v0/amp-list-0.1.js"></script>
  <script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.1.js"></script>
{/block}
{block main_title}
  <h1 class="main-title">{l s='Best sellers' mod='jxamp'}</h1>
{/block}
{block main_content}
  {if !$products.totalProducts}
    <p>{l s='There are no products' mod='jxamp'}</p>
  {else}
    {assign var='sort_way' value=Configuration::get('JXAMP_LISTING_SORT_WAY')}
    {assign var='sort_by' value=Configuration::get('JXAMP_LISTING_SORT_BY')}
    {include file="module:jxamp/views/templates/front/amp/catalog/listing/_partials/sorting.tpl" sort_way=$sort_way sort_by=$sort_by}
    {if $products.totalPages > 1}
      {include file="module:jxamp/views/templates/front/amp/catalog/listing/_partials/pagination.tpl" sort_way=$sort_way sort_by=$sort_by}
    {/if}
    {include file="module:jxamp/views/templates/front/amp/catalog/listing/product-list.tpl" sort_way=$sort_way sort_by=$sort_by}
  {/if}
{/block}