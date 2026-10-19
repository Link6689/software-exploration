#!/usr/bin/env sh
# Builds the two zips to upload in WordPress (Extensions > Ajouter, Apparence > Thèmes > Ajouter).
# Usage: sh bin/build-zips.sh   → dist/se-reviews.zip, dist/software-exploration.zip
set -e
cd "$(dirname "$0")/.."
php bin/build-tokens.php
mkdir -p dist
rm -f dist/se-reviews.zip dist/software-exploration.zip
(cd wordpress/plugins && zip -qr ../../dist/se-reviews.zip se-reviews -x '*.DS_Store')
(cd wordpress/themes && zip -qr ../../dist/software-exploration.zip software-exploration -x '*.DS_Store')
ls -l dist
