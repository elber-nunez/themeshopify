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

<div id="theme-update-message"></div>
<div class="panel-heading">
  <i class="icon icon-file"></i>
  {l s='Create new or select a child theme which you want to modify' mod='jxmegalayout'}
</div>
{if $children_themes}
  <div class="row">
    {foreach from=$children_themes item='theme'}
      <div class="col-sm-4 col-lg-3">
        <div class="theme-container">
          <h4 class="theme-title">{$theme.name}</h4>
          <div class="thumbnail-wrapper">
            <div class="action-wrapper">
              <div class="action-overlay"></div>
              <div class="action-buttons">
                <div class="btn-group">
                  <a href="#" class="btn btn-default theme-builder-process" data-action="load_child" data-theme-name="{$current_theme}" data-child-theme-name="{$theme.name}">
                    <i class="icon-check"></i> {l s='Modify this child theme' mod='jxmegalayout'}
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
{/if}
<div class="panel-footer">
  {if $has_update}
    <a href="#" class="btn btn-default" id="update-parent-theme" data-theme-name="{$current_theme}"><i class="process-icon-update"></i>{l s='Update parent theme' mod='jxmegalayout'}</a>
  {/if}
  {if !$has_update && $has_library_update}
    <a href="#" class="btn btn-warning" id="update-parent-theme-library" data-theme-name="{$current_theme}"><i class="process-icon-update"></i>{l s='Update parent theme library' mod='jxmegalayout'}</a>
  {/if}
  <a href="#" class="btn btn-default theme-builder-process pull-right" data-action="add_new_theme" data-theme-name="{$current_theme}"><i class="process-icon-new"></i>{l s='Add new child theme' mod='jxmegalayout'}</a>
</div>
