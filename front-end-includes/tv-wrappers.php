<?php if (!isset($onlyFullscreenTv) || !$onlyFullscreenTv): ?>
    <div id="tree-visualizer" class="tv">
        <div class="tv-error">
            <p></p>
        </div>
        <div class="tv-content-wrapper">
            <div class="tv-toolbar-wrapper">
                <div class="tv-sentence-wrapper">
                  <i class="fa fa-loaded fa-commenting" aria-hidden="true"></i>
                  <span></span>
                </div>
                <button class="tv-show-fs" title="Open the tree in full screen mode" type="button">
                    <i class="fa fa-loaded fa-fw fa-arrows-alt"></i>
                    <span class="sr-only">Open in fullscreen</span>
                </button>
            </div>
            <div class="tv-tree">
            </div>

            <aside class="tv-tooltip" style="display: none">
              <ul></ul>
              <button title="Close this tooltip" type="button"><i class="fa fa-loaded fa-fw fa-times"></i><span class="sr-only">Close</span></button>
            </aside>
        </div>
    </div>
<?php endif; ?>

<div id="tree-visualizer-fs" class="tv tv-fs">
    <div class="tv-error">
        <p></p>
    </div>
    <div class="tv-content-wrapper">
        <div class="tv-toolbar-wrapper">
            <div class="tv-sentence-wrapper">
              <i class="fa fa-loaded fa-commenting" aria-hidden="true"></i>
              <span></span>
            </div>
            <nav class="tv-navigation-wrapper">
              <button class="tv-prev-tree" type="button" title="Show previous tree">
                  <i class="fa fa-loaded fa-fw fa-chevron-left" aria-hidden="true"></i>
                  <span class="sr-only">Previous tree</span>
              </button>
              <button class="tv-next-tree" type="button" title="Show next tree">
                  <i class="fa fa-loaded fa-fw fa-chevron-right" aria-hidden="true"></i>
                  <span class="sr-only">Next tree</span>
              </button>
            </nav>
            <div class="tv-zoom-wrapper">
                <a href="#" target="_blank" title="Show XML">Show XML</a>
                <div class="tv-zoom-opts">
                    <button class="tv-zoom-out" type="button">
                        <i class="fa fa-loaded fa-fw fa-search-minus" aria-hidden="true"></i>
                        <span class="sr-only">Zoom out</span>
                    </button>
                    <button class="tv-zoom-default" type="button">Default</button>
                    <button class="tv-zoom-in" type="button">
                        <i class="fa fa-loaded fa-fw fa-search-plus" aria-hidden="true"></i>
                        <span class="sr-only">Zoom in</span>
                    </button>

                    <button title="Close fullscreen mode" type="button" class="tv-close-fs">
                        <i class="fa fa-loaded fa-fw fa-times"></i>
                        <span class="sr-only">Close</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="tv-tree">
        </div>
        <aside class="tv-tooltip" style="display: none">
          <ul></ul>
          <button title="Close this tooltip" type="button"><i class="fa fa-loaded fa-fw fa-times"></i><span class="sr-only">Close</span></button>
        </aside>
    </div>
    <div class="tv-loading-wrapper">
        <p>Loading tree...<br>Please wait</p>
    </div>
</div>
