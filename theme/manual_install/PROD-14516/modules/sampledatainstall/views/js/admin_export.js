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
  var progress_bar = $('#progress-bar');
  var progress_bar_current = progress_bar.find('.current-progress');
  var progress_bar_info = progress_bar.find('.info-text');
  $(document).on('click', 'button[name="submitExport"], .ajax-export', function(event) {
    event.preventDefault();
    progress_bar_current.removeClass('error');
    exportLanguageId = $('select[name="export_language"]').val();
    sampledataexport.run('clearOutputDirectory');
  });

  var sampledataexport = {
    run: function(method) {
      /*$.post(exportDir, 'ajax=true&method='+method).then(function(respose) {
        console.log(respose);
      });*/
      this.doStep(method).then(function(response) {
        progress_bar.removeClass('hidden');
        progress_bar_current.animate({width: response.progress+'%'});
        progress_bar_info.text(response.info);

        return response.method;
      }).then(function(data) {
        if (data !== 'finished') {
          sampledataexport.run(data);
        } else {
          progress_bar_current.animate({width: '100%'});
          sampledataexport.getArchive();
        }
      });
    },
    doStep: function(method) {
      return new Promise(function(resolve, reject) {
        $.post(exportDir, 'ajax=true&export_language='+exportLanguageId+'&method='+method).then(function(response) {
          var result = JSON.parse(response);
          if (result.status) {
            resolve(result);
          } else {
            progress_bar_current.addClass('error');
            var error = new Error(result.response);
            reject(error);
          }
        }).fail(function(xhr) {
          reject(xhr);
        })
      });
    },
    getArchive: function() {
      $.post(exportDir, 'getArchive=true').then(function(response) {
        var result = JSON.parse(response);
        location.href = result.href;
      });
    }
  }
});
