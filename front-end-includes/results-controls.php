<nav class="controls">
    <span class="count"># of results: <strong>0</strong> / <span>--</span></span>

    <span class="status"><span>Searching</span><span>Counting</span></span>

    <div class="filter-wrapper">
        <label for="filter-components" class="disabled">
          <input type="checkbox" id="filter-components" name="filter-components" hidden disabled aria-describedby="filter-tooltip">Filter components <i class="fa fa-fw fa-angle-down" aria-hidden="true"></i>
        </label>
        <div class="filter-sel-wrapper">
            <label class="disabled active" for="all-components"><i class="fa fa-fw fa-check" aria-hidden="true"></i><input type="checkbox" id="all-components" name="all-components" checked disabled hidden>All</label>
            <?php foreach ($components as $comp) {
                echo '<label class="disabled"><input type="checkbox" name="component" value="'.strtoupper($comp).'" checked disabled aria-describedby="filter-tooltip"> '.strtoupper($comp).'</label>';
            } ?>
        </div>
        <div class="help-tooltip" id="filter-tooltip" data-title="Filter the current results so that only those from selected components are shown. Note that when you download the output, filters are not taken into account: all results displayed here are downloaded. The distribution of the results per component can be found in the download section below.">
          <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
          <span class="sr-only">Filter the current results so that only those from selected components are shown. Note that when you download the output, filters are not taken into account: all results displayed here are downloaded. The distribution of the results per component can be found in the download section below.</span>
        </div>
    </div>
    <div class="searching-animation">
        <div></div>
    </div>
</nav>
