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

<form id="search-form" method="get" class="p2" action="{$link->getPageLink('search')}" target="_top">
  <div class="row no-gutters">
    {if !Configuration::get('PS_REWRITING_SETTINGS')}
      <input type="hidden" name="controller" value="search" />
    {/if}
    <input type="text" class="block col" name="s" placeholder="{l s='Search' mod='jxamp'}" required>
    <button type="submit" class="amp-start-btn btn-search col-auto">
      <i class="fa fa-search"></i>
    </button>
  </div>
</form>