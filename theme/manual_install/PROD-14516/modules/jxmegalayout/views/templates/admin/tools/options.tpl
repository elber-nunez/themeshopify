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

<div class="jxmegalayout-lsettins">
  <div class="form-wrapper">
    <div class="form-group">
      <label class="control-label pull-left">{l s='Optimization (test mode)'  mod='jxmegalayout'}</label>
      <div class="jxlist-layout-btns" data-layout-id="4">
        <a class="layout-btn" id="optionShowMessages" href="#" data-layout-id="4">
          <i class="process-icon-toggle-on {if !Configuration::get(JXMEGALAYOUT_SHOW_MESSAGES)}hidden{/if}"></i>
          <i class="process-icon-toggle-off {if Configuration::get(JXMEGALAYOUT_SHOW_MESSAGES)}hidden{/if}"></i>
        </a>
      </div>
      <p class="desc"><small>{l s='This option allow you optimize files includes. If you will optimize it, only usable files will be included in that or other pages(pages are automatically checked), in other way all files will be included on all pages. You must reoptimize, after do any changes on one of preset.' mod='jxmegalayout'}</small>
      </p>
    </div>
    <div class="form-group">
      <label class="control-label pull-left">{l s='Reset to default'  mod='jxmegalayout'}</label>
      <a href="#" class="btn btn-default btn-sm reset-layouts">{l s='Reset' mod='jxmegalayout'}</a>
      <p class="desc"><small>{l s='Remove all presets that you have and install default presets' mod='jxmegalayout'}</small>
      </p>
    </div>
  </div>
</div>