/**
 * 2017 Zemez
 *
 * JX Blog Comment
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
 *  @copyright 2017 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */
$(function() {
  var id_jxblog_post = $('#blog-comments').attr('data-post-id');
  var saveComment    = function(data) {

    // Convert pings to human readable format
    $(data.pings).each(function(index, id) {
      var user     = usersArray.filter(function(user) {
        return user.id == id
      })[0];
      data.content = data.content.replace('@' + id, '@' + user.fullname);
    });
    return data;
  };
  $('#blog-comments-container').comments({
    profilePictureURL                : jxCurrentUserIcon,
    textareaPlaceholderText          : jxTextareaPlaceholderText,
    newestText                       : jxNewestText,
    oldestText                       : jxOldestText,
    popularText                      : jxPopularText,
    attachmentsText                  : jxAttachmentsText,
    sendText                         : jxSendText,
    replyText                        : jxReplyText,
    editText                         : jxEditText,
    editedText                       : jxEditedText,
    youText                          : jxYouText,
    saveText                         : jxSaveText,
    deleteText                       : jxDeleteText,
    viewAllRepliesText               : jxViewAllRepliesText,
    hideRepliesText                  : jxHideRepliesText,
    noCommentsText                   : jxNoCommentsText,
    noAttachmentsText                : jxNoAttachmentsText,
    attachmentDropText               : jxAttachmentDropText,
    readOnly                         : jxReadOnly,
    enableReplying                   : jxReplying,
    enableEditing                    : jxEditing,
    enableUpvoting                   : jxVoting,
    enableDeleting                   : jxDeleting,
    enableDeletingCommentWithReplies : jxDeletingReplied,
    enableAttachments                : jxAttachments,
    enableHashtags                   : jxHashtags,
    enablePinging                    : jxPinging,
    enableNavigation                 : jxNavigation,
    postCommentOnEnter               : jxPostOnEnter,
    roundProfilePictures             : true,
    textareaRows                     : 2,
    getUsers                         : function(success, error) {
      setTimeout(function() {
        success(usersArray);
      }, 500);
    },
    getComments                      : function(success, error) {
      setTimeout(function() {
        success(commentsArray);
      }, 500);
    },
    postComment                      : function(data, success, error) {
      $.ajax({
          type     : 'POST',
          url      : ajaxPath,
          headers  : {"cache-control" : "no-cache"},
          dataType : 'json',
          data     : {
            ajax           : true,
            id_jxblog_post : id_jxblog_post,
            action         : 'addComment',
            content        : data.content,
            parent         : data.parent
          },
          success  : function(response) {
            if (response.status) {
              if (!response.moderation) {
                setTimeout(function() {
                  data['id'] = response.id_comment;
                  success(saveComment(data));
                }, 500);
              } else {
                $('#blog-comments-container').find('span.close').trigger('click');
                jxDisplayMessage(response.response);
              }
            } else {
              jxDisplayMessage(response.response);
              error();
            }
          }
        }
      );
    },
    putComment                       : function(data, success, error) {
      $.ajax({
          type     : 'POST',
          url      : ajaxPath,
          headers  : {"cache-control" : "no-cache"},
          dataType : 'json',
          data     : {
            ajax       : true,
            id_comment : data.id,
            action     : 'updateComment',
            content    : data.content,
            parent     : data.parent
          },
          success  : function(response) {
            if (response.status) {
              if (!response.moderation) {
                setTimeout(function() {
                  success(saveComment(data));
                }, 500);
              } else {
                jxDisplayMessage(response.response);
                $('#comment-list li[data-id="' + data.id + '"]').remove();
              }
            } else {
              jxDisplayMessage(response.response);
              error();
            }
          }
        }
      );
    },
    deleteComment                    : function(data, success, error) {
      $.ajax({
          type     : 'POST',
          url      : ajaxPath,
          headers  : {"cache-control" : "no-cache"},
          dataType : 'json',
          data     : {
            ajax           : true,
            action         : 'deleteComment',
            id_comment     : data.id
          },
          success  : function(response) {
            if (response.status) {
              success();
            } else {
              jxDisplayMessage(response.response);
              error();
            }
          }
        }
      );
    },
    upvoteComment                    : function(data, success, error) {
      $('#comment-list li[data-id="' + data.id + '"]').find('.upvote-count').text('');
      $.ajax({
          type     : 'POST',
          url      : ajaxPath,
          headers  : {"cache-control" : "no-cache"},
          dataType : 'json',
          data     : {
            ajax       : true,
            action     : 'voteComment',
            id_comment : data.id
          },
          success  : function(response) {
            if (response.status) {
              $('#comment-list li[data-id="' + data.id + '"]').find('.upvote-count').text(response.result);
            } else {
              jxDisplayMessage(response.response);
            }
          }
        }
      );
    },
    uploadAttachments                : function(dataArray, success, error) {
      var formData = new FormData();
      formData.append('file', dataArray[0].file);
      jxLoadFile(formData).then(function(response) {
        if (response.status) {
          $.ajax({
              type     : 'POST',
              url      : ajaxPath,
              headers  : {"cache-control" : "no-cache"},
              dataType : 'json',
              data     : {
                ajax           : true,
                id_jxblog_post : id_jxblog_post,
                action         : 'uploadAttachment',
                content        : dataArray[0].content,
                parent         : dataArray[0].parent,
                name           : response.name,
                type           : response.type,
                path           : response.path
              },
              success  : function(res) {
                if (res.moderation === true) {
                  $('#blog-comments-container').find('span.close').trigger('click');
                  jxDisplayMessage(res.response);
                  error(dataArray);
                } else {
                  dataArray[0]['file_mime_type'] = response.type;
                  dataArray[0]['file_url']       = response.path + response.name;
                  setTimeout(function() {
                    success(dataArray);
                  }, 500);
                }
              }
            }
          );
        } else {
          jxDisplayMessage(response.response);
          error(dataArray);
        }
      });
    }
  });

  function jxLoadFile(file) {
    return new Promise(function(resolve, reject) {
      $.ajax({
          type     : 'POST',
          url      : ajaxPath,
          dataType : 'text',
          contentType: false,
          processData: false,
          data     : file,
          async    : true
        }
      ).then(function(response) {
        var result = JSON.parse(response);
        resolve(result);
      }).fail(function(xhr) {
        reject(xhr);
      });
    });
  }

  function jxDisplayMessage(message) {
    alert(message);
  }
});


