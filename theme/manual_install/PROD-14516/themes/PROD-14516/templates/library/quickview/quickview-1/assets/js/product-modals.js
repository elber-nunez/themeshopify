!function(e){var n={};function t(i){if(n[i])return n[i].exports;var r=n[i]={i:i,l:!1,exports:{}};return e[i].call(r.exports,r,r.exports,t),r.l=!0,r.exports}t.m=e,t.c=n,t.d=function(e,n,i){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:i})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(t.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var r in e)t.d(i,r,function(n){return e[n]}.bind(null,r));return i},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="",t(t.s=92)}({2:function(module,exports,__webpack_require__){"use strict";eval('\n\n!function () {\n  var e = function e(t, i) {\n    function s() {\n      this.q = [], this.add = function (e) {\n        this.q.push(e);\n      };var e, t;this.call = function () {\n        for (e = 0, t = this.q.length; e < t; e++) {\n          this.q[e].call();\n        }\n      };\n    }function o(e, t) {\n      return e.currentStyle ? e.currentStyle[t] : window.getComputedStyle ? window.getComputedStyle(e, null).getPropertyValue(t) : e.style[t];\n    }function n(e, t) {\n      if (e.resizedAttached) {\n        if (e.resizedAttached) return void e.resizedAttached.add(t);\n      } else e.resizedAttached = new s(), e.resizedAttached.add(t);e.resizeSensor = document.createElement("div"), e.resizeSensor.className = "resize-sensor";var i = "position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: hidden; z-index: -1; visibility: hidden;",\n          n = "position: absolute; left: 0; top: 0; transition: 0s;";e.resizeSensor.style.cssText = i, e.resizeSensor.innerHTML = \'<div class="resize-sensor-expand" style="\' + i + \'"><div style="\' + n + \'"></div></div><div class="resize-sensor-shrink" style="\' + i + \'"><div style="\' + n + \' width: 200%; height: 200%"></div></div>\', e.appendChild(e.resizeSensor), { fixed: 1, absolute: 1 }[o(e, "position")] || (e.style.position = "relative");var d,\n          r,\n          l = e.resizeSensor.childNodes[0],\n          c = l.childNodes[0],\n          h = e.resizeSensor.childNodes[1],\n          a = (h.childNodes[0], function () {\n        c.style.width = l.offsetWidth + 10 + "px", c.style.height = l.offsetHeight + 10 + "px", l.scrollLeft = l.scrollWidth, l.scrollTop = l.scrollHeight, h.scrollLeft = h.scrollWidth, h.scrollTop = h.scrollHeight, d = e.offsetWidth, r = e.offsetHeight;\n      });a();var f = function f() {\n        e.resizedAttached && e.resizedAttached.call();\n      },\n          u = function u(e, t, i) {\n        e.attachEvent ? e.attachEvent("on" + t, i) : e.addEventListener(t, i);\n      },\n          p = function p() {\n        e.offsetWidth == d && e.offsetHeight == r || f(), a();\n      };u(l, "scroll", p), u(h, "scroll", p);\n    }var d = Object.prototype.toString.call(t),\n        r = "[object Array]" === d || "[object NodeList]" === d || "[object HTMLCollection]" === d || "undefined" != typeof jQuery && t instanceof jQuery || "undefined" != typeof Elements && t instanceof Elements;if (r) for (var l = 0, c = t.length; l < c; l++) {\n      n(t[l], i);\n    } else n(t, i);this.detach = function () {\n      if (r) for (var i = 0, s = t.length; i < s; i++) {\n        e.detach(t[i]);\n      } else e.detach(t);\n    };\n  };e.detach = function (e) {\n    e.resizeSensor && (e.removeChild(e.resizeSensor), delete e.resizeSensor, delete e.resizedAttached);\n  },  true && "undefined" != typeof module.exports ? module.exports = e : window.ResizeSensor = e;\n}();\n//# sourceMappingURL=maps/ResizeSensor.min.js.map\n\n//# sourceURL=webpack:///./node_modules/theia-sticky-sidebar/dist/ResizeSensor.min.js?')},4:function(module,exports,__webpack_require__){"use strict";eval("/* WEBPACK VAR INJECTION */(function(ResizeSensor) {\n\nObject.defineProperty(exports, \"__esModule\", {\n  value: true\n});\n\nvar _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if (\"value\" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();\n\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\n/**\r\n * 2007-2018 PrestaShop\r\n *\r\n * NOTICE OF LICENSE\r\n *\r\n * This source file is subject to the Academic Free License 3.0 (AFL-3.0)\r\n * that is bundled with this package in the file LICENSE.txt.\r\n * It is also available through the world-wide-web at this URL:\r\n * https://opensource.org/licenses/AFL-3.0\r\n * If you did not receive a copy of the license and are unable to\r\n * obtain it through the world-wide-web, please send an email\r\n * to license@prestashop.com so we can send you a copy immediately.\r\n *\r\n * DISCLAIMER\r\n *\r\n * Do not edit or add to this file if you wish to upgrade PrestaShop to newer\r\n * versions in the future. If you wish to customize PrestaShop for your\r\n * needs please refer to http://www.prestashop.com for more information.\r\n *\r\n * @author    PrestaShop SA <contact@prestashop.com>\r\n * @copyright 2007-2018 PrestaShop SA\r\n * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)\r\n * International Registered Trademark & Property of PrestaShop SA\r\n */\n\nvar ProductCommons = function () {\n  function ProductCommons(el) {\n    _classCallCheck(this, ProductCommons);\n\n    this.el = el;\n  }\n\n  _createClass(ProductCommons, [{\n    key: 'productSpin',\n    value: function productSpin(el) {\n      var $quantityInput = $(el.find('#quantity_wanted'));\n\n      $quantityInput.TouchSpin({\n        verticalbuttons: true,\n        verticalup: '',\n        verticaldown: '',\n        verticalupclass: 'fa fa-angle-up',\n        verticaldownclass: 'fa fa-angle-down',\n        buttondown_class: 'btn',\n        buttonup_class: 'btn',\n        min: parseInt($quantityInput.attr('min'), 10),\n        max: 1000000\n      });\n\n      $quantityInput.on('change keyup', function (e) {\n        $(e.currentTarget).trigger('touchspin.stopspin');\n        prestashop.emit('updateProduct', {\n          eventType: 'updatedProductQuantity',\n          event: e\n        });\n      });\n    }\n  }, {\n    key: 'inputFile',\n    value: function inputFile(el) {\n      $(el.find('.js-file-input')).on('change', function (event) {\n        var target = void 0,\n            file = void 0;\n        if ((target = $(event.currentTarget)[0]) && (file = target.files[0])) {\n          $(target).prev().text(file.name);\n        }\n      });\n    }\n  }, {\n    key: 'coverImage',\n    value: function coverImage(el) {\n      $(el.find('.js-thumb')).on('click', function (event) {\n        $(el.find('.selected')).removeClass('selected');\n        $(event.target).addClass('selected');\n        $(el.find('.js-qv-product-cover')).prop('src', $(event.currentTarget).data('image-large-src'));\n      });\n    }\n  }, {\n    key: 'slider',\n    value: function slider(el) {\n      var slider = new Swiper(el.find('.swiper-container'), {\n        navigation: {\n          nextEl: '.swiper-button-next',\n          prevEl: '.swiper-button-prev'\n        },\n        on: {\n          init: function init() {\n            this.slideTo($('.js-thumb.selected').data('index'), 0);\n            if (this.isBeginning && this.isEnd) {\n              this.allowTouchMove = false;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).hide();\n            }\n          },\n          resize: function resize() {\n            if (this.isBeginning && this.isEnd) {\n              this.allowTouchMove = false;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).hide();\n            } else {\n              this.allowTouchMove = true;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).show();\n            }\n          },\n          slideChange: function slideChange() {\n            this.$el.find('.js-thumb.selected').removeClass('selected');\n            $(this.slides[this.activeIndex]).find('.js-thumb').addClass('selected');\n          }\n        }\n      });\n    }\n  }, {\n    key: 'gallery',\n    value: function gallery(el, createGallery, observer) {\n      var count = createGallery.match(/[0-9]/g);\n      var gallery = new Swiper(el.find('.swiper-container'), {\n        slidesPerView: count[0],\n        spaceBetween: 10,\n        observer: observer,\n        observeParents: observer,\n        slideToClickedSlide: true,\n        navigation: {\n          nextEl: '.swiper-button-next',\n          prevEl: '.swiper-button-prev'\n        },\n        on: {\n          init: function init() {\n            var _this = this;\n\n            this.slideTo($('.js-thumb.selected').data('index'), 0);\n            $(this.slides[$('.js-thumb.selected').data('index')]).find('img').click();\n\n            if (this.isBeginning && this.isEnd) {\n              this.allowTouchMove = false;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).hide();\n            }\n\n            el.find('[class*=\"product-cover\"] [class*=\"swiper-button\"]').on('click', function (e) {\n              var selectedIndex = $(_this.$el.find('.selected')).data('index');\n              if ($(e.target).hasClass('swiper-button-prev')) {\n                $(_this.slides[selectedIndex !== 0 ? selectedIndex - 1 : _this.slides.length - 1]).find('img').click();\n                _this.slideTo(selectedIndex !== 0 ? selectedIndex - 1 : _this.slides.length - 1);\n              } else {\n                $(_this.slides[selectedIndex !== _this.slides.length - 1 ? selectedIndex + 1 : 0]).find('img').click();\n                _this.slideTo(selectedIndex !== _this.slides.length - 1 ? selectedIndex + 1 : 0);\n              }\n            });\n          },\n          resize: function resize() {\n            this.update();\n            if (this.isBeginning && this.isEnd) {\n              this.allowTouchMove = false;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).hide();\n            } else {\n              this.allowTouchMove = true;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).show();\n            }\n          }\n        },\n        breakpoints: {\n          991: {\n            slidesPerView: eval(count[2])\n          },\n          1640: {\n            slidesPerView: eval(count[1])\n          }\n        }\n      });\n    }\n  }, {\n    key: 'verticalGallery',\n    value: function verticalGallery(el, createVerticalGallery) {\n      var count = createVerticalGallery.match(/[0-9]/g);\n      var productThumbnailsSwiper = new Swiper(el.find('.swiper-container'), {\n        direction: 'vertical',\n        slidesPerView: eval(count[0]),\n        spaceBetween: 20,\n        navigation: {\n          nextEl: el.find('.swiper-button-next'),\n          prevEl: el.find('.swiper-button-prev')\n        },\n        preloadImages: false,\n        on: {\n          init: function init() {\n            if (this.isBeginning && this.isEnd) {\n              this.allowTouchMove = false;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).hide();\n            }\n          },\n          resize: function resize() {\n            this.update();\n            if (this.isBeginning && this.isEnd) {\n              this.allowTouchMove = false;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).hide();\n            } else {\n              this.allowTouchMove = true;\n              $(this.$el.children('.swiper-pagination, .swiper-button-next, .swiper-button-prev')).show();\n            }\n          }\n        },\n        breakpoints: {\n          991: {\n            spaceBetween: 10,\n            slidesPerView: eval(count[2])\n          },\n          1789: {\n            spaceBetween: 10,\n            slidesPerView: eval(count[1])\n          }\n        }\n      });\n      productThumbnailsSwiper.update();\n      el.find('.product-images').css('opacity', 1);\n    }\n  }, {\n    key: 'calcModalWidth',\n    value: function calcModalWidth(fluidWrapper, maxWidth, modalK, thumbHeight) {\n      var modalWidth = (window.innerHeight - thumbHeight) * modalK;\n      if (modalWidth > window.innerWidth && modalWidth < maxWidth) {\n        modalWidth = window.innerWidth;\n      } else if (modalWidth >= maxWidth) {\n        modalWidth = maxWidth;\n      }\n      fluidWrapper.attr('style', 'max-width: ' + modalWidth + 'px !important;');\n    }\n  }, {\n    key: 'modalZoom',\n    value: function modalZoom(el) {\n      var self = this;\n      var thumbs = document.getElementById('modalThumb');\n      var fluidWrapper = el.find('.modal-dialog');\n      var maxWidth = el.find('.swiper-slide:first').attr('data-modal-width');\n      var modalK = el.find('.swiper-slide:first').attr('data-modal-k');\n      self.calcModalWidth(fluidWrapper, maxWidth, modalK, 0);\n\n      el.on('shown.bs.modal', function () {\n        self.coverImage(el);\n        self.gallery(el, '7-5-4', true);\n        new ResizeSensor(thumbs, function () {\n          self.calcModalWidth(fluidWrapper, maxWidth, modalK, thumbs.clientHeight);\n        });\n      });\n\n      $(window).on('resize', function () {\n        self.calcModalWidth(fluidWrapper, maxWidth, modalK, thumbs.clientHeight);\n      });\n    }\n  }, {\n    key: 'init',\n    value: function init(createCoverImage, createInputFile, createProductSpin, createZoom, createSlider, createGallery, createVerticalGallery) {\n      createCoverImage ? this.coverImage(this.el) : false;\n      createInputFile ? this.inputFile(this.el) : false;\n      createProductSpin ? this.productSpin(this.el) : false;\n      createZoom ? this.modalZoom($('#product-modal')) : false;\n      createSlider ? this.slider(this.el) : false;\n      createGallery ? this.gallery(this.el, createGallery, false) : false;\n      createVerticalGallery ? this.verticalGallery(this.el, createVerticalGallery) : false;\n    }\n  }]);\n\n  return ProductCommons;\n}();\n\nexports.default = ProductCommons;\n/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(2)))\n\n//# sourceURL=webpack:///./js/components/product-commons.js?")},92:function(module,exports,__webpack_require__){"use strict";eval("\n\nvar _productCommons = __webpack_require__(4);\n\nvar _productCommons2 = _interopRequireDefault(_productCommons);\n\nfunction _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }\n\n$(document).ready(function () {\n  prestashop.on('clickQuickView', function (elm) {\n    var data = {\n      'action': 'quickview',\n      'id_product': elm.dataset.idProduct,\n      'id_product_attribute': elm.dataset.idProductAttribute\n    };\n    $.post(prestashop.urls.pages.product, data, null, 'json').then(function (resp) {\n      $('body').append(resp.quickview_html);\n      $('#quickview-modal-' + resp.product.id + '-' + resp.product.id_product_attribute).modal('show').on('shown.bs.modal', function () {\n        var productQuickView = new _productCommons2.default($('#quickview-product-card'));\n        productQuickView.init(true, false, true, true, true, false, false);\n        zoomUpdater();\n\n        prestashop.on('updatedProduct', function (event) {\n          var $newImagesContainer = $('<div>').append(event.product_images_modal);\n          $('#quickview-product-card .quickview-images-container').replaceWith($newImagesContainer.find('.quickview-images-container'));\n          $('#product-modal').replaceWith($newImagesContainer.find('#product-modal'));\n\n          if (event && event.product_minimal_quantity) {\n            var minimalProductQuantity = parseInt(event.product_minimal_quantity, 10);\n            var quantityInputSelector = '#quickview-product-card #quantity_wanted';\n            var quantityInput = $(quantityInputSelector);\n            quantityInput.trigger('touchspin.updatesettings', { min: minimalProductQuantity });\n          }\n\n          productQuickView.init(true, false, true, true, true, false, false);\n          zoomUpdater();\n        });\n      }).on('hidden.bs.modal', function () {\n        $('[id*=\"quickview-modal-\"], #product-modal').remove();\n      });\n    }).fail(function (resp) {\n      prestashop.emit('handleError', { eventType: 'clickQuickView', resp: resp });\n    });\n  });\n}); /**\r\n     * 2007-2018 PrestaShop\r\n     *\r\n     * NOTICE OF LICENSE\r\n     *\r\n     * This source file is subject to the Academic Free License 3.0 (AFL-3.0)\r\n     * that is bundled with this package in the file LICENSE.txt.\r\n     * It is also available through the world-wide-web at this URL:\r\n     * https://opensource.org/licenses/AFL-3.0\r\n     * If you did not receive a copy of the license and are unable to\r\n     * obtain it through the world-wide-web, please send an email\r\n     * to license@prestashop.com so we can send you a copy immediately.\r\n     *\r\n     * DISCLAIMER\r\n     *\r\n     * Do not edit or add to this file if you wish to upgrade PrestaShop to newer\r\n     * versions in the future. If you wish to customize PrestaShop for your\r\n     * needs please refer to http://www.prestashop.com for more information.\r\n     *\r\n     * @author    PrestaShop SA <contact@prestashop.com>\r\n     * @copyright 2007-2018 PrestaShop SA\r\n     * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)\r\n     * International Registered Trademark & Property of PrestaShop SA\r\n     */\n\n// Init ProductCommons with six parameters:\n// 1) Create Cover Image, 2) Create Input File,\n// 3) Create Product Spin, 4) Create Image Slider,\n// 5) Create Gallery(example: '5 4 3'), 6) Create Vertical Gallery(example: '5-4-3')\n\n\nfunction zoomUpdater() {\n  $('#quickview-product-card .quickview-images-container li').on('mouseenter', function (e) {\n    $('#quickview-product-card').find('.product-cover').removeClass('product-cover').find('img').removeClass('selected');\n    $(e.currentTarget).addClass('product-cover').find('img').addClass('selected');\n    var imgLarge = $(e.currentTarget).find('img');\n    imgLarge.attr('src', imgLarge.attr('data-image-large-src'));\n    window.dispatchEvent(new Event('resize'));\n  });\n}\n\n//# sourceURL=webpack:///./templates/library/quickview/quickview-1/assets/js/product-modals.js?")}});