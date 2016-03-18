/**
 * jQuery "plug-in" (not really) to convert an XML tree structure into a
 * plug-and-play HTML tree that allows user interaction.
 *
 * @version 0.1
 * @license MIT
 * @author Bram Vanroy
 */

(function($) {
    var fontSizes = [8, 10, 12, 14, 16, 20];
    var zoomCounter = Math.round(fontSizes.length/2),
        FS, SS,
        errorContainer,
        treeFS, treeSS, tooltipFS, tooltipSS,
        anyTree,
        anyTooltip,
        zoomOpts, normalView, fsView, advanced;

    $.treeVisualizer = function(xml, options) {
        var args = $.extend({}, $.treeVisualizer.defaults, options);

        if ((args.normalView || args.fsView) && $(args.container).length) {
            initVars(args);
            loadXML(xml);
            // Show fs-tree-visualizer tree
            $(".tv-show-fs").click(function(e) {
                anyTooltip.css("top", "-100%").children("ul").empty();
                if (typeof treeSS != "undefined") treeSS.find("a").removeClass("hovered");

                noMoreZooming();
                fontSizeTreeFS();

                FS.fadeIn(250);
                sizeTreeFS();
                e.preventDefault();
            });

            // Adjust scroll position
            anyTree.scroll(function() {
                tooltipPosition();
            });

            $(window).scroll(function() {
                tooltipPosition();
            });

            if (fsView) {
                // Zooming
                zoomOpts.find("button").click(function() {
                    var $this = $(this);

                    if ($this.is(".close")) {
                        FS.fadeOut(250, function() {
                            treeFS.find("a").removeClass("hovered");
                        });
                    } else {
                        if ($this.is(".zoom-in")) {
                            zoomCounter++;
                        } else if ($this.is(".zoom-out")) {
                            zoomCounter--;
                        } else if ($this.is(".zoom-default")) {
                            zoomCounter = Math.round(fontSizes.length/2);
                        }
                        noMoreZooming();
                        fontSizeTreeFS();
                        sizeTreeFS();
                        tooltipPosition();
                    }
                });

                // Make the fs-tree-visualizer tree responsive
                window.onresize = sizeTreeFS;
            }
            anyTree.on("click", "a", function(e) {
                var $this = $(this),
                    listItem = $this.parent("li"),
                    data = listItem.data(),
                    tree = $this.closest(".tree"),
                    treeLeafs = tree.find("a"),
                    tooltipList = tree.next(".tooltip").children("ul");

                tooltipList.empty();
                treeLeafs.removeClass("hovered");
                $this.addClass("hovered");
                var i;
                for (i in data) {
                    if (data.hasOwnProperty(i)) {
                        $("<li>", {
                            html: "<strong>" + i + "</strong>: " + data[i]
                        }).prependTo(tooltipList);
                    }
                }
                tooltipPosition();
                e.preventDefault();
            });

            anyTooltip.find("button").click(function() {
                var $this = $(this),
                    tooltip = $this.parent(".tooltip"),
                    tree = tooltip.prev(".tree"),
                    treeLeafs = tree.find("a");

                tooltip.fadeOut(250);
                treeLeafs.removeClass("hovered");
            });
        } else {
            console.error("Cannot initialize Tree Visualizer: either the container " +
                "does not exist, or you have set both normal and fullscreen view to " +
                "false, which does not make sense.");
        }
    }

    $.treeVisualizer.defaults = {
      normalView: true,
      fsView: true,
      advanced: false,
      fontSize: 12,
      fsBtn: "",
      container: "body"
    };

    function initVars(args) {
        $(args.container).append('<div id="tv-error" style="display: none"><p></p></div>');
        errorContainer = $("#tv-error");
        var trees = [],
            tooltips = [];

        if (args.normalView) {
            normalView = true;
            $(args.container).append('<div id="tree-visualizer" style="display: none"></div>');
            SS = $("#tree-visualizer");
            var SSHTML = '<div class="tree" style="font-size: ' + args.fontSize + 'px;"></div>' +
                '<aside class="tooltip" style="display: none"><ul></ul>' +
                '<button>&#10005;</button></aside>';
            if (args.fsView) {
                SSHTML += '<button class="tv-show-fs">Fullscreen</button>';
            }
            SS.append(SSHTML);

            treeSS = SS.find(".tree");
            tooltipSS = SS.find(".tooltip");

            trees.push("#tree-visualizer .tree");
            tooltips.push("#tree-visualizer .tooltip");
        }
        if (args.fsView) {
            fsView = true;
            $("body").append('<div id="fs-tree-visualizer" style="display: none"></div>');
            FS = $("#fs-tree-visualizer");
            var FSHTML = '<div class="tree size0"></div><aside class="tooltip" style="display: none"><ul></ul>' +
                '<button>&#10005;</button></aside><div class="zoom-opts"><button class="zoom-out">-</button>' +
                '<button class="zoom-default">Default</button><button class="zoom-in">+</button>' +
                '<button class="close">&#10005;</button></div>';
            FS.hide().append(FSHTML);
            treeFS = FS.find(".tree");
            tooltipFS = FS.find(".tooltip");
            zoomOpts = FS.find(".zoom-opts");

            trees.push("#fs-tree-visualizer .tree");
            tooltips.push("#fs-tree-visualizer .tooltip");
        }
        advanced = args.advanced;

        if (args.fsBtn != "") {
            $(args.fsBtn).addClass("tv-show-fs");
        }

        anyTree = $(trees.join());
        anyTooltip = $(tooltips.join());
    }

    function loadXML(src) {
        $.ajax({
                type: "GET",
                url: src,
                dataType: "xml"
            })
            .done(function(data) {
                if (data == null) {
                    errorHandle("Your XML appears to be empty or not in a compatible format.");
                } else {
                    parseXMLObj(data);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                errorHandle(textStatus + ": " + errorThrown);
            });
    }

    function parseXMLObj(xml) {
        var xmlObject = $(xml);

        // Remove error class and add success
        removeError();

        // See the buildOutputList function below
        anyTree.html(buildOutputList(xmlObject.find("node").first()));

        // Empty tooltips
        anyTooltip.children("ul").empty();

        // Do some small tree modifications
        treeModifier();
    }

    // Build the HTML list output
    function buildOutputList(nodes) {
        var newList = $("<ol/>");

        nodes.each(function(x, e) {
            var newItem = $('<li><a href="#"></a></li>');

            for (var i = 0, l = e.attributes.length, a = null; i < l; i++) {
                // Don't forget to add properties as data-attributes
                a = e.attributes[i];
                // Some data-attributes have initial spaces. Get rid of them
                newItem.attr("data-" + a.nodeName, a.value.replace(/^\s(.+)/g, "$1"));
            }
            if ($(this).children("node").length) {
                newItem.append(buildOutputList($(this).children("node")));
            }
            newList.append(newItem);
        });
        return newList;
    }

    // Small modifications to the tree
    function treeModifier() {
        anyTree.find("a").each(function() {
            var $this = $(this),
                li = $this.parent("li");

            if (li.data("rel")) {
                $this.append("<span>"+li.data("rel")+"<span>");
                if (li.data("index")) $this.append("<span>"+li.data("index")+"</span>");
            }
            if (li.data("pt"))  {
                if (li.data("index")) $this.children("span:last-child").append(":"+li.data("pt"));
                else $this.append("<span>" + li.data("pt") + "</span>");
            }
            else if (li.data("cat")) {
                if (li.data("index")) $this.children("span:last-child").append(":"+li.data("cat"));
                else $this.append("<span>" + li.data("cat") + "</span>");
            }

            if ($this.is(":only-child")) {
                if (li.data("lemma")) li.append("<span>" + li.data("lemma") + "</span>");
                if (li.data("word")) li.append("<span><em>" + li.data("word") + "</em></span>");
            }

            // addClass because after appending new children, it isn't necessarily the
            // only child any longer
            $this.addClass("only-child");
        });

        anyTree.find("li:only-child").addClass("only-child");
        anyTree.find("li:first-child").addClass("first-child");
        anyTree.find("li:last-child").addClass("last-child");

        if (normalView) {
            SS.show();
        }
    }

    function noMoreZooming() {
        if (zoomCounter >= fontSizes.length-1) {
            zoomOpts.find("button.zoom-in").prop("disabled", true);
        } else if (zoomCounter <= 0) {
            zoomOpts.find("button.zoom-out").prop("disabled", true);
        } else {
            zoomOpts.find("button").prop("disabled", false);
        }
    }

    function fontSizeTreeFS() {
        treeFS.css("fontSize",fontSizes[zoomCounter]+"px");
    }

    function tooltipPosition() {
        var tree;
        if (typeof treeFS != "undefined" && treeFS.is(":visible")) {
            tree = treeFS;
        } else if (typeof treeSS != "undefined" && treeSS.is(":visible")) {
            tree = treeSS;
        }
        var targetLink = tree.find("a.hovered");
        if (targetLink.length) {
            var tooltip = tree.next(".tooltip"),
                targetOffset = targetLink.offset(),
                treeOffset = tree.offset(),
                win = $(window),
                linkV = {
                    top: targetOffset.top,
                    right: win.outerWidth() - (targetOffset.left + targetLink.outerWidth()),
                    bottom: win.outerHeight() - (targetOffset.top + targetLink.outerHeight()),
                    left: targetOffset.left,
                    w: targetLink.outerWidth(),
                    h: targetLink.outerHeight()
                },
                treeV = {
                    top: treeOffset.top,
                    right: win.outerWidth() - (treeOffset.left + tree.outerWidth()),
                    bottom: win.outerHeight() - (treeOffset.top + tree.outerHeight()),
                    left: treeOffset.left,
                    w: tree.outerWidth(),
                    h: tree.outerHeight()
                };
            tooltip.css({
                "left": parseInt(linkV.left + (linkV.w / 2) - (tooltip.outerWidth() / 2) + 7.5, 10),
                "top": parseInt(linkV.top - tooltip.outerHeight() - 24, 10) - win.scrollTop()
            });
            console.log(win.scrollTop());

            if (((linkV.left + (linkV.w / 2)) < treeV.left) ||
                ((linkV.right + (linkV.w / 2)) < treeV.right) ||
                ((linkV.top + (linkV.h/2)) < treeV.top) ||
                ((linkV.bottom + linkV.h) < treeV.bottom)) {
                tooltip.fadeOut(400);
            }
            else {
                tooltip.show();
            }
        }
    }

    /* Set width of the fs-tree-visualizer elements
    Can't be done in CSS without losing other functionality
    */
    function sizeTreeFS() {
        var padR = parseInt(treeFS.css("paddingRight"), 10) || 0,
            padT = parseInt(treeFS.css("paddingTop"), 10) || 0,
            FSpadR = parseInt(FS.css("paddingRight"), 10) || 0,
            FSpadT = parseInt(FS.css("paddingTop"), 10) || 0,
            children = treeFS.children("ol"),
            w = $(window);

        treeFS.css({
            "width": children.outerWidth() + (padR * 2),
            "height": children.outerHeight() + (padT * 2),
            "max-width": w.width() - (FSpadR * 2),
            "max-height": w.height() - (FSpadT * 2)
        });
        // We need the current width before setting margins
        treeFS.css({
            "margin-left": (w.width() - (FSpadR * 2) - treeFS.outerWidth()) / 2,
            "margin-top": (w.height() - (FSpadT * 2) - treeFS.outerHeight()) / 2
        });
    }

    function errorHandle(message) {
        errorContainer.children("p").text(message).parent().fadeIn(250);
        if (normalView) {
            treeSS.scrollLeft(0);
            SS.find(".tv-show-fs").prop("disabled", true);
        }
    }

    function removeError() {
        errorContainer.hide();
        if (normalView) {
            SS.find(".tv-show-fs").prop("disabled", false);
        }
    }
}(jQuery));
