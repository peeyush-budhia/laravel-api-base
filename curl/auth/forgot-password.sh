#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

print_header "Forgot Password"

curl --silent \
    --request POST \
    --url "${BASE_URL}/auth/forgot-password" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --data '{
        "email": "peeyush@example.com"
    }'

echo
