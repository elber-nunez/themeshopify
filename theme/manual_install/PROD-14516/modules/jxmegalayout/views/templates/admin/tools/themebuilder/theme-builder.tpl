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

<div class="jxpanel-content cleafix panel" id="theme_child_layout">
  {if $compatible_parent_themes}
    <div class="panel-heading">
      <i class="icon-html5"></i>
      {l s='Select a parent theme which you want to modify' mod='jxmegalayout'}
    </div>
    <div class="row">
      {foreach from=$compatible_parent_themes item='theme'}
        <div class="col-sm-4 col-lg-3">
          <div class="theme-container">
            <h4 class="theme-title">{$theme.name}</h4>
            <div class="thumbnail-wrapper">
              <div class="action-wrapper">
                <div class="action-overlay"></div>
                <div class="action-buttons">
                  <div class="btn-group">
                    <a href="#" class="btn btn-default" id="manage-theme" data-action="load_parent" data-theme-name="{$theme.name}">
                      <i class="icon-check"></i> {l s='Use this theme' mod='jxmegalayout'}
                    </a>
                  </div>
                </div>
              </div>
              <img src="{$theme.image}" alt="{$theme.name}" class="center-block img-thumbnail">
            </div>
          </div>
        </div>
      {/foreach}
    </div>
  {else}
    <p class="alert alert-info">
      {l s='You have no themes wich can be managed' mod='jxmegalayout'}
    </p>
  {/if}
</div>