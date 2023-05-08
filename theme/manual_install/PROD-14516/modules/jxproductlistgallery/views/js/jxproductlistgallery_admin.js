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

$(document).ready(function() {
  jxproductlistgallery_type_check();
  $(document).on('change', 'select[name="JX_PLG_TYPE"]', function() {
      jxproductlistgallery_type_check();
  });
});

function jxproductlistgallery_type_check() {
  jxproductlistgallery_type = $('select[name="JX_PLG_TYPE"]').val();
  if (jxproductlistgallery_type == 'rollover') {
    $('.form-wrapper > .form-group').each(function() {
      if ($(this).hasClass('rollover-type')) {
        $(this).removeClass('hidden');
      } else {
        $(this).addClass('hidden');
      }
    });
  } else if (jxproductlistgallery_type == 'gallery') {
    $('.form-wrapper > .form-group').each(function() {
      if ($(this).hasClass('gallery-type')) {
        $(this).removeClass('hidden');
      } else {
        $(this).addClass('hidden');
      }
    });
  } else if (jxproductlistgallery_type == 'slideshow') {
    $('.form-wrapper > .form-group').each(function() {
      if ($(this).hasClass('slideshow-type')) {
        $(this).removeClass('hidden');
      } else {
        $(this).addClass('hidden');
      }
    });
  }
}