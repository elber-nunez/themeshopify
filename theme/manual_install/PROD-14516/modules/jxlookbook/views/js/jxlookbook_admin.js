/*
 * 2017-2018 Zemez
 *
 * JX Look Book
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

var jxlb = {
  getParameterByName: function(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return true;
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  },
  ajax : function() {
    this.init = function(options) {
      this.options = $.extend({}, this.defaults, options || {});
      this.request();
      return this;
    };
    this.defaults = {
      type : 'POST',
      url : '.index.php',
      headers : {"cache-control" : "no-cache"},
      cache : false,
      dataType : "json",
      async : false,
      success : function() {
      }
    };
    this.request = function() {
      $.ajax(this.options);
    };
  },
  list : function() {
    this.init = function(json) {
      if (json == '') {
        json = '[]';
      }
      this.array = JSON.parse(json);
    };
    this.extend = function(json) {
      var products = JSON.parse(json);
      for (var i = 0; i < products.length; i++) {
        this.array[this.array.length] = products[i];
      }
      return JSON.stringify(this.array);
    };
    this.add = function(elem) {
      if (this.array.indexOf(elem) == -1) {
        this.array[this.array.length] = elem;
      }
      return JSON.stringify(this.array);
    };
    this.remove = function(elem) {
      var index = this.array.indexOf(elem);
      this.array.splice(index, 1);
      return JSON.stringify(this.array);
    }
  },
  fancy : function() {
    this.init = function(options) {
      this.options = $.extend(this.options, options);
      return this;
    };
    this.options = {
      type : 'inline',
      autoScale : true,
      minHeight : 30,
      minWidth : 285,
      padding : 0,
      content : '',
      showCloseButton : true,
      helpers : {
        overlay : {
          locked : false
        }
      }
    };
    this.show = function() {
      $.fancybox(this.options);
    };
  }
};

function changeHook(){
    var selectParent = $('select[name=type], select[name=id_link]').parents('.form-group');
    if ($('select[name=hook_name]').attr('value') == 'page') {
      selectParent.addClass('hidden');
    } else {
      selectParent.removeClass('hidden');
    }
}

$(document).ready(function() {
  var hook = jxlb.getParameterByName('hook'),
      duplicatecollection = jxlb.getParameterByName('duplicatecollection');
  if (hook && $('select[name=hook_name]').length) {
    $('select[name=hook_name]').attr('value', jxlb.getParameterByName('hook'));
    changeHook();
  }

  if(duplicatecollection) {
    $('[name=savecollection]').addClass('hidden');
  } else {
    $('.duplicatecollection').addClass('hidden');
  }

  $('select[name=hook_name]').change(function(){
    changeHook();
  });

  var $d = $(this);

  $d.on('click', 'button[name=savehotspot]', function(e) {
    e.preventDefault();
    $(this).parents('form').find('textarea').each(function(){
      $(this).attr('value', tinyMCE.get($(this).attr('name')).getContent())
    });

    var params = $(this).parents("form").serialize(),
      coordinates = JSON.parse($('[name=coordinates]').attr('value')),
      options = {
        url : jxml_theme_url + '&ajax&' + params,
        data : $.extend({
          action : 'savehotspot'
        }, params),
        success : function(response) {
          if (response.status) {
            showSuccessMessage(response.message);
            if ($('[name=id_spot]').attr('value') == '') {
              var spot = $('<div>', {
                class : 'point'
              });
              spot.css({
                position : 'absolute',
                'left' : coordinates.x+'%',
                'top' : coordinates.y+'%'
              }).appendTo($('.hotspot'));

              spot.attr('data-point-id', response.id_spot);
              $('<a>', {
                href: '#',
                class : 'delete-point',
                html: '<i class="process-icon-cancel"></i>'
              }).appendTo(spot);
            }
            $.fancybox.close();
          } else {
            $('.hotspot-block .panel-heading').after(response.errors);
          }
          pointDrag();
        }
      },
      ajaxRequest = new jxlb.ajax();
    ajaxRequest.init(options);
  }).on('click', '.template', function(e) {
    e.preventDefault();
    var template = $(this).attr('data-template');
    $('[name=template]').attr('value', template);
    $('.template_value').text(template);
    $.fancybox.close();
  }).on('click', '.select-template', function(e) {
    e.preventDefault();

    var ajaxRequest = new jxlb.ajax(),
      selected_template = $('#template').attr('value'),
      options = {
        url : jxml_theme_url + '&ajax',
        data : {
          action : 'getTemplates'
        },
        success : function(response) {
          $.fancybox.open({
            content : response.templates_form,
            afterShow: function(){
              $('[data-template='+selected_template+']').addClass('active');
            }
          })
        }
      };
    ajaxRequest.init(options);
  }).on('click', '.delete-point', function(e) {
    e.preventDefault();
    var $s = $(this),
      ajaxRequest = new jxlb.ajax(),
      id_spot = $(this).parent().attr('data-point-id'),
      options = {
        url : jxml_theme_url + '&ajax',
        data : {
          action : 'removehotspot',
          id_spot : id_spot,
        },
        success: function(response) {
          if (response.status) {
            $s.parent().remove();
          }
        }
      };
    ajaxRequest.init(options);

    return false;
  }).on('click', '.hotspot:not(.disabled) img, .point', function(e) {
    e.preventDefault();

    var coordinates = {
        x : parseInt((e.offsetX / e.currentTarget.clientWidth) * 100),
        y : parseInt((e.offsetY / e.currentTarget.clientHeight) * 100)
      },
      id_spot = $(this).attr('data-point-id'),
      ajaxRequest = new jxlb.ajax(),
      options = {
        url : jxml_theme_url + '&ajax',
        data : {
          action : 'gethotspotform',
          id_spot : id_spot,
          id_tab : $('[name=id_tab]').attr('value'),
          coordinates : JSON.stringify(coordinates)
        },
        success : function(response) {
          var fancy = new jxlb.fancy();
          fancy.init({
            minWidth : 950,
            content : '<div class="bootstrap hotspot-block" id="content">' + response.form + '</div>',
            afterShow:
              function() {
                selectPointType($('.hotspot-block select[name=type] option:selected'));
              }
          }).show();
        }
      };
    ajaxRequest.init(options);
  }).on('click', '#add_products', function(e) {
    e.preventDefault();
    var products = new jxlb.list(),
      new_products = $(this).parents('.bootstrap').find('input[name=products]').attr('value');
    products.init($('input[name=selected_products]').attr('value'));
    $('input[name=selected_products]').attr('value', products.extend(new_products));
    $('.fancybox-inner .product.active').show().appendTo('#selected_products');
    $.fancybox.close();
  }).on('click', '#manage-products', function(e) {
    e.preventDefault();
    var options = {
        url : jxml_theme_url + '&ajax',
        success : function(response) {
          var fancy_options = {
              content : response.content
            },
            fancybox = new jxlb.fancy();
          fancybox.init(fancy_options).show();
        },
        data : {
          id_category : $('[name=category]').attr('value'),
          selected_products : $('input[name=selected_products]').attr('value'),
          action : 'getProducts',
          type: 'multiselect'
        }
      },
      ajaxRequest = new jxlb.ajax();
    ajaxRequest.init(options);
  }).on('click', '.point-product', function(e) {
    e.preventDefault();

    $p = $(this);
    $p.addClass('disabled');
    $('.preloader', $p).removeClass('hidden');
    var options = {
        url : jxml_theme_url + '&ajax',
        success : function(response) {
          $p.parent().after(response.content);
          $('.preloader', $p).addClass('hidden');
        },
        data : {
          selected_products : {},
          action : 'getProducts'
        }
      },
      ajaxRequest = new jxlb.ajax();
    ajaxRequest.init(options);
  }).on('click', '.simpleselect .product:not(.selected)', function(){
    $p = $(this);
    $i = $('input[name=id_product]');
    $n = $i.next().next();
    $('.point-product').removeClass('disabled');
    $i.attr('value', $p.attr('data-product-id'));
    $n.removeClass('hidden');
    $n.find('p').text($p.find('p').text()+ '  ');
    $n.find('img').attr('src', $p.find('img').attr('src'));
    $p.parents('.bootstrap')[0].remove();
  }).on('change', '.hotspot-block select[name=type]', function(){
    console.log('e');
    var $o = $(this);
    selectPointType($o);
  });

  function selectPointType(elem)
  {
    var $o = elem,
      val = $o.attr('value'),
      name = $('.point-name').parents('.form-group'),
      description = $('.point-description').parents('.form-group'),
      product = $('.point-product').parents('.form-group');

    switch(parseInt(val)) {
      case 1:
        name.addClass('hidden');
        description.addClass('hidden');
        product.removeClass('hidden');
        break;
      case 2:
        product.addClass('hidden');
        name.removeClass('hidden');
        description.removeClass('hidden');
        break;
    }
  }

  $('.iframe-btn').fancybox({
    'width' : 900,
    'height' : 600,
    'type' : 'iframe',
    'autoScale' : false,
    'autoDimensions' : false,
    'fitToView' : false,
    'autoSize' : false,
    onUpdate : function() {
      $('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
      $('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
    },
    afterShow : function() {
      $('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
      $('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
    },
    afterClose : function() {
      var hotspotImage = $('.hotspot img'),
        pageImage = $('.page-image img'),
        points = $('.hotspot .point');
      setTimeout(function() {
        if (hotspotImage.attr('src') != '' && points.length) {
          var ans = confirm('All points will delete');
          if (ans) {
            var options = {
                url : jxml_theme_url + '&ajax',
                success : function(response) {
                  hotspotImage.attr('src', $('#image').attr('value')).removeClass('hidden');
                  $('.point').remove();
                },
                data : {
                  id_tab : $('[name=id_tab]').attr('value'),
                  action : 'deleteHotSpots'
                }
              },
              ajaxRequest = new jxlb.ajax();
            ajaxRequest.init(options);
          }
        } else {
          hotspotImage.attr('src', $('#image').attr('value')).removeClass('hidden');
          pageImage.attr('src', $('#image').attr('value')).removeClass('hidden');
        }
      }, 50);
    }
  });
  jxcp = {
    ajax : function() {
      this.init = function(options) {
        this.options = $.extend(this.options, options);
        this.request();
        return this;
      };
      this.options = {
        type : 'POST',
        url : jxml_theme_url + '&ajax',
        headers : {"cache-control" : "no-cache"},
        cache : false,
        dataType : "json",
        async : false,
        success : function() {
        }
      };
      this.request = function() {
        $.ajax(this.options);
      };
    },
    list : function() {
      this.init = function(json) {
        if (json == '') {
          json = '[]';
        }
        this.array = JSON.parse(json);
      };
      this.extend = function(json) {
        var products = JSON.parse(json);
        for (var i = 0; i < products.length; i++) {
          this.array[this.array.length] = products[i];
        }
        return JSON.stringify(this.array);
      };
      this.add = function(elem) {
        if (this.array.indexOf(elem) == -1) {
          this.array[this.array.length] = elem;
        }
        return JSON.stringify(this.array);
      };
      this.remove = function(elem) {
        var index = this.array.indexOf(elem);
        this.array.splice(index, 1);
        return JSON.stringify(this.array);
      }
    },
    fancy : function() {
      this.init = function(options) {
        this.options = $.extend(this.options, options);
        return this;
      };
      this.options = {
        type : 'inline',
        autoScale : true,
        minHeight : 30,
        minWidth : 285,
        padding : 0,
        content : '',
        showCloseButton : true,
        helpers : {
          overlay : {
            locked : false
          }
        }
      };
      this.show = function() {
        $.fancybox(this.options);
      };
    }
  };
  $(document).ready(function() {
    var category_option = $('[name=category] option:selected');
    var category_value = category_option.attr('value');
    $('[name=category]').on('change', function() {
      if (category_value != $(this).find('option:selected').attr('value')) {
        if (confirm(jxcp_category_warning)) {
          category_option = $(this).find('option:selected');
          category_value = category_option.attr('value');
          $('#selected_products>*').remove();
          $('input[name=selected_products]').attr('value', '[]');
        } else {
          $('[name=category] option:selected').attr('selected', '');
          category_option.attr('selected', 'selected');
        }
      }
    });
    $('.fancybox-inner .close').live('click', function() {
      $.fancybox.close();
    });
    $('#manage-products').on('click', function(e) {
      e.preventDefault();
      var options = {
        success : function(response) {
          var fancy_options = {
            content : response.content
          };
          var fancybox = new jxcp.fancy();
          fancybox.init(fancy_options).show();
        },
        data : {
          id_category : $('[name=category]').attr('value'),
          selected_products : $('input[name=selected_products]').attr('value'),
          action : 'getProducts',
        }
      };
      var ajax = new jxcp.ajax();
      ajax.init(options);
    });
    if ($('#select_products_off').attr('checked') == 'checked') {
      $('#manage-products').parents('.form-group').hide();
    } else {
      $('#num').parents('.form-group').hide();
    }
    $(document).on('click', '#select_products_off', function() {
      $('#manage-products').parents('.form-group').hide();
      $('#num').parents('.form-group').show();
    });
    $(document).on('click', '#select_products_on', function() {
      $('#num').parents('.form-group').hide();
      $('#manage-products').parents('.form-group').show();
    });
    if ($('#use_carousel_off').attr('checked') == 'checked') {
      $('#use_carousel_off').parents('.form-group').nextAll('.form-group').hide();
    } else {
      $('#use_carousel_off').parents('.form-group').nextAll('.form-group').show();
    }
    $(document).on('click', '#use_carousel_off', function() {
      $(this).parents('.form-group').nextAll('.form-group').hide();
      if ($('#carousel_auto_off').attr('checked') == 'checked') {
        $('#carousel_auto_off').parents('.form-group').next().hide();
      } else {
        $('#carousel_auto_off').parents('.form-group').next().show();
      }
    });
    $(document).on('click', '#use_carousel_on', function() {
      $(this).parents('.form-group').nextAll('.form-group').show();
      if ($('#carousel_auto_off').attr('checked') == 'checked') {
        $('#carousel_auto_off').parents('.form-group').next().hide();
      } else {
        $('#carousel_auto_off').parents('.form-group').next().show();
      }
    });
    if ($('#carousel_auto_off').attr('checked') == 'checked') {
      $('#carousel_auto_off').parents('.form-group').next().hide();
    } else {
      $('#carousel_auto_off').parents('.form-group').next().show();
    }
    $(document).on('click', '#carousel_auto_off', function() {
      $(this).parents('.form-group').next().hide();
    });
    $(document).on('click', '#carousel_auto_on', function() {
      $(this).parents('.form-group').next().show();
    });
    if ($('#use_name_off').attr('checked') == 'checked') {
      $('#use_name_off').parents('.form-group').next().hide();
    } else {
      $('#use_name_off').parents('.form-group').next().show();
    }
    $(document).on('click', '#use_name_off', function() {
      $(this).parents('.form-group').next().hide();
    });
    $(document).on('click', '#use_name_on', function() {
      $(this).parents('.form-group').next().show();
    });
    $('.multiselect .product').live('click', function() {
      var products = new jxcp.list();
      products.init($(this).parents('.bootstrap').find('input[name=products]').attr('value'));
      var product_id = $(this).attr('data-product-id');
      if ($(this).hasClass('active')) {
        $(this).parents('.bootstrap').find('input[name=products]').attr('value', products.remove(product_id));
      } else {
        $(this).parents('.bootstrap').find('input[name=products]').attr('value', products.add(product_id));
      }
      $(this).toggleClass('active');
    });
    $('#select_all_products').live('click', function(e) {
      e.preventDefault();
      $('.multiselect .product:not(.active)').trigger('click');
    });
    $('#deselect_all_products').live('click', function(e) {
      e.preventDefault();
      $('.multiselect .product.active').trigger('click');
    });
    $('#add_products').live('click', function(e) {
      e.preventDefault();
      $.fancybox.close();
      var products = new jxcp.list();
      products.init($('input[name=selected_products]').attr('value'));
      var new_products = $(this).parents('.bootstrap').find('input[name=products]').attr('value');
      $('input[name=selected_products]').attr('value', products.extend(new_products));
      $('.multiselect .product.active').show().appendTo('#selected_products');
    });
    function removeProductFromList(child) {
      var products = new jxcp.list();
      var input = $('input[name=selected_products]');
      products.init(input.attr('value'));
      var elem = child.parents('.product');
      input.attr('value', products.remove(elem.attr('data-product-id')));
      elem.remove();
    }

    $('#selected_products .remove-product').live('click', function(e) {
      e.preventDefault();
      removeProductFromList($(this));
    });
    $('#selected_products .remove-product').on('click', function(e) {
      e.preventDefault();
      removeProductFromList($(this));
    });
    $('.categoryproducts_tabs > tbody tr, .categoryproducts_blocks > tbody tr').each(function() {
      var id = $(this).find('td:first').text();
      $(this).attr('id', 'item_' + id.trim());
    });
    $('.categoryproducts_tabs > tbody, .categoryproducts_blocks > tbody').sortable().bind('sortupdate', function() {
      var orders = $(this).sortable('toArray');
      var options = {
        data : {
          action : 'updateposition',
          item : orders,
        },
        success : function(msg) {
          if (msg.error) {
            showErrorMessage(msg.error);
            return;
          }
          showSuccessMessage(msg.success);
        }
      };
      var ajax = new jxcp.ajax();
      ajax.init(options);
    });
    $("#selected_products").sortable({
      cursor : 'move',
      update : function(event, ui) {
        var products = new jxcp.list();
        products.init('[]');
        $(this).find('.product').each(function() {
          products.add($(this).attr('data-product-id'));
        });
        $('input[name=selected_products]').attr('value', JSON.stringify(products.array));
      }
    });
    $("#selected_products").disableSelection();
    $('.fancybox-inner input[name=product_search]').live('keyup', function() {
      var find_text = $('.fancybox-inner input[name=product_search]').attr('value').toLowerCase();
      $('.fancybox-inner .product').hide();
      $('.fancybox-inner .product p').each(function() {
        var text = $(this).text().toLowerCase();
        if (text.indexOf(find_text) + 1) {
          $(this).parents('.product').show();
        }
      });
    });
    $('.clear_serach').live('click', function(e) {
      e.preventDefault();
      $('.fancybox-inner input[name=product_search]').attr('value', '').trigger('keyup');
    });
  });
  $('#form-collection  tbody  tr  td.dragHandle, #form-tab  tbody  tr  td.dragHandle, #form-hook  tbody  tr  td.dragHandle').wrapInner('<div class="positions"/>');
  $('#form-collection  tbody  tr  td.dragHandle, #form-tab  tbody  tr  td.dragHandle, #form-hook  tbody  tr  td.dragHandle').wrapInner('<div class="dragGroup"/>');
  var collectionSlides = $('#form-collection  tbody');
    var hooks = $('#form-hook  tbody');
  var $tabs = $('#form-tab tbody');
  $('#form-collection tbody tr, #form-tab tbody tr, #form-hook  tbody  tr').each(function() {
    var id = $(this).find('td:first').text();
    $(this).attr('id', 'item_' + id.trim());
  });
  $tabs.sortable({
    cursor : 'move',
    handle : '.dragHandle',
    update : function(event, ui) {
      $('#form-tab tbody > tr').each(function(index) {
        $(this).find('.positions').text(index + 1);
      });
    }
  }).bind('sortupdate', function() {
    var orders = $(this).sortable('toArray');
    var options = {
      data : {
        action : 'updatetabsposition',
        item : orders,
        id_page : $('table td.id_page').text().trim()
      },
      success : function(msg) {
        if (msg.error) {
          showErrorMessage(msg.error);
          return;
        }
        showSuccessMessage(msg.success);
      }
    };
    var ajax = new jxcp.ajax();
    ajax.init(options);
  });
  collectionSlides.sortable({
    cursor : 'move',
    handle : '.dragHandle',
    update : function(event, ui) {
      $('>tr', collectionSlides).each(function(index) {
        $(this).find('.positions').text(index + 1);
      });
    }
  }).bind('sortupdate', function() {
    var collections = $(this).sortable('toArray');
    var options = {
      data : {
        action : 'updatecollectionsposition',
        item : collections,
      },
      success : function(msg) {
        if (msg.error) {
          showErrorMessage(msg.error);
          return;
        }
        showSuccessMessage(msg.success);
      }
    };
    var ajax = new jxcp.ajax();
    ajax.init(options);
  });

    hooks.sortable({
        cursor : 'move',
        handle : '.dragHandle',
        update : function(event, ui) {
            $('#form-loading .tab-pane.active tr').each(function(index) {
                index--;
                $(this).find('.positions').text(index + 1);
            });
        }
    }).bind('sortupdate', function() {
        var collections = $(this).sortable('toArray');
        var options = {
            data : {
                action : 'updatehooksposition',
                item : collections,
                hook_name : $('#form-loading .nav-item.active').text().trim()
            },
            success : function(msg) {
                if (msg.error) {
                    showErrorMessage(msg.error);
                    return;
                }
                showSuccessMessage(msg.success);
            }
        };
        var ajax = new jxcp.ajax();
        ajax.init(options);
    });

  var spot_input = $('input[name=hotspots]');
  if (spot_input.length) {
    var hotspotParams = JSON.parse(spot_input.attr('value') || new Array());
    for (key in hotspotParams) {
      hotspotParams[key].coordinates = JSON.parse(hotspotParams[key].coordinates);
    }
    $('.hotspot').hotSpot({
      items : hotspotParams
    });
  }

  function pointDrag() {
    $('.hotspot .point').draggable({
      containment: ".hotspot",
      scroll: false,
      stop: function(e, ui) {
        var id = $(this).attr('data-point-id'),
            el = $(ui.helper[0]),
            parent = el.parent(),
            coordinates = {
              x : parseInt((el.css('left').replace('px','') / parent.width()) * 100),
              y : parseInt((el.css('top').replace('px','') / parent.height()) * 100)
            },
            options = {
              url : jxml_theme_url + '&ajax&',
              data : {
                action : 'updatepointposition',
                coordinates: JSON.stringify(coordinates),
                id: id
              },
              success : function(response) {
                if (!response.error) {
                  showSuccessMessage(response.success);
                }
              }
            },
            ajaxRequest = new jxlb.ajax();
        ajaxRequest.init(options);
      }
    });
  }

  pointDrag();

    var navWidth = 50,
        navStep = $('.js-tabs').width() - 40,
        maxTabWidth = 0,
        newTabWidth = 0;

    $('.js-nav-tabs>li').each(function (index, item) {
        if (maxTabWidth < $(item).width()) {
            maxTabWidth = $(item).width();
        }
    });
    newTabWidth = parseInt(maxTabWidth + ((navStep - (navStep/maxTabWidth>>0)*maxTabWidth)/(navStep/maxTabWidth>>0)));
    navWidth = newTabWidth * $('.js-nav-tabs>li').length;
    $('.js-nav-tabs').width(navWidth+20);
    $('.js-nav-tabs>li').width(newTabWidth>>0);

    if($('.js-nav-tabs').width() < $('.js-tabs').width()) {
        $('.js-nav-tabs').width($('.js-tabs').width());
        return $('.js-arrow').hide();
    }
    else {
        $('.js-arrow').show();
    }

    $('.js-arrow').on('click', function (e) {
        if ($('.js-arrow').is(':visible')) {
            var left = $('.js-nav-tabs').css('left').replace('px', '');
            $('.js-nav-tabs').animate({
                left: $(e.currentTarget).hasClass('right-arrow') ? parseInt(left)-parseInt(navStep) + 10 : parseInt(left)+parseInt(navStep) - 10
            }, 400, function () {
                if (($('.js-nav-tabs').css('left').replace('px', '') - parseInt(navStep) + 10) < -navWidth) {
                    $('.right-arrow').removeClass('visible');
                    $('.left-arrow').addClass('visible');
                } else if ($('.js-nav-tabs').css('left').replace('px', '') >= 0) {
                    $('.right-arrow').addClass('visible');
                    $('.left-arrow').removeClass('visible');
                } else {
                    $('.left-arrow').addClass('visible');
                    $('.right-arrow').addClass('visible');
                }
            });
        }
    });
});