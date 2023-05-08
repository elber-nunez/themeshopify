/*
* 2017-2018 Zemez
*
* JX Wishlist
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

$(document).ready(function(e) {
  jxwl_row_3 = '';
  jxwl_row_3 += '<li class="jxwl_popup_item col-md-6 jxwl_row_3">';
    jxwl_row_3 += '<div class="popup_store_logo"><img class="logo img-fluid" src="'+logo_url+'" /></div>';
    jxwl_row_3 += '<h5></h5>';
    jxwl_row_3 += '<ul id="jxwl_row_3" class="clearfix jxwl_row_3 items">';
      jxwl_row_3 += '<li class="col-12 col-md-6 col-lg-3 item"><div class="content"></div></li>';
      jxwl_row_3 += '<li class="col-12 col-md-6 col-lg-3 item"><div class="content"></div></li>';
      jxwl_row_3 += '<li class="col-12 col-md-6 col-lg-3 item"><div class="content"></div></li>';
      jxwl_row_3 += '<li class="col-12 col-md-6 col-lg-3 item"><div class="content"></div></li>';
    jxwl_row_3 += '</ul>';
  jxwl_row_3 += '</li>';
  jxwl_row_3 += '<input type="hidden" name="id_layout" value="3" />';
  jxwl_layouts.push({name : 'jxwl_row_3', value : jxwl_row_3});
});