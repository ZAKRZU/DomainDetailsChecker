# Domain Checker

A small simple web application, that allows you to check DNS records (A, CAA, TXT, NS) for a domain, and www subdomain. SSL certificates, redirects to other domains, and https protocol and more.
## Requirements
PHP: version 8.0+  (recomended 8.3)
PHP Modules: curl, mbstring, mysqli or nd_mysqli, openssl
max_execution_time: 120 seconds should be enough, but we recommend 300 seconds

## How to install
1. Clone repo

    git clone https://github.com/ZAKRZU/DomainDetailsChecker.git

2. Run composer inside app folder

    composer install

3. Open app in your browser, which will generate configuration file src/configuration.php (optional)

The database is not needed for this appliaction to work.
