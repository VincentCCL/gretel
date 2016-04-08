<!DOCTYPE html>
<!-- index.php -->
<!-- GrETEL info -->

<!-- version 0.3 date: 17.11.2014  Documentation added -->
<!-- version 0.2 date: 14.10.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<title>GrETEL 2.0</title>
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oswald:400,300,700|Carrois+Gothic">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/4.0.0/normalize.min.css">

<link rel="stylesheet" href="style/css/gretel.css">
<link rel="stylesheet" href="style/css/tooltip.css">

<link rel="shortcut icon" type="image/png" href="img/gretel_logo_trans.png" />

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="js/tooltip.js"></script>
<script src="js/browserDetection.js"></script>
<?php include_once("scripts/AnalyticsTracking.php") ?>
</head>

<body>
<div id="container">
<div id="ccl-logo">
<a href="http://ccl.kuleuven.be" target="_blank"><img src="img/ccl-logo-square.png" height="60"></img></a>
</div>

<h1>GrETEL 2.0</h1>
<hr>

<h2>What is GrETEL?</h2>

<p>GrETEL stands for <b>Gr</b>eedy <b>E</b>xtraction of <b>T</b>rees for <b>E</b>mpirical <b>L</b>inguistics. It is a user-friendly search engine for the exploitation of treebanks. It comes in two formats:</br>

<h4><a href="ebs/input.php">Example-based search</a></h4>

<p>In this search mode you can use a natural language example as a starting point for searching a treebank<a href="#" class="clickMe" tooltip="A text corpus with syntactic annotations"> <sup>[?]</sup></a> with limited knowledge about tree representations and formal query languages.<a href="#" class="clickMe" tooltip="The formal (XPath) query is automatically generated"> <sup>[?]</sup></a></br>

<h4><a href="xps/input-xp.php">XPath search</a></h4>

<p>In this search mode you have to build the XPath query yourself. We strongly recommend to use the XPath search tool only when you are an experienced XPath user!</p>
<br/>

<p>Please <b>cite</b> the following paper if you are using GrETEL for your research:<br/><br/>
Liesbeth Augustinus, Vincent Vandeghinste, and Frank Van Eynde (2012). <a href="http://nederbooms.ccl.kuleuven.be/documentation/LREC2012-ebq.pdf" target="_blank">"Example-Based Treebank Querying"</a> In: Proceedings of the 8th International Conference on Language Resources and Evaluation (LREC-2012). Istanbul, Turkey. pp. 3161-3167</p>

<h2>Documentation</h2>
<h4>Tutorials</h4>
<ul>
<li><a href="http://gretel.ccl.kuleuven.be/docs/GrETEL2-tutorial.pdf" target="_blank">Slides</a></li>
<li><a href="http://gretel.ccl.kuleuven.be/docs/GrETEL2-tutorial-handson.pdf" target="_blank">Exercises</a></li>
<li><a href="http://nederbooms.ccl.kuleuven.be/documentation/EducationalModule.pdf" target="_blank">CLARIN Educational Module</a></li>
<li><a href="http://www.meertens.knaw.nl/mimore/educational_module/case_study_mimore_gretel.html" target="_blank">Combined Case Study MIMORE - GrETEL</a></li>
</ul>

<h4>Information</h4>
<ul>
<li><a href="http://gretel.ccl.kuleuven.be/project" target="_blank">Project website</a></li>
<li><a href="http://gretel.ccl.kuleuven.be/project/docs.php" target="_blank">Manuals and documentation</a></li>
<li><a href="http://gretel.ccl.kuleuven.be/project/publications.php" target="_blank">Publications and Talks</a></li>
</ul>

<h4>Related tools</h4>
<ul>
<li><a href="http://gretel.ccl.kuleuven.be/afribooms" target="_blank">GrETEL 4 Afrikaans</a></li>
<li><a href="http://gretel.ccl.kuleuven.be/poly-gretel" target="_blank">Poly-GrETEL</a></li>
</ul>

</div>
</body>
</html>
