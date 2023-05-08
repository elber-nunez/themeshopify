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

<div class="new-theme">
  <div class="panel-heading">
    <i class="icon-cogs"></i>
    {if !isset($current_child_theme)}
      {l s='Add a new theme' mod='jxmegalayout'}
    {else}
      {l s='Edit child theme:' mod='jxmegalayout'}
      <b>{$current_child_theme.display_name}</b>
    {/if}
  </div>
  <form id="info-theme" class="defaultForm form-horizontal">
    <div class="form-wrapper">
      <div class="form-group {if isset($current_child_theme)}hidden{/if}">
        <label class="control-label col-lg-3 required"> {l s='Theme public name' mod='jxmegalayout'}</label>
        <div class="col-lg-6">
          <input type="text" name="theme_public_name" {if isset($current_child_theme)}value="{$current_child_theme.display_name}" disabled{else}value=""{/if} placeholder="{l s='Type your theme public name here' mod='jxmegalayout'}"/>
          <p class="help-block">{l s='This name will be displayed in an admin panel. Do remember that once you have given a name for your theme you will not be able to change it ever.' mod='jxmegalayout'}</p>
        </div>
      </div>
      <div class="form-group {if isset($current_child_theme)}hidden{/if}">
        <label class="control-label col-lg-3 required"> {l s='Theme name' mod='jxmegalayout'}</label>
        <div class="col-lg-6">
          <input type="text" name="theme_name" {if isset($current_child_theme)}value="{$current_child_theme.name}" disabled{else}value=""{/if} placeholder="{l s='Type your theme name here' mod='jxmegalayout'}"/>
          <p class="help-block">{l s='Do remember that once you have given a name for your theme you will not be able to change it ever. Only latin characters and sign "-" are allowed' mod='jxmegalayout'}</p>
        </div>
      </div>
      {if $theme_library && isset($theme_library.pages_list)}
        <ul class="nav nav-tabs">
          {foreach from=$theme_library.pages_list key=type item='page' name='loop'}
            <li {if $smarty.foreach.loop.first}class="active"{/if}>
              <a href="#{$type}" data-toggle="tab">
                {$page.name}
              </a>
            </li>
          {/foreach}
        </ul>
        <div class="tab-content panel">
          {foreach from=$theme_library.pages_list key=type item='page' name='loop'}
            <div id="{$type}" class="tab-pane row{if $smarty.foreach.loop.first} active{/if}">
              {assign var='has_update' value=false}
              {if isset($page.layouts) && $page.layouts}
                <div id="theme-builder-layouts" class="row">
                  {foreach from=$page.layouts key=ltype item='layout'}
                    <div data-page-type="{$type}" class="col-xs-6 col-sm-4">
                      <label class="thumbnail">
                        {if isset($theme_library_previews) && $theme_library_previews[$type][$ltype]}
                          <img class="img-responsive" src="{$theme_library_previews[$type][$ltype]}" alt="{$layout.name}"/>
                        {else}
                          <div class="alert alert-warning" role="alert">{l s='No preview available' mod='jxmegalayout'}</div>
                        {/if}
                        <div class="caption">
                          <input type="checkbox" {if isset($current_child_theme) && isset($current_child_theme['layouts']) && isset($current_child_theme['layouts'][$type]) && $current_child_theme['layouts'][$type]['name'] == $ltype}checked="checked"{/if} data-layout-version="{$layout.version}" name="{$type}" value="{$ltype}"/>
                          <i class="material-icons action-enabled ">check</i>
                          {$layout.name}
                          {if isset($current_child_theme) && isset($current_child_theme['layouts']) && isset($current_child_theme['layouts'][$type]['version']) && $current_child_theme['layouts'][$type]['version'] != $layout.version}
                            {assign var='has_update' value=true}
                            <span>{l s='Has updates' l='jxmegalaoyt'}</span>
                          {/if}
                        </div>
                      </label>
                    </div>
                  {/foreach}
                </div>
              {/if}
            </div>
          {/foreach}
        </div>
      {/if}
      <div class="panel-footer">

        <div class="btn-group pull-right">
          {if isset($has_update) && $has_update}
            <a href="#" class="btn btn-lg btn-warning" data-theme-name="{$current_theme}" id="save-builder-theme">
              {l s='Update' mod='jxmegalayout'}
            </a>
          {else}
            <a href="#" class="btn btn-lg btn-success" data-theme-name="{$current_theme}" id="save-builder-theme">
              {l s='Save' mod='jxmegalayout'}
            </a>
          {/if}
          <button type="button" class="btn btn-lg btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span> <span class="sr-only">Toggle Dropdown</span></button>
          <ul class="dropdown-menu">
            {if isset($current_child_theme)}
              <li>
                <a href="#" data-parent-theme="{$current_theme}" data-theme-name="{$current_child_theme.name}" id="remove-builder-theme">{l s='Remove' mod='jxmegalayout'}</a>
              </li>
            {/if}
            <li>
              <a href="#" id="manage-theme" data-action="load_parent" data-theme-name="{$current_theme}">{l s='Go back' mod='jxmegalayout'}</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </form>
</div>