/*
 * 2017-2018 Zemez
 *
 * JX Header Account
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0

 * @author     Zemez
 * @copyright  2017-2018 Zemez
 * @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */
$(document).ready(function() {
  if ($('#JXHEADERACCOUNT_FSTATUS_off').attr('checked')) {
    $('.fb-field').parents('.form-group').hide();
  }
  if ($('#JXHEADERACCOUNT_GSTATUS_off').attr('checked')) {
    $('.google-field').parents('.form-group').hide();
  }
  if ($('#JXHEADERACCOUNT_VKSTATUS_off').attr('checked')) {
    $('.vk-field').parents('.form-group').hide();
  }
  if ($('#JXHEADERACCOUNT_USE_AVATAR_off').attr('checked')) {
    $('#JXHEADERACCOUNT_AVATAR').parents('.form-group').hide();
  }
  if ($('#JXHEADERACCOUNT_GSTATUS_off').attr('checked')) {
    $('.google-field').parents('.form-group').hide();
  }
  if ($('#JXHEADERACCOUNT_VKSTATUS_off').attr('checked')) {
    $('.vk-field').parents('.form-group').hide();
  }
  $('#JXHEADERACCOUNT_FSTATUS_on').on('click', function() {
    $('.fb-field').parents('.form-group').slideDown();
  });
  $('#JXHEADERACCOUNT_USE_AVATAR_off').on('click', function() {
    $('#JXHEADERACCOUNT_AVATAR').parents('.form-group').slideUp();
  });
  $('#JXHEADERACCOUNT_USE_AVATAR_on').on('click', function() {
    $('#JXHEADERACCOUNT_AVATAR').parents('.form-group').slideDown();
  });
  $('#JXHEADERACCOUNT_FSTATUS_off').on('click', function() {
    $('.fb-field').parents('.form-group').slideUp();
  });
  $('#JXHEADERACCOUNT_GSTATUS_on').on('click', function() {
    $('.google-field').parents('.form-group').slideDown();
  });
  $('#JXHEADERACCOUNT_GSTATUS_off').on('click', function() {
    $('.google-field').parents('.form-group').slideUp();
  });
  $('#JXHEADERACCOUNT_VKSTATUS_on').on('click', function() {
    $('.vk-field').parents('.form-group').slideDown();
  });
  $('#JXHEADERACCOUNT_VKSTATUS_off').on('click', function() {
    $('.vk-field').parents('.form-group').slideUp();
  });
});


