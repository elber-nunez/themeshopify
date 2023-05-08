/**
 * 2017-2018 Zemez
 *
 * JX Deal of Day
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
 *  @author    Zemez (Sergiy Sakun)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

function JXDayDealCheckbox() {
  $('.daydeal-checkbox').click(function() {
    if ($(this).prop('checked') == true) {
      $('#module_form .form-wrapper .form-group').next().addClass('hidden');
    } else if ($(this).prop('checked') == false) {
      $('#module_form .form-wrapper .form-group').next().removeClass('hidden');
    }
    $('.daydeal-checkbox').not(this).prop('checked', false);
  });
}
$(document).ready(function() {
  $('select#reduction_type').bind('change', function(e) {
    if ($('select#reduction_type').val() == 'percentage') {
      $('select#reduction_tax').hide();
    } else {
      $('select#reduction_tax').show();
    }
  }).trigger('change');
  $('.daydeal-alert-container').insertAfter('#divproducts');
  JXDayDealCheckbox();
});
function updateProductInfo() {
  $('.daydeal-prices').remove();
  $('#module_form .form-wrapper .form-group').next().removeClass('hidden');
  var product_id = $('input#inputproducts').val().replace(/\-/g, '');
  $.ajax({
    type     : 'POST',
    url      : theme_url + '&ajax',
    headers  : {"cache-control" : "no-cache"},
    dataType : 'json',
    async    : false,
    data     : {
      action    : 'getProductsSpecificPrice',
      productId : product_id
    },
    success  : function(response) {
      if (response.status) {
        displayJXDayDealWarning(response.data);
      }
    }
  });
}
function displayJXDayDealWarning(data) {
  jxdaydealWarningMessage = '';
  for (i = 0; i < data.length; i++) {
    jxdaydealWarningMessage += '<div class="daydeal-prices alert alert-warning">';
    jxdaydealWarningMessage += '<div>';
    jxdaydealWarningMessage += '<p>';
    jxdaydealWarningMessage += jxdd_msg;
    jxdaydealWarningMessage += '</p>';
    jxdaydealWarningMessage += '<p>';
    jxdaydealWarningMessage += jxdd_msg_period;
    jxdaydealWarningMessage += '&nbsp;'
    jxdaydealWarningMessage += data[i]['from'];
    jxdaydealWarningMessage += ' - ';
    jxdaydealWarningMessage += data[i]['to'];
    jxdaydealWarningMessage += '</p>';
    jxdaydealWarningMessage += '<p>';
    jxdaydealWarningMessage += jxdd_msg_sale;
    jxdaydealWarningMessage += '&nbsp;'
    jxdaydealWarningMessage += data[i]['reduction'];
    jxdaydealWarningMessage += '&nbsp;'
    jxdaydealWarningMessage += data[i]['reduction_type'];
    if (data[i]['reduction_type'] == 'amount') {
      jxdaydealWarningMessage += ',';
      jxdaydealWarningMessage += '&nbsp;'
      if (data[i]['reduction_tax'] == 1) {
        jxdaydealWarningMessage += jxdd_msg_included;
      }
      if (data[i]['reduction_tax'] == 0) {
        jxdaydealWarningMessage += jxdd_msg_excluded;
      }
    }
    jxdaydealWarningMessage += '</p>';
    if (!data[i]['status']) {
      id_specific_price = data[i]['id_specific_price'];
      jxdaydealWarningMessage += '<label>';
      jxdaydealWarningMessage += '<input class="daydeal-checkbox" type="checkbox" value="' + id_specific_price + '" name="specific_price_old" />';
      jxdaydealWarningMessage += '&nbsp;'
      jxdaydealWarningMessage += jxdd_msg_use;
      jxdaydealWarningMessage += '</label>';
    }
    jxdaydealWarningMessage += '</div>';
    jxdaydealWarningMessage += '</div>';
  }
  if (jxdaydealWarningMessage) {
    $('#divproducts').parent().append(jxdaydealWarningMessage);
  }
  JXDayDealCheckbox();
}
$(document).ready(function() {
  jxdaydeal.autocompleteInit($('#products_autocomplete_input'), 'ajax_products_list.php?exclude_packs=0&excludeVirtuals=0');
  $('#divproducts').delegate('.delproducts', 'click', function() {
    jxdaydeal.delProduct($(this).attr('name'));
  });
});
jxdaydeal = {
  autocompleteInit : function(block, url) {
    block.autocomplete(url, {
      minChars      : 3,
      autoFill      : true,
      max           : 100,
      matchContains : true,
      mustMatch     : false,
      scroll        : false,
      cacheLength   : 0,
      formatItem    : function(item) {
        return item[1] + ' - ' + item[0];
      }
    }).result(this.addProduct);
    block.setOptions({
      extraParams : {
        excludeIds : this.getProductIds()
      }
    });
  },
  getProductIds    : function() {
    return $('#inputproducts').val().replace(/\-/g, ',');
  },
  addProduct       : function(event, data, formatted) {
    if (data == null) {
      return false;
    }
    var productId      = data[1];
    var productName    = data[0];
    var $divProducts   = $('#divproducts');
    var $inputProducts = $('#inputproducts');
    var $nameProducts  = $('#nameproducts');
    $divProducts.html($divProducts.html() + '<div class="form-control-static"><button type="button" class="delproducts btn btn-default" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + productName + '</div>');
    $nameProducts.val($nameProducts.val() + productName + '¤');
    $inputProducts.val($inputProducts.val() + productId + '-');
    $('#products_autocomplete_input').val('');
    $('#ajax_choose_products').hide();
    updateProductInfo();
    $('#products_autocomplete_input').setOptions({
      extraParams : {excludeIds : jxdaydeal.getProductIds()}
    });
  },
  delProduct       : function(id) {
    var div      = getE('divproducts');
    var input    = getE('inputproducts');
    var name     = getE('nameproducts');
    // Cut hidden fields in array
    var inputCut = input.value.split('-');
    var nameCut  = name.value.split('¤');
    if (inputCut.length != nameCut.length) {
      return jAlert('Bad size');
    }
    // Reset all hidden fields
    input.value   = '';
    name.value    = '';
    div.innerHTML = '';
    for (i in inputCut) {
      // If empty, error, next
      if (!inputCut[i] || !nameCut[i]) {
        continue;
      }
      // Add to hidden fields no selected products OR add to select field selected product
      if (inputCut[i] != id) {
        input.value += inputCut[i] + '-';
        name.value += nameCut[i] + '¤';
        div.innerHTML += '<div class="form-control-static"><button type="button" class="delproducts btn btn-default" name="' + inputCut[i] + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</div>';
      }
      else {
        input.value += '-';
        name.value += '¤';
      }
    }
    updateProductInfo();
    $('#ajax_choose_products').show();
    $('#products_autocomplete_input').setOptions({
      extraParams : {excludeIds : jxdaydeal.getProductIds()}
    });
  }
};

