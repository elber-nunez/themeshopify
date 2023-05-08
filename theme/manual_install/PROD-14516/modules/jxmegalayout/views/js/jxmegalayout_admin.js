/**
 * 2017-2018 Zemez
 *
 * JX Mega Layout
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
 *  @author    Zemez (Alexander Grosul & Alexander Pervakov)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */
$(document).ready(function(e) {
  jxml.init();
  $(document).on('change', '.jxmegalayout-styles input:not([type="radio"])', function(e) {
    jxml.validate.styleInput($(this));
    return false;
  });
  var dropDownButton   = $('#sectionsDropdown');
  var firstSectionText = dropDownButton.parent().find('ul > li:first-child a').text();
  dropDownButton.text(firstSectionText);
  var section_name = $('#jxml-sections>ul>li.active').attr('data-section-name');
  $('.jxmegalayout-nav > li:not(.jxml-tools_tab , .jxml-sections)').hide();
  $('.jxmegalayout-nav > li[data-section="' + section_name + '"]').show();
  $('#selectLayoutArchive').live('click', function(e) {
    $('#layoutArchive').trigger('click');
  });
  $('#selectExtraContentArchive').live('click', function(e) {
    $('#extraContentArchive').trigger('click');
  });
  $('#layoutArchiveName').live('click', function(e) {
    $('#layoutArchive').trigger('click');
  });
  $('#layoutArchiveName').live('dragenter', function(e) {
    e.preventDefault();
  });
  $('#layoutArchiveName').live('dragover', function(e) {
    e.preventDefault();
  });
  $('#layoutArchiveName').live('drop', function(e) {
    e.preventDefault();
    var files                       = e.originalEvent.dataTransfer.files;
    $('#layoutArchive')[0].files[0] = files;
    $(this).val(files[0].name);
  });
  $('#layoutArchive').live('change', function(e) {
    if ($(this)[0].files !== undefined) {
      var files = $(this)[0].files;
      var name  = '';
      $.each(files, function(index, value) {
        name += value.name + ', ';
      });
      $('#layoutArchiveName').val(name.slice(0, -2));
    } else {
      var name = $(this).val().split(/[\\/]/);
      $('#layoutArchiveName').val(name[name.length - 1]);
    }
    e.preventDefault();
    var file      = $('#layoutArchive')[0].files[0];
    var form_data = new FormData();
    form_data.append('file', file);
    send_file(form_data, file.name, file.size, 'preview');
  });
  $('#extraContentArchive').live('change', function(e) {
    $('#import_extra_content_form').find('.alert').addClass('hidden');
    if ($(this)[0].files !== undefined) {
      var files = $(this)[0].files;
      var name  = '';
      $.each(files, function(index, value) {
        name += value.name + ', ';
      });
      $('#extraContentArchiveName').val(name.slice(0, -2));
    } else {
      var name = $(this).val().split(/[\\/]/);
      $('#extraContentArchiveName').val(name[name.length - 1]);
    }
    e.preventDefault();
    var file      = $('#extraContentArchive')[0].files[0];
    if (file.name != 'extracontent.zip') {
      $('#import_extra_content_form').find('.alert').removeClass('hidden');

      return;
    }
    var form_data = new FormData();
    form_data.append('file', file);
    sendExtraContentImportArchive(form_data, file.name, file.size);
  });
  $('#importLayoutArchive').live('click', function(e) {
    e.preventDefault();
    var file      = $('#layoutArchive')[0].files[0];
    var form_data = new FormData();
    form_data.append('file', file);
    var name_layout = $('input[name="new_name_layout"]').val();
    if (typeof(name_layout) != 'undefined' && name_layout.length) {
      if (jxml.validate.layoutName(name_layout)) {
        send_file(form_data, file.name, file.size, 'import');
      } else {
        $('.layout-preview-box').find('p.alert').remove();
        $('.layout-preview-box').prepend('<p class="alert alert-warning text-left">' + jxml_layout_validate_error_text + '</p>');
      }
    } else {
      send_file(form_data, file.name, file.size, 'import');
    }
  });
  function send_file(file_to_send, fileName, fileSize, type) {
    var xhr = new XMLHttpRequest();
    if (type == 'preview') {
      xhr.open('POST', jxml_theme_url + '&ajax&action=getImportInfo', false);
    } else {
      var name_layout = $('input[name="new_name_layout"]').val();
      if (typeof(name_layout) != 'undefined' && name_layout.length) {
        xhr.open('POST', jxml_theme_url + '&ajax&action=importLayout&name_layout=' + name_layout, false);
      } else {
        xhr.open('POST', jxml_theme_url + '&ajax&action=importLayout', false);
      }
    }
    xhr.setRequestHeader("Cache-Control", "no-cache");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader('X-FILE-NAME', fileName);
    xhr.setRequestHeader('X-FILE-SIZE', fileSize);
    xhr.send(file_to_send);
    if (xhr.status == 200) {
      if (type == 'preview') {
        $('.layout-preview-wrapper').removeClass('hidden');
        $('.layout-preview-wrapper').html(JSON.parse(xhr.responseText)['preview']);
      } else {
        if (JSON.parse(xhr.responseText).type != 'popup') {
          if (JSON.parse(xhr.responseText)['status']) {
            showSuccessMessage(JSON.parse(xhr.responseText)['response_msg']);
            $('.layout-preview-wrapper').html('');
            $('#layoutArchiveName').attr('value', '');
            jxml.ajax.afterImport();
          }
          jxml.ajax.optimizeMessage();
        } else {
          $('.layout-preview-box').find('p.alert').remove();
          $('.layout-preview-box').prepend('<p class="alert alert-danger text-left">' + JSON.parse(xhr.responseText).message + '</p>');
        }
      }
    }
  }
  function sendExtraContentImportArchive(file, fileName, fileSize) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', jxml_theme_url + '&ajax&action=importExtraContent', false);
    xhr.setRequestHeader("Cache-Control", "no-cache");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader('X-FILE-NAME', fileName);
    xhr.setRequestHeader('X-FILE-SIZE', fileSize);
    xhr.send(file);
    if (xhr.status == 200) {
      if (JSON.parse(xhr.response).status) {
        $('#jxml-tools').find('#tab-4 > a').trigger('click');
      } else {
        $('#import_extra_content_form').append('<p class="alert alert-danger">'+JSON.parse(xhr.response).message+'</p>');
      }
    }
  }
  $('.iframe-btn').fancybox({
    'width'          : 900,
    'height'         : 600,
    'type'           : 'iframe',
    'autoScale'      : false,
    'autoDimensions' : false,
    'fitToView'      : false,
    'autoSize'       : false,
    onUpdate         : function() {
      $('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
      $('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
    },
    afterShow        : function() {
      $('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
      $('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
    },
    afterClose       : function() {
      setTimeout(function() {
        $('.edit-styles.active').trigger('click')
      }, 50);
    }
  });
  // extra content script
  // button to reload extra content tabs without a form saving
  $(document).on('click', '#extra_content_layout form a.btn-default, a.return-btn', function(e) {
    e.preventDefault();
    // do trigger click to reload extra content tab with ajax in order to refresh all extra data
    $('#jxml-tools').find('#tab-4 > a').trigger('click');
    return false;
  });

  // display list of extra content during a slider management. Display content related to the selected type (HTML, Banner, etc.)
  $(document).on('change', 'select#extra-content-types-list', function(e) {
    $('.extra-content-types-list').addClass('hidden');
    $('.add-extra-content-slider').addClass('disabled');
    $('.extra-content-types-list[name="'+$(this).val()+'"]').removeClass('hidden').val('');
  });

  // display/hide add button in slider management(in order to avoid an empty slide creation)
  $(document).on('change keyup', 'select.extra-content-types-list, input.extra-content-types-list', function(e){
    if ($(this).val()) {
      if ($.isNumeric($(this).val())) {
        $(this).removeClass('error');
        $('.add-extra-content-slider').removeClass('disabled');
      } else {
        $(this).addClass('error');
        $('.add-extra-content-slider').addClass('disabled');
      }
    } else {
      $('.add-extra-content-slider').addClass('disabled');
    }
  });

  // determine a behaviour of an "add-extra-content-slider" button
  $(document).on('click', '.add-extra-content-slider', function(e) {
    e.preventDefault;
    var type = $('select#extra-content-types-list').val();
    var content = $('.extra-content-types-list[name="'+type+'"]').val();
    var name = $('.extra-content-types-list[name="'+type+'"] option:selected').text();
    if (!name.length) {
      name = $('input.extra-content-types-list[name="'+type+'"]').val();
    }
    if ($(this).hasClass('disabled') || !type || !content) {
      return;
    } else {
      $('#extra-content-slider-slides').append('<div class="item col-lg-12"><div class="row"><span class="type col-lg-2">'+type+'</span><span class="name col-lg-8">'+name+'</span><span class="button col-lg-2"><a href="#" class="btn btn-danger remove-extra-content-slider"><i class="icon icon-remove"></i></a></span><input name="slides[]" value="'+type+'-'+content+'" type="hidden" /></div></div>');
      return false;
    }
  });
  $(document).on('click', '.remove-extra-content-slider', function(e) {
    $(this).closest('.item').remove();
    return false;
  });

  // selects in the extra content choosing pop-up
  $(document).on('change', 'select[name="extra_content_type"]', function(e) {
    $('.extra-content-type-selector').addClass('hidden');
    $('.extra-content-type-selector[name="'+$(this).val()+'"]').removeClass('hidden');
  });

  // this part responses for avoiding double selecting in theme builder layouts form
  $(document).on('change', '#theme-builder-layouts input[type="checkbox"]', function(e) {
    var check = false;
    if ($(this).is(':checked')) {
      check = true;
    }
    $(this).closest('#theme-builder-layouts').find('[data-page-type="'+$(this).attr('name')+'"] input[type="checkbox"]').each(function() {
      $(this).attr('checked', false);
    });

    $(this).attr('checked', check);
  });

  // hover effect when choose theme in builder
  $(document).on({
    mouseenter: function(){
      var w = $(this).parent('div').outerWidth(true);
      var h = $(this).parent('div').outerHeight(true);
      $(this).children('.action-wrapper').css('width', w+'px');
      $(this).children('.action-wrapper').css('height', h+'px');
      $(this).children('.action-wrapper').show();
    },
    mouseleave: function(){
      $(this).children('.action-wrapper').hide();
    }
  }, '.thumbnail-wrapper');
});
function fancyBoxOpen(type, data, action, content, classes, active) {
  if (type == 'wrapper') {
    jxmegalayout_content = getWrapperSettings(data);
  } else if (type == 'row') {
    jxmegalayout_content = getRowSettings(data);
  } else if (type == 'col') {
    jxmegalayout_content = getColSettings(data, action);
  } else if (type == 'module') {
    jxmegalayout_content = getModulesList(data, classes, active);
  } else if (type == 'content') {
    jxmegalayout_content = getExtraContent(data);
  } else if (type == 'message') {
    jxmegalayout_content = content
  }
  $.fancybox.open({
    type       : 'inline',
    autoScale  : true,
    minHeight  : 30,
    minWidth   : 320,
    maxWidth   : 815,
    padding    : 0,
    content    : '<div class="bootstrap jxml-popup">' + jxmegalayout_content + '</div>',
    helpers    : {
      overlay : {
        locked : false
      }
    },
    afterClose : function() {
      $('.button-container a:not(.edit-styles)').removeClass('active');
    }
  });
}
function getWrapperSettings(data) {
  if (!data) {
    data = '';
  }
  jxmegalayout_wrapper_content = '';
  jxmegalayout_wrapper_content += '<h2 class="popup-heading">' + jxml_wrapper_heading + '</h2>';
  jxmegalayout_wrapper_content += '<div class="form-group popup-content">';
  jxmegalayout_wrapper_content += '<label for="wrapper-classes">' + jxml_row_classese_text + '</label>';
  jxmegalayout_wrapper_content += '<input name="wrapper-classes" value="' + data + '" class="form-control" />';
  jxmegalayout_wrapper_content += '</div>';
  jxmegalayout_wrapper_content += '<div class="popup-btns">';
  jxmegalayout_wrapper_content += '<a href="#" class="edit-wrapper-confirm btn btn-success">' + jxml_confirm_text + '</a>';
  jxmegalayout_wrapper_content += '</div>';
  return jxmegalayout_wrapper_content;
}
function getRowSettings(data) {
  if (!data) {
    data = '';
  }
  jxmegalayout_row_content = '';
  jxmegalayout_row_content += '<h2 class="popup-heading">' + jxml_row_heading + '</h2>';
  jxmegalayout_row_content += '<div class="form-group popup-content">';
  jxmegalayout_row_content += '<label for="row-classes">' + jxml_row_classese_text + '</label>';
  jxmegalayout_row_content += '<input name="row-classes" value="' + data + '" class="form-control" />';
  jxmegalayout_row_content += '</div>';
  jxmegalayout_row_content += '<div class="popup-btns">';
  jxmegalayout_row_content += '<a href="#" class="edit-row-confirm btn btn-success">' + jxml_confirm_text + '</a>';
  jxmegalayout_row_content += '</div>';
  return jxmegalayout_row_content;
}
function getModulesList(data, classes, active) {
  jxml_modules_select = '';
  jxml_modules_select += '<h2 class="popup-heading">' + jxml_module_heading + '</h2>';
  jxml_modules_select += '<div class="form-group popup-content">';
  jxml_modules_select += '<div class="form-group">';
  jxml_modules_select += '<label>' + jxml_sp_class_text + '</label>';
  jxml_modules_select += '<input class="form-control" name="module-classes" value="' + data + '" />';
  jxml_modules_select += '</div>';
  if (classes) {
    jxml_modules_select += '<label>' + jxml_sp_css_text + '</label>';
    jxml_modules_select += '<select class="form-control" name="module-css">';
    jxml_modules_select += '<option></option>';
    for (i = 0; i < classes.length; i++) {
      var name     = classes[i].split('.');
      var selected = '';
      if (active && active == name[0]) {
        selected = 'selected="selected"';
      }
      jxml_modules_select += '<option ' + selected + ' value="' + name[0] + '">' + name[0] + '</option>';
    }
    jxml_modules_select += '</select>';
  }
  jxml_modules_select += '</div>';
  jxml_modules_select += '<div class="popup-btns">';
  jxml_modules_select += '<a href="#" class="edit-module-confirm btn btn-success">' + jxml_confirm_text + '</a>';
  jxml_modules_select += '</div>';
  return jxml_modules_select;
}
function getExtraContent(data) {
  jxml_modules_select = '';
  jxml_modules_select += '<h2 class="popup-heading">' + jxml_module_heading + '</h2>';
  jxml_modules_select += '<div class="form-group popup-content">';
  jxml_modules_select += '<div class="form-group">';
  jxml_modules_select += '<label>' + jxml_sp_class_text + '</label>';
  jxml_modules_select += '<input class="form-control" name="extra-content-classes" value="' + data + '" />';
  jxml_modules_select += '</div>';
  jxml_modules_select += '</div>';
  jxml_modules_select += '<div class="popup-btns">';
  jxml_modules_select += '<a href="#" class="edit-extra-content-confirm btn btn-success">' + jxml_confirm_text + '</a>';
  jxml_modules_select += '</div>';
  return jxml_modules_select;
}
function getColSettings(data, action) {
  specific_class = data.attr('data-specific-class');
  if (typeof (specific_class) == 'undefined') {
    specific_class = '';
  }
  jxml_cols_dimensions = [data.attr('data-col'), data.attr('data-col-xs'), data.attr('data-col-sm'), data.attr('data-col-md'), data.attr('data-col-lg'), data.attr('data-col-xl'), data.attr('data-col-xxl')];
  jxml_cols_select = '';
  jxml_cols_sizes  = ['', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 'auto'];
  jxml_cols_types  = ['col', 'col-sm', 'col-md', 'col-lg', 'col-xl', 'col-xxl'];
  jxml_cols_select += '<h2 class="popup-heading">' + jxml_col_heading + '</h2>';
  jxml_cols_select += '<div class="form-group popup-content">';
  jxml_cols_select += '<div class="form-wrapper form-group">';
  jxml_cols_select += '<label>' + jxml_sp_class_text + '</label>';
  jxml_cols_select += '<input class="form-control" name="col-specific-class" value="' + specific_class + '" />';
  jxml_cols_select += '</div>';
  jxml_cols_select += '<div class="form-wrapper row">';
  selected_item    = '';
  for (i = 0; i < jxml_cols_types.length; i++) {
    jxml_cols_select += '<div class="col-md-2">';
    jxml_cols_select += '<label>' + jxml_cols_types[i] + '*</label>';
    jxml_cols_select += '<select class="form-group" name="jxml-cols-' + jxml_cols_types[i] + '">';
    jxml_cols_select += '<option value=""></option>';
    for (k = 0; k < jxml_cols_sizes.length; k++) {
      if (jxml_cols_sizes[k]) {
        if ($.inArray(jxml_cols_types[i] + '-' + jxml_cols_sizes[k], jxml_cols_dimensions) != -1) {
          selected_item = 'selected=selected';
        }
      } else {
        if ($.inArray(jxml_cols_types[i], jxml_cols_dimensions) != -1) {
          selected_item = 'selected=selected';
        }
      }
      if (jxml_cols_sizes[k] == '') {
        jxml_cols_select += '<option ' + selected_item + ' value="' + jxml_cols_types[i]  + '">' + jxml_cols_types[i] + '</option>';
      } else {
        jxml_cols_select += '<option ' + selected_item + ' value="' + jxml_cols_types[i] + '-' + jxml_cols_sizes[k] + '">' + jxml_cols_types[i] + '-' + jxml_cols_sizes[k] + '</option>';
      }
      selected_item = '';
    }
    jxml_cols_select += '</select>';
    jxml_cols_select += '</div>';
  }
  jxml_cols_select += '</div>';
  jxml_cols_select += '</div>';
  jxml_cols_select += '<div class="popup-btns">';
  if (action == 'edit') {
    jxml_cols_select += '<a href="#" class="edit-column-confirm btn btn-success">' + jxml_confirm_text + '</a>';
  } else {
    jxml_cols_select += '<a href="#" class="add-column-confirm btn btn-success">' + jxml_confirm_text + '</a>';
  }
  jxml_cols_select += '</div>';
  return jxml_cols_select;
}

function getFormElements(form) {
  var fields = {};
  form.find('input').each(function() {
    fields[$(this).attr('name')] = $(this).attr('value');
  });
  form.find('textarea').each(function() {
    fields[$(this).attr('name')] = tinyMCE.get($(this).attr('id')).getContent();
  });

  return fields;
}

//jxml obj
jxml = {
  ajax            : {
    request                            : function(sendData, successFunction, elem, errorFunction) {
      elem            = elem || null;
      errorFunction   = errorFunction || function(response) {
        };
      successFunction = successFunction || function(response) {
        };
      $.ajax({
        type     : 'POST',
        url      : jxml_theme_url + '&ajax',
        headers  : {"cache-control" : "no-cache"},
        dataType : 'json',
        async    : false,
        data     : sendData,
        error    : function(response) {
          errorFunction(response, sendData, elem);
        },
        success  : function(response) {
          successFunction(response, sendData, elem);
        }
      });
    },
    /* this method has been developed in order to implement banners uploading.
    The cause is that we need different ajax settings for forms management and the
    previous method "request" doesn't support it */
    formDataRequest: function(sendData, successFunction, elem, errorFunction) {
      elem            = elem || null;
      errorFunction   = errorFunction || function(response) {
        };
      successFunction = successFunction || function(response) {
        };
      $.ajax({
        type: 'POST',
        url: jxml_theme_url + '&ajax',
        headers: {"cache-control" : "no-cache"},
        dataType : 'json',
        processData: false,
        contentType: false,
        data     : sendData,
        error    : function(response) {
          errorFunction(response, sendData, elem);
        },
        success  : function(response) {
          successFunction(response, sendData, elem);
        }
      });
    },
    optimizeMessage                    : function() {
      data = {
        action : 'optimizeMessage'
      };
      this.request(data, this.optimizeMessageSuccess);
    },
    optimizeMessageSuccess             : function(response) {
      if (response.status == 'true') {
        JXMEGALAYOUT_OPTIMIZE = false;
        if (JXMEGALAYOUT_SHOW_MESSAGES) {
          $('.alertMessage').removeClass('hidden');
        }
      }
    },
    optionShowMessages                 : function(elem) {
      data = {
        action : 'showMessages'
      };
      this.request(data, this.optionShowMessagesSuccess, elem);
    },
    optionShowMessagesSuccess          : function(response, data, elem) {
      $('#optionShowMessages').find('i').addClass('hidden');
      if (response.status == 'true') {
        $('#optionShowMessages').find('.process-icon-toggle-on').removeClass('hidden');
        showSuccessMessage(response.response_msg);
        JXMEGALAYOUT_SHOW_MESSAGES = true;
      } else {
        $('.alertMessage').addClass('hidden');
        $('#optionShowMessages').find('.process-icon-toggle-off').removeClass('hidden');
        showSuccessMessage(response.response_msg);
        JXMEGALAYOUT_SHOW_MESSAGES = false;
      }
    },
    reloadTab                          : function(elem) {
      tab_name = elem.attr('data-tab-name');
      data     = {
        action   : 'reloadTab',
        tab_name : tab_name
      };
      this.request(data, this.reloadTabSuccess);
    },
    reloadTabSuccess                   : function(response, data, elem) {
      if (response.status == 'true') {
        $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.layouts_list);
        $('.layout-tab-content.active .jxpanel-content').html(response.tab_content);
        $('ul.nav li.active .layouts-tab').removeClass('afterReset');
        $('.ajax_running').remove();
        jxml.sortInit();
        jxml.tooltipInit();
        jxml.multiselectInit();
      }
    },
    resetToDefault                     : function() {
      data = {
        action : 'resetToDefault'
      };
      this.request(data, this.resetToDefaultSuccess);
    },
    resetToDefaultSuccess              : function(response, data, elem) {
      if (response.status == 'true') {
        $('ul.nav .layouts-tab').not().addClass('afterReset');
        jxml.ajax.optimizeMessage();
        showSuccessMessage(response.message);
      } else {
        showErrorMessage(response.message);
      }
    },
    afterExport                        : function() {
      data = {
        action : 'afterExport'
      };
      this.request(data);
    },
    afterImport                        : function() {
      data = {
        action : 'afterImport'
      };
      this.request(data);
    },
    addModuleConfirmation              : function(elem) {
      hook_name = elem.find('input[name="jxml_hook_name"]').val();
      id_layout = elem.find('input[name="jxml_id_layout"]').val();
      data      = {
        action    : 'addModuleConfirmation',
        hook_name : hook_name,
        id_layout : id_layout
      };
      this.request(data, this.addModuleConfirmationSuccess);
    },
    addModuleConfirmationSuccess       : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', response.message);
      } else {
        showErrorMessage(response.message);
      }
    },
    addExtraContentConfirmation              : function(elem) {
      hook_name = elem.find('input[name="jxml_hook_name"]').val();
      id_layout = elem.find('input[name="jxml_id_layout"]').val();
      data      = {
        action    : 'addExtraContentConfirmation',
        hook_name : hook_name,
        id_layout : id_layout
      };
      this.request(data, this.addExtraContentConfirmationSuccess);
    },
    addExtraContentConfirmationSuccess       : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', response.message);
      } else {
        showErrorMessage(response.message);
      }
    },
    enableLayout                       : function(hook_name, id_layout, pages, layout_status) {
      data = {
        action        : 'enableLayout',
        id_layout     : id_layout,
        hook_name     : hook_name,
        pages         : pages,
        layout_status : layout_status
      };
      this.request(data, this.enableLayoutSuccess);
    },
    enableLayoutSuccess                : function(response, data, elem) {
      if (response.status == 'true') {
        var jxbtns_disable = $('.jxlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.disable-layout');
        var jxbtns_enable  = $('.jxlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.use-layout');
        var jxlist         = $('.jxml-layouts-list[data-list-id="' + data.hook_name + '"]');
        var jxlistgroup    = $('.jxlist-group-item[data-layout-id="' + data.id_layout + '"]');
        if (response.type == 'all') {
          jxbtns_disable.removeClass('hidden');
          jxbtns_enable.addClass('hidden');
          jxlist.find('i.icon-star.visible').addClass('hidden');
          $('.jxlist-group-item[data-layout-id="' + data.id_layout + '"]').find('i.icon-star').removeClass('hidden').addClass('visible');
        } else if (response.type == 'sub') {
          jxbtns_disable.removeClass('hidden');
          jxbtns_enable.addClass('hidden');
          jxlistgroup.find('i.icon-star-half-empty').removeClass('hidden').addClass('visible');
          jxlistgroup.find('i.icon-star').removeClass('visible').addClass('hidden');
        } else if (response.type == 'clear') {
          jxlistgroup.find('i.icon-star-half-empty').removeClass('visible').addClass('hidden');
        }
        showSuccessMessage(response.message);
        jxml.ajax.optimizeMessage();
      } else {
        showErrorMessage(response.message);
      }
    },
    disableLayout                      : function(id_layout) {
      data = {
        action    : 'disableLayout',
        id_layout : id_layout
      };
      this.request(data, this.disableLayoutSuccess);
    },
    disableLayoutSuccess               : function(response, data, elem) {
      if (response.status == 'true') {
        $('.jxlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.disable-layout').addClass('hidden');
        $('.jxlist-layout-btns[data-layout-id="' + data.id_layout + '"]').find('.use-layout').removeClass('hidden');
        $('.jxlist-group-item[data-layout-id="' + data.id_layout + '"]').find('i').addClass('hidden');
        showSuccessMessage(response.message);
        jxml.ajax.optimizeMessage();
      } else {
        showErrorMessage(response.message);
      }
    },
    renameLayout                       : function(id_layout, layout_name) {
      data = {
        action      : 'renameLayout',
        id_layout   : id_layout,
        layout_name : layout_name
      };
      this.request(data, this.renameLayoutSuccess);
    },
    renameLayoutSuccess                : function(response, data, elem) {
      if (response.status == 'true') {
        $.fancybox.close();
        $('.jxlist-group li[data-layout-id="' + data.id_layout + '"]').parent().prev('button').text(data.layout_name);
        $('.jxmegalayout-admin[data-layout-id="' + data.id_layout + '"]').find('.jxmlmegalayout-layout-name').text(data.layout_name);
        $('.jxlist-group li[data-layout-id="' + data.id_layout + '"]').find('i').each(function() {
          var jxml_active_icon_class = $(this).attr('class');
          $('.jxlist-group li[data-layout-id="' + data.id_layout + '"]').children('a').html(data.layout_name + '<i class="' + jxml_active_icon_class + '"></i>');
        });
        showSuccessMessage(response.message);
      } else {
        if (response.type != 'popup') {
          $.fancybox.close();
          showErrorMessage(response.message);
        } else {
          $('.fancybox-inner .popup-btns').find('p.alert').remove();
          $('.fancybox-inner .popup-btns').prepend('<p class="alert alert-danger text-left">' + response.message + '</p>');
        }
      }
    },
    getLayoutRenameConfirmation        : function(id_layout) {
      data = {
        action    : 'getLayoutRenameConfirmation',
        id_layout : id_layout
      };
      this.request(data, this.getLayoutRenameConfirmationSuccess);
    },
    getLayoutRenameConfirmationSuccess : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', response.message);
      } else {
        showErrorMessage(response.message);
      }
    },
    removeLayout                       : function(id_layout, hook_name) {
      data = {
        action    : 'removeLayout',
        id_layout : id_layout,
        hook_name : hook_name
      };
      this.request(data, this.removeLayoutSuccess);
    },
    removeLayoutSuccess                : function(response, data, elem) {
      if (response.status == 'true') {
        jxml_hook_layout_list = $('.jxlist-group[data-list-id="' + data.hook_name + '"]');
        $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.new_layouts);
        $('.jxlist-group li[data-layout-id="' + data.id_layout + '"]').remove();
        $('.jxmegalayout-admin[data-layout-id="' + data.id_layout + '"]').remove();
        $('.jxlist-group[data-list-id="' + data.hook_name + '"] li').eq(0).trigger('click');
        if (jxml_hook_layout_list.find('li').length < 1) {
          jxml_hook_layout_list.prev('button').remove();
          jxml_hook_layout_list.closest('.jxmegalayout-lsettins').find('.jxlist-layout-btns').remove();
          jxml_hook_layout_list.closest('.jxmegalayout-lsettins').find('.btn-group').remove();
          jxml_hook_layout_list.closest('.jxmegalayout-lsettins').find('.jxmegalayout-availible-pages').remove();
        }
        showSuccessMessage(response.message);
        jxml.ajax.optimizeMessage();
      } else {
        showErrorMessage(response.message);
      }
    },
    getLayoutRemoveConfirmation        : function(id_layout) {
      data = {
        action    : 'getLayoutRemoveConfirmation',
        id_layout : id_layout
      };
      this.request(data, this.getLayoutRemoveConfirmationSuccess);
    },
    getLayoutRemoveConfirmationSuccess : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', response.message)
      } else {
        showErrorMessage(response.message);
      }
    },
    loadLayoutContent                  : function(elem) {
      jxml_layout_container = elem.closest('.tab-pane').find('.layout-container');
      jxml_layout_container.html('');
      jxml_layout_container.append('<p class="loading col-xs-12">' + jxml_loading_text + '</p>');
      data = {
        action    : 'loadLayoutContent',
        id_layout : elem.attr('data-layout-id')
      };
      this.request(data, this.loadLayoutContentSuccess);
    },
    loadLayoutContentSuccess           : function(response, data, elem) {
      if (response.status == 'true') {
        jxml_layout_container.append(response.layout);
        jxml_layout_container.prev('.jxmegalayout-lsettins').find('.jxlist-layout-buttons').html(response.layout_buttons);
        jxml_layout_container.find('p.loading').remove();
        $('.jxml-layouts-list').removeClass('loading');
        jxml.sortInit();
        jxml.tooltipInit();
        jxml.multiselectInit();
      } else {
        showErrorMessage(response.message);
      }
    },
    addLayout                          : function(elem, layout_name) {
      hook_name = elem.attr('data-hook-name');
      data      = {
        action      : 'addLayout',
        hook_name   : hook_name,
        layout_name : layout_name
      };
      this.request(data, this.addLayoutSuccess);
    },
    addLayoutSuccess                   : function(response, data, elem) {
      if (response.status == 'true') {
        $.fancybox.close();
        $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.new_layouts);
        if ($('.jxlist-group[data-list-id="' + data.hook_name + '"] li').length < 1) {
          $('.jxlist-group[data-list-id="' + data.hook_name + '"]').before('<button class="btn btn-default' + ' dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">' + data.layout_name + '</button>');
        }
        $('.jxlist-group[data-list-id="' + data.hook_name + '"]').append('<li data-layout-id="' + response.id_layout + '" class="jxlist-group-item"><a href="#">' + data.layout_name + '<i class="icon-check hidden pull-right"></i></a></li>');
        $('.jxlist-group li[data-layout-id="' + response.id_layout + '"]').trigger('click');
        showSuccessMessage(response.message);
      } else {
        if (response.type != 'popup') {
          $.fancybox.close();
          showErrorMessage(response.message);
        } else {
          $('.fancybox-inner .popup-btns').find('p.alert').remove();
          $('.fancybox-inner .popup-btns').prepend('<p class="alert alert-danger text-left">' + response.message + '</p>');
        }
      }
    },
    addLayoutForm                      : function(elem) {
      hook_name = elem.attr('data-hook-name');
      data      = {
        action    : 'addLayoutForm',
        hook_name : hook_name
      };
      this.request(data, this.addLayoutFormSuccess);
    },
    addLayoutFormSuccess               : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', response.response_msg);
      }
    },
    loadToolTab                        : function(elem) {
      tool_name = elem.attr('data-tool-name');
      data      = {
        action    : 'loadTool',
        tool_name : tool_name
      };
      this.request(data, this.loadToolTabSuccess);
    },
    loadToolTabSuccess                 : function(response, data, elem) {
      if (response.status == 'true') {
        $('#jxml-tools .active .jxpanel').html(response.content);
        $('.ajax_running').remove();
      }
    },
    loadExtraContent                   : function(elem) {
      $('#extra_content_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      item_type = elem.context.dataset.contentType;
      data      = {
        action    : 'loadExtraContent',
        item_type : item_type
      };

      if (elem.context.dataset.contentId != undefined && elem.context.dataset.contentId) {
        data['id_item'] = elem.context.dataset.contentId;
      }

      this.request(data, this.loadExtraContentSuccess);
    },
    loadExtraContentSuccess            : function(response, data, elem) {
      if (response.status == 'true') {
        jxml.ajax.refreshExtraContentContainer(response.content);
      }
    },
    loadThemesContent                   : function(elem) {
      $('#theme_child_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      parent_theme = elem.context.dataset.themeName;
      child_theme = elem.context.dataset.childThemeName;
      theme_action = elem.context.dataset.action;
      data      = {
        action    : 'loadThemesContent',
        theme : parent_theme,
        child_theme: child_theme,
        theme_action: theme_action
      };

      this.request(data, this.loadThemesContentSuccess);
    },
    loadThemesContentSuccess            : function(response, data, elem) {
      if (response.status) {
        jxml.ajax.refreshThemeContainer(response.content);
      }
    },
    refreshThemeContainer       : function(content) {
      var themeContainer = $('#theme_child_layout');
      themeContainer.html(content);
      themeContainer.find('.ajax_running-1').remove();
    },
    refreshExtraContentContainer       : function(content) {
      var extraContentContainer = $('#extra_content_layout');
      extraContentContainer.html(content);
      extraContentContainer.find('.ajax_running-1').remove();
    },
    saveExtraContent                   : function(elem) {
      $('#theme_child_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      var form = elem.closest('form');
      var contentType = elem.attr('name');
      if (contentType == 'updateBanner' || contentType == 'updateSlider') {
        var data = new FormData(form.get(0));
        data.append('action', 'saveExtraItem');
        // add data from textarea fields which use tinyMCE
        form.find('textarea').each(function() {
          data.append($(this).attr('name'), tinyMCE.get($(this).attr('id')).getContent());
        });
        this.formDataRequest(data, this.saveExtraContentSuccess);
      } else {
        data           = getFormElements(form);
        data['action'] = 'saveExtraItem';
        this.request(data, this.saveExtraContentSuccess);
      }
    },
    saveExtraContentSuccess            : function(response, data, elem) {
      if (response.status == 'invalid') {
        jxml.ajax.invalidExtraContentMessage(response.content);
      } else if (response.status == 'success') {
        jxml.ajax.refreshExtraContentContainer(response.content);
      } else {
        jxml.ajax.refreshExtraContentContainer(response.content);
      }
    },
    invalidExtraContentMessage         : function(message) {
      $('#extra_content_layout').find('.ajax_running-1').remove();
      $('#extra_content_layout #configuration_form').find('.errors-box').remove();
      $('#extra_content_layout #configuration_form .form-wrapper').prepend(message);
    },
    removeExtraContent                   : function(elem) {
      $('#extra_content_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      item_type = elem.context.dataset.contentType;
      id_item = elem.context.dataset.contentId;
      data      = {
        action    : 'removeExtraItem',
        item_type : item_type,
        id_extra_item : id_item
      };

      this.request(data, this.removeExtraContentSuccess, elem);
    },
    removeExtraContentSuccess            : function(response, data, elem) {
      $('#extra_content_layout').find('.ajax_running-1').remove();
      if (response.status == 'true') {
        $('#extra_content_container').find('.errors-box').remove();
        $('#extra_content_container .active').prepend(response.content.report);
        if (response.content.status = 'success') {
          elem.closest('li').remove();
        }
      }
    },
    exportExtraContent: function() {
      $('#extra_content_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      data      = {
        action    : 'exportExtraContent'
      };

      this.request(data, this.exportExtraContentSuccess);
    },
    exportExtraContentSuccess            : function(response) {
      $('#extra_content_layout').find('.ajax_running-1').remove();
      if (response.status) {
        $('#extra_content_container').find('.errors-box').remove();
        if (response.href) {
          location.href = response.href;
        }
      }
    },
    importExtraContent: function() {
      $('#extra_content_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      data      = {
        action    : 'importExtraContentForm'
      };

      this.request(data, this.importExtraContentFormSuccess);
    },
    importExtraContentFormSuccess            : function(response) {
      $('#extra_content_layout').find('.ajax_running-1').remove();
      if (response.status) {
         jxml.ajax.refreshExtraContentContainer(response.content);
      }
    },
    saveBuilderTheme                   : function(elem) {
      $('#theme_child_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      var layouts = {};
      $('#theme-builder-layouts input[type="checkbox"]:checked').each(function() {
        layouts[$(this).attr('name')] = { 'name' : $(this).val(), 'version' : $(this).attr('data-layout-version')};
      });

      data = {
        action: 'saveBuilderTheme',
        parent_theme: elem.context.dataset.themeName,
        theme_name: $('#theme_child_layout input[name="theme_name"]').val(),
        theme_public_name: $('#theme_child_layout input[name="theme_public_name"]').val(),
        layouts: layouts
      };

      this.request(data, this.saveBuilderThemeSuccess);
    },
    saveBuilderThemeSuccess            : function(response, data, elem) {
      if (response.status) {
        jxml.ajax.refreshThemeContainer(response.content);
      } else {
        jxml.ajax.invalidBuilderThemeMessage(response.content);
      }
    },
    invalidBuilderThemeMessage         : function(message) {
      $('#theme_child_layout').find('.ajax_running-1').remove();
      $('#theme_child_layout').find('.module_error').closest('.bootstrap').remove();
      $('#theme_child_layout .form-wrapper').prepend(message);
    },
    removeBuilderTheme : function(elem) {
      $('#theme_child_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      theme = elem.context.dataset.themeName;
      parent_theme = elem.context.dataset.parentTheme;
      data  = {
        action        : 'removeBuilderTheme',
        theme_name    : theme,
        parent_theme  : parent_theme
      };
      this.request(data, this.removeBuilderThemeSuccess, elem);
    },
    removeBuilderThemeSuccess : function(response, data, elem) {
      $('#theme_child_layout').find('.ajax_running-1').remove();
      if (response.status) {
        jxml.ajax.refreshThemeContainer(response.content);
      } else {
        jxml.ajax.invalidBuilderThemeMessage(response.content);
      }
    },
    updateParentTheme: function(elem) {
      $('#theme_child_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      parent_theme = elem.context.dataset.themeName;
      data  = {
        action        : 'updateParentTheme',
        parent_theme  : parent_theme
      };
      this.request(data, this.updateParentThemeSuccess, elem);
    },
    updateParentThemeSuccess : function(response, data, elem) {
      $('#theme_child_layout').find('.ajax_running-1').remove();
      if (response.status) {
        $('#update-parent-theme').remove();
        $('#theme-update-message').html(response.content);
      } else {
        $('#theme-update-message').html(response.content);
      }
    },
    updateParentThemeLibrary: function(elem) {
      $('#theme_child_layout').append('<span class="ajax_running-1"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      parent_theme = elem.context.dataset.themeName;
      data  = {
        action        : 'updateParentThemeLibrary',
        parent_theme  : parent_theme
      };
      this.request(data, this.updateParentThemeLibrarySuccess, elem);
    },
    updateParentThemeLibrarySuccess : function(response, data, elem) {
      $('#theme_child_layout').find('.ajax_running-1').remove();
      if (response.status) {
        $('#theme-update-message').html(response.content);
        $('#update-parent-theme-library').remove();
      } else {
        $('#theme-update-message').html(response.content);
      }
    },
    loadLayoutTab                      : function(elem) {
      tab_name    = elem.attr('data-tab-name');
      old_layouts = elem.parent('li').find('.layout-list-info').attr('value');
      data        = {
        action      : 'loadLayoutTab',
        tab_name    : tab_name,
        old_layouts : old_layouts
      };
      this.request(data, this.loadLayoutTabSuccess);
    },
    loadLayoutTabSuccess               : function(response, data, elem) {
      if (response.status == 'true') {
        $('.nav.nav-tabs li.active .layout-list-info').attr('value', response.old_layouts);
        new_layouts = JSON.parse(response.new_layouts);
        new_layouts.forEach(function(item, i, arr) {
          if ($('.tab-content .tab-pane.active .jxml-layouts-list li').length < 1) {
            $('.tab-content .tab-pane.active .jxml-layouts-list').before('<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">--</button>')
          }
          list_item_html = '<li data-layout-id="' + item['id_layout'] + '" class="jxlist-group-item">'
          list_item_html += '<a href="#">' + item['layout_name'] + '<i class="icon-check pull-right hidden"></i></a></li>'
          html           = $('.tab-content .tab-pane.active .jxml-layouts-list').html();
          $('.tab-content .tab-pane.active .jxml-layouts-list').html(html + list_item_html);
        });
        $('.ajax_running').remove();
      }
    },
    layoutExport                       : function(elem) {
      id_layout = elem.attr('data-id-layout');
      data      = {
        id_layout : id_layout,
        action    : 'layoutExport'
      };
      this.request(data, this.layoutExportSuccess);
    },
    layoutExportSuccess                : function(response, data, elem) {
      location.href = response.href;
      jxml.ajax.afterExport();
    },
    layoutPreview                      : function(elem) {
      id_layout = elem.attr('data-id-layout');
      data      = {
        action    : 'layoutPreview',
        id_layout : id_layout
      };
      this.request(data, this.layoutPreviewSuccess);
    },
    layoutPreviewSuccess               : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', '<div class="jxmegalayout-admin container"><div class="preview-popup-content">' + response.msg + '</div></div>')
      }
    },
    deleteLayoutItem                   : function(elem) {
      id_item           = elem.attr('data-id');
      jxml_itemsdel_ids = [id_item];
      $(elem).find('div').each(function() {
        jxml_itemsdel_ids.push($(this).attr('data-id'));
      });
      data = {
        action  : 'deleteLayoutItem',
        id_item : jxml_itemsdel_ids
      };
      this.request(data, this.deleteLayoutItemSuccess);
    },
    deleteLayoutItemSuccess            : function(response, data, elem) {
      if (response.status == 'true') {
        $('div[data-id="' + data.id_item[0] + '"]').remove();
        showSuccessMessage(response.response_msg);
        jxml.ajax.optimizeMessage();
        return;
      }
      showErrorMessage(response.response_msg);
    },
    clearItemStyles                    : function(elem, id_unique) {
      elem.find('select, input:not([type="hidden"])').val('').attr('style', '');
      data = {
        action    : 'clearItemStyles',
        id_unique : id_unique
      };
      this.request(data, this.clearItemStylesSuccess, elem, this.clearItemStylesError);
    },
    clearItemStylesSuccess             : function(response, data, elem) {
      if (response.status == 'true') {
        elem.find('.alert').remove();
        elem.prepend('<p class="alert alert-success">' + response.message + '<button class="close" aria-label="close" data-dismiss="alert" type="button">×</button></p>');
        return;
      }
      showErrorMessage(response.response_msg);
    },
    clearItemStylesError               : function(response, data, elem) {
      elem.find('.alert').remove();
    },
    saveItemStyles                     : function(elem, id_unique, data) {
      data = {
        action    : 'saveItemStyles',
        id_unique : id_unique,
        data      : data
      };
      this.request(data, this.saveItemStylesSuccess, elem, this.saveItemStylesError);
    },
    saveItemStylesSuccess              : function(response, data, elem) {
      if (response.status == 'true') {
        elem.find('.alert').remove();
        elem.prepend('<p class="alert alert-success">' + response.message + '<button class="close" aria-label="close" data-dismiss="alert" type="button">×</button></p>');
        return;
      }
      showErrorMessage(response.response_msg);
    },
    saveItemStylesError                : function(response, data, elem) {
      elem.find('.alert').remove();
    },
    getItemStyles                      : function(id_unique) {
      data = {
        action    : 'getItemStyles',
        id_unique : id_unique
      };
      this.request(data, this.getItemStylesSuccess);
    },
    getItemStylesSuccess               : function(response, data, elem) {
      if (response.status == 'true') {
        fancyBoxOpen('message', '', '', response.content);
        $('.jxml_color_input').mColorPicker();
        jxml_jxp_img = $('.jxpanel-content').find('input[id="flmbgimg"]').val();
        if (jxml_jxp_img.length) {
          jxml_jxp_img = jxml_jxp_img.substring(jxml_jxp_img.indexOf('/img/cms') + 1);
          $('input[name="background-image"]').val('url(../../../../../' + jxml_jxp_img + ')');
          $('.jxpanel-content').find('input[id="flmbgimg"]').val('');
        }
        return;
      }
    },
    updateSortOrders                   : function(elem) {
      jxml_itemsorder_ids = {};
      elem.find('> div.sortable').each(function() {
        jxml_itemsorder_ids[$(this).attr('data-id')] = $(this).attr('data-sort-order');
      });
      data = {
        action : 'updateLayoutItemsOrder',
        data   : jxml_itemsorder_ids
      };
      this.request(data, this.updateSortOrdersSuccess);
    },
    updateSortOrdersSuccess            : function(response, data, elem) {
      if (response.status == 'true') {
        showSuccessMessage(response.response_msg);
        return;
      }
      showErrorMessage(response.response_msg);
    },
    saveLayoutItem                     : function(id_item, elem, type, type_class, specific_class, col, col_xs, col_sm, col_md, col_lg, col_xl, col_xxl, module_name, public_module_name, origin_hook, extra_css) {
      type_class         = type_class || '';
      public_module_name = public_module_name || null;
      jxml_edit_item     = id_item;
      id_parent          = jxml.get.parentId(elem);
      id_layout          = elem.closest('.layout-container').find('input[name="jxml_id_layout"]').val();
      sort_order         = jxml.get.sortOrder(elem);
      if (jxml_edit_item) {
        sort_order = elem.attr('data-sort-order');
        id_parent  = elem.attr('data-parent-id');
      }

      if (typeof (specific_class) == 'undefined') {
        specific_class = '';
      }
      itemData = {
        'id_layout'      : id_layout,
        'id_parent'      : id_parent,
        'type'           : type,
        'sort_order'     : sort_order,
        'specific_class' : specific_class,
        'col'            : col,
        'col_xs'         : col_xs ? col_xs : '',
        'col_sm'         : col_sm,
        'col_md'         : col_md,
        'col_lg'         : col_lg,
        'col_xl'         : col_xl,
        'col_xxl'        : col_xxl,
        'module_name'    : module_name,
        'origin_hook'    : origin_hook,
        'extra_css'      : extra_css
      };
      data     = {
        action  : 'updateLayoutItem',
        id_item : id_item,
        data    : itemData
      };
      this.request(data, this.saveLayoutItemSuccess, elem);
    },
    saveLayoutItemSuccess              : function(response, data, elem) {
      if (response.status == 'true') {
        if (!jxml_edit_item) {
          if (!elem.hasClass('min-level')) {
            elem.closest('article').append(response.content);
          } else {
            elem.closest('article').find('.add-buttons').before(response.content);
          }
          jxml.sortInit();
          jxml.tooltipInit();
          jxml.multiselectInit();
        }
        jxml.ajax.optimizeMessage();
        showSuccessMessage(response.response_msg);
        return;
      }
      showErrorMessage(response.response_msg);
    },
    optionOptimize                     : function(elem) {
      data = {
        action : 'updateOptionOptimize',
      };
      elem.append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      this.request(data, this.optionOptimizeSuccess, elem);
    },
    optionOptimizeSuccess              : function(response, data, elem) {
      if (response.status == 'true') {
        showSuccessMessage(response.response_msg);
        elem.find('.ajax_running').remove();
        $('.alertMessage').addClass('hidden');
        JXMEGALAYOUT_OPTIMIZE = true;
      }
    },
    optionDeoptimize                   : function(elem) {
      data = {
        action : 'updateoptionDeoptimize',
      };
      elem.append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      this.request(data, this.optionDeoptimizeSuccess, elem);
    },
    optionDeoptimizeSuccess            : function(response, data, elem) {
      if (response.status == 'true') {
        showSuccessMessage(response.response_msg);
        elem.find('.ajax_running').remove();
        if (JXMEGALAYOUT_SHOW_MESSAGES) {
          $('.alertMessage').removeClass('hidden');
        }
        JXMEGALAYOUT_OPTIMIZE = false;
      }
    },
    productInfoPageChange              : function(elem, theme_name) {
      data = {
        action     : 'updateProductInfoPage',
        theme_name : theme_name
      };
      elem.parents('#jxmegalayout-product-info-pages').append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
      elem.parents('#jxmegalayout-product-info-pages').find('li.active').removeClass('active');
      this.request(data, this.productInfoPageChangeSuccess, elem);
    },
    productInfoPageChangeSuccess       : function(response, data, elem) {
      if (response.status == 'true') {
        showSuccessMessage(response.response_msg);
        elem.parents('#jxmegalayout-product-info-pages').find('.ajax_running').remove();
        elem.closest('li').addClass('active');
      }
    }
  },
  events          : {
    docClick                    : function(elemSelector, innerFunc) {
      $(document).on('click', elemSelector, function(e) {
        innerFunc($(this));
        e.preventDefault();
      });
    },
    layoutExport                : function() {
      this.docClick('.layout-export', function(elem) {
        jxml.ajax.layoutExport(elem);
      });
    },
    loadToolTab                 : function() {
      this.docClick('#jxml-tools ul.nav.jxmegalayout-nav a, #jxml-tools > ul.nav.nav-pills a, #jxml-tools_tab', function(elem) {
        elem.append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
        setTimeout(function() {
          jxml.ajax.loadToolTab($('#jxml-tools ul.nav li.active a'))
        }, 100);
      });
    },
    loadExtraContent            : function() {
      this.docClick('#add_extra_content, #extra-content-buttons ul li a, #extra_content_container a.edit-item', function(elem) {
        setTimeout(function() {
          jxml.ajax.loadExtraContent(elem)
        }, 100);
        return false;
      });
    },
    loadThemesContent            : function() {
      this.docClick('#manage-theme, .theme-builder-process', function(elem) {
        setTimeout(function() {
          jxml.ajax.loadThemesContent(elem)
        }, 100);
        return false;
      });
    },
    saveExtraContent            : function() {
      this.docClick('#extra_content_layout #configuration_form_submit_btn', function(elem) {
        setTimeout(function() {
          jxml.ajax.saveExtraContent(elem)
        }, 100);
      });
    },
    removeExtraContent            : function() {
      this.docClick('#extra_content_container a.remove-extra-item', function(elem) {
        setTimeout(function() {
          jxml.ajax.removeExtraContent(elem)
        }, 100);
        return false;
      });
    },
    exportExtraContent            : function() {
      this.docClick('#export_extra_content', function(elem) {
        setTimeout(function() {
          jxml.ajax.exportExtraContent(elem)
        }, 100);
        return false;
      });
    },
    importExtraContent            : function() {
      this.docClick('#import_extra_content', function(elem) {
        setTimeout(function() {
          jxml.ajax.importExtraContent(elem)
        }, 100);
        return false;
      });
    },
    saveBuilderTheme: function() {
      this.docClick('#save-builder-theme', function(elem) {
        setTimeout(function() {
          jxml.ajax.saveBuilderTheme(elem)
        }, 100);
      });
    },
    removeBuilderTheme: function() {
      this.docClick('#remove-builder-theme', function(elem) {
        setTimeout(function() {
          jxml.ajax.removeBuilderTheme(elem)
        }, 100);
      });
    },
    updateParentTheme: function() {
      this.docClick('#update-parent-theme', function(elem) {
        $('#theme-update-message').html('');
        setTimeout(function() {
          jxml.ajax.updateParentTheme(elem)
        }, 100);
      });
    },
    updateParentThemeLibrary: function() {
      this.docClick('#update-parent-theme-library', function(elem) {
        $('#theme-update-message').html('');
        setTimeout(function() {
          jxml.ajax.updateParentThemeLibrary(elem)
        }, 100);
      });
    },
    addLayoutForm               : function() {
      this.docClick('.add_layout', function(elem) {
        jxml.ajax.addLayoutForm(elem);
      });
    },
    addLayout                   : function() {
      this.docClick('.save-layout', function(elem) {
        layout_name = $('input[name="layout_name"]').attr('value');
        if (jxml.validate.layoutName(layout_name)) {
          jxml.ajax.addLayout(elem, layout_name);
        } else {
          error_container = elem.parent('div');
          error_container.find('p.alert').remove();
          error_container.prepend('<p class="alert alert-warning text-left">' + jxml_layout_validate_error_text + '</p>');
        }
      });
    },
    loadLayoutContent           : function() {
      this.docClick('.jxml-layouts-list .jxlist-group-item:not(.active)', function(elem) {
        parent_element = elem.parent();
        parent_element.find('li').removeClass('active');
        parent_element.addClass('loading');
        elem.addClass('active');
        parent_element.prev('button').text(elem.find('a').text());
        jxml.ajax.loadLayoutContent(elem);
      });
    },
    resetToDefault              : function() {
      this.docClick('.reset-layouts', function(elem) {
        jxml.ajax.resetToDefault();
      });
    },
    addModuleConfirmation       : function() {
      this.docClick('.add-module', function(elem) {
        parentElem = elem.parents('.jxpanel-content');
        $('.add-module').removeClass('active');
        elem.addClass('active');
        jxml.ajax.addModuleConfirmation(parentElem);
      });
    },
    addExtraContentConfirmation       : function() {
      this.docClick('.add-extra-content', function(elem) {
        parentElem = elem.parents('.jxpanel-content');
        $('.add-extra-content').removeClass('active');
        elem.addClass('active');
        jxml.ajax.addExtraContentConfirmation(parentElem);
      });
    },
    loadLayoutTab               : function() {
      this.docClick('ul.nav.jxmegalayout-nav .layouts-tab', function(elem) {
        elem.append('<span class="ajax_running"><i class="icon-refresh icon-spin icon-fw"></i></span>');
        if (elem.hasClass('afterReset')) {
          setTimeout(function() {
            jxml.ajax.reloadTab($('ul.nav li.active .layouts-tab'));
          }, 100);
        } else {
          setTimeout(function() {
            jxml.ajax.loadLayoutTab($('ul.nav li.active .layouts-tab'));
          }, 100);
        }
      });
    },
    layoutPreview               : function() {
      this.docClick('#export_layout .layout-preview', function(elem) {
        jxml.ajax.layoutPreview(elem);
      });
    },
    deleteLayoutItem            : function() {
      this.docClick('.remove-item', function(elem) {
        jxml.ajax.deleteLayoutItem(elem.closest('div:not(.button-container)'));
      });
    },
    disableLayout               : function() {
      this.docClick('.disable-layout', function(elem) {
        var id_layout = elem.attr('data-layout-id');
        jxml.ajax.disableLayout(id_layout);
      });
    },
    enableLayout                : function() {
      this.docClick('.use-layout', function(elem) {
        var hook_name = elem.parents('.jxpanel-content').find('input[name="jxml_hook_name"]').val();
        var id_layout = elem.attr('data-layout-id');
        var pages     = elem.parents('.jxpanel-content').find('select[name="jxmegalayout-availible-pages"]').val();
        jxml.ajax.enableLayout(hook_name, id_layout, pages, 1);
      });
    },
    cleanImage                  : function() {
      this.docClick('a.clear-image', function(elem) {
        elem.parents('.input-group').find('input').val('');
      });
    },
    cleanImageNone              : function() {
      this.docClick('a.clear-image-none', function(elem) {
        elem.parents('.input-group').find('input').val('none');
      });
    },
    cleanItemStyles             : function() {
      this.docClick('.clear-styles', function(elem) {
        elem              = elem.parents('.form-wrapper');
        element_id_unique = elem.find('input[name="id_unique"]').val();
        jxml.ajax.clearItemStyles(elem, element_id_unique);
      });
    },
    saveItemStyles              : function() {
      this.docClick('.save-styles', function(elem) {
        jxml_item_styles  = {};
        elem              = elem.parents('.form-wrapper');
        element_id_unique = elem.find('input[name="id_unique"]').val();
        elem.find('select, input:not([name="id_unique"])').each(function(e) {
          if (jxml_style_value = $(this).val()) {
            if ($(this).attr('type') == 'radio') {
              if (typeof($(this).attr('checked')) != 'undefined') {
                jxml_item_styles[$(this).attr('name')] = jxml_style_value;
              }
            } else {
              jxml_item_styles[$(this).attr('name')] = jxml_style_value;
            }
          }
        });
        jxml.ajax.saveItemStyles(elem, element_id_unique, jxml_item_styles);
      });
    },
    editItemStyles              : function() {
      this.docClick('.edit-styles', function(elem) {
        $('.edit-styles').removeClass('active');
        elem.addClass('active');
        element_id_unique = elem.closest('div:not(.button-container)').attr('data-id-unique');
        jxml.ajax.getItemStyles(element_id_unique);
      });
    },
    getLayoutRemoveConfirmation : function() {
      this.docClick('.remove-layout', function(elem) {
        jxml.ajax.getLayoutRemoveConfirmation(elem.attr('data-layout-id'));
      });
    },
    removeLayout                : function() {
      this.docClick('.remove-layout-confirm', function(elem) {
        hook_name = $('.nav.nav-tabs li.active a.layouts-tab').attr('data-tab-name');
        jxml.ajax.removeLayout(elem.attr('data-layout-id'), hook_name);
        $.fancybox.close();
      });
    },
    getLayoutRenameConfirmation : function() {
      this.docClick('.edit-layout', function(elem) {
        jxml.ajax.getLayoutRenameConfirmation(elem.attr('data-layout-id'));
      });
    },
    renameLayout                : function() {
      this.docClick('.edit-layout-confirm', function(elem) {
        layout_name = elem.closest('.jxml-popup').find('input[name="layout_name"]').attr('value');
        if (jxml.validate.layoutName(layout_name)) {
          jxml.ajax.renameLayout(elem.attr('data-layout-id'), layout_name);
        } else {
          error_container = elem.parent('div');
          error_container.find('p.alert').remove();
          error_container.prepend('<p class="alert alert-warning text-left">' + jxml_layout_validate_error_text + '</p>');
        }
      });
    },
    addWrapper                  : function() {
      this.docClick('.add-wrapper', function(elem) {
        jxml.layout.add.wrapper(elem);
      });
    },
    editWrapper                 : function() {
      this.docClick('.edit-wrapper-confirm', function(elem) {
        parentElem = elem.closest('.jxml-popup');
        parentElem.find('.alert').remove();
        specific_class = parentElem.find('input[name="wrapper-classes"]').val().trim();
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        }
        jxml.layout.edit.wrapper($('.edit-wrapper.active').closest('div:not(.button-container)'), specific_class);
        $.fancybox.close($('.edit-wrapper').removeClass('active'));
      });
    },
    editWrapperConfirmation     : function() {
      this.docClick('.edit-wrapper', function(elem) {
        $('.edit-wrapper').removeClass('active');
        elem.addClass('active');
        fancyBoxOpen('wrapper', elem.closest('div:not(.button-container)').attr('data-specific-class'));
      });
    },
    addRow                      : function() {
      this.docClick('.add-row', function(elem) {
        jxml.layout.add.row(elem);
      });
    },
    editRowConfirmation         : function() {
      this.docClick('.edit-row', function(elem) {
        $('.edit-row').removeClass('active');
        elem.addClass('active');
        fancyBoxOpen('row', elem.closest('div:not(.button-container)').attr('data-specific-class'));
      });
    },
    editRow                     : function() {
      this.docClick('.edit-row-confirm', function(elem) {
        parentElem = elem.closest('.jxml-popup');
        parentElem.find('.alert').remove();
        specific_class = parentElem.find('input[name="row-classes"]').val().trim();
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        }
        jxml.layout.edit.row($('.edit-row.active').closest('div:not(.button-container)'), specific_class);
        $.fancybox.close($('.edit-row').removeClass('active'));
      });
    },
    addColumnConfirmation       : function() {
      this.docClick('.add-column', function(elem) {
        $('.add-column').removeClass('active');
        elem.addClass('active');
        fancyBoxOpen('col', elem);
      });
    },
    editColumnConfirmation      : function() {
      this.docClick('.edit-column', function(elem) {
        $('.edit-column').removeClass('active');
        elem.addClass('active');
        fancyBoxOpen('col', elem.closest('div:not(.button-container)'), 'edit');
      });
    },
    addColumn                   : function() {
      this.docClick('.add-column-confirm', function(elem) {
        parentElem     = elem.closest('.jxml-popup');
        specific_class = parentElem.find('input[name="col-specific-class"]').val().trim();
        c_             = parentElem.find('select[name="jxml-cols-col"]').val();
        xs_            = parentElem.find('select[name="jxml-cols-col-xs"]').val();
        sm_            = parentElem.find('select[name="jxml-cols-col-sm"]').val();
        md_            = parentElem.find('select[name="jxml-cols-col-md"]').val();
        lg_            = parentElem.find('select[name="jxml-cols-col-lg"]').val();
        xl_            = parentElem.find('select[name="jxml-cols-col-xl"]').val();
        xxl_           = parentElem.find('select[name="jxml-cols-col-xxl"]').val();
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        } else if (c_ == '' && xs_ == '' && sm_ == '' && md_ == '' && lg_ == '' && xl_ == '' && xxl_ == '') {
          parentElem.prepend('<p class="alert alert-danger">' + jxml_cols_validate_error + '</p>');
          return;
        }
        jxml.layout.add.col($('.add-column.active'), specific_class, c_, xs_, sm_, md_, lg_, xl_, xxl_);
        $.fancybox.close($('.add-column').removeClass('active'));
      });
    },
    editColumn                  : function() {
      this.docClick('.edit-column-confirm', function(elem) {
        parentElem     = elem.closest('.jxml-popup');
        specific_class = parentElem.find('input[name="col-specific-class"]').val().trim();
        c_             = parentElem.find('select[name="jxml-cols-col"]').val();
        xs_            = parentElem.find('select[name="jxml-cols-col-xs"]').val();
        sm_            = parentElem.find('select[name="jxml-cols-col-sm"]').val();
        md_            = parentElem.find('select[name="jxml-cols-col-md"]').val();
        lg_            = parentElem.find('select[name="jxml-cols-col-lg"]').val();
        xl_            = parentElem.find('select[name="jxml-cols-col-xl"]').val();
        xxl_           = parentElem.find('select[name="jxml-cols-col-xxl"]').val();
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        } else if (c_ == '' && xs_ == '' && sm_ == '' && md_ == '' && lg_ == '' && xl_ == '' && xxl_ == '') {
          parentElem.prepend('<p class="alert alert-danger">' + jxml_cols_validate_error + '</p>');
          return;
        }
        jxml.layout.edit.col($('.edit-column.active').closest('div:not(.button-container)'), specific_class, c_, xs_, sm_, md_, lg_, xl_, xxl_);
        $.fancybox.close($('.edit-column').removeClass('active'));
      });
    },
    addModule                   : function() {
      this.docClick('.add-module-confirm', function(elem) {
        parentElem         = elem.closest('.jxml-popup');
        specific_class     = parentElem.find('input[name="module-classes"]').val().trim();
        data_select_id     = parentElem.find('select').attr('data-select-id');
        module_name        = parentElem.find('select[name="jxml_module_' + data_select_id + '"]').val();
        public_module_name = parentElem.find('select[name="jxml_module_' + data_select_id + '"] option[value="' + module_name + '"]').text();
        origin_hook        = parentElem.find('select[name="jxml_module_' + data_select_id + '"] option[value="' + module_name + '"]:selected').attr('data-origin-hook');
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        }
        jxml.layout.add.module($('.add-module.active'), specific_class, module_name, public_module_name, origin_hook);
        $.fancybox.close($('.add-module').removeClass('active'));
      });
    },
    editModuleConfirmation      : function() {
      this.docClick('.edit-module', function(elem) {
        $('.edit-module').removeClass('active');
        elem.addClass('active');
        var unique_id = elem.parents('div.module').attr('data-id-unique')
        $.ajax({
          type     : 'POST',
          url      : jxml_theme_url + '&ajax',
          headers  : {"cache-control" : "no-cache"},
          dataType : 'json',
          async    : false,
          data     : {
            action    : 'getCssClassesByUnique',
            unique_id : unique_id
          },
          success  : function(response) {
            fancyBoxOpen('module', elem.closest('div:not(.button-container)').attr('data-specific-class'), 'edit', '', response.classes, response.active);
          }
        });
      });
    },
    editModule                  : function() {
      this.docClick('.edit-module-confirm', function(elem) {
        parentElem         = elem.closest('.jxml-popup');
        var specific_class = parentElem.find('input[name="module-classes"]').val().trim();
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        }
        var extra_css = parentElem.find('select[name="module-css"]').val();
        if (typeof(extra_css) == 'undefined') {
          extra_css = '';
        }
        jxml.layout.edit.module($('.edit-module.active').closest('div:not(.button-container)'), specific_class, '', '', '', extra_css);
        $.fancybox.close($('.edit-module').removeClass('active'));
      });
    },
    addExtraContent                   : function() {
      this.docClick('.add-extra-content-confirm', function(elem) {
        parentElem.find('p.alert').remove();
        parentElem         = elem.closest('.jxml-popup');
        var contentID = parseInt(parentElem.find('.extra-content-type-selector[name="'+parentElem.find('select[name="extra_content_type"]').val()+'"]').val());
        specific_class     = parentElem.find('input[name="extra-content-classes"]').val().trim();
        module_name        = parentElem.find('select[name="extra_content_type"]').val() + '-' + contentID;
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        }
        if (Number.isInteger(contentID) == false) {
          parentElem.prepend('<p class="alert alert-danger">' + jxml_id_validate_error + '</p>');
          return;
        }
        jxml.layout.add.content($('.add-extra-content.active'), specific_class, module_name, '', '');
        $.fancybox.close($('.add-module').removeClass('active'));
      });
    },
    editExtraContentConfirmation      : function() {
      this.docClick('.edit-extra-content', function(elem) {
        $('.edit-extra-content').removeClass('active');
        elem.addClass('active');
        var unique_id = elem.parents('div.extra-content').attr('data-id-unique')
        $.ajax({
          type     : 'POST',
          url      : jxml_theme_url + '&ajax',
          headers  : {"cache-control" : "no-cache"},
          dataType : 'json',
          async    : false,
          data     : {
            action    : 'getCssClassesByUnique',
            unique_id : unique_id
          },
          success  : function(response) {
            fancyBoxOpen('content', elem.closest('div:not(.button-container)').attr('data-specific-class'), 'edit', '', response.classes, response.active);
          }
        });
      });
    },
    editExtraContent               : function() {
      this.docClick('.edit-extra-content-confirm', function(elem) {
        parentElem         = elem.closest('.jxml-popup');
        var specific_class = parentElem.find('input[name="extra-content-classes"]').val().trim();
        if (error = jxml.validate.spClasses(specific_class)) {
          parentElem.prepend(error);
          return;
        }
        jxml.layout.edit.content($('.edit-extra-content.active').closest('div:not(.button-container)'), specific_class, '', '', '', '');
        $.fancybox.close($('.edit-extra-content').removeClass('active'));
      });
    },
    selectSection               : function() {
      this.docClick('#jxml-sections > ul > li', function(elem) {
        var section_name = elem.attr('data-section-name');
        var section_text = elem.find('a').text();
        elem.parents('.dropdown').find('button').text(section_text);
        $('#jxml-sections > ul > li.active').removeClass('active');
        elem.addClass('active');
        $('.jxmegalayout-nav > li:not(.jxml-tools_tab , .jxml-sections)').hide();
        $('.jxmegalayout-nav > li[data-section="' + section_name + '"]').show();
        $('.jxmegalayout-nav > li[data-section="' + section_name + '"]:first a').trigger('click');
      });
    },
    optionOptimize              : function() {
      this.docClick('#optionOptimize', function(elem) {
        jxml.ajax.optionOptimize(elem);
      });
    },
    optionDeoptimize            : function() {
      this.docClick('#optionDeoptimize', function(elem) {
        jxml.ajax.optionDeoptimize(elem);
      });
    },
    productPage                 : function() {
      this.docClick('#jxmegalayout-product-info-pages ul li a', function(elem) {
        var theme = elem.attr('data-theme-name');
        jxml.ajax.productInfoPageChange(elem, theme);
      });
    },
    optionShowMessages          : function() {
      this.docClick('#optionShowMessages, #hideMessages', function(elem) {
        jxml.ajax.optionShowMessages(elem);
      });
    },
    init                        : function() {
      this.layoutExport();
      this.loadToolTab();
      this.loadExtraContent();
      this.loadThemesContent();
      this.saveExtraContent();
      this.removeExtraContent();
      this.exportExtraContent();
      this.importExtraContent();
      this.saveBuilderTheme();
      this.removeBuilderTheme();
      this.updateParentTheme();
      this.updateParentThemeLibrary();
      this.addLayoutForm();
      this.addLayout();
      this.loadLayoutContent();
      this.resetToDefault();
      this.loadLayoutTab();
      this.layoutPreview();
      this.deleteLayoutItem();
      this.disableLayout();
      this.enableLayout();
      this.cleanImage();
      this.cleanImageNone();
      this.cleanItemStyles();
      this.saveItemStyles();
      this.editItemStyles();
      this.getLayoutRemoveConfirmation();
      this.removeLayout();
      this.getLayoutRenameConfirmation();
      this.renameLayout();
      this.addWrapper();
      this.editWrapper();
      this.editWrapperConfirmation();
      this.addRow();
      this.editRowConfirmation();
      this.editRow();
      this.addColumn();
      this.editColumn();
      this.addColumnConfirmation();
      this.editColumnConfirmation();
      this.addModuleConfirmation();
      this.addExtraContentConfirmation();
      this.editModuleConfirmation();
      this.editExtraContentConfirmation();
      this.addModule();
      this.addExtraContent();
      this.editModule();
      this.editExtraContent();
      this.selectSection();
      this.optionOptimize();
      this.optionDeoptimize();
      this.optionShowMessages();
      this.productPage();
    }
  },
  validate        : {
    layoutName       : function(name) {
      if ($.trim(name) == '') {
        return false;
      }
      for (i = 0; i < name.length; i++) {
        if (i == 0 && name[i] == '-') {
          return false;
        }
        if (/^[a-zA-Z0-9-]*$/.test(name[i]) == false) {
          return false;
        }
      }
      return true;
    },
    styleClr         : function(content) {
      colorProhibitedString = "~!@$%^&*_+=`{}[]|\:;'<>/?-";
      for (i = 0; i < colorProhibitedString.length; i++) {
        if (content.indexOf(colorProhibitedString[i]) != -1) {
          return false;
        }
      }
      return true;
    },
    styleShdw        : function(content) {
      prohibitedString = "~!@$%^&*_+=`{}[]|\:;'<>/?";
      for (i = 0; i < prohibitedString.length; i++) {
        if (content.indexOf(prohibitedString[i]) != -1) {
          return false;
        }
      }
      return true;
    },
    styleDmns        : function(content) {
      if (content == 0) {
        return true;
      }
      dimension = content.substr(content.length - 2);
      value     = content.replace(dimension, '');
      if (!dimension || (dimension != 'px' && dimension != 'em') || !$.isNumeric(value) || !value) {
        return false;
      }
      return true;
    },
    styleCheckErrors : function() {
      stylesBtn       = $('.save-styles');
      jxmlStyleErrors = false;
      $('.jxmegalayout-styles').find('input').each(function() {
        if ($(this).hasClass('error')) {
          jxmlStyleErrors = true;
        }
      });
      if (jxmlStyleErrors) {
        stylesBtn.addClass('disabled');
      } else {
        stylesBtn.removeClass('disabled');
      }
    },
    styleInput       : function(elem) {
      jxml_input_content = elem.val();
      jxml_input_type    = elem.attr('data-type');
      if ($.trim(jxml_input_content) == '') {
        elem.val('');
        result = true;
      } else if (jxml_input_type == 'dmns') {
        result = this.styleDmns(jxml_input_content);
      } else if (jxml_input_type == 'shdw') {
        result = this.styleShdw(jxml_input_content);
      } else if (jxml_input_type == 'clr') {
        result = this.styleClr(jxml_input_content);
      }
      if (!result) {
        elem.addClass('error');
      } else {
        elem.removeClass('error');
      }
      this.styleCheckErrors();
    },
    spClasses        : function(clasess) {
      jxml_prohibited_chars    = "<>@!#$%^&*()+[]{}?:;|'\"\\,./~`=";
      jxml_classes_to_validate = clasess.trim().split(' ');
      if (jxml_classes_to_validate.length && jxml_classes_to_validate != '') {
        for (i = 0; i < jxml_classes_to_validate.length; i++) {
          if (!jxml_classes_to_validate[i][0].match(/^([a-z\(\)]+)$/i)) {
            return '<p class="alert alert-danger">' + jxml_class_validate_error + '</p>';
          }
          for (k = 0; k < jxml_prohibited_chars.length; k++) {
            if (jxml_classes_to_validate[i].indexOf(jxml_prohibited_chars[k]) > -1) {
              return '<p class="alert alert-danger">' + jxml_class_validate_error + '<button class="close" aria-label="close" data-dismiss="alert" type="button">×</button></p>';
            }
          }
        }
      }
      return false;
    }
  },
  layout          : {
    add  : {
      wrapper : function(elem) {
        jxml.ajax.saveLayoutItem(false, elem, 'wrapper', '', '', '', '', '', '', '', '', '', '', '', '', '');
      },
      row     : function(elem) {
        jxml.ajax.saveLayoutItem(false, elem, 'row', '', '', '', '', '', '', '', '', '', '', '', '', '');
      },
      col     : function(elem, specific_class, col, col_xs, col_sm, col_md, col_lg, col_xl, col_xxl) {
        classes = 'col ' + col + ' ' + col_xs + ' ' + col_sm + ' ' + col_md + ' ' + col_lg + ' ' + col_xl + ' ' + col_xxl + ' ' + specific_class;
        jxml.ajax.saveLayoutItem(false, elem, 'col', classes, specific_class, col, col_xs, col_sm, col_md, col_lg, col_xl, col_xxl, '', '', '', '');
      },
      module  : function(elem, specific_class, module_name, public_module_name, origin_hook) {
        classes = 'module ' + specific_class;
        jxml.ajax.saveLayoutItem(false, elem, 'module', classes, specific_class, '', '', '', '', '', '', '', module_name, public_module_name, origin_hook, '');
      },
      content  : function(elem, specific_class, module_name, public_module_name, origin_hook) {
        classes = 'content ' + specific_class;
        jxml.ajax.saveLayoutItem(false, elem, 'content', classes, specific_class, '', '', '', '', '', '', '', module_name, public_module_name, origin_hook, '');
      }
    },
    edit : {
      wrapper : function(elem) {
        id_element = elem.attr('data-id');
        id_unique  = elem.attr('data-id-unique');
        classes    = 'wrapper sortable ' + id_unique + ' ' + specific_class;
        $('.jxmegalayout-admin .wrapper[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
        $('.jxmegalayout-admin .wrapper[data-id="' + id_element + '"] > article > .button-container .element-name').find('.identificator').text('(' + specific_class.replace(' ', ' | ') + ')');
        jxml.ajax.saveLayoutItem(id_element, elem, 'wrapper', classes, specific_class, '', '', '', '', '', '', '', '', '', '', '');
      },
      row     : function(elem, specific_class) {
        id_element = elem.attr('data-id');
        id_unique  = elem.attr('data-id-unique');
        classes    = 'row sortable ' + id_unique + ' ' + specific_class;
        $('.jxmegalayout-admin .row[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
        $('.jxmegalayout-admin .row[data-id="' + id_element + '"] > article > .button-container .element-name').find('.identificator').text('(' + specific_class.replace(' ', ' | ') + ')');
        jxml.ajax.saveLayoutItem(id_element, elem, 'row', classes, specific_class, '', '', '', '', '', '', '', '', '', '', '');
      },
      col     : function(elem, specific_class, col, col_xs, col_sm, col_md, col_lg, col_xl, col_xxl) {
        id_element = elem.attr('data-id');
        id_unique  = elem.attr('data-id-unique');
        classes    = 'col sortable ' + id_unique + ' ' + col + ' ' + col_xs + ' ' + col_sm + ' ' + col_md + ' ' + col_lg + ' ' + col_xl + ' ' + col_xxl + ' ' + specific_class;
        $('.jxmegalayout-admin .col[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('data-col', col).attr('data-col-xs', col_xs).attr('data-col-sm', col_sm).attr('data-col-md', col_md).attr('data-col-lg', col_lg).attr('data-col-xl', col_xl).attr('data-col-xxl', col_xxl).attr('class', classes);
        $('.jxmegalayout-admin .col[data-id="' + id_element + '"] > article > .button-container .element-name').find('.identificator').text('(' + specific_class.replace(' ', ' | ') + ')');
        jxml.ajax.saveLayoutItem(id_element, elem, 'col', classes, specific_class, col, col_xs, col_sm, col_md, col_lg, col_xl, col_xxl, '', '', '', '');
      },
      module  : function(elem, specific_class, module_name, public_module_name, origin_hook, extra_css) {
        id_element  = elem.attr('data-id');
        id_unique   = elem.attr('data-id-unique');
        module_name = elem.attr('data-module');
        classes     = 'module sortable ' + id_unique + ' ' + specific_class;
        $('.jxmegalayout-admin .module[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
        $('.jxmegalayout-admin .module[data-id="' + id_element + '"] > article > .button-container .module-name').find('.identificator').text('(' + specific_class.replace(' ', ' | ') + ')');
        jxml.ajax.saveLayoutItem(id_element, elem, 'module', classes, specific_class, '', '', '', '', '', '', '', module_name, '', origin_hook, extra_css);
      },
      content  : function(elem, specific_class, module_name, public_module_name, origin_hook, extra_css) {
        id_element  = elem.attr('data-id');
        id_unique   = elem.attr('data-id-unique');
        module_name = elem.attr('data-extra-content');
        classes     = 'content sortable ' + id_unique + ' ' + specific_class;
        $('.jxmegalayout-admin .content[data-id="' + id_element + '"]').attr('data-specific-class', specific_class).attr('class', classes);
        $('.jxmegalayout-admin .content[data-id="' + id_element + '"] > article > .button-container .content-name').find('.identificator').text('(' + specific_class.replace(' ', ' | ') + ')');
        jxml.ajax.saveLayoutItem(id_element, elem, 'content', classes, specific_class, '', '', '', '', '', '', '', module_name, '', origin_hook, extra_css);
      }
    }
  },
  sortInit        : function() {
    $('.jxmegalayout-admin, .jxmegalayout-admin article, .jxmegalayout-admin article article').sortable({
      cursor : 'move',
      items  : '> div.col, > div.row, > div.wrapper, > div.module, > div.content',
      update : function(event, ui) {
        $(this).find('> div.sortable').each(function(index) {
          index = index + 1;
          $(this).attr('data-sort-order', index);
          $(this).find('.sort-order').text(index);
        });
        jxml.ajax.updateSortOrders($(this));
      }
    });
  },
  tooltipInit     : function() {
    $('span.module-name').tooltip(),
    $('span.content-name').tooltip()
  },
  multiselectInit : function() {
    $('.jxmegalayout-availible-pages').multiselect({
      enableFiltering   : true,
      optionClass       : function(element) {
        return 'col-xs-6 col-sm-4';
      },
      buttonClass       : 'btn btn-link',
      nonSelectedText   : jxml_multiselect_all_text,
      filterPlaceholder : jxml_multiselect_search_text,
      onDropdownShow    : function(event) {
        multiselect_temp = this.$button.parents('.jxlist-layout-buttons').find('select').val();
        if (!multiselect_temp) {
          multiselect_temp = '';
        }
      },
      onDropdownHide    : function(event) {
        var elem          = $('.jxlist-layout-buttons .btn-group.open');
        var hook_name     = elem.parents('.jxpanel-content').find('input[name="jxml_hook_name"]').val();
        var id_layout     = elem.parents('.jxlist-layout-buttons').find('.jxlist-layout-btns').attr('data-layout-id');
        var pages         = elem.parents('.jxpanel-content').find('select[name="jxmegalayout-availible-pages"]').val();
        var layout_status = 0;
        if (elem.parents('.jxpanel-content .jxlist-layout-buttons').find('a.use-layout').hasClass('hidden')) {
          var layout_status = 1;
        }
        multiselect_now = this.$button.parents('.jxlist-layout-buttons').find('select').val();
        if (!multiselect_now) {
          multiselect_now = '';
          if (!layout_status) {
            pages = 'empty';
          }
        }
        if (multiselect_temp.toString() != multiselect_now.toString()) {
          jxml.ajax.enableLayout(hook_name, id_layout, pages, layout_status);
        }
      },
      templates         : {
        filter         : '<li class="multiselect-item filter"><div class="input-group"><span class="input-group-addon"><i class="icon icon-search"></i></span><input class="form-control multiselect-search" type="text"></div></li>',
        filterClearBtn : '<span class="input-group-btn"><button class="btn btn-default multiselect-clear-filter" type="button"><i class="icon icon-remove-circle"></i></button></span>'
      }
    });
  },
  get             : {
    sortOrder : function(elem) {
      sort_order = [];
      elem.closest('article').find('> div.sortable').each(function() {
        sort_order.push($(this).attr('data-sort-order'));
      });
      if ($.isEmptyObject(sort_order)) {
        sort_order = 1;
      } else {
        sort_order = Math.max.apply(Math, sort_order) + 1;
      }
      return sort_order;
    },
    parentId  : function(elem) {
      parent_id = elem.closest('div:not(.button-container)').attr('data-id');
      if (typeof (parent_id) == 'undefined' && !parent_id) {
        parent_id = 0;
      }
      return parent_id;
    },
    elementId : function() {
      ids_items = [];
      $('.container').find('div[data-id]').each(function() {
        ids_items.push($(this).attr('data-id'));
      });
      if ($.isEmptyObject(ids_items)) {
        id_item = 1;
      } else {
        id_item = Math.max.apply(Math, ids_items) + 1;
      }
      return id_item;
    }
  },
  init            : function() {
    this.sortInit();
    this.tooltipInit();
    this.multiselectInit();
    this.events.init();
  }
};