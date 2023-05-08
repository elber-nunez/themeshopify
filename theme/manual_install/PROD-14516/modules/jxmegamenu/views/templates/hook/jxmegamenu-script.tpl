{*
* 2017-2018 Zemez
*
* JX Mega Menu
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

<script type="text/javascript">
  {foreach from=$maps item=map}
    {if isset($map) && $map && $map.latitude && $map.longitude}
      {literal}
        $(document).ready(function() {
          $('#jxmegamenu_map_{/literal}{$map.id_item}{literal}').parents('.top-level-menu-li').one('mouseenter click', function() {
            setTimeout(function() {
              initMap{/literal}{$map.id_item}{literal}()
            }, 300)
          });
        });
        function initMap{/literal}{$map.id_item}{literal}() {
          var myLatLng{/literal}{$map.id_item}{literal}    = {
            lat : {/literal}{$map.latitude}{literal},
            lng : {/literal}{$map.longitude}{literal}};
          var map_element{/literal}{$map.id_item}{literal} = document.getElementById('jxmegamenu_map_{/literal}{$map.id_item}{literal}');
          var image                                        = '{/literal}{$map.icon}{literal}';
          var map{/literal}{$map.id_item}{literal}         = new google.maps.Map(map_element{/literal}{$map.id_item}{literal}, {
            center                : myLatLng{/literal}{$map.id_item}{literal},
            zoom                  : {/literal}{$map.scale}{literal},
            scrollwheel           : false,
            mapTypeControl        : false,
            streetViewControl     : false,
            draggable             : true,
            panControl            : false,
            mapTypeControlOptions : {
              style : google.maps.MapTypeControlStyle.DROPDOWN_MENU
            }
          });
          {/literal}{if $map.icon}{literal}
          var marker{/literal}{$map.id_item}{literal} = new google.maps.Marker({
            position : myLatLng{/literal}{$map.id_item}{literal},
            map      : map{/literal}{$map.id_item}{literal},
            icon     : image
          });
          {/literal}{else}{literal}
          var marker{/literal}{$map.id_item}{literal} = new google.maps.Marker({
            position : myLatLng{/literal}{$map.id_item}{literal},
            map      : map{/literal}{$map.id_item}{literal},
          });
          {/literal}{/if}{literal}
        }
      {/literal}
    {/if}
  {/foreach}
</script>
