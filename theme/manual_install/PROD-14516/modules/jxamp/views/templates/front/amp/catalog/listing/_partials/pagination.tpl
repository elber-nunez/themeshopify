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

<div class="nav">
  <ul class="navigation-list">
    <li
            [class]="pageNumber < 2 ? 'prev disabled' : 'prev'"
            class="prev disabled"
            on="tap: AMP.setState({
                pageNumber: pageNumber - 1,
                pageOrderBy: pageOrderBy ? pageOrderBy : {$sort_by},
                pageOrderWay: pageOrderWay ? pageOrderWay : {$sort_way}
              })"
    >
      <span>
        {l s='Prev' mod='jxamp'}
      </span>
    </li>
    {if $products.totalPages}
      {for $foo=1 to $products.totalPages}
        <li
            class="btn-{$foo}{if $foo == 1} current{/if}"
            [class]="pageNumber == {$foo} ? 'btn-{$foo} current' : 'btn-{$foo}'"
            on="tap: AMP.setState({
              pageNumber: {$foo},
              pageOrderBy: pageOrderBy ? pageOrderBy : {$sort_by},
              pageOrderWay: pageOrderWay ? pageOrderWay : {$sort_way}
            })"
        >
          <span>{$foo}
          </span>
        </li>
      {/for}
    {/if}
    <li
            [class]="pageNumber == {$products.totalPages} ? 'next disabled' : 'next'"
            class="next"
            on="tap: AMP.setState({
                pageNumber: pageNumber ? pageNumber + 1 : 2,
                pageOrderBy: pageOrderBy ? pageOrderBy : {$sort_by},
                pageOrderWay: pageOrderWay ? pageOrderWay : {$sort_way}
            })"
    >
      <span>
        {l s='Next' mod='jxamp'}
      </span>
    </li>
  </ul>
</div>