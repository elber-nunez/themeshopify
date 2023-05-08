{*
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

<ul class='nav nav-tabs'>
  <li {if !$active || $active == 1}class="active"{/if}><a href="{$module_url}&ampTab=1">{l s='AMP Pages' mod='jxamp'}</a></li>
  <li {if $active == 2}class="active"{/if}><a href="{$module_url}&ampTab=2">{l s='Homepage settings' mod='jxamp'}</a></li>
  <li {if $active == 3}class="active"{/if}><a href="{$module_url}&ampTab=3">{l s='Listing settings' mod='jxamp'}</a></li>
  <li {if $active == 4}class="active"{/if}><a href="{$module_url}&ampTab=4">{l s='Product info page' mod='jxamp'}</a></li>
  <li {if $active == 5}class="active"{/if}><a href="{$module_url}&ampTab=5">{l s='Share buttons' mod='jxamp'}</a></li>
  <li {if $active == 6}class="active"{/if}><a href="{$module_url}&ampTab=6">{l s='Google analytic' mod='jxamp'}</a></li>
  <li {if $active == 7}class="active"{/if}><a href="{$module_url}&ampTab=7">{l s='Extra styles' mod='jxamp'}</a></li>
</ul>
<div class="amp-admin-tab-content">
  {$content nofilter}
</div>