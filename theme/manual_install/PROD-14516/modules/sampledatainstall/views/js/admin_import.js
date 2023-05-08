/**
 * 2017-2018 Zemez
 *
 * Sampledatainstall
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
 *  @author    Zemez (Alexander Grosul)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

$(document).ready(function() {
  var upload_files_html5 = $("#upload_files_html5"),
      upload_button = $('.upload-files'),
      drop_zone = $('body'),
      upload_table = $('#file_list'),
      continue_install = $('.next_step'),
      successMessage = $('#successMessage'),
      files_array = new Array(),
      row_class='alternate',
      last_add_file,
      drop_file_list,
      loaded_settings_file = false,
      file_error = false,
      settings_list = new Array(),
      file_true = false,
      load_all = false,
      load_logos = false,
      load_modules = false,
      load_content = false,
      all_files = false;

  drop_zone.on('dragover', function() {
    $('#upload_files').addClass('hover');
    return false;
  }).on('dragleave', function() {
    $('#upload_files').removeClass('hover');
    return false;
  }).on('drop', function(event) {
    if (!load_content && !load_logos && !load_modules) {
      showConfirmationPopup();
      all_files = event.originalEvent.dataTransfer.files;
    } else {
      get_file_list(event.originalEvent.dataTransfer.files);
    }
    return false;
  });

  upload_button.on('click', add_more_files);

  upload_files_html5.on('change', function() {
    if (!load_content && !load_logos && !load_modules) {
      showConfirmationPopup();
      all_files = $(this)[0].files
    } else {
      get_file_list($(this)[0].files);
    }
  });

  $(document).on('click', '#confirm-data-installation', function(e) {
    $.fancybox.close();
    if (setLoadingDataTypes()) {
      get_file_list(all_files);
    } else {
      $('#upload_files').removeClass('hover');
    }
  });

  $(document).on('click', '.selected-type, input[name="all"]', function() {
    if ($(this).hasClass('active')) {
      $(this).removeClass('active');
      $('input[name="all"]').attr('checked', false);
      $('input[name="logos"]').parents('li').removeClass('disabled');
      $('input[name="content"]').parents('li').removeClass('disabled');
      $('input[name="modules"]').parents('li').removeClass('disabled');
    } else {
      $(this).addClass('active');
      $('input[name="all"]').attr('checked', true);
      $('input[name="logos"]').parents('li').addClass('disabled');
      $('input[name="content"]').parents('li').addClass('disabled');
      $('input[name="modules"]').parents('li').addClass('disabled');
    }
  });

  $(document).on('click', '#download-type li', function(e) {
    e.preventDefault();
    if ($(this).hasClass('disabled') == false) {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active').addClass('not-active').find('input').attr('checked', false);
      } else {
        $(this).removeClass('not-active').addClass('active').find('input').attr('checked', true);
      }
    }
  });

  $(document).on('click', '.sdi_show_error_info', function() {
    $(this).next('.sdi_show_error_info_container').toggle();
    return false;
  });

  $(document).on('click', '.install-uploaded-data', function() {
    load_all = true;
    sampledataimport.run('clearTemporaryConfigEntries');
    $('#import_step_2').removeClass('hidden_ell');
    $('#import_xml_status').removeClass('hidden_ell');
    $('#file_list_holder').addClass('hidden_ell');
    $('#importing_warning').addClass('hidden_ell');
    $('#upload_status, #info_holder').addClass('hidden_ell');
    $(this).off('click').addClass('hidden_ell');
    return false;
  });

  function showConfirmationPopup() {
    $.fancybox.open([
      {
        type: 'inline',
        autoScale: true,
        minHeight: 440,
        maxHeight: 440,
        minWidth: 670,
        maxWidth: 670,
        content: confirmationPopup,
        helpers: {
          overlay: {
            locked: false
          }
        },
        padding : 0
      }
    ], {
      padding: 15
    });
  }

  function setLoadingDataTypes() {
    load_all = $('.confirm-data-container input[name="all"]').prop('checked');
    load_logos = $('.confirm-data-container input[name="logos"]').prop('checked');
    load_content = $('.confirm-data-container input[name="content"]').prop('checked');
    load_modules = $('.confirm-data-container input[name="modules"]').prop('checked');
    if (!load_logos && !load_content && !load_modules && !load_all) {
      return false;
    }
    return true;
  }

  function add_more_files() {
    upload_files_html5.click();
    return !1;
  }

  function get_file_list(file_list) {
    upload_button.off();
    drop_file_list = file_list;
    last_add_file = 0;
    $('#upload_status .loader_bar span b').css({'width':'0%'});
    $('#upload_files').removeClass('hover');
    $("form#upload_files").removeClass('add_files');
    upload_table.parents('#import_step_2').removeClass('hidden_ell');
    $('#data_location_message').addClass('hidden_ell');
    $('.drop_icon').addClass('second_step');
    add_file(drop_file_list[last_add_file]);
  }

  function filter_files_list(file_name) {
    var file_type = file_name.split('.');
    if (file_type[1] == 'vsc' && file_type[0] != 'settings' && !load_content) {
      // upload configuration file if logo's information upload but content doesn't upload
      if ((load_logos || load_modules) && file_type[0] == 'configurations') {
        return true;
      }
      return false;
    }
    if(file_type[1] == 'lqs' && !load_modules) {
      return false;
    }
    if (in_array(['gpj', 'gnp', 'fig'], file_type[1]) && !load_content) {
      if (file_name.indexOf('#c@') != -1 ||
        file_name.indexOf('#p#') != -1 ||
        file_name.indexOf('#m@') != -1 ||
        file_name.indexOf('#su@') != -1 ||
        file_name.indexOf('#cms@') != -1 ||
        file_name.indexOf('#s@') != -1) {
        return false;
      }
    }
    if (in_array(['gpj', 'gnp', 'fig', '4pm', 'vgo', 'mbew'], file_type[1]) && !load_modules) {
      if (file_name.indexOf('modules#') != -1) {
        return false;
      }
    }
    if (in_array(['gpj', 'gnp', 'oci', 'fig'], file_type[1]) && !load_logos) {
      if (file_name.indexOf('img@') != -1 && file_name.indexOf('#img@') === -1) {
        return false;
      }
    }

    return true;
  }

  function add_file(file) {
    var file_name = file.name,
        file_type = file.type;
    file_type = file_type.replace(' ', '');
    var accept_file = true;
    if (!load_all) {
      var accept_file = filter_files_list(file.name);
    }
    last_add_file++;
    if (accept_file) {
      if (!in_array(files_array, file_name)) {
        var upload_file_num = files_array.length - 1,
            file_size = file.size,
            file_size_type = ['B', 'KB', 'MB', 'GB'],
            sss = sss++;

        files_array.push(file_name);
        row_class = row_class == 'alternate' ? '' : 'alternate';

        // check and define if the settings file is loaded
        if (file_name.indexOf('settings.vsc') != -1) {
          loaded_settings_file = true;
        }

        file_name.indexOf('.mbew') != -1 ||
        file_name.indexOf('download@') != -1 ||
        file_name.indexOf('.vgo') != -1 ||
        file_name.indexOf('.4pm') != -1 ||
        file_name.indexOf('.gpj') != -1 ||
        file_name.indexOf('.gnp') != -1 ||
        file_name.indexOf('.fig') != -1 ||
        file_name.indexOf('.lqs') != -1 ||
        file_name.indexOf('.oci') != -1 ||
        file_name.indexOf('.vsc') != -1 ? format_error = false : format_error = true;

        for (var i = 0; file_size > 1024 && i < file_size_type.length - 1; i++) {
          file_size /= 1024;
        }

        $('#file_list_body', upload_table).prepend('<div id="file_status_' + upload_file_num + '" class="row ' + row_class + '" ><div class="column_1">' + file_name + '</div><div class="column_2">' + file_size.toFixed(2) + ' ' + file_size_type[i] + '</div><div class="column_3"><span class="file_progress_bar"></span><span class="file_progress_text">' + import_text['upload'] + ' <span class="load_percent">0</span> %</span></div></div>');

        if (format_error) {
          $('#file_status_' + upload_file_num).addClass('error_file').find('.file_progress_text').html(import_text['format_error']);
          file_error = true;
          switch_file(last_add_file, false);
        } else if (file.size > max_file_size) {
          $('#file_status_' + upload_file_num).addClass('error_file').find('.file_progress_text').html(import_text['error_size']);
          file_error = true;
          switch_file(last_add_file, false);
        } else if (file.name.indexOf('.') == -1 && file.name.indexOf('download/') != -1 && file.type == "") {
          $('#file_status_' + upload_file_num).addClass('error_file').find('.file_progress_text').html(import_text['error_folder']);
          file_error = true;
          switch_file(last_add_file, false);
        } else {
          var form_data = new FormData();
          form_data.append('file', file);
          file_true = true;
          send_file(form_data, upload_file_num, file.name, file.size);
        }
      } else {
        alert(import_text['error_added']);
        $('#file_status_' + upload_file_num).addClass('error_file').find('.file_progress_text').html(import_text['error_added']);
        switch_file(last_add_file, false);
      }
    } else {
      switch_file(last_add_file, true);
    }
  }

  function send_file(file_to_send, file_num, fileName, fileSize) {
    var xhr = new XMLHttpRequest();
    xhr.onload = function(data) {
      var file_status_row =  $('#file_status_'+file_num),
          loader_bar = $('.file_progress_bar', file_status_row);

      $('.load_percent', file_status_row).text('100');
      loader_bar.css({'width':'100%'});
      setTimeout(function() {
        loader_bar.addClass('transition').css({'opacity':0});
      },500);

      switch_file(last_add_file, true);
    };
    xhr.upload.onprogress = function(event) {
      upload_progress(event, file_num);
    };
    xhr.open('POST', importDir, true);
    xhr.setRequestHeader("Cache-Control", "no-cache");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader('X-FILE-NAME', fileName);
    xhr.setRequestHeader('X-FILE-SIZE', fileSize);
    xhr.send(file_to_send);
  }

  function upload_progress(event, file_num) {
    var percent = parseInt((event.loaded / event.total) * 100);
    $('.load_percent', '#file_status_'+file_num).text(percent);
    $('.file_progress_bar', '#file_status_'+file_num).css({'width':percent+'%'});
  }

  function switch_file(file_num, status) {
    if(status) {
      var percent = parseInt(file_num / drop_file_list.length * 100);
      $('#upload_status .loader_bar span').css({'width':percent+'%'});
      $('#upload_status .loader_bar .loaded-text').html(percent+'%');
      $('#upload_counter b').html(parseInt($('#upload_counter b').text())+1);
    }
    if(drop_file_list[file_num]) {
      add_file(drop_file_list[file_num]);
    } else {
      setTimeout(function() {
        load_all_content();
      }, 1000);
    }
  }

  function load_all_content() {
    if(file_error == true && file_true == false) {
      $('#info_holder').removeClass().addClass('alert alert-danger');
      $('#info_holder').find('.upload_status_text').html(import_text['uploaded_status_text_2']);
    } else if (file_error == true && file_true == true) {
      $('#info_holder').removeClass().addClass('alert alert-warning');
      $('#info_holder').find('.upload_status_text').html(import_text['uploaded_status_text_3']);
    } else if (loaded_settings_file == false) {
      $('#info_holder').removeClass().addClass('alert alert-warning');
      $('#info_holder').find('.upload_status_text').html(import_text['uploaded_status_text_6']);
    } else {
      $('#warning_holder').find('p').remove();
      $('#info_holder').removeClass().addClass('alert alert-success');
      $('#info_holder').find('.upload_status_text').html(import_text['uploaded_status_text_4']);
    }

    upload_button.on('click', add_more_files);
    continue_install.off();
    if(loaded_settings_file == true) {
      settings_list = get_sampledata_settings();
      files_list = new Array();
      missed_files = '';

      for (i = 0; i<settings_list.length; i++) {
        if ($.isArray(settings_list[i])) {
          files_list = settings_list[i];
          for (i = 0; i < files_list.length; i++) {
            if ($.inArray(files_list[i], files_array) == -1) {
              missed_files += ' '+files_list[i]+',';
            }
          }
        } else {
          $('#warning_holder').prepend('<p class="alert alert-warning">' + settings_list[i] + '</p>');
        }
      }

      if(file_error == true || missed_files.length) {
        $('#info_holder p .upload_status_text').html(import_text['uploaded_status_text_3']);
        continue_install.off();
      } else {
        $('#warning_holder').find('p.list').remove();
        $('#info_holder p .upload_status_text').html(import_text['uploaded_status_text_1']);
      }

      if (missed_files.length) {
        $('#warning_holder').find('p.list').remove();
        $('#warning_holder').prepend('<p class="alert alert-warning list">'+import_text['files_missed_text']+'<br />'+missed_files.substring(0, missed_files.length - 1)+'</p>');
      }

      $("#not_load_file").addClass('hidden_ell');
      $('#upload_status').addClass('upload_done');
      if (!missed_files.length) {
        continue_install.removeClass('not_active').on('click', function() {
          drop_zone.off();
          upload_button.off();
          upload_files_html5.off();

          if(loaded_settings_file){
            sampledataimport.run('clearTemporaryConfigEntries');
            $("#install_sample_form").submit();
          }

          $('#warning_holder').find('p.list').remove();
          $('#info_holder').removeClass('alert-success').addClass('alert-warning');
          $('#info_holder').find('.upload_status_text').html(import_text['uploaded_status_text']);
          $('#info_holder').find('.upload-files').addClass('hidden_ell');
          $('#import_xml_status').removeClass('hidden_ell');
          $('#file_list_holder').addClass('hidden_ell');
          $('#importing_warning').addClass('hidden_ell');
          $('#upload_status, #info_holder').addClass('hidden_ell');
          $(this).off('click').addClass('hidden_ell');

          return false;
        });
      }
    } else {
      continue_install.on('click', function() {
        $("#not_load_file").removeClass('hidden_ell');
      })
    }
  }

  function get_sampledata_settings() {
    var result = new Array();
    $.ajax({
      type:'POST',
      url: importDir,
      headers: { "cache-control": "no-cache" },
      dataType: 'json',
      async: false,
      data: {
        action: 'getSettigs',
        modules: load_modules,
        logos: load_logos,
        content: load_content
      },
      success: function(msg) {
        if (msg.current_ps_ver != msg.sd_ps_ver) {
          result.push(import_text['diferent_ps_version'] + import_text['current_ps_version'] + msg.current_ps_ver + import_text['sd_ps_version'] + msg.sd_ps_ver);
        }
        if (msg.current_db_ver != msg.sd_db_ver) {
          result.push(import_text['diferent_db_version'] + import_text['current_db_version'] + msg.current_db_ver + import_text['sd_db_version'] + msg.sd_db_ver);
        }
        result.push(msg.files_list);
      }
    });

    return result;
  }

  function getCurrentStatus(msg) {
    $.ajax({
      type:'POST',
      url: importDir,
      headers: { "cache-control": "no-cache" },
      dataType: 'json',
      data: {
        action: 'getCurrentStatus'
      },
      success:function(response) {
        showSDIError(response.responseText, msg);
        console.log(response);
      }
    });
  }

  function add_text_status(text_index) {
    $('#info_holder').addClass('hidden_ell');
    if(text_index == 'import_complete') {
      $('#status_log').html('<p class ="done_import"><i class ="process-icon-ok"></i>'+import_text['import_complete']+'</p>');
      $('#success_install').removeClass('hidden_ell');
    }
  }

  function error_status() {
    $('#status_log p:last-child').removeClass().addClass('error_import').find('i').removeClass().addClass('process-icon-remove');
    $('#status_log').html('<p class="error_import"><i class ="icon-warning-sign"></i>'+import_text['instal_error']+'</p>');
    $('#import_data .loader_bar span').css({'width':'100%', 'background':'red'});
  }

  function in_array(array, value) {
    for(var i=0; i < array.length; i++) {
      if (array[i] == value) {
        return true;
      }
    }
    return false;
  }

  function showSDIError(msg, link) {
    if (!link) {
      $('#import_status').prepend('<p class="alert alert-danger">' + msg + '</p>');
    } else {
      $('#import_status').prepend('<div class="alert alert-danger"><a href="#" class="sdi_show_error_info">' + msg + '</a><div class="sdi_show_error_info_container">' + link + '</div></div>');
    }
  }

  first_step_is_done = false;
  var sampledataimport = {
    run: function(method) {
      var progress_bar = $('#import_xml_status').find('.loader');
      sampledataimport.doProgress('');
      this.doStep(method).then(function(response) {
        progress_bar.text(response.progress+'%');
        sampledataimport.doProgress(response.info);

        return response.method;
      }).then(function(data) {
        if (data !== 'finished') {
          sampledataimport.run(data);
        } else {
          //progress_bar_current.animate({width: '100%'});
          add_text_status('import_complete');
        }
      });
    },
    doStep: function(method) {
      return new Promise(function(resolve, reject) {
        $.post(importDir, 'action=installData&method=' + method +'&modules='+load_modules+'&logos='+load_logos+'&content='+load_content+'&all='+load_all).then(function(response) {
          var result = JSON.parse(response);
          if (result.status) {
            resolve(result);
          } else {
            error_status();
            sampledataimport.errorProgress();
            var error = new Error(result.response);
            reject(error);
          }
        }).fail(function(response) {
          sampledataimport.errorProgress();
          getCurrentStatus(response.responseText);
          reject(response);
        })
      });
    },
    doProgress: function(text) {
      var current_step = $('#progress-list ul li[data-item="1"]');
      if ($('#progress-list ul li.current').length && first_step_is_done && text != '') {
        var current_step_num = parseInt($('#progress-list ul li.current').attr('data-item')) + 1;
        current_step = $('#progress-list ul li[data-item="'+current_step_num+'"]');
        $('#progress-list ul li.current').removeClass('current').addClass('done');
        current_step.addClass('current').text(text);
      }
      if (!first_step_is_done) {
        current_step.text(import_text['import_first_step_description']).addClass('current');
        first_step_is_done = true;
      }
    },
    errorProgress: function() {
      $('#progress-list ul li.current').removeClass('current').addClass('error');
    }
  }
});
