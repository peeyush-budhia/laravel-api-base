#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

source "${SCRIPT_DIR}/variables.sh"

echo "======================================="
echo "GET /auth/me"
echo "======================================="

curl --request GET \
    --url "${BASE_URL}/auth/me" \
    --header "${JSON_HEADER}" \
    --header "${AUTH_HEADER}"

echo