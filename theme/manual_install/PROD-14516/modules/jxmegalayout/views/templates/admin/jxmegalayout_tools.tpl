{**
* 2017-2018 Zemez
*
* JX Mega Layout
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
*  @author    Zemez (Alexander Grosul & Alexander Pervakov)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

<div id="jxml-tools">
  <ul class="nav nav-pills col-sm-2 nav-stacked">
    {foreach from=$tools item=tab_name name=tabs}
      <li id="tab-{$smarty.foreach.tabs.iteration|escape:'htmlall':'UTF-8'}" class="{if $smarty.foreach.tabs.iteration == 1}active{/if}">
        <a href="#tools-{$smarty.foreach.tabs.iteration|escape:'htmlall':'UTF-8'}" data-toggle="tab" data-tool-name="{$tab_name|escape:'htmlall':'UTF-8'}">{$tab_name|escape:'htmlall':'UTF-8'}</a>
      </li>
    {/foreach}
  </ul>
  <div class="tab-content col-sm-10">
    {foreach from=$tools item=content key=tab_name name=content}
      <div id="tools-{$smarty.foreach.content.iteration|escape:'htmlall':'UTF-8'}" class="tab-pane {if $smarty.foreach.content.iteration == 1}active{/if}">
        <div class="jxpanel">
        </div>
      </div>
    {/foreach}
  </div>
</div>