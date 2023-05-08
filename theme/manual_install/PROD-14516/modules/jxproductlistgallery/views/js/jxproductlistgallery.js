/*
 * 2017-2018 Zemez
 *
 * JX Product List Gallery
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

/* Rollover */
$(document).ready(function () {
  if (JX_PLG_TYPE != 'rollover') {
    initCarousel();
  }
  var target = document.getElementById('products');
  if (target) {
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        initCarousel();
      });
    });
    observer.observe(target, {childList: true});
  }
});

function initCarousel() {
  $('.thumbnails-carousel').each(function () {
    var thumbnailsCarousel = $(this);
    thumbnailsCarousel.carousel({
      interval: false
    });
    $(this).on('click', '.left', function (e) {
      e.preventDefault();
      e.stopPropagation();
      thumbnailsCarousel.carousel('prev');
    });
    $(this).on('click', '.right', function (e) {
      e.preventDefault();
      e.stopPropagation();
      thumbnailsCarousel.carousel('next');
    });
    $(this).on('click', '.carousel-indicators li', function (e) {
      e.preventDefault();
      e.stopPropagation();
      thumbnailsCarousel.carousel($(this).data('slide-to'));
    });

    if ((JX_PLG_TYPE == 'gallery' && JX_PLG_USE_CAROUSEL) || (JX_PLG_TYPE == 'slideshow' && JX_PLG_USE_PAGER)) {
      thumbnailsPosition(thumbnailsCarousel);
      thumbnailsCarousel.on('slide.bs.carousel', function (e) {
        thumbnailsPosition(thumbnailsCarousel, e);
      })
    }
  });
}

function thumbnailsPosition(thumbnailsCarousel, e) {
  var widthThumb = thumbnailsCarousel.find('.carousel-indicators li:first-child').outerWidth(true);
  if (JX_PLG_TYPE != 'gallery') {
    JX_PLG_CAROUSEL_NB = 5;
  }
  if (e) {
    var activeThumb = $(e.relatedTarget).index() + 1;
  } else {
    var activeThumb = thumbnailsCarousel.find('.carousel-indicators li.active').index() + 1;
  }
  if (activeThumb != thumbnailsCarousel.find('.carousel-indicators li').length) {
    var visibleThumb = JX_PLG_CAROUSEL_NB - 1;
  } else {
    var visibleThumb = JX_PLG_CAROUSEL_NB;
  }
  if (activeThumb > visibleThumb) {
    thumbnailsCarousel.find('.carousel-indicators li').css({
      '-webkit-transform' : 'translateX(-' + widthThumb * (activeThumb - visibleThumb) + 'px)',
      '-moz-transform'    : 'translateX(-' + widthThumb * (activeThumb - visibleThumb) + 'px)',
      '-ms-transform'     : 'translateX(-' + widthThumb * (activeThumb - visibleThumb) + 'px)',
      '-o-transform'      : 'translateX(-' + widthThumb * (activeThumb - visibleThumb) + 'px)',
      'transform'         : 'translateX(-' + widthThumb * (activeThumb - visibleThumb) + 'px)'
    });
  } else {
    thumbnailsCarousel.find('.carousel-indicators li').css({'-webkit-transform' : 'translateX(0)'});
  }
}