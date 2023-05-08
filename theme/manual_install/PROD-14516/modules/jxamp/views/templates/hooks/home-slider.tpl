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

{if isset($slider) && $slider}
  <amp-carousel width="779"
                height="448"
                layout="responsive"
                type="slides"
                {if $slider['homeslider']['speed']}
                  autoplay
                  delay="{$slider['homeslider']['speed']}"
                {/if}>
    {foreach from=$slider['homeslider']['slides'] item='slide'}
      <a target="_blank" title="{$slide['title']}" href="{$slide['url']}">
        <amp-img src='{$slide['image_url']}'
               width="{$slide.sizes[0]}"
               height="{$slide.sizes[1]}"
               layout="responsive"
               alt="{$slide['title']}"></amp-img>
      </a>
    {/foreach}
  </amp-carousel>
{/if}