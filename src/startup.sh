#!/bin/bash
cd /opt/indieweb/src
composer update
cd /opt/indieweb/src/public
php -S 0.0.0.0:8000