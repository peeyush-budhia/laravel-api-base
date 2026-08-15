#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

print_header "Create User"

curl --silent \
    --request POST \
    --url "${BASE_URL}/users" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --header "${AUTH_HEADER}" \
    --data '{
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "status": "active",
        "role": "user",
        "must_change_password": true
        "password": "password",
        "password_confirmation": "password"
    }'

echo
