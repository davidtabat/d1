#!/usr/bin/env bash

PHP_PATH=$1
PROJECT_ROOT=$2
IMPORT_START=$(date +%s)

${PHP_PATH} ${PROJECT_ROOT}/magmi_dru24/cli/magmi.cli.php -chain=Samsung:create,Kyocera:create,Epson:create,Lg:create
${PHP_PATH} ${PROJECT_ROOT}/shell/devAll_disableProducts.php --import_start ${IMPORT_START}
${PHP_PATH} ${PROJECT_ROOT}/shell/indexer.php -- reindexall
