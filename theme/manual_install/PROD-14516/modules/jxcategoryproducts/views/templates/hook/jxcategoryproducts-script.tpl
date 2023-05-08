{**
* 2017-2019 Zemez
*
* JX Category Products
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

<script type="text/javascript">
  {if isset($block_settings) && $block_settings}
    {foreach from=$block_settings item='block' name='block'}
      {assign var="block_identificator" value="{$block.id_tab}"}
      {if $block.use_carousel}
        {literal}
          $(document).ready(function () {
            if ($('#{/literal}{$block.hook_name}{literal}-block-category-{/literal}{$block_identificator}{literal}').length) {
              $('#{/literal}{$block.hook_name}{literal}-block-category-{/literal}{$block_identificator}{literal}').find('.product-miniature').addClass('swiper-slide');
              initJXCategoryProductsCarousel('#{/literal}{$block.hook_name}{literal}-block-category-{/literal}{$block_identificator}{literal}'{/literal}, {$block.carousel_settings.carousel_nb}, {$block.carousel_settings.carousel_slide_margin}, {$block.carousel_settings.carousel_item_scroll}, {$block.carousel_settings.carousel_auto}, {$block.carousel_settings.carousel_speed}, {$block.carousel_settings.carousel_auto_pause}, {$block.carousel_settings.carousel_loop}{literal});
            }
          });
        {/literal}
      {/if}
    {/foreach}
    function initJXCategoryProductsCarousel(cp_id_carousel, cp_caroucel_nb, cp_caroucel_slide_margin, cp_caroucel_item_scroll, cp_caroucel_auto, cp_caroucel_speed, cp_caroucel_auto_pause, cp_caroucel_loop) {
      var cp_slider = new Swiper(cp_id_carousel, {
        autoplay: !!cp_caroucel_auto ? { delay: cp_caroucel_auto_pause } : false,
        slidesPerView: cp_caroucel_nb,
        spaceBetween: cp_caroucel_slide_margin,
        slidesPerGroup: cp_caroucel_item_scroll,
        speed: cp_caroucel_speed,
        loop: cp_caroucel_loop,
        pagination: {
          el: cp_id_carousel + '-swiper-pagination',
          clickable: true,
          type: 'bullets'
        },
        navigation: {
          nextEl: cp_id_carousel + '-swiper-next',
          prevEl: cp_id_carousel + '-swiper-prev'
        },
        breakpoints: {
          767: {
            spaceBetween: 10,
            slidesPerView: 2
          },
          991: {
            slidesPerView: 3
          },
          1199: {
            slidesPerView: 4
          },
          1615: {
            slidesPerView: 5
          }
        }
      });
    }
  {/if}
</script>