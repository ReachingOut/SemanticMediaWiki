#! /bin/bash

set -x

originalDirectory=pwd

cd ..

git clone https://gerrit.wikimedia.org/r/p/mediawiki/core.git phase3 --depth 1

cd phase3

mysql -e 'create database its_a_mw;'
php maintenance/install.php --dbtype $DBTYPE --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan TravisWiki admin

composer create-project mediawiki/semantic-mediawiki:dev-master -s dev
composer require satooshi/php-coveralls:dev-master

# Replace SemanticMediaWiki with the version that should be tested
rm -rf SemanticMediaWiki
mkdir SemanticMediaWiki
cp -r $originalDirectory/* SemanticMediaWiki

echo 'require_once( __DIR__ . "/vendor/mediawiki/semantic-mediawiki/SemanticMediaWiki.php" );' >> LocalSettings.php

echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
echo 'ini_set("display_errors", 1);' >> LocalSettings.php
echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

php maintenance/update.php --quick