#!/bin/bash
set -e
vendor/bin/phpstan \
    -ctests/PhpStan/phpstan.neon \
    analyse src/ \
    --error-format=json \
    >"tests/Reports/phpstan.report.json"
