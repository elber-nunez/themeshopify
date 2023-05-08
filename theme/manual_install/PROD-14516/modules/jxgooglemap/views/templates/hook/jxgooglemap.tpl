{*
* 2017-2018 Zemez
*
* JX Google Map
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
* @author   Zemez
* @copyright  2017-2018 Zemez
* @license  http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if $settings && $jxdefaultLat && $jxdefaultLong && $googleAPIKey}
  <div id="{$hookName}googlemap" class="jxgooglemap">
    <div data-type="current-map-settings" class="hidden" style="display: none !important;">
      <div data-type="google-api-key" data-value="{$googleAPIKey}"></div>
      <div data-type="default-latitude" data-value="{$jxdefaultLat}"></div>
      <div data-type="default-longtitude" data-value="{$jxdefaultLong}"></div>
      <div data-type="img-store-dir" data-value="{$urls.img_store_url}"></div>
      <div data-type="marker-path" data-value="{$marker_path}"></div>
      <div data-type="map-settings">
        {foreach from=$settings key=name item=value}
          <div class="hidden" data-setting-name="{$name}" data-setting-value="{$value.value}"  data-setting-type="{$value.type}"></div>
        {/foreach}
      </div>
      {if $jx_stores}
        <div class="map-stores">
          {foreach from=$jx_stores item=store}
            <div
              data-store-name="{$store.name}"
              data-store-id="{$store.id_store}"
              data-store-id_tab="{$store.id_tab}"
              data-store-marker="{$store.marker}"
              data-store-content="{$store.content}"
              data-store-id_country="{$store.id_country}"
              data-store-id_state="{$store.id_state}"
              data-store-address1="{$store.address1}"
              data-store-address2="{$store.address2}"
              data-store-postcode="{$store.postcode}"
              data-store-city="{$store.city}"
              data-store-latitude="{$store.latitude}"
              data-store-longitude="{$store.longitude}"
              data-store-phone="{$store.phone}"
              data-store-fax="{$store.fax}"
              data-store-note="{$store.note}"
              data-store-email="{$store.email}"
              data-store-id_image="{$store.id_image}"
              {if $store.hours}
                {foreach from=$store.hours key=name item=hours}
                  data-store-hour{$name}="{$hours}"
                {/foreach}
              {/if}
            ></div>
          {/foreach}
        </div>
      {/if}
      <div data-type="translations">
        <div data-lang-variable="jx_directions" data-value="{l s='Get directions' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_1" data-value="{l s='Mon' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_2" data-value="{l s='Tue' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_3" data-value="{l s='Wed' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_4" data-value="{l s='Thu' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_5" data-value="{l s='Fri' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_6" data-value="{l s='Sat' mod='jxgooglemap'}"></div>
        <div data-lang-variable="translation_7" data-value="{l s='Sun' mod='jxgooglemap'}"></div>
      </div>
    </div>
    <div id="{$hookName}map" data-type="map-container"></div>
  </div>
{/if}