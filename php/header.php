<body <?php setBodyClasses(); ?>>
    <div id="container">
        <header class="page-header">
            <h1>GrETEL 2.0</h1>

            <nav class="primary-navigation">
                <ul>
                    <li><a href="<?php echo $home; ?>/index.php" title="Home"
                    <?php if ($currentPage == "home") echo 'class="active"'; ?>>Home</a></li>
                    <li><a href="<?php echo $home; ?>/ebs/input.php" title="Example-based search"
                    <?php if ($currentPage == "ebs") echo 'class="active"'; ?>>Example-based search</a></li>
                    <li><a href="<?php echo $home; ?>/xps/input.php" title="XPath search"
                    <?php if ($currentPage == "xps") echo 'class="active"'; ?>>XPath search</a></li>
                    <li><a href="<?php echo $home; ?>/documentation.php" title="Documentation"
                    <?php if ($currentPage == "docs") echo 'class="active"'; ?>>Documentation</a></li>
                </ul>
            </nav>
        </header>
        <main>
          <header <?php if ($is_search) echo 'class="progress-header"'; ?>>
            <h1>
              <?php echo $pageTitle; ?>
              <?php if ($is_search && $step > 1) echo "<span>$sm search mode</span>"; ?>
            </h1>
            <?php if ($is_search) require "$root/php/secondary-navigation.php"; ?>
          </header>
          <?php if ($is_search) echo "<h2><span>Step $step:</span> " . $searchStepTitles[$step-1] . "</h2>"; ?>