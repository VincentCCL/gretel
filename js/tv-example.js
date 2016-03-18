(function($) {
    // Only fullscreen. Links ending in .xml will automatically
    // open in a fullscreen representation
    $('a[href$="xml"]').click(function(e) {
        $("#tree-visualizer, #fs-tree-visualizer, #tv-error").remove();
        $.treeVisualizer($(this).attr("href"), {
            normalView: false
        });
        $("#fs-tree-visualizer").fadeIn("fast");
        e.preventDefault();
    });

    // When you want to display both fullscreen and
    // normal view. container is where you want the normal view to be
    $.treeVisualizer("xml/ambitie.xml", {
        container: "#output"
    });
})(jQuery);
