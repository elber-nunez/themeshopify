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
  jxproductzoomer_extended_settings_check();

  $(document).on('change', 'select[name="JXPRODUCTZOOMER_ZOOM_TYPE"]', function() {
    if (jxproductzoomer_extended_settings_status()) {
      jxproductzoomer_type_check();
    }
  });

  $(document).on('change', 'input[name="JXPRODUCTZOOMER_ZOOM_TINT"]', function() {
    jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_TINT', 'tint');
  });

  $(document).on('change', 'input[name="JXPRODUCTZOOMER_ZOOM_SHOW_LENS"]', function() {
    jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_SHOW_LENS', 'lens');
  });

  $(document).on('change', 'input[name="JXPRODUCTZOOMER_ZOOM_EASING"]', function() {
    jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_EASING', 'easing');
  });

  $(document).on('change', 'input[name="JXPRODUCTZOOMER_EXTENDED_SETTINGS"]', function() {
    jxproductzoomer_extended_settings_check();
  });
});

function jxproductzoomer_setting_check(name, type) {
  jxproductzoomer_setting_status = $('input[name="'+name+'"]:checked').val();

  if (jxproductzoomer_setting_status) {
    $('.form-group.'+type+'-block').removeClass('hidden');
  } else {
    $('.form-group.'+type+'-block').addClass('hidden');
  }
}

function jxproductzoomer_type_check() {
  jxproductzoomer_type = $('select[name="JXPRODUCTZOOMER_ZOOM_TYPE"]').val();
  if (jxproductzoomer_type == 'window') {
    $('.form-wrapper > .form-group').each(function() {
      if ($(this).hasClass('window-type')) {
        $(this).removeClass('hidden');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_TINT', 'tint');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_SHOW_LENS', 'lens');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_EASING', 'easing');
      } else {
        $(this).addClass('hidden');
      }
    });
  } else if (jxproductzoomer_type == 'lens') {
    $('.form-wrapper > .form-group').each(function() {
      if ($(this).hasClass('lens-type')) {
        $(this).removeClass('hidden');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_TINT', 'tint');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_SHOW_LENS', 'lens');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_EASING', 'easing');
      } else {
        $(this).addClass('hidden');
      }
    });
  } else if (jxproductzoomer_type == 'inner') {
    $('.form-wrapper > .form-group').each(function() {
      if ($(this).hasClass('inner-type')) {
        $(this).removeClass('hidden');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_TINT', 'tint');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_SHOW_LENS', 'lens');
        jxproductzoomer_setting_check('JXPRODUCTZOOMER_ZOOM_EASING', 'easing');
      } else {
        $(this).addClass('hidden');
      }
    });
  }
}

function jxproductzoomer_extended_settings_check() {
  if (jxproductzoomer_extended_settings_status()) {
    jxproductzoomer_type_check();
  } else {
    $('.form-group.extended-settings').addClass('hidden');
  }
}

function jxproductzoomer_extended_settings_status() {
  return $('input[name="JXPRODUCTZOOMER_EXTENDED_SETTINGS"]:checked').val();
}