/**
 * 2017-2018 Zemez
 *
 * JX Product Zoomer
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
 */

$(document).ready(function() {
  if (typeof(JXPRODUCTZOOMER_LIVE_MODE) == 'undefined' || !JXPRODUCTZOOMER_LIVE_MODE) {
    return false;
  }

  var $d = $(this);

  // update zoomed image when page is loaded
  applyProductElevateZoom('body', $('.product-images li img.selected:visible').attr('data-image-large-src'));

  // do if image changing is on hover
  if (JXPRODUCTZOOMER_IMAGE_CHANGE_EVENT && !JXPRODUCTZOOMER_IS_MOBILE) {;
    $d.on('mouseenter', '.product-images li img', function() {
      if (!$(this).hasClass('selected')) {
        $(this).closest('.product-images').find('img').removeClass('selected');
        $(this).trigger('click').addClass('selected');
      }
      if (!$('.modal:visible').length) {
        applyProductElevateZoom('#content', $(this).attr('data-image-large-src'));
      } else if ($('.modal.quickview').length) {
        applyProductElevateZoom('.quickview', $(this).attr('data-image-large-src'));
      }
    });
  } else {
    // refresh zoomed image on item click
    $d.on('click', '.product-images li img', function() {
      if (!$('.modal:visible').length) {
        applyProductElevateZoom('#content', $(this).attr('data-image-large-src'));
      } else if ($('.modal.quickview').length) {
        applyProductElevateZoom('.quickview', $(this).attr('data-image-large-src'));
      }
    });
  }

  if (!JXPRODUCTZOOMER_FANCY_BOX) {
    $('.layer').hide();
  }

  // refresh zoomed image on color change
  prestashop.on('updatedProduct', function (e) {
    if (!$('.quickview').length) {
      applyProductElevateZoom('#content', $('.product-images li img.selected:visible').attr('data-image-large-src'));
    } else {
      applyProductElevateZoom('.quickview', $('.product-images li img.selected:visible').attr('data-image-large-src'));
    }
  });

  // refresh zoomed image in quickview modal
  prestashop.on('clickQuickView', function (e) {
    setTimeout(function() {
      applyProductElevateZoom('.quickview', $('.product-images li img.selected:visible').attr('data-image-large-src'));
    }, 1000);
  });
});

$(window).on('resize', function() {
  $('.zoomContainer').remove();
  if (!$('.quickview').length) {
    applyProductElevateZoom('#content', $('.product-images li img.selected:visible').attr('data-image-large-src'));
  } else {
    applyProductElevateZoom('.quickview', $('.product-images li img.selected:visible').attr('data-image-large-src'));
  }
});

// reload the image zoomer when event happened
function applyProductElevateZoom(container, image) {
  var bigimage = image;

  if (JXPRODUCTZOOMER_IS_MOBILE || (typeof(contentOnly) != 'undefined') && contentOnly) {
    JXPRODUCTZOOMER_ZOOM_TYPE = 'lens';
    JXPRODUCTZOOMER_ZOOM_SHOW_LENS = true;
  }

  if (JXPRODUCTZOOMER_ZOOM_TYPE == 'inner') {
    JXPRODUCTZOOMER_ZOOM_SCROLL = false;
    JXPRODUCTZOOMER_ZOOM_LEVEL = 1;
  }

  if (JXPRODUCTZOOMER_ZOOM_TYPE == 'lens') {
    JXPRODUCTZOOMER_ZOOM_BORDER_SIZE = JXPRODUCTZOOMER_ZOOM_LENS_BORDER_SIZE;
    JXPRODUCTZOOMER_ZOOM_BORDER_COLOR = JXPRODUCTZOOMER_ZOOM_LENS_BORDER_COLOR;
  }

  $(container).find('.product-cover img').ezPlus({
    attrBigImageSrc: bigimage,
    zoomType: JXPRODUCTZOOMER_ZOOM_TYPE,
    zoomContainerAppendTo: $(container).find('.product-cover'), //zoom container parent selector
    responsive: JXPRODUCTZOOMER_ZOOM_RESPONSIVE,
    cursor: JXPRODUCTZOOMER_ZOOM_CURSOR,
    easing: JXPRODUCTZOOMER_ZOOM_EASING,
    easingAmount: JXPRODUCTZOOMER_ZOOM_EASING_AMOUNT,
    scrollZoom: JXPRODUCTZOOMER_ZOOM_SCROLL,
    zoomLevel: JXPRODUCTZOOMER_ZOOM_LEVEL,
    minZoomLevel: JXPRODUCTZOOMER_ZOOM_MIN_LEVEL,
    maxZoomLevel: JXPRODUCTZOOMER_ZOOM_MAX_LEVEL,
    scrollZoomIncrement: JXPRODUCTZOOMER_ZOOM_SCROLL_INCREMENT,
    touchEnabled: false,
    zIndex: 5,
    // window settings
    zoomWindowFadeIn: JXPRODUCTZOOMER_ZOOM_WINDOW_FADE_IN,
    zoomWindowFadeOut: JXPRODUCTZOOMER_ZOOM_WINDOW_FADE_OUT,
    zoomWindowWidth: JXPRODUCTZOOMER_ZOOM_WINDOW_WIDTH,
    zoomWindowHeight: JXPRODUCTZOOMER_ZOOM_WINDOW_HEIGHT,
    zoomWindowOffsetX: JXPRODUCTZOOMER_ZOOM_WINDOW_OFFSET_X,
    zoomWindowOffsetY: JXPRODUCTZOOMER_ZOOM_WINDOW_OFFSET_Y,
    zoomWindowPosition: JXPRODUCTZOOMER_ZOOM_WINDOW_POSITION,
    zoomWindowBgColour: JXPRODUCTZOOMER_ZOOM_WINDOW_BG_COLOUR,
    borderSize: JXPRODUCTZOOMER_ZOOM_BORDER_SIZE,
    borderColour: JXPRODUCTZOOMER_ZOOM_BORDER_COLOR,
    // end window settings
    // lens setings
    showLens: JXPRODUCTZOOMER_ZOOM_SHOW_LENS,
    lensSize: JXPRODUCTZOOMER_ZOOM_LENS_SIZE,
    lensFadeIn: JXPRODUCTZOOMER_ZOOM_FADE_IN,
    lensFadeOut: JXPRODUCTZOOMER_ZOOM_FADE_OUT,
    lensOpacity: JXPRODUCTZOOMER_ZOOM_LENS_OPACITY,
    lensShape: JXPRODUCTZOOMER_ZOOM_LENS_SHAPE,
    lensColour: JXPRODUCTZOOMER_ZOOM_LENS_COLOUR,
    lensBorderSize: JXPRODUCTZOOMER_ZOOM_LENS_BORDER_SIZE,
    lensBorderColour: JXPRODUCTZOOMER_ZOOM_LENS_BORDER_COLOR,
    containLensZoom: JXPRODUCTZOOMER_ZOOM_CONTAIN_LENS_ZOOM,
    //end lens settings
    // tint settins
    tint: JXPRODUCTZOOMER_ZOOM_TINT,
    tintColour: JXPRODUCTZOOMER_ZOOM_TINT_COLOUR,
    tintOpacity: JXPRODUCTZOOMER_ZOOM_TINT_OPACITY,
    zoomTintFadeIn: JXPRODUCTZOOMER_ZOOM_WINDOW_TINT_FADE_IN,
    zoomTintFadeOut: JXPRODUCTZOOMER_ZOOM_WINDOW_TINT_FADE_OUT,
    // callBacks
    onZoomedImageLoaded: function() {
      if (!JXPRODUCTZOOMER_FANCY_BOX) {
        $('.layer').hide();
      }
    },
    // responsive
    respond: [
      {
        range: '1-767',
        zoomType: 'lens'
      }]
  });
}