{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{if $page.page_name != 'index'}
  <section class="featured-products">
    <h1 class="h4">
      {l s='On sale' d='Shop.Theme.Catalog'}
    </h1>
    <div class="products-small">
      {foreach from=$products item="product" name="product"}
        {include file="catalog/_partials/miniatures/product-small.tpl" product=$product}
        {if $smarty.foreach.product.iteration == 2}{break}{/if}
      {/foreach}
    </div>
    <a class="link mt-3" href="{$allSpecialProductsLink}">
      {l s='All sale products' d='Shop.Theme.Catalog'} <i></i>
    </a>
  </section>
{else}
  <section class="featured-products grid">
    <h1 class="h5 text-center">
      {l s='On sale' d='Shop.Theme.Catalog'}
    </h1>
    <div class="products">
      {foreach from=$products item="product"}
        {include file="catalog/_partials/miniatures/product.tpl" product=$product}
      {/foreach}
    </div>
  </section>
{/if}