$(function() {
  /* Specify how long it should take for functions to start
   * Flushing of individual results starts immediately
   * - Before ALL results are being gathered on the background (ms), OR
   * - Amount of hits before ALL results are being gathered (hits)
   */
  var timeoutBeforeMore = 500,
    xResultsBeforeMore = 4;

  // Cache DOM elements
  var hash = window.location.hash,
    $window = $(window),
    $document = $(document),
    body = $("body"),
    controls = $(".controls"),
    resultsWrapper = $(".results-ajax-wrapper"),
    filterSelWrapper = $(".filter-sel-wrapper"),
    downloadWrapper = $("#download-overview .flex-content"),
    messages = downloadWrapper.find(".messages"),
    notificationWrapper = $(".notification-wrapper");

  // Initialize variables
  var xhrAllSentences,
    xhrFetchSentences,
    xhrCount,
    resultID = 0,
    resultsCount = 0,
    done = false,
    doneCounting = false,
    hasError = false;

  // Immediately start flushing results and counting
  getSentences();
  countAll();
  body.addClass("results-loading counts-loading");

  // Flushes the results one by one
  function getSentences() {
    if (!done && (resultID <= phpVars.resultsLimit)) {
      $(".loading-wrapper.searching").addClass("active");
      xhrFetchSentences = $.ajax(phpVars.fetchResultsPath)
        .done(function(json) {
          if (!done && !hasError) {
            var data = $.parseJSON(json);
            if (!data.error && data.data) {
              resultsWrapper.find("tbody.empty").hide();
              loopResults(data.data, false);
            } else {
              $(".loading-wrapper.searching").removeClass("active");
              messages.closest(".results-messages-wrapper").addClass("active");
              downloadWrapper.addClass("active");
              if (data.error) {
                messageOnError(data.data)
              } else if (resultsWrapper.find("tbody:not(.empty)").children().length == 0) {
                messageNoResultsFound();
              } else {
                messageAllResultsFound();
              }

              clearTimeout(findAllTimeout);
              done = true;
              hasError = true;

              if (xhrAllSentences) xhrAllSentences.abort();
              if (xhrCount) xhrCount.abort();
            }
          }
        })
        .fail(function(jqXHR, textStatus, error) {
          // Edge triggers a fail when an XHR request is aborted
          // We don't want that, so if the error message is abort, ignore
          if (error != 'abort') {
            var string = "An error occurred";
            if (error != '') string += ": " + error + ".";

            messageOnError(string);
          }
        })
        .always(function() {
          if ((resultID == xResultsBeforeMore) && !done && !hasError) {
            findAll();
          }
        });
    }
  }

  // Short time-out to make sure at least a few results are already flushed
  var findAllTimeout = setTimeout(function() {
    findAll();
  }, timeoutBeforeMore);

  // Find ALL results in one go (limited in PHP to the max amount allowed)
  function findAll() {
    // If the Ajax request is already sent, return
    if (xhrAllSentences) return;
    // Abort time out so the script won't fire findAll() again
    clearTimeout(findAllTimeout);
    xhrAllSentences = $.ajax(phpVars.getAllResultsPath)
      .done(function(json) {
        var data = $.parseJSON(json);
        if (!data.error && data.data) {
          resultsWrapper.find("tbody.empty").hide();
          loopResults(data.data, true);
        } else {
          $(".loading-wrapper.searching").removeClass("active");
          messages.children("div").removeClass("error notice").closest(".results-messages-wrapper").show();
          downloadWrapper.addClass("active");
          if (data.error) {
            messageOnError(data.data);
          } else if (resultsWrapper.find("tbody:not(.empty)").children().length == 0) {
            messageNoResultsFound();
          }
        }
      })
      .fail(function(jqXHR, textStatus, error) {
        // Edge triggers a fail when an XHR request is aborted
        // We don't want that, so if the error message is abort, ignore
        if (error != 'abort') {
          var string = "An error occurred: " + error + ".";
          messageOnError(string);
        }
      })
      .always(function() {
        done = true;
        if (xhrFetchSentences) xhrFetchSentences.abort();
      });
  }

  // Count ALL results in one go (not limited)
  function countAll() {
    xhrCount = $.ajax(phpVars.fetchCountsPath)
      .done(function(json) {
        var data = $.parseJSON(json);
        console.log(data);
        if (!data.error && data.sum && data.counts) {
          var sum = data.sum,
            totalArray = data.counts

          resultsCount = sum;
          sum = numericSeparator(parseInt(sum));

          controls.find(".count strong + span").text(sum);
          messages.find(".amount-hits").text(sum);
          messages.find(".is-still-counting").remove();

          body.removeClass("counts-loading");
          body.addClass("counts-done");
          // Prepare distribution table. ONLY for lassy and cgn
          if (resultsCount > 0) {
            // Parse array containing database + total counts
            var size = Object.keys(totalArray).length;
            if (typeof totalArray !== 'undefined' && size > 0) {
              var hitsSum = 0,
                totalSum = 0;
              for (var database in totalArray) {
                var databaseString = database.split("_")[2],
                  componentHits = numericSeparator(parseInt(totalArray[database][0])),
                  componentTotal = numericSeparator(parseInt(totalArray[database][1]));

                hitsSum += parseInt(totalArray[database][0]);
                totalSum += parseInt(totalArray[database][1]);
                downloadWrapper.find(".distribution-wrapper tbody").append('<tr><td>' + databaseString + '</td><td>' + componentHits + '</td><td>' + componentTotal + '</td></tr>');
              }
              downloadWrapper.find(".distribution-wrapper tbody").append('<tr><th>Total</th><th>' + numericSeparator(hitsSum) + '</th><th>' + numericSeparator(totalSum) + '</th></tr>');
              downloadWrapper.find(".distribution-wrapper").show();
            }
            // If the total number of hits is larger than the allowed limit, show a message
            if (resultsCount > phpVars.resultsLimit) {
              messages.find(".is-restricted").show();
            }
          }
        }
      })
      .fail(function(jqXHR, textStatus, error) {
        if (error != 'abort') {
          var string = "An error occurred: " + error + ".";
          messageOnError(string);
        }
      })
      .always(function() {
        doneCounting = true;
      });
  }

  // Converts an integer with four or more digits to a comma-separated string
  function numericSeparator(integer) {
    if (Number.isInteger(integer) && integer > 999) {
      return integer.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
    }
    return integer;
  }

  function messageAllResultsFound() {
    disableAndEnableInputs();
    body.removeClass("results-loading").addClass("results-done");

    messages.load(phpVars.fetchHomePath + '/front-end-includes/results-messages.php #results-found', function() {
      if (doneCounting) {
        messages.find(".amount-hits").html(numericSeparator(parseInt(resultsCount)));
        messages.find(".is-still-counting").remove();
      }

      // Stop counting if we already have the amount of hits
      if (resultID < phpVars.resultsLimit) {
        if (xhrCount != null) {
          xhrCount.abort();
          xhrCount = null;
        }
        countString = controls.find(".count strong").text();

        controls.find(".count strong + span").text(countString);
        messages.find(".amount-hits").text(countString);
        messages.find(".is-still-counting").remove();
      }

      messages.children("div").removeClass("error").addClass("notice active").closest(".results-messages-wrapper").show();
      downloadWrapper.addClass("active");
      notificationWrapper.show();
    });
  }

  function messageNoResultsFound() {
    body.removeClass("results-loading").addClass("results-none");
    resultsWrapper.add(controls).remove();
    downloadWrapper.addClass("no-results");
    body.addClass("search-no-results");
    messages.load(phpVars.fetchHomePath + '/front-end-includes/results-messages.php #no-results-found', function() {
      messages.children("div").removeClass("notice").addClass("error active").closest(".results-messages-wrapper").show();
    });
  }

  function messageOnError(error) {
    body.removeClass("results-loading").addClass("results-failed");
    resultsWrapper.add(controls).remove();
    downloadWrapper.addClass("error");
    body.addClass("search-error");
    messages.load(phpVars.fetchHomePath + '/front-end-includes/results-messages.php #error', function() {
      var link = '<p>If this error persists, please do not hesitate to <a href="documentation.php#faq-4" title="How can you contact us?">contact us</a>!</p>';
      messages.find(".error-msg").text(error).after(link);

      messages.children("div").removeClass("notice").addClass("error active").closest(".results-messages-wrapper").show();
    });
  }

  function showTvFsOnLoad() {
    if (!body.hasClass("tv-fs-open")) {
      if (hash.indexOf("tv-") == 1) {
        var index = hash.match(/\d+$/);
        $("a.tv-show-fs").eq(index[0] - 1).click();
      }
    }
  }

  if (hash) {
    if (hash.indexOf("tv-") == 1) {
      messages.load(phpVars.fetchHomePath + '/front-end-includes/results-messages.php #looking-for-tree', function() {
        messages.children("div").removeClass("error").addClass("notice active").closest(".results-messages-wrapper").show();
        downloadWrapper.addClass("active");
      });
    }
  }

  function loopResults(sentences, returnAllResults) {
    if (returnAllResults) {
      done = true;
      resultID = 0;

      resultsWrapper.find("tbody:not(.empty)").empty();
    }

    // Append sentences to results table
    $.each(sentences, function(id, value) {
      if (returnAllResults || (!returnAllResults && !done)) {
        resultID++;
        var link = value[0];
        link = link.replace(/\bhref=\"([^"]+)\"/, "href=\"#tv-" + resultID + "\" data-tv-url=\"$1\"");

        resultsWrapper.find("tbody:not(.empty)").append('<tr data-result-id="' + resultID + '" data-component="' + value[2] + '">' +
          '<td>' + resultID + '</td><td>' + link + '</td><td>' +
          value[2] + '</td><td>' + value[1] + '</td></tr>');
      }
      // Check if we have to show the loaded sentence
      showTvFsOnLoad();
    });

    resultIDString = numericSeparator(parseInt(resultID));
    controls.find(".count strong").text(resultIDString);

    if (!returnAllResults && !done) {
      getSentences();
    } else {
      messageAllResultsFound();
    }
  }

  /* Filter results */
  $document.click(function(e) {
    if (controls.find(".filter-wrapper").hasClass("active")) {
      var target = $(e.target);
      if (!target.closest(".filter-wrapper").length) {
        controls.find(".filter-wrapper").removeClass("active");
      }
    }
  });
  controls.find("[for='filter-components']").click(function() {
      $(this).parent().addClass("active");
    }),
    filterSelWrapper.find("[type='checkbox']").change(function() {
      var $this = $(this),
        component = $this.val();

      $("#all-components").prop("indeterminate", false);

      if ($this.is("[name='component']")) {
        // Show/hide designated components in results
        if ($this.is(":checked")) {
          resultsWrapper.find("tbody:not(.empty) tr[data-component^='" + component + "']").show();
        } else {
          resultsWrapper.find("tbody:not(.empty) tr[data-component^='" + component + "']").hide();
        }

        // If none of the component checkboxes are checked
        if (!filterSelWrapper.find("[name='component']").is(":checked")) {
          resultsWrapper.find(".empty").css("display", "table-row-group").find("td").text("No results matching the specified filters");
          filterSelWrapper.find("#all-components").prop("checked", false).parent().removeClass("active");
        } else {
          if (filterSelWrapper.find("[name='component']:not(:disabled)").length == filterSelWrapper.find("[name='component']:not(:disabled):checked").length) {
            filterSelWrapper.find("#all-components").prop("checked", true).parent().addClass("active");
          } else {
            filterSelWrapper.find("#all-components").prop("checked", false).prop("indeterminate", true).parent().removeClass("active");
          }
          resultsWrapper.find(".empty").hide();
        }
      }
      // One checkbox to rule them all
      else if ($this.is("#all-components")) {
        $this.parent().toggleClass("active");
        filterSelWrapper.find("[type='checkbox'][name='component']:not(:disabled)").prop("checked", $this.is(":checked")).change();
      }
    });

  function disableAndEnableInputs() {
    $("[for='filter-components'], .filter-sel-wrapper label").removeClass("disabled").children("input").prop("disabled", false);

    // Disable the checkboxes (comonents) for which there are no results
    filterSelWrapper.find("[type='checkbox'][name='component']").each(function(i, el) {
      var $this = $(el),
        component = $this.val();

      if (resultsWrapper.find("tbody:not(.empty) tr[data-component^='" + component + "']").length == 0) {
        $this.prop("disabled", true);
        $this.prop("checked", false);
        $this.parent("label").addClass("disabled");
      }
    });
  }

  /* Search aborting */
  // Abort searching request, so we can start a new request more quickly
  window.addEventListener("beforeunload", function() {
    unLoadRequests();
  });

  // Because we are actually going back in history, some browsers might not
  // call the beforeunload event
  $(".secondary-navigation a").click(function() {
    unLoadRequests();
  });

  function unLoadRequests() {
    if (xhrAllSentences != null) {
      xhrAllSentences.abort();
      xhrAllSentences = null;
    }
    if (xhrCount != null) {
      xhrCount.abort();
      xhrCount = null;
    }
    if (xhrFetchSentences != null) {
      xhrFetchSentences.abort();
      xhrFetchSentences = null;
    }
    done = true;
  }

  /* Tree visualizer */
  resultsWrapper.find("tbody").on("click", "a.tv-show-fs", function(e) {
    var $this = $(this),
      treeSentence = $this.parent().nextAll("td:last-child").html(),
      tvFs = $("#tree-visualizer-fs");

    resultsWrapper.find("tbody a").removeClass("tv-toggled-link");

    if (!this.hasAttribute("data-tv-fontsize")) {
      $this.attr("data-tv-fontsize", 0);
    }
    tvFs.attr("data-tv-active-index", $this.closest("tr").index());
    $this.addClass("tv-toggled-link");

    body.treeVisualizer($this.data("tv-url"), {
      normalView: false,
      initFSOnClick: true,
      sentence: treeSentence,
      hasNavigation: true
    });
    e.preventDefault();
  });


  /* Notifications */
  // Close notification
  notificationWrapper.find("button").click(function() {
    notificationWrapper.hide();
  });
  // Scroll to download
  notificationWrapper.find("a").click(function(e) {
    e.preventDefault();

    $("html, body").stop().animate({
      scrollTop: $("#download-overview").offset().top
    }, 150);
    notificationWrapper.fadeOut("fast");
  });
  // Scroll to section on results page
  $("#results-menu a").off("click").click(function(e) {
    $("html, body").stop().animate({
      scrollTop: $($(this).attr("href")).offset().top
    }, 150);
    e.preventDefault();
  });
});
