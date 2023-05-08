/*
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
 * @author     Zemez
 * @copyright  2017-2018 Zemez
 * @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

// init google map script
function initJxgoogleMapScript(url, callback) {
  var jxGoogleScript = document.createElement('script');
  if (callback) {
    jxGoogleScript.onload = callback;
  }
  jxGoogleScript.async = true;
  jxGoogleScript.type  = 'text/javascript';
  jxGoogleScript.src   = url;
  document.body.appendChild(jxGoogleScript);
}
// load google map api
function loadJXGoogleMapsAPI() {
  if (typeof(googleScriptStatus) != 'undefined') {
    if (!googleScriptStatus) {
      if ($('.jxgooglemap').length) {
        var googleAPIKey = $('.jxgooglemap').find('div[data-type="google-api-key"]').attr('data-value');
        initJxgoogleMapScript('//maps.googleapis.com/maps/api/js?key=' + googleAPIKey + '&callback=initJXGoogleMap');
      }
    } else {
      initJXGoogleMap();
    }
  }
}
$(window).load(function() {
  loadJXGoogleMapsAPI();
});
function initJXGoogleMap() {
  var maps = $('.jxgooglemap');
  maps.each(function() {
    if (!$('#' + this.id + ' div[data-type="map-container"]').length || !$('#' + this.id + ' div[data-type="current-map-settings"]').length) {
      return;
    }
    var container = $('#' + this.id + ' div[data-type="map-container"]'),
        settings  = $('#' + this.id + ' div[data-type="current-map-settings"]');
    if (!settings.find('div[data-type="default-longtitude"]').attr('data-value') || !settings.find('div[data-type="default-latitude"]').attr('data-value')) {
      return;
    }
    var default_longtitude = settings.find('div[data-type="default-longtitude"]').attr('data-value'),
        default_latitude   = settings.find('div[data-type="default-latitude"]').attr('data-value'),
        map_id             = container.attr('id');
    if ($('#' + map_id).length < 1) {
      return;
    }
    var isDraggable     = $(window).width() > 1023 ? true : false,
        isPan           = $(window).width() < 1024 ? true : false,
        map_zoom        = settings.find('div[data-setting-name="JXGOOGLE_ZOOM"]').attr('data-setting-value'),
        map_style       = settings.find('div[data-setting-name="JXGOOGLE_STYLE"]').attr('data-setting-value'),
        map_type        = settings.find('div[data-setting-name="JXGOOGLE_TYPE"]').attr('data-setting-value'),
        map_scroll      = settings.find('div[data-setting-name="JXGOOGLE_SCROLL"]').attr('data-setting-value'),
        map_control     = settings.find('div[data-setting-name="JXGOOGLE_TYPE_CONTROL"]').attr('data-setting-value'),
        map_street_view = settings.find('div[data-setting-name="JXGOOGLE_STREET_VIEW"]').attr('data-setting-value'),
        map_animation   = settings.find('div[data-setting-name="JXGOOGLE_ANIMATION"]').attr('data-setting-value'),
        map_popup       = settings.find('div[data-setting-name="JXGOOGLE_POPUP"]').attr('data-setting-value'),
        map_img         = settings.find('div[data-type="img-store-dir"]').attr('data-value'),
        map_icon        = settings.find('div[data-type="marker-path"]').attr('data-value'),
        map_stores      = settings.find('.map-stores'),
        translations    = settings.find('div[data-type="translations"]');
    jxmap               = new google.maps.Map(document.getElementById(map_id), {
      center                : new google.maps.LatLng(parseFloat(default_latitude), parseFloat(default_longtitude)),
      zoom                  : parseInt(map_zoom),
      mapTypeId             : map_type,
      scrollwheel           : parseInt(map_scroll),
      mapTypeControl        : parseInt(map_control),
      streetViewControl     : parseInt(map_street_view),
      draggable             : isDraggable,
      panControl            : isPan,
      mapTypeControlOptions : {
        style : google.maps.MapTypeControlStyle.DROPDOWN_MENU
      },
      styles                : google_map_style[map_style]
    });
    infoWindow          = new google.maps.InfoWindow();
    initJxMarkers(jxmap, map_stores, map_icon, translations, map_animation, map_popup, map_img);
  });
}
function initJxMarkers(map, stores, map_icon, translations, animation, popup, img) {
  stores.find('> div').each(function() {
    var s                   = $(this);
    var jxlatlng            = new google.maps.LatLng(
      parseFloat(s.attr('data-store-latitude')),
      parseFloat(s.attr('data-store-longitude'))
        ),
        jxname              = s.attr('data-store-name'),
        jxaddress           = s.attr('data-store-address1'),
        jxother             = s.attr('data-store-name'),
        jxid_store          = s.attr('data-store-id'),
        jxhas_store_picture = s.attr('data-store-id_image'),
        jxid                = s.attr('data-store-id_tab'),
        jximagemarker       = s.attr('data-store-marker') ? map_icon + s.attr('data-store-marker') : '',
        jxcontent           = s.attr('data-store-content'),
        jxdays_1            = s.attr('data-store-hour1'),
        jxdays_2            = s.attr('data-store-hour2'),
        jxdays_3            = s.attr('data-store-hour3'),
        jxdays_4            = s.attr('data-store-hour4'),
        jxdays_5            = s.attr('data-store-hour5'),
        jxdays_6            = s.attr('data-store-hour6'),
        jxdays_7            = s.attr('data-store-hour7');
    createJxMarker(
      map, translations, animation, popup, img, jxlatlng, jxname, jxaddress, jxother, jxid_store, jxhas_store_picture, jxid, jximagemarker, jxcontent, jxdays_1, jxdays_2, jxdays_3, jxdays_4, jxdays_5, jxdays_6, jxdays_7
    );
  });
}
function createJxMarker(map, translations, animation, popup, img, jxlatlng, jxname, jxaddress, jxother, jxid_store, jxhas_store_picture, jxid, jximagemarker, jxcontent, jxdays_1, jxdays_2, jxdays_3, jxdays_4, jxdays_5, jxdays_6, jxdays_7) {
  var jxmarker          = '';
  var jxanimationmarker = '';
  if (animation == true) {
    jxanimationmarker = google.maps.Animation.BOUNCE;
  } else {
    jxanimationmarker = false;
  }
  jxmarker = new google.maps.Marker({
    map       : map,
    position  : jxlatlng,
    icon      : jximagemarker,
    title     : jxname,
    animation : jxanimationmarker
  });
  if (popup == true) {
    var jx_directions = translations.find('div[data-lang-variable="jx_directions"]').attr('data-value'),
        translation_1 = translations.find('div[data-lang-variable="translation_1"]').attr('data-value'),
        translation_2 = translations.find('div[data-lang-variable="translation_2"]').attr('data-value'),
        translation_3 = translations.find('div[data-lang-variable="translation_3"]').attr('data-value'),
        translation_4 = translations.find('div[data-lang-variable="translation_4"]').attr('data-value'),
        translation_5 = translations.find('div[data-lang-variable="translation_5"]').attr('data-value'),
        translation_6 = translations.find('div[data-lang-variable="translation_6"]').attr('data-value'),
        translation_7 = translations.find('div[data-lang-variable="translation_7"]').attr('data-value');
    var jxhours_1     = '<p><b>' + translation_1 + ': </b><span>' + jxdays_1 + '</span></p>',
        jxhours_2     = '<p><b>' + translation_2 + ': </b><span>' + jxdays_2 + '</span></p>',
        jxhours_3     = '<p><b>' + translation_3 + ': </b><span>' + jxdays_3 + '</span></p>',
        jxhours_4     = '<p><b>' + translation_4 + ': </b><span>' + jxdays_4 + '</span></p>',
        jxhours_5     = '<p><b>' + translation_5 + ': </b><span>' + jxdays_5 + '</span></p>',
        jxhours_6     = '<p><b>' + translation_6 + ': </b><span>' + jxdays_6 + '</span></p>',
        jxhours_7     = '<p><b>' + translation_7 + ': </b><span>' + jxdays_7 + '</span></p>';
    var jxhours       = '<div class="jxgooglemap-hours clearfix">' +
      '' + (!!(jxdays_1) ? jxhours_1 : '') + '' +
      '' + (!!(jxdays_2) ? jxhours_2 : '') + '' +
      '' + (!!(jxdays_3) ? jxhours_3 : '') + '' +
      '' + (!!(jxdays_4) ? jxhours_4 : '') + '' +
      '' + (!!(jxdays_5) ? jxhours_5 : '') + '' +
      '' + (!!(jxdays_6) ? jxhours_6 : '') + '' +
      '' + (!!(jxdays_7) ? jxhours_7 : '') +
      '</div>';
    var jxhtml        = '<div class="marker_content jxgooglemap-popup-' + jxid_store + '">' +
      '<div class="jxaddress-content clearfix">' + ((!!jxhas_store_picture) ? '<img class="marker_logo" src="' + img + parseInt(jxid) + '.jpg" alt="" />' : '') + '' +
      '<div class="description"><h5>' + jxname + '</h5><p>' + jxaddress + '</p></div>' +
      '</div><div class="jxgooglemap-content clearfix">' + jxcontent + '</div>' + jxhours + '<a class="jxlink-directions" href="//maps.google.com/maps?saddr=&daddr=' + jxlatlng + '" target="_blank">' + jx_directions + '</a>' +
      '</div>';
    google.maps.event.addListener(jxmarker, 'click', function() {
      infoWindow.setContent(jxhtml);
      infoWindow.open(jxmap, jxmarker);
    });
  }
}
