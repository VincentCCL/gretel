<?php
require '../config/config.php';
require "$root/helpers.php";

session_cache_limiter('private');
session_start();
header('Content-Type:text/html; charset=utf-8');

$currentPage = 'ebs';
$step = 1;

$id = session_id();
if (isset($_SESSION['example'])) {
    $input = $_SESSION['example'];
} else {
    $input = 'Dit is een zin.';
}

require "$root/functions.php";
require "$root/php/head.php";
?>
</head>

<?php require "$root/php/header.php"; ?>
    <form action="parse.php" method="post" enctype="multipart/form-data">
        <p>Enter a <strong>sentence</strong> containing the (syntactic) characteristics you are looking for:</p>
        <p>
          <input type="text" name="input" value="<?php echo $input; ?>" required>
          <button type="button" name="clear">Clear</button>
        </p>
        <p>Select the <strong>search mode</strong> you want to use. <em>Basic search</em> doesn't
            require any knowledge of the used formal query language, but it also has less
            search options. <em>Advanced search</em> on the other hand allows a more specific
            search and offers you the possibility to adapt the automatically generated XPath query.</p>
        <div class="label-wrapper"><label><input type="radio" name="search" value="basic" checked> Basic search</label></div>
        <div class="label-wrapper"><label><input type="radio" name="search" value="advanced"> Advanced search</label></div>
        <div class="continue-btn-wrapper"><button type="submit">Continue <i>&rarr;</i></button></div>
    </form>

    <?php
    require "$root/php/footer.php";
    include "$root/scripts/AnalyticsTracking.php";
    ?>
    <script src="<?php echo $home; ?>/js/TaalPortaal.js"></script>
    <script>
    $(document).ready(function() {
        $('[name="clear"]').click(function() {
            $(this).prev('[name="input"]').val("").focus();
        });
        taalPortaalFiller();
    });
    </script>
</body>
</html>
