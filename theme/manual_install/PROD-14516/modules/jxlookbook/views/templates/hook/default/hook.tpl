{**
* 2017-2018 Zemez
*
* JX Look Book
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
*  @author    Zemez
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if $collections && count($collections) > 0}
<div class="jxlookbooks">
    {foreach from=$collections item=collection}
        {if $collection.type == 1}
            <a href="{$link->getModuleLink('jxlookbook', 'jxlookbook', ['collection' => $collection.id_collection])}" class="thumbnail">
                <img src="{$base_url|escape:'htmlall':'UTF-8'}{$collection.image|escape:'htmlall':'UTF-8'}" alt="...">
                <div class="caption">
                    <h3 class="name">{$collection.name|escape:'quotes':'UTF-8'}</h3>
                    <div class="description">{$collection.description nofilter}</div>
                </div>
            </a>
        {else}
            {if isset($collection.tabs) && $collection.tabs}
                {assign var=tabs value=$collection.tabs}
                {if "./_templates/{$collection.template}.tpl"|file_exists}
                    {include file="./_templates/{$collection.template}.tpl"}
                {else}
                    {include file="../default/_templates/{$collection.template}.tpl"}
                {/if}
            {/if}
        {/if}
    {/foreach}
</div>
{/if}