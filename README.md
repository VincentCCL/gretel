# GrETEL 3

## Info

v3.0.2 July 2017: Show error message if the BaseX server is down  
v3.0. November 2016: GrETEL 3 initial release  

Available at http://gretel.ccl.kuleuven.be/gretel3

### Branches

master: official version of GrETEL 3, available at http://gretel.ccl.kuleuven.be/gretel3  
dev: development version  
gretel2.0: official version of GrETEL 2.0, available at http://gretel.ccl.kuleuven.be/gretel-2.0  

## Installation

### Prerequisites

Next to a standard LAMP server (with a PHP version > 5.4), GrETEL requires the following packages to be installed on your machine:

* BaseX (https://packages.debian.org/jessie/database/basex)
* Perl (https://packages.debian.org/jessie/perl/perl) with:
  * XML::Twig (https://packages.debian.org/jessie/libxml-twig-perl)
  * XML::XPath (https://packages.debian.org/jessie/libxml-xpath-perl)

### Next steps

1. Download (or clone) GrETEL from GitHub.
2. Download the Alpino dependency parser. Current binary used in the live version: `Alpino-x86_64-linux-glibc2.5-20548-sicstus` (available [here](http://www.let.rug.nl/vannoord/alp/Alpino/versions/binary).
3. Create BaseX databases containing the treebanks you want to make available.
4. Adapt `config.example.php` file and change name to `config.php`, and then:
  * Set the path to the Alpino dependency parser in the variable `$alpinoDirectory` (by default: directory `parsers`)
  * Set BaseX variables (machine names, port numbers, password and username)

## Notes for developers

### Front-end
* We used [SCSS](http://sass-lang.com/documentation/file.SCSS_FOR_SASS_USERS.html) as our stylesheet markdown of preference. All styles are available as `.scss` files (`styles/scss/`). For users who do not want to work with SCSS/SASS we also included the expanded CSS output of the SCSS files (`styles/css/`). In production, however, we use the minfied files (`styles/css/min/`) to decrease load times.
* We do not support Internet Explorer because we highly rely on the power of the flexbox specification. Internet Explorer does not provide (good) support for flex properties. Microsoft's newer, and better, browser Edge is supported.
* JavaScript files are also provided as expanded (`js/`) as well as minified files (`js/min/`). We use jQuery as the library of choice.
* Results are 'flushed' to the user by use of subsequent ajax requests. Therefore, JavaScript _has_ to be enabled by users.
* Opening XPath code directly in the [XPath Beautifier](http://bramvanroy.be/projects/xpath-beautifier/) will only work on our own URL. This is due to cross-origin restrictions. This is a deliberate security measure as to not flood that tool with possibly malicious requests.


### Back-end
* The results that are flushed to the user at a time as well as the maximum results that will be fetched is stored in variables in `config.php`. Change `$flushLimit` and `$resultsLimit` to the values that you want.
* Scripts are organised according to their function:
  * `basex-search-scripts/`: scripts that are required to do the actual searching for results. However, the `basex-client.php` is sometimes needed in other cases as well to open up a BaseX session.
  * `front-end-includes/`: files that are included or required by other scripts and that present content rather than execute functions are stored here (e.g. `head.php`, `footer.php`). Content that is shared between the example-based search and the XPath search can be found here as well §e.g. `results-shared-content.php`).
  * `preparatory-scripts/`: scripts that run functions on the input leading up to but not including the actual fetching of results. These scripts manipulate do things such as creating XPath, generating breadth-first patterns, parsing the input, and modifying input examples.
  * `functions.php`: contains general functions that are often required but that are not specific to any part of the process
  * `helpers.php`: basic helper functions that return booleans.

## Credits

* [Liesbeth Augustinus](http://www.ccl.kuleuven.be/~liesbeth/): concept and initial implementation;
* [Vincent Vandeghinste](http://www.ccl.kuleuven.be/~vincent/ccl): concept and initial implementation;
* [Bram Vanroy](http://bramvanroy.be/): GrETEL 3 improvements and design
* Colleagues at the Centre for Computational Linguistics at KU Leuven for their feedback, as well as [Martijn van der Klis](http://www.uu.nl/staff/MHvanderKlis) for beta testing.
