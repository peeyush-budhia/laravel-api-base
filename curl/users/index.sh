#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

print_header "List users"

curl --silent \
    --request GET \
    --url "${BASE_URL}/users" \
    --header "${JSON_HEADER}" \
    --header "${AUTH_HEADER}"

echo
