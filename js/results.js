/**
 * Documentation loosely based on the JSDoc standard
 */
$(document).ready(function() {
    // Customizable attributes!
    // Specify how long it should take for functions to session
    // * Before counting ALL hits, independent of current fetched results (ms)
    // * Before ALL results are being gathered on the background (ms), OR
    // * Amount of hits before ALL results are being gathered (hits)
    var timeoutBeforeCount = 200,
        timeoutBeforeMore = 300,
        xResultsBeforeMore = 4;

    var hash = window.location.hash,
        $window = $(window),
        tvLink = $("a.tv-show-fs"),
        controls = $(".controls"),
        resultsWrapper = $(".results-wrapper"),
        filterWrapper = $(".filter-wrapper"),
        controls = $(".controls"),
        dummy = $(".dummy-controls");

    var xhrAllSentences,
        resultID = 0,
        resultsCount = 0,
        done = false,
        doneCounting = false;

    getSentences();

    function getSentences() {
        if (!done && (resultID <= phpVars.resultsLimit)) {
            $(".loading-wrapper.searching").addClass("active");
            $.ajax(phpVars.fetchResultsPath)
                .done(function(json) {
                    if (!done) {
                        var data = $.parseJSON(json);
                        $(".results-wrapper tbody:not(.empty) .added").removeClass("added");
                        if (!data.error && data.data) {
                            loopResults(data.data, false);
                        } else {
                            $(".loading-wrapper.searching").removeClass("active");
                            $(".results-wrapper tbody:not(.empty) .added").removeClass("added");
                            $(".messages").addClass("active");
                            if (data.error) {
                                messageOnError(data.data)
                            } else if ($(".results-wrapper tbody:not(.empty)").children().length == 0) {
                                messageNoResultsFound();
                            } else {
                                messageAllResultsFound();
                                clearTimeout(findAllTimeout);
                            }

                            done = true;
                            if (xhrAllSentences) xhrAllSentences.abort();
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
                    if ((resultID == xResultsBeforeMore) && !done) {
                        findAll();
                    }
                });
        }
    }
    var findAllTimeout = setTimeout(function() {
        findAll();
    }, timeoutBeforeMore);

    function findAll() {
        xhrAllSentences = $.ajax(phpVars.getAllResultsPath)
            .done(function(json) {
                var data = $.parseJSON(json);
                $(".results-wrapper tbody:not(.empty) .added").removeClass("added");
                if (!data.error && data.data) {
                    loopResults(data.data, true);
                    messageAllResultsFound();

                    clearTimeout(findAllTimeout);
                } else {
                    $(".loading-wrapper.searching").removeClass("active");
                    $(".messages").addClass("active");
                    if (data.error) {
                        messageOnError(data.data);
                    } else if ($(".results-wrapper tbody:not(.empty)").children().length == 0) {
                        messageNoResultsFound();
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
                done = true;
            });
    }

    setTimeout(function() {
        $.get(phpVars.fetchCountsPath, function(count) {
            if (count) {
                count = parseInt(count);
                $(".notice .still-counting").remove();
                resultsCount = numericSeparator(count);
                $(".count strong + span").text(resultsCount);
                $(".notice strong").text(resultsCount);

                doneCounting = true;
            }
        });
    }, timeoutBeforeCount);

    /**
     * Converts an integer with four or more digits to a comma-separated string
     * @param {number} integer - Any (positive) integer
     * @example
     * // returns 1,234,567
     * numericSeparator(1234567);
     * @returns {string} Returns thhe string representation of the number.
     */
    function numericSeparator(integer) {
        if (Number.isInteger(integer) && integer > 999) {
            return integer.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
        }
        return integer;
    }

    function messageAllResultsFound() {
        $(".loading-wrapper.searching").removeClass("active");
        $(".notice").html("<p></p>");
        disableAndEnableInputs();
        var stringCount = doneCounting ? [resultsCount, ''] : ['--', '<small class="still-counting">(still counting, can take a while)</small>'];
        notice = '<strong>' + stringCount[0] + '</strong> result(s) have/has been found! ' + stringCount[1];
        if (resultID >= phpVars.resultsLimit) {
            notice += '<br>We have restricted the output to 500 hits. ' +
                'You can find the reason for this <a href="' + phpVars.fetchHomePath + '/documentation.php#faq-1" ' +
                'title="Why is the output limited to 500 sentences?" target="_blank">in our FAQ</a>.';
        }
        notice += '<br><a href="'+phpVars.downloadPath + '"' +
            'title="Download results" class="download-link" target="_blank" download="gretel-results.txt">' +
            '<i class="fa fa-arrow-down"></i> Download results</a>';
        $(".notice p").html(notice).after('<small><a href="' + phpVars.fetchHomePath + '/documentation.php#faq-6" '+
        'style="float: right;" title="FAQ: what is inside the download file" target="_blank">What is inside this file?</a></small>');

        $(".messages").addClass("active");
        $(".notice").addClass("active");

        $(".notice").one("transitionend", function() {
            setDummyVariables();
        });
    }

    function messageNoResultsFound() {
        $(".controls").remove();
        $(".error p").text("No results found!");

        $(".messages").addClass("active");
        $(".error").addClass("active");

        $(".error").one("transitionend", function() {
            setDummyVariables();
        });
    }

    function messageOnError(error) {
        $(".controls").remove();
        $(".error p").text("Error! " + error);

        $(".messages").addClass("active");
        $(".error").addClass("active");

        $(".error").one("transitionend", function() {
            setDummyVariables();
        });
    }

    function showTvFsOnLoad() {
      var tvLink = $("a.tv-show-fs"),
        hash = window.location.hash;
      if (hash && !$(".loading-wrapper.fullscreen").hasClass("active") && !$(".tree-visualizer-fs").is(":visible")) {
          if (hash.indexOf("tv-") == 1) {
              var index = hash.match(/\d+$/);
              tvLink.eq(index[0] - 1).click();
          }
      }
    }

    function loopResults(sentences, returnAllResults) {
        if (returnAllResults) {
            done = true;
            resultID = 0;

            $(".results-wrapper tbody:not(.empty)").empty();
            $(".loading-wrapper.searching").removeClass("active");
        }
        $.each(sentences, function(id, value) {
            if (returnAllResults || (!returnAllResults && !done)) {
                resultID++;
                var link = value[0];
                link = link.replace(/\bhref=\"([^"]+)\"/, "href=\"#tv-" + resultID + "\" data-tv-url=\"$1\"");

                $(".results-wrapper tbody:not(.empty)").append('<tr data-result-id="' + resultID + '" data-component="'+value[2]+'">' +
                    '<td>' + resultID + '</td><td>' + link + '</td><td>' +
                    value[2] + '</td><td>' + value[1] + '</td></tr>');
            }
            showTvFsOnLoad();
        });

        $(".results-wrapper").fadeIn("fast");
        resultIDString = numericSeparator(resultID);
        $(".count strong").text(resultIDString);

        if (!returnAllResults) {
            getSentences();
        }
    }

    $(".controls [name='go-to']").keyup(function(e){
        var keycode = e.keyCode,
        $this = $(this);
        // Reset customValidity on backspace or delete keys, or up and down arrows
        // Additionally allow a user to move throguh rows by using up and down arrows in
        // the input field
        if (keycode == 8 || keycode == 46 || keycode == 38 || keycode == 40) {
            this.setCustomValidity("");
            // UP arrow
            if (keycode == 38 && $this.val() < resultID) $this.val(parseInt($this.val(), 10)+1).change();
            // DOWN arrow
            if (keycode == 40 && $this.val() > 1) $this.val(parseInt($this.val(), 10)-1).change();
        }
    })
    .change(function() {
        var val = $(this).val(),
            row = $(".results-wrapper tbody:not(.empty) tr[data-result-id='" + val + "']");

        if (!row.is(":visible")) {
            this.setCustomValidity("Please choose a row that is visible. Some rows are hidden depending on the filters you set.");
        } else {
            this.setCustomValidity("");
            var offset = row.offset(),
                hControls = $(".controls").outerHeight();

            $("html, body").stop().animate({
                scrollTop: offset.top - hControls
            });
        }
    });

    $(".controls form").submit(function(e) {e.preventDefault();});

    $(".controls [name='filter-components']").change(function() {
        $(this).parent().toggleClass("active");
    });

    $(document).click(function(e) {
        if ($(".controls [for='filter-components']").hasClass("active")) {
            var target = $(e.target);
            if (!target.closest(".filter-wrapper, .controls [for='filter-components']").length) {
                $(".controls [for='filter-components']").removeClass("active");
            }
        }
    });

    $(".filter-wrapper [type='checkbox']").change(function() {
        var $this = $(this),
            component = $this.val();

        $("#all-components").prop("indeterminate", false);

        if ($this.is("[name='component']")) {
            // Show/hide designated components in results
            if ($this.is(":checked")) {
                resultsWrapper.find("tbody:not(.empty) tr[data-component='" + component + "']").show();
            } else {
                resultsWrapper.find("tbody:not(.empty) tr[data-component='" + component + "']").hide();
            }

            // If none of the component checkboxes are checked
            if (!filterWrapper.find("[name='component']").is(":checked")) {
                resultsWrapper.find(".empty").css("display", "table-row-group");
                filterWrapper.find("#all-components").prop("checked", false).parent().removeClass("active");
                $("[for='go-to']").addClass("disabled").children("input").prop("disabled", true);
            } else {
                if (filterWrapper.find("[name='component']:not(:disabled)").length == filterWrapper.find("[name='component']:not(:disabled):checked").length) {
                    filterWrapper.find("#all-components").prop("checked", true).parent().addClass("active");
                }
                else {
                    filterWrapper.find("#all-components").prop("checked", false).prop("indeterminate", true).parent().removeClass("active");
                }
                resultsWrapper.find(".empty").hide();
                $("[for='go-to']").removeClass("disabled").children("input").prop("disabled", false);
            }
        }
        // One checkbox to rule them all
        else if ($this.is("#all-components")) {
            $this.parent().toggleClass("active");
            filterWrapper.find("[type='checkbox'][name='component']:not(:disabled)").prop("checked", $this.is(":checked")).change();
        }

        $("#go-to").val(resultsWrapper.find("tbody:not(.empty) tr:visible").first().attr("data-result-id") || "--");
    });

    function disableAndEnableInputs() {
        $("[for='go-to'], [for='filter-components'], .filter-wrapper label").removeClass("disabled").children("input").prop("disabled", false);

        // Disable the checkboxes which don't have any results
        filterWrapper.find("[type='checkbox'][name='component']").each(function() {
            var $this = $(this),
                component = $this.val();

            if (resultsWrapper.find("tbody:not(.empty) tr[data-component='" + component + "']").length == 0) {
                $this.prop("disabled", true);
                $this.prop("checked", false);
                $this.parent("label").addClass("disabled");
            }
        });
    }

    var controlsTop = controls[0].getBoundingClientRect().top + controls.scrollTop(),
        controlsHeight = controls[0].getBoundingClientRect().height;

    dummy.height(controlsHeight);

    $window.resize($.throttle(250, setDummyVariables));
    $window.scroll($.throttle(250, scrollMenu));

    function setDummyVariables() {
        if (!controls.hasClass("scroll")) {
            controlsTop = controls.offset().top;
            controlsHeight = controls.outerHeight();
            dummy.height(controlsHeight);
        } else {
            controlsTop = dummy.offset().top;
        }
    }

    function scrollMenu() {
        if ($window.scrollTop() >= controlsTop) {
            dummy.show();
            controls.addClass("scroll");
        } else {
            dummy.hide();
            controls.removeClass("scroll");
        }
    }

    /* Tree visualizer */
    resultsWrapper.find("tbody").on("click", "a.tv-show-fs", function(e) {
        var $this = $(this);
        $(".loading-wrapper.tv").addClass("active");
        $("body").treeVisualizer($this.data("tv-url"), {
            normalView: false,
            initFSOnClick: true,
            fsFontSize: 12
        });
        e.preventDefault();
    });

    if (hash) {
        if (hash.indexOf("tv-") == 1) {
          $(".messages").addClass("active");
          $(".notice").html("<p>It seems that you want to open a tree in a new window. "
           + "The tree first needs to be found again, but as soon as it is found it will pop up.</p>").addClass("active");
        }
    }
});
