/**
 * 2017-2018 Zemez
 *
 * JX Blog Post Posts
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
  jxblogpost.autocompleteInit($('#posts_autocomplete_input'), $('input[name="ajax_url"]').val()+'&ajax=1&action=searchPosts');
  $('#divposts').delegate('.delposts', 'click', function() {
    jxblogpost.delPost($(this).attr('name'));
  });
});
i = 0;

jxblogpost = {
  autocompleteInit : function(block, url) {
    block.autocomplete(url, {
      minChars      : 3,
      autoFill      : true,
      max           : 20,
      matchContains : true,
      mustMatch     : false,
      scroll        : false,
      cacheLength   : 0,
      formatItem    : function(item) {
        return item[1] + ' - ' + item[0];
      }
    }).result(this.addPost);
    block.setOptions({
      extraParams : {
        excludeIds : this.getPostIds()
      }
    });
  },
  getPostIds       : function() {
    var result = $('#inputposts').val().replace(/\-/g, ',');
    if ($('input[name="id_jxblog_post"]').length) {
      result = result + $('input[name="id_jxblog_post"]').val();
    }
    return result;
  },
  addPost          : function(event, data, formatted) {
    if (data == null) {
      return false;
    }
    var postId      = data[1];
    var postName    = data[0];
    var $divPosts   = $('#divposts');
    var $inputPosts = $('#inputposts');
    var $namePosts  = $('#nameposts');
    $divPosts.html($divPosts.html() + '<div class="form-control-static"><button type="button" class="delposts btn btn-default" name="' + postId + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + postName + '</div>');
    $namePosts.val($namePosts.val() + postName + '¤');
    $inputPosts.val($inputPosts.val() + postId + '-');
    $('#posts_autocomplete_input').val('');
    $('#posts_autocomplete_input').setOptions({
      extraParams : {excludeIds : jxblogpost.getPostIds()}
    });
  },
  delPost          : function(id) {
    var div      = getE('divposts');
    var input    = getE('inputposts');
    var name     = getE('nameposts');
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
        div.innerHTML += '<div class="form-control-static"><button type="button" class="delposts btn btn-default" name="' + inputCut[i] + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</div>';
      }
      else {
        input.value += '-';
        name.value += '¤';
      }
    }
    $('#posts_autocomplete_input').setOptions({
      extraParams : {excludeIds : jxblogpost.getPostIds()}
    });
  }
};