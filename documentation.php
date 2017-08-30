<?php

$currentPage="docs";

require "config.php";
require ROOT_PATH."/helpers.php";
require ROOT_PATH."/functions.php";
require ROOT_PATH."/front-end-includes/head.php";
?>
</head>

        <?php require ROOT_PATH."/front-end-includes/header.php"; ?>
<p>This page gathers all sorts of documentation on GrETEL, such as tutorials, related tools, and frequently asked questions. If you have any more questions, you can always consult the GrETEL <a href="http://gretel.ccl.kuleuven.be/project" target="_blank">project website</a> or you can
                <a href="documentation.php#faq-4" title="FAQ how to contact us">contact us</a>.</p>
            <p>Please cite the following paper if you are using GrETEL for your research.</p>
            <cite>
              <a href="http://gretel.ccl.kuleuven.be/docs/Augustinus2012-ebq-LREC.pdf"
                title="Download Example-Based Treebank Querying" target="_blank">
                <span class="stack">
                  <i class="fa fa-file-text-o"></i>
                  <i class="fa fa-download"></i>
                </span>
                <span class="sr-only">Example-Based Treebank Querying</span>
              </a>
              <p>Liesbeth Augustinus, Vincent Vandeghinste, and Frank Van Eynde (2012). <a href="http://gretel.ccl.kuleuven.be/docs/Augustinus2012-ebq-LREC.pdf" title="Download Example-Based Treebank Querying" target="_blank"><strong>"Example-Based Treebank Querying"</strong></a>. In: <em>Proceedings of the 8th International Conference on Language Resources and Evaluation (LREC-2012)</em>. Istanbul, Turkey. pp. 3161-3167.</p>
            </cite>
            <div class="flex-content">
                <article>
                    <h2>Tutorials</h2>
                    <ul>
                    <li><a href="http://gretel.ccl.kuleuven.be/docs/GrETEL2-tutorial.pdf" title="Gretel slides" target="_blank">Slides</a></li>
                    <li><a href="http://gretel.ccl.kuleuven.be/docs/GrETEL2-tutorial-handson.pdf" title="Gretel exercises" target="_blank">Exercises</a></li>
                    <li><a href="http://nederbooms.ccl.kuleuven.be/documentation/EducationalModule.pdf" title="CLARIN Educational Module" target="_blank">CLARIN Educational Module</a></li>
                    <li><a href="http://www.meertens.knaw.nl/mimore/educational_module/case_study_mimore_gretel.html" title="Combined Case Study MIMORE - GrETEL" target="_blank">Combined Case Study MIMORE - GrETEL</a></li>
                    </ul>
                </article>
                <article>
                    <h2>Information</h2>
                    <ul>
                    <li><a href="http://gretel.ccl.kuleuven.be/project" target="_blank">Project website</a></li>
                    <li><a href="http://gretel.ccl.kuleuven.be/project/docs.php" target="_blank">Manuals and documentation</a></li>
                    <li><a href="http://gretel.ccl.kuleuven.be/project/publications.php" target="_blank">Publications and Talks</a></li>
                    </ul>
                </article>
                <article>
                    <h2>Related tools</h2>
                    <ul>
                    <li><a href="http://gretel.ccl.kuleuven.be/afribooms" title="GrETEL 4 Afrikaans" target="_blank">GrETEL 4 Afrikaans</a></li>
                    <li><a href="http://gretel.ccl.kuleuven.be/poly-gretel" title="Poly-GrETEL" target="_blank">Poly-GrETEL</a></li>
                    <li><a href="http://gretel.ccl.kuleuven.be/gretel-2.0" title="GrETEL 2.0" target="_blank">GrETEL 2.0</a></li>
                    </ul>
                </article>
            </div>
            <section id="faq">
                <h2>Frequently Asked Questions</h2>
                <dl>
                    <dt id="faq-1" data-collapse-btn="true">Why is the output limited to 500 sentences?</dt>
  <dd data-collapse-target="true"><p>GrETEL is free for students and academic research, but the corpora that are accessible via GrETEL are
                    <strong>not meant for distribution</strong>. In other words, we do not have the rights to give out the corpus
                    as a whole. If a user would search for a structure with only a <code>cat="top"</code> node, they could
  literally download the whole corpus - which is not the intention of this project. If you would like to obtain the raw corpus data (for academic or commercial use), you should contact the <a href="http://ivdnt.org" target="_blank" title="Go to the project page">INT</a></p></dd>

                    <dt id="faq-2" data-collapse-btn="true" data-collapse-btn="true">For whom is GrETEL intended?</dt>
                    <dd data-collapse-target="true"><p>GrETEL is designed as a corpus query tool which means that it is useful for anyone who is
                    interested in searching through the Lassy Small, CGN, or SoNaR treebanks. The tool is especially useful if you want to look for <em>specific linguistic patterns</em> in those corpora.</p></dd>

                    <dt id="faq-3" data-collapse-btn="true">Where can I find more information about the corpora available in GrETEL?</dt>
                    <dd data-collapse-target="true"><p>GrETEL currently provides access to three corpora: Lassy Small, CGN treebank, and SoNaR treebank. More information on these corpora is provided on
                      <a href="http://gretel.ccl.kuleuven.be/project/docs.php#treebanks" target="_blank" title="Go to the project page">GrETEL's project page</a>.</p>
                      <ul>
                        <li><a href="http://www.let.rug.nl/~vannoord/Lassy/" target="_blank" title="The project page of Lassy Small">Lassy Small</a>
                          was the first corpus to be supported in GrETEL. It is a one-million words treebank
                          that consists of written data. All of its annotations have been manually checked and verified.</li>
                        <li><a href="http://tst-centrale.org/images/stories/producten/documentatie/cgn_website/doc_English/topics/index.htm" target="_blank" title="The project page of Corpus Gesproken Nederlands">CGN treebank</a> is a treebank of one million words that consists of transcribed Dutch speech. All the provided annotations have been manually checked and verified. CGN stands for "Corpus Gesproken Nederlands" (Spoken Dutch Corpus). The CGN treebank is a syntactically enriched part of the 10-million word CGN corpus.</li>
                        <li><a href="http://lands.let.ru.nl/projects/SoNaR/" target="_blank" title="The project page of SoNaR">SoNaR treebank</a> is the parsed version of the 500-million word SoNaR-500 corpus. It is a corpus that consists of 25 components of written data. Because of its size, the syntactic annotations have not been manually verified.</li>
                      </ul>
                    </dd>

                    <dt id="faq-4" data-collapse-btn="true">How can I contact you?</dt>
                    <dd data-collapse-target="true"><p>This website and this tool were developed at the Centre for Computational Linguistics (CCL). If you have any suggestions,
                      questions, or general feedback you are welcome to give us a ring, or send us an email. You can find contact information on
                      <a href="http://www.arts.kuleuven.be/ling/ccl" title="Centre for Computational Linguistics homepage" target="_blank">CCL's website</a> or
                      in the footer of this website.</p></dd>

                     <dt id="faq-5" data-collapse-btn="true">Why does XPath generated for SoNaR only have one leading slash, when the code for LASSY and CGN has two?</dt>
                     <dd data-collapse-target="true"><p>It has to do with how XPath structures work on the one hand, and how we optimised the SoNaR database on the other. An XPath pattern that begins with a double slash makes sure that the pattern is searched for in all descendants of the current node (or implied root), whereas a single slash restricts the search to its direct children.
                         How that difference is relevant for SoNaR has been described in the paper cited below.</p>
                         <cite>
                           <a href="http://gretel.ccl.kuleuven.be/docs/Vandeghinste2014-GrETELSoNaR-LREC.pdf"
                           title="Download 'Making Large Treebanks Searchable. The SoNaR case.'" target="_blank">
                           <span class="stack">
                             <i class="fa fa-file-text-o"></i>
                             <i class="fa fa-download"></i>
                           </span>
                           <span class="sr-only">Download 'Making Large Treebanks Searchable. The SoNaR case.'</span>
                          </a>

                           <p>Vincent Vandeghinste and Liesbeth Augustinus. (2014). <a href="http://gretel.ccl.kuleuven.be/docs/Vandeghinste2014-GrETELSoNaR-LREC.pdf"
                           title="Download 'Making Large Treebanks Searchable. The SoNaR case.'" target="_blank"><strong>"Making Large Treebanks Searchable. The SoNaR case"</strong></a>.
                             In: Marc Kupietz, Hanno Biber, Harald Lüngen, Piotr Bański, Evelyn Breiteneder, Karlheinz Mörth, Andreas Witt &amp; Jani Takhsha (eds.),
                             <em>Proceedings of the LREC2014 2nd workshop on Challenges in the management of large corpora (CMLC-2)</em>. Reykjavik, Iceland. pp. 15-20.
                           </p>
                         </cite>
                     </dd>
                     <dt id="faq-6" data-collapse-btn="true">What is new in version 3?</dt>
                     <dd data-collapse-target="true"><p>In addition to an overall design update, major changes include a more intuitive query builder in the example-based search mode and a visualizer for syntax trees that is compatible with all modern browsers. Moreover, the results are presented as soon as they are found, so you can browse the matching sentences before the treebank search is completed.
                       Furthermore it is possible to query the 500-million word SoNaR treebank in a similar fashion as the two one-million word treebanks CGN and LASSY Small.</p></dd>
                </dl>
            </section>
        </main>
    </div>

    <?php
    require ROOT_PATH."/front-end-includes/footer.php";
    include ROOT_PATH."/front-end-includes/analytics-tracking.php";
    ?>
</body>
</html>
