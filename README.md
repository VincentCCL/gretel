# gretel

----------
GrETEL 2.0
----------

--- INFO ---
v2.0.1 March 2016: minor bug fixes
v2.0 November 2014: delivered to the TST-Centrale

URL: http://gretel.ccl.kuleuven.be/gretel-2.0

--- INSTALLATION ---

1. Download GrETEL
2. Create directories log, tmp, and parsers (make sure tmp and log are writable)
3. Put Alpino parser in the parsers directory. Current version in GrETEL: Alpino-x86_64-linux-glibc2.5-20548-sicstus (http://www.let.rug.nl/vannoord/alp/Alpino/versions/binary)
4. Adapt config/config.example.php file and change name to config/config.php
   - Set paths to home directory and home URL
   - Set path to Alpino parser
   - Set BaseX variables
5. Adapt config/config.example.pm file and change name to config/config.pm (needed for SoNaR only)
   - Set paths to BaseX database locations (@basexpaths)
   - Set BaseX database servers (@dbname_server)

--- KNOWN BUGS ---

- Tree visualisation is no longer supported in Google Chrome
