#!/usr/bin/env bash

PHP_PATH=$1
PROJECT_ROOT=$2

${PHP_PATH} ${PROJECT_ROOT}/magmi/cli/magmi.cli.php -chain=Samsung:xcreate,Kyocera:xcreate,Epson:xcreate,Lg:xcreate
${PHP_PATH} ${PROJECT_ROOT}/shell/indexer.php -- reindexall