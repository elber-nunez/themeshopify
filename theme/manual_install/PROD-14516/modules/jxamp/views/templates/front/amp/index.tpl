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

<!doctype html>
<html ⚡>
  <head>
    <meta charset="utf-8">
    <title>{block name='title'}{$shop.name}{/block}</title>
    <link rel="canonical" href="{block name='canonical_url'}{if isset($amp_canonical) && $amp_canonical}{$amp_canonical}{/if}{/block}">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    {block name='head_icons'}
      <link rel="icon" type="image/vnd.microsoft.icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
      <link rel="shortcut icon" type="image/x-icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
    {/block}
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    {block name='head_seo'}
      <title>{block name='head_seo_title'}{$page.meta.title}{/block}</title>
      <meta name="description" content="{block name='head_seo_description'}{$page.meta.description}{/block}">
      <meta name="keywords" content="{block name='head_seo_keywords'}{$page.meta.keywords}{/block}">
      {if $page.meta.robots !== 'index'}
        <meta name="robots" content="{$page.meta.robots}">
      {/if}
    {/block}
    {block name='head_extra'}{/block}
    <style amp-boilerplate>
      {literal}
        body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}
      {/literal}
    </style></noscript>
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    {block name='amp_blocks'}
      <script async custom-element="amp-font" src="https://cdn.ampproject.org/v0/amp-font-0.1.js"></script>
      <script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
      <script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>
      <script async custom-element="amp-carousel" src="https://cdn.ampproject.org/v0/amp-carousel-0.1.js"></script>
      <script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
      <script async custom-element="amp-lightbox" src="https://cdn.ampproject.org/v0/amp-lightbox-0.1.js"></script>
      {assign var='analytitc_status' value=Configuration::get('JXAMP_ANALYTIC_STATUS')}
      {if $analytitc_status}
        <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
      {/if}
    {/block}
    <style amp-custom>
      {block name='amp_custom_styles'}
        {* add main AMP theme css styles *}
        {$amp_styles nofilter}
        {if isset($colors) && $colors}
          {foreach from=$colors key=id item='color'}
            {literal}ul.list-attribute .attribute-color-{/literal}{$id}{literal} {
              background: {/literal}{$color}{literal};
              border-color: {/literal}{$color}{literal}
            }{/literal}
          {/foreach}
        {/if}
        {literal}
          body {{/literal}
          {if Configuration::get('JXAMP_EXTRA_STYLES_BACKGROUND')}
              {literal}background:{/literal}{Configuration::get('JXAMP_EXTRA_STYLES_BACKGROUND')}{literal};{/literal}
          {/if}
          {literal}}{/literal}
      {/block}
    </style>
    {if isset($microdata) && $microdata}
      <script type="application/ld+json">
        {$microdata nofilter}
      </script>
    {/if}
  </head>
  <body class="body{foreach from=$page.body_classes key='name' item='value'}{if $value} {$name}{/if}{/foreach}">
  {* add google analytic *}
  {if $analytitc_status}
    {assign var='analytic_key' value=Configuration::get('JXAMP_ANALYTIC_KEY')}
    <amp-analytics type="googleanalytics" id="analytic">
      <script data-keepinline="true" type="application/json">
        {literal}{
          "vars": {
            "account":{/literal}"{$analytic_key}"{literal}
          },
          "triggers": {
            "trackPageview": {
              "on": "visible",
              "request": "pageview"
            }
          }
        }{/literal}
      </script>
    </amp-analytics>
  {/if}

  {*  Main menu block *}
  {block name='main_menu'}
    {include file='module:jxamp/views/templates/front/amp/blocks/main_menu.tpl'}
    {hook h='jxampMainMenuExtend'}
  {/block}

  {* Hook for main menu overriding in a case when module using *}
  {hook h='jxampMainMenuOverride'}

  {* Header block *}
  <header id="header">
    <div class="header-row-1-container">
      <div class="container">
        <div id="header-row-1" class="row no-gutters">
          <div class="col-auto menu-btns">
            <button on="tap:sidebar.toggle,btn-sidebar-open.hide,btn-sidebar-close.show" id="btn-sidebar-open" class="amp-start-btn caps m2"><i class="fa fa-bars"></i></button>
            <button on="tap:sidebar.toggle,btn-sidebar-close.hide,btn-sidebar-open.show" hidden id="btn-sidebar-close" class="amp-start-btn caps m2"><i class="fa fa-remove"></i></button>
          </div>
          <div class="col">
            {block name='site_search'}
              {include file='module:jxamp/views/templates/front/amp/blocks/search_top.tpl'}
            {/block}
          </div>
        </div>
      </div>
    </div>
    <div class="header-row-2-container">
      <div class="container">
        <div id="header-row-2" class="row">
          <div class="col">
            <a href="{$shop.url}" title="{$shop.name}">
              <amp-img class="logo" src="{$shop.logo}" height="23" width="117"></amp-img>
            </a>
          </div>
          <div class="col-auto">
            <ul class="user-info-btns">
              <li>
              <a href="{$link->getPagelink('my-account')}" title="{l s='Log in' mod='jxamp'}">
                <i class="fa fa-user"></i>
              </a>
            </li>
            <li>
              <a href="{$link->getPagelink('cart', ['action' => 'show'])}" title="{l s='Cart' mod='jxamp'}">
                <i class="fa fa-shopping-cart"></i>
              </a>
            </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </header>
  {* end of header block *}

  {* main content block *}
  <section id="container">
    <div class="container main-content-container">
      {block name='main_title'}{/block}
      {block name='main_content'}
          {if $homepage_content}
            {$homepage_content nofilter}
          {/if}
      {/block}
    </div>
  </section>
  {*  end of main content block   *}

  {* footer block *}
  <footer id="footer">
    {block name="footer_content"}
      <div class="footer-row-1-container">
        <div class="container">
          <div class="row">
            {widget name="ps_contactinfo"}
            {block name='copyright_link'}
              <div class="col-12 copy-right-block">
                <a class="_blank" href="http://www.prestashop.com" target="_blank">
                  {l s='%copyright% %year% - Ecommerce software by %prestashop%' sprintf=['%prestashop%' => 'PrestaShop™', '%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme.Global'}
                </a>
              </div>
            {/block}
          </div>
        </div>
      </div>
    {/block}
    {*  hook for footer overriding or extending via modules   *}
    {hook h='jxampFooterOverride'}
  </footer>
  {if Configuration::get('JXAMP_USE_FULL_SITE_BUTTON')}
    {if isset($amp_canonical) && $amp_canonical}
      <a id="show-full-page-btn" href="{$amp_canonical}{if $amp_canonical|strpos:'?'}&{else}?{/if}no_amp=1">{l s='Show Original Page' mod='jxamp'}</a>
    {/if}
  {/if}
  </body>
</html>