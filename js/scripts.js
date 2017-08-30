$(function() {
  // Cache DOM elements
  var $body = $("body"),
    nav1 = $(".primary-navigation"),
    $window = $(window),
    $document = $(document);

  $("#old-ie-script").remove();

  // Throttle resize and scroll events; don't overload browser
  $window.resize($.throttle(500, function() {
    mobileMenu();
    helpTooltipPosition();
  })).resize();
  $window.scroll($.throttle(700, helpTooltipPosition)).scroll();

  $("#popup-wrapper").on("click", "button.cancel, button.continue", function() {
    var $this = $(this),
      popup = $this.closest(".popup"),
      popupId = popup.data("popup-id");

    if ($this.hasClass("continue")) {
      Cookies.set('popup-continue-' + popupId, 'true');
    }
    hidePopup(popup);
  });

  $("[data-collapse-btn]").wrap('<a href="#" class="collapse" data-collapse="visible" title="Show or hide this section" />').after(
    '<span class="btn-wrapper">' +
    '<i class="fa fa-fw fa-angle-up" aria-hidden="true"></i>' +
    '<span class="sr-only">Show or hide this section</span>' +
    '</span>');

  // Scroll to top
  $("[name='to-top']").click(function() {
    $("html, body").animate({
      scrollTop: 0
    });
  });

  // Make anchors work, even though we've set a base-tag
  $('a[href^="#"]').click(function(e) {
    // Do an .is() test for dynamic urls
    if ($(this).is('[href^="#"]')) {
      document.location.hash = $(this).attr("href").substring(1);
      e.preventDefault();
    }
  });

  // Unset previous handler first, then attach new
  $("a.collapse").off().click(function(e) {
    var $this = $(this),
      isCollapsed = ($this.attr("data-collapse") == 'hidden') ? true : false;

    if (isCollapsed) {
      $this.attr("data-collapse", 'visible');
      $this.next("[data-collapse-target]").show();
    } else {
      $this.attr("data-collapse", 'hidden');
      $this.next("[data-collapse-target]").hide();
    }

    e.preventDefault();
  });

  // Immediately hide FAQ sections
  $("body.docs a.collapse").click();

  // ... unless it's in the hash
  if ($body.hasClass("docs") && window.location.hash) {
    $(window.location.hash).parent("a").click();
  }

  // Open current XPath in external beautifier
  $("body.matrix .xpath-wrapper a, body.xps.input .open-beautifier-wrapper a").click(function(e) {
    e.preventDefault();
    var href = $(this).attr('href');
    postXpath($("#xpath").serialize(), href);
  });

  // Toggle mobile nav
  nav1.find("button").click(function() {
    nav1.toggleClass("active");
  });

  // Hide mobile nav when clicking outside it
  $document.click(function(e) {
    if (nav1.hasClass("active")) {
      if (!$(e.target).closest(nav1.find("button"), nav1.find("ul")).length) {
        nav1.removeClass("active");
      }
    }
  });

  /* PAGE SPECIFIC */
  if ($body.hasClass("input")) {
    var clearBtn = $("[name='clear']");
    clearBtn.click(function(e) {
      var $this = $(this);
      $this.prev("[name='input'], textarea").val("").addClass("no-content").focus();
      $this.prop("disabled", true);
      // Prevent form submission / page reload
      e.preventDefault();
    });

    $("[name='input'], textarea").keyup(function() {
      var $this = $(this);
      if ($this.val()) {
        $this.removeClass("no-content");
        clearBtn.prop("disabled", false);
      } else {
        $this.addClass("no-content");
        clearBtn.prop("disabled", true);
      }
    }).keyup();

    if ($body.hasClass("ebs")) {
      if (Cookies.get('ebs-input')) {
        $("[name='input']").val(Cookies.get('ebs-input')).change();
      }
      $("[type='submit']").click(function() {
        Cookies.set('ebs-input', $("[name='input']").val(), { expires: 7, path: ''});
      });
    }


    // Allow taalportaal integration for the input pages
    taalPortaalFiller();

    if ($body.hasClass("xps")) {
      var input = $("[name='xpath']");
      if (Cookies.get('xps-input')) {
        input.val(Cookies.get('xps-input')).change();
      }
      /* Basic XPath validation */
      // Check whether the XPath has the same amount of opening and closing tags
      // If not, throw custom validation error message
      $("[type='submit']").click(function() {
        var openBrackets = (input.val().match(/\[/g) || "").length,
          closeBrackets = (input.val().match(/\]/g) || "").length;
        if (openBrackets == closeBrackets) {
          input[0].setCustomValidity("");
          Cookies.set('xps-input', input.val(), { expires: 7, path: ''});
        } else {
          if (openBrackets > closeBrackets) {
            input[0].setCustomValidity("Fix XPath inconsistency: missing ] or unnecessary [ (x" + (openBrackets - closeBrackets) + ")");
          }
          if (openBrackets < closeBrackets) {
            input[0].setCustomValidity("Fix XPath inconsistency: missing [ or unnecessary ] (x" + (closeBrackets - openBrackets) + ")");
          }
        }
      });

      input.keyup(function() {
        this.setCustomValidity("");
      });
    }

  } else if ($body.hasClass("matrix")) {
    var input = $("[name='xpath']");
    // Check whether the XPath has the same amount of opening and closing tags
    // If not, throw custom validation error message
    $("[type='submit']").click(function() {
      var openBrackets = (input.val().match(/\[/g) || "").length,
        closeBrackets = (input.val().match(/\]/g) || "").length;
      if (openBrackets == closeBrackets) {
        input[0].setCustomValidity("");
      } else {
        if (openBrackets > closeBrackets) {
          input[0].setCustomValidity("Fix XPath inconsistency: missing ] or unnecessary [ (x" + (openBrackets - closeBrackets) + ")");
        }
        if (openBrackets < closeBrackets) {
          input[0].setCustomValidity("Fix XPath inconsistency: missing [ or unnecessary ] (x" + (closeBrackets - openBrackets) + ")");
        }
      }
    });

    input.keyup(function() {
      this.setCustomValidity("");
    });

    // Upon changing selection in the matrix, re-draw the representation tree
    var getTreePathXHR;
    getTreePath();

    $("main form input[type='radio'], main form input[type='checkbox']:not([name='ct'])").change(function() {
      getTreePath();
    });

    // Final submission is necessary, because 're-drawing' also stores final
    // variables in PHP
    $("form").submit(getTreePath());

    // Allow cell clicks to go down to the inputs, but prevent clicks on the input
    // going up. Otherwise, we'll get stuck in an infinite loop
    $("tbody td").click(function() {
      $(this).find("input").click();
    }).find("input").click(function(e) {
      e.stopPropagation();
    });

    $(".table-wrapper .case-sensitive input[type='checkbox']").each(function(i, el) {
      var $el = $(el);
      if ($el.is(":checked")) {
        $el.prop("disabled", false).parent("td:not(.punctuation)").removeClass("disabled");
      }
    });

    // Enable disable the option for case-sensitivity
    $(".table-wrapper input[type='radio']").click(function() {
      var $this = $(this),
        mother = $this.parent("td:not(.punctuation)"),
        checkCell = $(".table-wrapper .case-sensitive td:nth-child(" + (mother.index() + 1) + ")")

      if (mother.length) {
        if ($this.val() == "token") {
          checkCell.removeClass("disabled").children("input[type='checkbox']").prop("disabled", false);
        } else {
          checkCell.addClass("disabled").children("input[type='checkbox']").prop("checked", false).prop("disabled", true);
        }
      }
    });

    // Toggle advanced options
    var advancedBtn = $(".advanced-btn-wrapper button"),
      advancedRow = $(".table-wrapper tr.advanced-option");

    advancedBtn.click(function() {
      $(".advanced-option:not(tr)").toggle();
      advancedRow.toggleClass("visible-row");
      var str;
      if (advancedRow.hasClass("visible-row")) {
        str = 'Hide advanced options';
        adjustTextareaHeight($("#xpath"));
        helpTooltipPosition();
      } else {
        str = 'Show advanced options';
      }
      advancedBtn.attr("title", str).children("span").text(str);
    });

    $(".xpath-wrapper input[type='reset']").click(function(e) {
      e.preventDefault();
      $("#xpath").val($(".xpath-wrapper input[name='originalXp']").val()).change();
      $(".input-wrapper, .xpath-wrapper > div:first-child, #tree-output, .guidelines").removeClass("advanced-mode-active").find(":not(.disabled) > input, :not(.disabled) > button").prop("disabled", false);
      $('[name="manualMode"]').val("false");
      $(".xpath-wrapper textarea").on("mousedown", editWarningOnClick);
    });

    $(".xpath-wrapper textarea").on("mousedown", editWarningOnClick);

    // Show warning when editing XPath
    // Only do this once, use cookies
    function editWarningOnClick() {
      var popupClass = "edit-warning";
      if ($("." + popupClass).length == 0) {
        var html = "<p>Editing the XPath will disable all options including the selection table and the tree representation. Furthermore, you will <strong>not</strong> be able to select SoNaR as the corpus of interest.</p>" +
          "<p><strong>Only edit this code if you know what you are doing!</strong></p>";
        html += "<small>You can re-enable all options and reset XPath to the last unmodified structure by pressing the button <em>Reset XPath</em></small>";

        fillPopup(html, true, popupClass);
      }

      var popup = $("." + popupClass),
        popupId = popup.data("popup-id");

      if (!Cookies.get('popup-continue-' + popupId)) {
        showPopup(popup, $("#xpath"));
        popup.find("button.continue").click(function() {
          $(".input-wrapper, .xpath-wrapper > div:first-child, #tree-output, .guidelines").addClass("advanced-mode-active").find(":not(.disabled) > input, :not(.disabled) > button").prop("disabled", true);
          $('[name="manualMode"]').val("true");

          $(".continue-btn-wrapper [type='submit']").prop("disabled", false);
          $(".xpath-wrapper textarea").off("mousedown");
        });
      } else {
        $(".input-wrapper, .xpath-wrapper > div:first-child, #tree-output, .guidelines").addClass("advanced-mode-active").find(":not(.disabled) > input, :not(.disabled) > button").prop("disabled", true);
        $('[name="manualMode"]').val("true");

        $(".continue-btn-wrapper [type='submit']").prop("disabled", false);
        $(".xpath-wrapper textarea").off("mousedown");
      }
    }
  } else if ($body.hasClass("treebanks")) {
    // Main treebank selection (CGN, Lassy, SONAR)
    $("[type='radio']").change(function() {
      var $this = $(this),
        value = $this.val();
      if ($this.is("[name='treebank']")) {
        $(".cgn, .lassy, .sonar").hide().removeClass("active");
        $(".cgn, .lassy, .sonar").find("[type='checkbox'], [type='radio']").prop("disabled", true);
        $("." + value).show().addClass("active").find("label:not(.disabled)").find("[type='checkbox'], [type='radio']").prop("disabled", false);

        if (value != 'sonar' && $("." + value + " [type='checkbox']:checked").length == 0) {
          $(".continue-btn-wrapper [type='submit']").prop("disabled", true);
        } else {
          $(".continue-btn-wrapper [type='submit']").prop("disabled", false);
        }
      }
    });
    // Selecting subtreebanks
    $("[type='checkbox']").change(function() {
      var $this = $(this);

      this.setCustomValidity('');

      // If this is not a check-all checkbox
      if (!$this.is("[data-check^='all']")) {
        // For subtreebanks with checkboxes (Lassy and CGN only, they have checkboxes)
        if ($this.is("[name='subtreebank[]']")) {
          var checkboxAll,
            treebank = $this.attr("data-treebank"),
            tableWrapper = $this.closest(".table-wrapper"),
            value = $this.val(),
            valStartsWith = value.substring(0, 1);

          if (valStartsWith == "n") {
            checkboxAll = tableWrapper.find("[data-check='all-cgn-nl']");
          } else if (valStartsWith == "v") {
            checkboxAll = tableWrapper.find("[data-check='all-cgn-vl']");
          } else {
            checkboxAll = tableWrapper.find("[data-check='all-lassy']");
          }

          var subtreebanks = tableWrapper.find("[name='subtreebank[]']"),
            cgnValueString = (treebank == 'cgn') ? "[value^='" + valStartsWith + "']" : "";

          // If all component checkboxes are checked
          if (subtreebanks.filter(cgnValueString + ":not(:disabled)").length == subtreebanks.filter(cgnValueString + ":not(:disabled):checked").length) {
            checkboxAll.prop("indeterminate", false);
            checkboxAll.prop("checked", true);
          }
          // If no checkboxes are checked
          else if (subtreebanks.filter(cgnValueString + ":checked").length == 0) {
            checkboxAll.prop("indeterminate", false);
            checkboxAll.prop("checked", false);
          }
          // In all other cases (i.e. some checked some not checked)
          else {
            checkboxAll.prop("indeterminate", true);
          }
        }
      }
      // If this is a check-all checkbox
      else {
        var checked = $this.prop("checked");
        if (!checked) {
          $(".continue-btn-wrapper [type='submit']").prop("disabled", true);
        } else {
          $(".continue-btn-wrapper [type='submit']").prop("disabled", false);
        }

        if ($this.is("[data-check='all-cgn-vl']")) {
          $(".cgn label:not(.disabled) [type='checkbox'][value^='v']").prop("checked", checked);
        } else if ($this.is("[data-check='all-cgn-nl']")) {
          $(".cgn label:not(.disabled) [type='checkbox'][value^='n']").prop("checked", checked);
        } else {
          $(".lassy label:not(.disabled) [type='checkbox']").prop("checked", checked);
        }
      }

      if ($("div.active .table-wrapper [type='checkbox']:checked").length > 0) {
        $(".continue-btn-wrapper [type='submit']").prop("disabled", false);
      } else {
        $(".continue-btn-wrapper [type='submit']").prop("disabled", true);
      }
    });

    // On document ready, set active treebank (from cache or not)
    if ($("[type='radio'][name='treebank']:checked").length == 0) {
      $("[type='radio'][name='treebank'][value='lassy']").click().change();
    } else {
      $("[type='radio'][name='treebank']:checked").click().change();
    }
  }

  function helpTooltipPosition() {
    // Not for .treebanks, because we want to hang right on those
    $("body:not(.treebanks) .help-tooltip[data-title]").each(function(i, el) {
      var $this = $(el),
        rect = el.getBoundingClientRect(),
        w = $window.width(),
        h = $window.height();
      $this.addClass("hang-right hang-left hang-bottom hang-top");
      if ((w - rect.right <= 320) || (rect.top <= 160) || (h - rect.bottom <= 160)) {
        $this.removeClass("hang-right");
      }
      if ((rect.left <= 320) || (rect.top <= 160) || (h - rect.bottom <= 160)) {
        $this.removeClass("hang-left");
      }
      if ((h - rect.bottom <= 320) || (w - rect.right <= 160) || (rect.left <= 160)) {
        $this.removeClass("hang-bottom");
      }
      if ((rect.top <= 320) || (w - rect.right <= 160) || (rect.left <= 160)) {
        $this.removeClass("hang-top");
      }
    });
  }

  function postXpath(xpath, href) {
    $.ajax({
      type: 'POST',
      url: 'http://bramvanroy.be/projects/xpath-beautifier/php/receive-post.php',
      crossDomain: true,
      data: xpath,
      headers: {
        "cache-control": "no-cache"
      },
      xhrFields: {
        withCredentials: true
      }
    }).done(function(data) {}).fail(function(a, b, c) {}).always(function() {
      var newTab = window.open(href, '_blank');
      if (newTab) {
        newTab.focus();
      } else {
        alert('Your browser blocked opening a new window. Please allow pop-ups for this website. We will never show advertisements.');
      }
    });
  }

  function mobileMenu() {
    if ($window.width() < 721) {
      nav1.addClass("small");
      nav1.find("button").addClass("active");
    } else {
      nav1.removeClass("active small");
      nav1.find("button").removeClass("active");
    }
  }

  function getTreePath() {
    if (getTreePathXHR) getTreePathXHR.abort();

    $(".continue-btn-wrapper [type='submit']").prop("disabled", true);
    getTreePathXHR = $.ajax({
        url: getTreePathScript,
        method: "POST",
        data: $("main form").serialize()
      })
      .done(function(json) {
        var data = $.parseJSON(json);

        if (data.location) {
          $("#tree-output").treeVisualizer(data.location, {
            extendedPOS: true
          });
        }

        if (data.xpath) {
          $("#xpath, .xpath-wrapper input[name='originalXp']").val(data.xpath);
          adjustTextareaHeight($("#xpath"));
        }

        $(".continue-btn-wrapper [type='submit']").prop("disabled", false);
      });
  }

  function adjustTextareaHeight($el) {
    $el.height(0).outerHeight($el[0].scrollHeight + 2);
  }

  function fillPopup(html, cancelOption, additionalClass) {
    var popupId = 1;
    while (true) {
      if ($("[data-popup-id='" + popupId + "'").length) {
        popupId++;
      } else {
        var popup = document.createElement("aside");
        $(popup).html(html).attr({
          "data-popup-id": popupId,
          "class": "popup"
        }).hide().appendTo("#popup-wrapper");
        $(popup).append('<div class="btn-wrapper"><button title="Continue editing XPath" type="button" class="continue">Continue</button></div>');
        if (cancelOption) {
          $(popup).children(".btn-wrapper").prepend('<button title="Close this pop-up" type="button" class="cancel">Cancel</button>');
        }

        if (additionalClass) {
          $(popup).addClass(additionalClass);
        }
        break;
      }
    }

    if ($("#popup-overlay").length == 0) {
      var overlay = document.createElement("div");
      $(overlay).attr("id", "popup-overlay").hide().appendTo("#popup-wrapper");
    }
  }

  /**
   * Mixed usage of offset and getBoundingClientRect
   * - offset: relative to document, does not take scrolling into account
   * - getBoundingClientRect: relative to viewport, takes scrolling into account
   * For the available space in the VIEWPORT, getBoundingClientRect is needed
   * For the actual, absolute, positioning, the offset is required
   */
  function showPopup($popup, $target) {
    $popup.css({
      visibility: "hidden",
      display: "block"
    });

    var w = $window.width(),
      h = $window.height(),
      tRect = $target[0].getBoundingClientRect(),
      tOff = $target.offset(),
      tRectR = w - tRect.right,
      tRectB = h - tRect.bottom,
      pRect = $popup[0].getBoundingClientRect(),
      diffW = (tRect.width - pRect.width) / 2,
      diffH = (tRect.height - pRect.height) / 2,
      m = 16;

    $popup.removeClass("hang-right hang-left hang-bottom hang-top");

    if ((tRect.top > pRect.height + m) && (tRectR > pRect.width / 2) && (tRect.left > pRect.width / 2)) {
      $popup.addClass("hang-top");
      $popup.css({
        "left": tOff.left + diffW,
        "top": tOff.top - pRect.height - m
      });
    } else if ((tRect.left > pRect.width + m) && (tRect.top > pRect.height / 2) && (tRectB > pRect.height / 2)) {
      $popup.addClass("hang-left");
      $popup.css({
        "left": tOff.left - pRect.width - m,
        "top": tOff.top + diffH
      });
    } else if ((tRectR > pRect.width + m) && (tRect.top > pRect.height / 2) && (tRectB > pRect.height / 2)) {
      $popup.addClass("hang-right");
      $popup.css({
        "left": tOff.left + tRect.width + m,
        "top": tOff.top + diffH
      });
    } else {
      $popup.addClass("hang-bottom");
      $popup.css({
        "left": tOff.left + diffW,
        "top": tOff.top + tRect.height + m
      });
    }

    $popup.css("visibility", "visible");
    $("#popup-overlay").show();
  }

  function hidePopup($popup) {
    $popup.fadeOut("fast");
    $("#popup-overlay").hide();
  }

  function taalPortaalFiller() {
    if (getUrlVars()["tpinput"]) {
      // Decode URI into readable string
      var tpinput = decodeURIComponent(getUrlVars()["tpinput"]);

      $('.input.ebs input').val(tpinput);
      $('.input.xps textarea[name=xpath]').val(tpinput);
    }
  }

  function getUrlVars() {
    var vars = [],
      hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  }
});
