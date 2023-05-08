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

<div class="jxpanel-content cleafix" id="extra_content_layout">
  <a id="add_extra_content" data-content-type="all" href="#" class="extra-content-action-buttons btn btn-lg btn-success">{l s='Add content' mod='jxmegalayout'}</a>
  <a id="export_extra_content" data-content-type="export_extra_content" href="#" class="extra-content-action-buttons btn btn-lg btn-info">{l s='Export content' mod='jxmegalayout'}</a>
  <a id="import_extra_content" data-content-type="import_extra_content" href="#" class="extra-content-action-buttons btn btn-lg btn-warning">{l s='Import content' mod='jxmegalayout'}</a>
  <ul class="nav nav-tabs jxmegalayout-extra-content-nav">
    <li id="tab-html" class="active" data-seaction="extra-html">
      <a href="#extra-content-html-tab" data-toggle="tab" data-tab-name="extra-content-html-tab" class="content-tab">{l s='Extra HTML' mod='jxmegalayout'}</a>
    </li>
    <li id="tab-banner" data-seaction="extra-banner">
      <a href="#extra-content-banner-tab" data-toggle="tab" data-tab-name="extra-content-banner-tab" class="content-tab">{l s='Extra Banner' mod='jxmegalayout'}</a>
    </li>
    <li id="tab-video" data-seaction="extra-video">
      <a href="#extra-content-video-tab" data-toggle="tab" data-tab-name="extra-content-video-tab" class="content-tab">{l s='Extra Video' mod='jxmegalayout'}</a>
    </li>
    <li id="tab-slider" data-seaction="extra-slider">
      <a href="#extra-content-slider-tab" data-toggle="tab" data-tab-name="extra-content-slider-tab" class="content-tab">{l s='Extra Slider' mod='jxmegalayout'}</a>
    </li>
  </ul>
  <div id="extra_content_container" class="tab-content">
    <div class="tab-pane active" id="extra-content-html-tab">
      {if isset($extra_content_html) && $extra_content_html}
        <ul>
          {foreach from=$extra_content_html item='item'}
            <li class="row">
              <div class="col-sm-1">{$item.id_extra_html}</div>
              <div class="col-sm-9">{$item.name}</div>
              <div class="col-sm-2 text-right">
                <a href="#" class="edit-item btn btn-sm btn-default" data-content-type="html" data-content-id="{$item.id_extra_html}">{l s='Edit' mod='jxmegalayout'}</a>
                <a href="#" class="remove-extra-item btn btn-sm btn-danger" data-content-type="html" data-content-id="{$item.id_extra_html}">{l s='Remove' mod='jxmegalayout'}</a>
              </div>
            </li>
          {/foreach}
        </ul>
      {else}
        <p class="alert alert-info">{l s='No extra HTML content yet.' mod='jxmegalayout'}</p>
      {/if}
    </div>
    <div class="tab-pane" id="extra-content-banner-tab">
      {if isset($extra_content_banner) && $extra_content_banner}
        <ul>
          {foreach from=$extra_content_banner item='item'}
            <li class="row">
              <div class="col-sm-1">{$item.id_extra_banner}</div>
              <div class="col-sm-4">{$item.name}</div>
              <div class="col-sm-3">{$item.link}</div>
              <div class="col-sm-2 banner-img">
                {if $item.img}
                  <img src="{$image_baseurl}{$item.img}" alt="{$item.name}" class="img-responsive" />
                {else}
                  {l s='No image' mod='jxmegalayout'}
                {/if}
              </div>
              <div class="col-sm-2 text-right">
                <a href="#" class="edit-item btn btn-sm btn-default" data-content-type="banner" data-content-id="{$item.id_extra_banner}">{l s='Edit' mod='jxmegalayout'}</a>
                <a href="#" class="remove-extra-item btn btn-sm btn-danger" data-content-type="banner" data-content-id="{$item.id_extra_banner}">{l s='Remove' mod='jxmegalayout'}</a>
              </div>
            </li>
          {/foreach}
        </ul>
      {else}
        <p class="alert alert-info">{l s='No extra Banners yet.' mod='jxmegalayout'}</p>
      {/if}
    </div>
    <div class="tab-pane" id="extra-content-video-tab">
      {if isset($extra_content_video) && $extra_content_video}
        <ul>
          {foreach from=$extra_content_video item='item'}
            <li class="row">
              <div class="col-sm-1">{$item.id_extra_video}</div>
              <div class="col-sm-3">{$item.name}</div>
              <div class="col-sm-6">
                <div class="video-container">
                  <iframe src="{$item.url}?enablejsapi=1&version=3&html5=1" frameborder="0"></iframe>
                </div>
              </div>
              <div class="col-sm-2 text-right">
                <a href="#" class="edit-item btn btn-sm btn-default" data-content-type="video" data-content-id="{$item.id_extra_video}">{l s='Edit' mod='jxmegalayout'}</a>
                <a href="#" class="remove-extra-item btn btn-sm btn-danger" data-content-type="video" data-content-id="{$item.id_extra_video}">{l s='Remove' mod='jxmegalayout'}</a>
              </div>
            </li>
          {/foreach}
        </ul>
      {else}
        <p class="alert alert-info">{l s='No extra video content yet.' mod='jxmegalayout'}</p>
      {/if}
    </div>
    <div class="tab-pane" id="extra-content-slider-tab">
      {if isset($extra_content_slider) && $extra_content_slider}
        <ul>
          {foreach from=$extra_content_slider item='item'}
            <li class="row">
              <div class="col-sm-1">{$item.id_extra_slider}</div>
              <div class="col-sm-3">{$item.name}</div>
              <div class="col-sm-6"></div>
              <div class="col-sm-2 text-right">
                <a href="#" class="edit-item btn btn-sm btn-default" data-content-type="slider" data-content-id="{$item.id_extra_slider}">{l s='Edit' mod='jxmegalayout'}</a>
                <a href="#" class="remove-extra-item btn btn-sm btn-danger" data-content-type="slider" data-content-id="{$item.id_extra_slider}">{l s='Remove' mod='jxmegalayout'}</a>
              </div>
            </li>
          {/foreach}
        </ul>
      {else}
        <p class="alert alert-info">{l s='No extra slider yet.' mod='jxmegalayout'}</p>
      {/if}
    </div>
  </div>
</div>