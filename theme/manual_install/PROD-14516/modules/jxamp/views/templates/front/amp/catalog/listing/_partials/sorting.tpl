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

<div class="listing-sort">
  <div class="sort-by">
    <label>{l s='Sort by:' mod='jxamp'}</label>
    <select on="change: AMP.setState({ pageNumber : pageNumber || 1, pageOrderBy : pageOrderBy || '{$sort_by}', pageOrderWay : event.value })" name="order">
      <option {if $sort_way == 'asc'}selected="selected"{/if} value="asc">{l s='asc' mod='jxamp'}</option>
      <option {if $sort_way == 'desc'}selected="selected"{/if} value="desc">{l s='desc' mod='jxamp'}</option>
    </select>
  </div>
  <div class="sort-way">
    <label>{l s='Sort way:' mod='jxamp'}</label>
    <select on="change: AMP.setState({ pageNumber : pageNumber || 1, pageOrderWay : pageOrderWay || '{$sort_way}', pageOrderBy : event.value })" name="order">
      <option {if $sort_by == 'name'}selected="selected"{/if} value="name">{l s='name' mod='jxamp'}</option>
      <option {if $sort_by == 'position'}selected="selected"{/if} value="position">{l s='position' mod='jxamp'}</option>
      <option {if $sort_by == 'price'}selected="selected"{/if} value="price">{l s='price' mod='jxamp'}</option>
    </select>
  </div>
</div>
<amp-state id="page"
           src="{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}ajax"
           [src]="'{$current_url}{if $current_url|strpos:'?'}&{else}?{/if}ajax&page=' + pageNumber  + '&orderWay=' + pageOrderWay + '&orderBy=' + pageOrderBy">
</amp-state>