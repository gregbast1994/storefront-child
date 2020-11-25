/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is not neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/*!**********************************!*\
  !*** ./src/front/front-index.js ***!
  \**********************************/
/*! unknown exports (runtime-defined) */
/*! runtime requirements:  */
eval("jQuery(document).ready(function ($) {\n  var msp = {\n    $modal: $(\"#msp_modal\"),\n    $header: $(\"#mobile-menu\"),\n    $checkout_fields: $(\"#customer_details > div:first-child\"),\n    bulk_order_list: {},\n    init: function () {\n      this.init_owl_carousel();\n      this.init_slideout(); // $(document.body).on( 'click', 'i.msp-star-rating', msp.bind_create_review_star_buttons )\n      // $('#msp_review').on( 'click', '.remove-product-image-from-review', msp.delete_user_product_image )\n\n      $(\"#msp_submit_question\").on(\"blur\", 'input[name=\"question\"]', msp.customer_faq_validate_question);\n      $(\"#msp_submit_question\").on(\"click\", \"button\", msp.customer_submit_question);\n      $(\"#msp_customer_faq\").on(\"click\", \".msp-submit-answer\", msp.customer_submit_awnser);\n      $(\".woocommerce-variation-add-to-cart\").on(\"change\", 'input[name=\"variation_id\"]', msp.replace_single_product_price_range);\n      $(\"#filter-button\").click(function () {\n        $(\".widget-area\").slideToggle();\n      }); // makes the update shipping options more consistant.\n\n      this.$checkout_fields.on(\"focusout\", \"input\", function () {\n        $(document.body).trigger(\"update_checkout\");\n      });\n      $(\"#msp-contact\").on(\"click\", \"button.submit\", msp.submit_contact_form);\n      $(\"#bulk-tab-content\").on(\"change\", \".var-bulk-update\", msp.add_to_bulk_list);\n      this.$modal.on(\"show.bs.modal\", this.route);\n      this.$modal.on(\"submit\", \"form\", this.submit);\n      this.$header.on(\"click\", \"li.menu-item-has-children\", this.open_nav_child_list);\n      this.promo_pop_up();\n    },\n    append_nav_layers: function () {\n      $(\".woocommerce-widget-layered-nav\").each(function (i, e) {\n        e.insertAdjacentHTML(\"beforeend\", '<a href=\"javascript:void(0)\" class=\"see_more\" >See more</a>');\n      });\n    },\n    see_more_nav_layers: function (e) {\n      let list = e.delegateTarget.getElementsByTagName(\"ul\")[0];\n\n      if (typeof list !== \"undefined\") {\n        console.log(list);\n      }\n    },\n    promo_pop_up: function () {\n      let cookie = getCookie(\"msp_promo_seen\");\n\n      if ($(\".promo_pop_up_btn\").length && cookie != wp_ajax.cookie_version) {\n        setTimeout(function () {\n          $(\".promo_pop_up_btn\").click();\n        }, 2000);\n      }\n    },\n    replace_single_product_price_range: async function (e) {\n      const main_price = $(\"#order-tab-content p.price\");\n      const availability = $(\".woocommerce-variation-availability\").html(); // Setup for ajax request\n\n      let data = {\n        action: \"msp_get_variation_price_html\",\n        id: $(\"input.variation_id\").val()\n      }; // Make sure every option is selected before replacing price range\n\n      var ready = [];\n      let options = $(\"table.variations select\");\n      options.each(function () {\n        ready.push(this.value.length);\n      });\n\n      if (ready.every(readyCheck)) {\n        main_price.html('<div class=\"spinner-border text-danger\" role=\"status\"><span class=\"sr-only\">Loading...</span></div>');\n        await $.post(wp_ajax.url, data, function (response) {\n          if (response) main_price.html(response + availability);\n        });\n        const newPrice = main_price.find(\"span.amount\").last().text().replace(\"$\", \"\").replace(\",\", \"\");\n\n        if (newPrice) {\n          msp.update_discount_table(newPrice);\n        }\n      }\n    },\n    update_discount_table: function (newPrice) {\n      console.log(newPrice);\n      $(\"#msp-bulk-pricing tbody > tr > td\").each((i, td) => {\n        if (i !== 0) {\n          const updatedDiscount = \"$\" + (parseFloat(td.className) * newPrice).toFixed(2);\n          td.innerText = updatedDiscount;\n        }\n      });\n    },\n    add_to_bulk_list: function (e) {\n      //https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/\n      let order_list = [];\n      let $input = $(e.target);\n      if (+$input.val() > +$input.attr(\"max\")) $input.val(+$(this).attr(\"max\"));\n      msp.bulk_order_list[$input.attr(\"id\")] = $input.val();\n\n      for (var key in msp.bulk_order_list) {\n        // validation\n        if (msp.bulk_order_list.hasOwnProperty(key)) {\n          // do not add if qty is 0; for accidental clicks.\n          if (msp.bulk_order_list[key] != 0) {\n            // formatting; if qty is 1, only push the key, not the value for -> woocommerce_maybe_add_multiple_products_to_cart()\n            msp.bulk_order_list[key] > 1 ? order_list.push(key + \":\" + msp.bulk_order_list[key]) : order_list.push(key);\n          }\n        }\n      }\n\n      order_list = order_list.join(\",\");\n      let url = window.location.protocol + \"//\" + window.location.hostname + \"/cart/?add-to-cart=\" + order_list + \",\";\n      let add_to_cart_str = !order_list.length ? \"#\" : url;\n      $(\"#iww_bulk_form_submit\").attr(\"href\", add_to_cart_str);\n      console.log(add_to_cart_str, order_list);\n    },\n    open_nav_child_list: function (e) {\n      $child = $(e.target.children[1]);\n      console.log(e, $child);\n    },\n    init_slideout: function () {\n      var slideout = new Slideout({\n        panel: document.getElementById(\"page\"),\n        menu: document.getElementById(\"mobile-menu\"),\n        padding: 256,\n        tolerance: 70,\n        touch: false\n      });\n      document.querySelector(\".mobile-menu-button\").addEventListener(\"click\", function () {\n        $(\"#mobile-menu\").show();\n        slideout.toggle();\n      });\n      document.querySelector(\"a.close\").addEventListener(\"click\", close);\n\n      function close(eve) {\n        eve.preventDefault();\n        slideout.close();\n      }\n\n      slideout.on(\"beforeopen\", function () {\n        this.panel.classList.add(\"panel-open\");\n      }).on(\"open\", function () {\n        this.panel.addEventListener(\"click\", close);\n      }).on(\"beforeclose\", function () {\n        this.panel.classList.remove(\"panel-open\");\n        this.panel.removeEventListener(\"click\", close);\n      });\n    },\n    init_owl_carousel: function () {\n      $(\".owl-carousel\").owlCarousel({\n        responsiveClass: true,\n        nav: true,\n        dots: true,\n        margin: 10,\n        responsive: {\n          0: {\n            items: 2\n          },\n          450: {\n            items: 4\n          },\n          1000: {\n            items: 5\n          }\n        }\n      });\n    },\n    get_json_from_url: function (url) {\n      // https://stackoverflow.com/questions/8486099/how-do-i-parse-a-url-query-parameters-in-javascript\n      if (!url) url = location.href;\n      var question = url.indexOf(\"?\");\n      var hash = url.indexOf(\"#\");\n      if (hash == -1 && question == -1) return {};\n      if (hash == -1) hash = url.length;\n      var query = question == -1 || hash == question + 1 ? url.substring(hash) : url.substring(question + 1, hash);\n      var result = {};\n      query.split(\"&\").forEach(function (part) {\n        if (!part) return;\n        part = part.split(\"+\").join(\" \"); // replace every + with space, regexp-free version\n\n        var eq = part.indexOf(\"=\");\n        var key = eq > -1 ? part.substr(0, eq) : part;\n        var val = eq > -1 ? decodeURIComponent(part.substr(eq + 1)) : \"\";\n        var from = key.indexOf(\"[\");\n        if (from == -1) result[decodeURIComponent(key)] = val;else {\n          var to = key.indexOf(\"]\", from);\n          var index = decodeURIComponent(key.substring(from + 1, to));\n          key = decodeURIComponent(key.substring(0, from));\n          if (!result[key]) result[key] = [];\n          if (!index) result[key].push(val);else result[key][index] = val;\n        }\n      });\n      return result;\n    },\n    route: function (e) {\n      let $button = $(e.relatedTarget);\n      var path = {\n        title: $button.attr(\"data-title\"),\n        model: $button.attr(\"data-model\"),\n        action: $button.attr(\"data-action\"),\n        id: $button.attr(\"data-id\")\n      };\n      msp.$modal.find(\".modal-title\").text(path.title);\n      msp[path.model](path.action, path.id);\n    },\n    submit: function (e) {\n      // this obviously wont work for other modal submissions.\n      e.preventDefault();\n      console.log(e);\n      let body = msp.$modal.find(\".modal-body\");\n      let action = $(e.target).find('input[name=\"action\"]').val();\n      let model = $(e.target).find('input[name=\"model\"]').val();\n      let data = {\n        action: action,\n        form_data: $(e.target).serialize()\n      };\n      $.post(wp_ajax.url, data, function (response) {\n        msp[model](\"post\", \"\", response);\n      });\n    },\n    close: function () {\n      msp.$modal.modal(\"toggle\");\n    },\n    size_guide: function (action, id) {\n      $.post(wp_ajax.url, {\n        action: \"msp_get_product_size_guide_src\",\n        id: id\n      }, function (response) {\n        msp.$modal.find(\".modal-body\").html($(\"<img/>\", {\n          src: response,\n          class: \"mx-auto\"\n        }));\n      });\n    },\n    promo: function (action, id) {\n      $.post(wp_ajax.url, {\n        action: \"msp_get_promo_pop_up_link_and_image\",\n        id: id\n      }, function (response) {\n        console.log(response);\n        let html = $(\"<a/>\", {\n          href: response.link\n        }).append($(\"<img/>\", {\n          src: response.src,\n          class: \"mx-auto\"\n        }));\n        msp.$modal.find(\".modal-body\").html(html);\n        document.cookie = \"msp_promo_seen=\" + wp_ajax.cookie_version + \"; path=/; max-age=2592000;\";\n      });\n    },\n    leave_feedback: function (action, id, response) {\n      let body = msp.$modal.find(\".modal-body\");\n\n      switch (action) {\n        case \"get\":\n          $.post(wp_ajax.url, {\n            action: \"msp_get_leave_feedback_form\",\n            id: id\n          }, function (response) {\n            body.html(response);\n          });\n          break;\n\n        case \"post\":\n          console.log(response);\n\n          if (!response) {\n            body.find(\".feedback\").text(\"Feedback requires atleast a star rating; thanks!\");\n          } else {\n            body.html('<div class=\"text-center\"><i class=\"fas fa-check-circle fa-2x text-success\"></i><h1>Thank you for your feedback!</h1></div>');\n            setTimeout(function () {\n              msp.close();\n            }, 3000);\n          }\n\n          break;\n      }\n    },\n    submit_contact_form: function (e) {\n      var data = $(e.delegateTarget.children[1]);\n      var errors = data.find(\"#errors\");\n      console.log(data, errors);\n      $.post(wp_ajax.url, data.serialize(), function (response) {\n        if (response) {\n          $(e.delegateTarget).html('<p class=\"lead\">Thank you, expect a response same or next business day.</p>');\n        } else {\n          data.find(\"#errors\").text(\"Error, please try again and fill everything out!\");\n        }\n      });\n    }\n  };\n  msp.init();\n\n  if ($(\"#msp_select2_products\").length != 0) {\n    $(\"#msp_select2_products\").select2({\n      ajax: {\n        url: wp_ajax.url,\n        // AJAX URL is predefined in WordPress admin\n        dataType: \"json\",\n        delay: 250,\n        // delay in ms while typing when to perform a AJAX search\n        data: function (params) {\n          return {\n            q: params.term,\n            // search query\n            action: \"msp_get_products\" // AJAX action for admin-ajax.php\n\n          };\n        },\n        processResults: function (data) {\n          var options = [];\n\n          if (data) {\n            // data is the array of arrays, and each of them contains ID and the Label of the option\n            $.each(data, function (index, text) {\n              // do not forget that \"index\" is just auto incremented value\n              options.push({\n                id: text[0],\n                text: text[1]\n              });\n            });\n          }\n\n          return {\n            results: options\n          };\n        },\n        cache: true\n      },\n      minimumInputLength: 3 // the minimum of symbols to input before perform a search\n\n    });\n  }\n\n  var max_chars = 5;\n  $(\"#billing_postcode\").keydown(function (e) {\n    if ($(this).val().length >= max_chars) {\n      $(this).val($(this).val().substr(0, max_chars));\n    }\n  });\n  $(\"#billing_postcode\").keyup(function (e) {\n    if ($(this).val().length >= max_chars) {\n      $(this).val($(this).val().substr(0, max_chars));\n    }\n  });\n  $(\"#billing_po\").val(\"\");\n\n  function setCookie(cname, cvalue, exdays) {\n    var d = new Date();\n    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);\n    var expires = \"max-age=\" + d.toUTCString();\n    document.cookie = cname + \"=\" + cvalue + \";\" + expires + \";path=/\";\n  }\n\n  function getCookie(cname) {\n    var name = cname + \"=\";\n    var decodedCookie = decodeURIComponent(document.cookie);\n    var ca = decodedCookie.split(\";\");\n\n    for (var i = 0; i < ca.length; i++) {\n      var c = ca[i];\n\n      while (c.charAt(0) == \" \") {\n        c = c.substring(1);\n      }\n\n      if (c.indexOf(name) == 0) {\n        return c.substring(name.length, c.length);\n      }\n    }\n\n    return \"\";\n  }\n\n  function readyCheck(length) {\n    return length > 0;\n  }\n});\n\n//# sourceURL=webpack://msp-storefront/./src/front/front-index.js?");
/******/ })()
;