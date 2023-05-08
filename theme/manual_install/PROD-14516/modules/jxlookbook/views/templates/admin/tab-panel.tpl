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

<div id="form-loading" class="col-md-12">
  <div class="tabs js-tabs">
    <div class="arrow js-arrow left-arrow pull-left" style="display: block;">
      <i class="icon-chevron-left"></i>
    </div>
    <ul class="nav nav-tabs js-nav-tabs" id="form-nav" role="tablist">
      {foreach from=$tabs item=tab name=tabs}
        <li class="nav-item {if $smarty.foreach.tabs.iteration == 1}active{/if}">
          <a href="#{$tab.name}" role="tab" data-toggle="tab" class="nav-link active" aria-expanded="true">{$tab.name}</a>
        </li>
      {/foreach}
    </ul>
    <div class="arrow js-arrow right-arrow pull-right visible" style="display: block;">
      <i class="icon-chevron-right"></i>
    </div>
  </div>
  <div id="form_content" class="tab-content">
    {foreach from=$tabs item=tab name=tabs}
      <div role="tabpanel" class="form-contenttab tab-pane {if $smarty.foreach.tabs.iteration == 1}active{/if}"
           id="{$tab.name}" aria-expanded="true">
        {$tab.content}
      </div>
    {/foreach}
  </div>
</div>