{*
* 2017-2018 Zemez
*
* JX Mega Menu
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if isset($map) && $map && $map.latitude && $map.longitude}
  <li class="megamenu_map">
    <h5>{$map.title}</h5>
    <div id="jxmegamenu_map_{$map.id}" class="frontend-map"></div>
    {if $map.description}
      <div class="megamenu-map-description">
        {$map.description nofilter}
      </div>
    {/if}
  </li>
{/if}
