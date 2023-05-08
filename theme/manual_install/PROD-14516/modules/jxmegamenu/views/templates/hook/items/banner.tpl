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

{if isset($banner) && $banner}
	<li class="megamenu_banner{if $banner.specific_class} {$banner.specific_class}{/if}">
    	<a href="{$banner.url}" {if $banner.blank}target="_blank"{/if}>
        	<img class="img-responsive" src="{$image_baseurl}{$banner.image}" alt="{$banner.title}" />
            {if isset($banner.public_title) && $banner.public_title}
            	{$banner.public_title nofilter}
            {/if}
            {if isset($banner.description) && $banner.description}
            	<div class="description">
            		{$banner.description nofilter}
                </div>
            {/if}
        </a>
    </li>
{/if}
