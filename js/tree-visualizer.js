/**
 * jQuery "plug-in" (not really) to convert an XML tree structure into a
 * plug-and-play HTML tree that allows user interaction.
 *
 * @version 0.1
 * @license MIT
 * @author Bram Vanroy
 */
var zoomCounter = 0,
    FS, SS,
    treeFS, treeSS, tooltipFS, tooltipSS,
    anyTree, anyTooltip, zoomOpts;

function buildTVFrame() {
    FS = $("#fs-tree-visualizer");
    SS = $("#tree-visualizer");
    var SSHTML = '<div class="tree base"></div><aside class="tooltip" style="display: none"><ul></ul><button>&#10005;</button></aside>' +
        '<button class="expand">Fullscreen</button><div class="error" style="display: none"><p></p></div>';
    SS.append(SSHTML);

    var FSHTML = '<div class="tree expanded size0"></div><aside class="tooltip" style="display: none"><ul></ul>' +
        '<button>&#10005;</button></aside><div class="zoom-opts"><button class="zoom-out">-</button>' +
        '<button class="zoom-default">Default</button><button class="zoom-in">+</button>' +
        '<button class="close">&#10005;</button></div>';
    FS.hide().append(FSHTML);

    treeFS = FS.find(".tree");
    treeSS = SS.find(".tree");
    tooltipFS = FS.find(".tooltip");
    tooltipSS = SS.find(".tooltip");
    anyTree = treeFS.add(treeSS);
    anyTooltip = tooltipFS.add(tooltipSS);
    zoomOpts = FS.find(".zoom-opts");
}

function treeVisualizer(src) {
    $.ajax({
            type: "GET",
            url: src,
            dataType: "xml"
        })
        .done(function(data) {
            if (data == null) {
                errorHandle("Your XML is empty. Please try again later.");
            } else {
                parseXMLObj(data);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            errorHandle(textStatus);
        });
}

function parseXMLObj(xml) {
    var xmlObject = $(xml);

    // Remove error class and add success
    removeError();

    // See the buildOutputList function below
    treeSS.html(buildOutputList(xmlObject.find("node").first()));

    // Empty tooltips
    anyTooltip.children("ul").empty();

    // Clone tree and ent it in the expanded view
    treeFS.empty();
    treeSS.children("ol").clone().appendTo(treeFS);

    // Do some small tree modifications
    treeModifier();
}

// Build the HTML list output
function buildOutputList(nodes) {
    var newList = $("<ol/>");

    nodes.each(function(x, e) {
        var newItem = $('<li><a href="#">&nbsp;</a></li>');

        for (var i = 0, l = e.attributes.length, a = null; i < l; i++) {
            // Don't forget to add properties as data-attributes
            a = e.attributes[i];
            // Some data-attributes have initial spaces. Get rid of them
            newItem.attr("data-" + a.nodeName, a.value.replace(/^\s(.+)/g, "$1"));
            if (a.nodeName == "cat" || a.nodeName == "word") {
                newItem.html('<a href="#">' + a.value + '</a>');
            }
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
    anyTree.find("a:only-child").each(function() {
        var $this = $(this),
        li = $this.parent("li");

        if (($this.next().length === 0) && $this.html() === "&nbsp;") {
            if (li.data("rel")) $this.html("<em>"+li.data("rel")+"</em>");
        }
        else {

            if (li.data("rel")) li.append("<span><em>"+li.data("rel")+"</em></span>");
        }
        if (li.data("pt")) li.append("<span>"+li.data("pt")+"</span>");
        if (li.data("lemma")) li.append("<span>"+li.data("lemma")+"</span>");

        // addClass because after appending new children, it isn't the
        // child any longer
        $this.addClass("only-child");
    });
}

function noMoreZooming() {
    if (zoomCounter > 2) {
        zoomOpts.find("button.zoom-in").prop("disabled", true);
    } else if (zoomCounter < -3) {
        zoomOpts.find("button.zoom-out").prop("disabled", true);
    } else {
        zoomOpts.find("button").prop("disabled", false);
    }
}

function treeInvestigator() {
    treeFS.attr("class", treeFS.attr("class").replace(/(size)-?\d/, "$1" + zoomCounter));
}

function tooltipPosition() {
    var tree = treeSS;
    if (treeFS.is(":visible")) {
        tree = treeFS;
    }
    var targetLink = tree.find("a.hovered");
    if (targetLink.length) {
        var tooltip = tree.next(".tooltip"),
            offsetTop = targetLink[0].getBoundingClientRect().top,
            offsetLeft = targetLink[0].getBoundingClientRect().left;
        tooltip.css({
            "left": parseInt(offsetLeft + (targetLink.outerWidth() / 2) - (tooltip.outerWidth() / 2) + 7.5, 10),
            "top": parseInt(offsetTop - tooltip.outerHeight() - 24, 10)
        });
    }
}

/* Set width of the fs-tree-visualizer elements
Can't be done in CSS without losing other functionality
*/
function setSizeTreeFS() {
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
    SS.find(".error > p").text(message).closest(".error").fadeIn(250);
    treeSS.scrollLeft(0);
    SS.find(".expand").prop("disabled", true);
}

function removeError() {
    SS.find(".error").hide();
    SS.find(".expand").prop("disabled", false);
}


$(document).ready(function() {
    buildTVFrame();
    // Show fs-tree-visualizer tree
    SS.find(".expand").click(function() {
        anyTooltip.css("top", "-100%").children("ul").empty();

        zoomCounter = 0;
        noMoreZooming();
        treeInvestigator();

        FS.fadeIn(250);
        setSizeTreeFS();
    });

    // Adjust scroll position
    anyTree.scroll(function() {
        if ($(this).next(".tooltip").is(":visible")) {
            tooltipPosition();
        }
    });

    $(window).scroll(function() {
        tooltipPosition();
    });

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
                zoomCounter = 0;
            }
            noMoreZooming();
            treeInvestigator();
            setSizeTreeFS();
            tooltipPosition();
        }
    });

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
        tree.next(".tooltip").show();
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
    // Make the fs-tree-visualizer tree responsive
    window.onresize = setSizeTreeFS;
});