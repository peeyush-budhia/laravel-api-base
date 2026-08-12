#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

FIRST_NAME="${1:-Peeyush}"
LAST_NAME="${2:-Budhia}"
EMAIL="${3:-peeyush@example.com}"

print_header 'Update Authenticated User Profile'
echo
echo "Name:  ${FIRST_NAME} ${LAST_NAME}"
echo "Email: ${EMAIL}"
echo

PAYLOAD="$(
    jq -n \
        --arg first_name "$FIRST_NAME" \
        --arg last_name "$LAST_NAME" \
        --arg email "$EMAIL" \
        '{
            first_name: $first_name,
            last_name: $last_name,
            email: $email
        }'
)"

curl \
    -X PUT \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $(cat "${ROOT_DIR}/.token")" \
    -d "$PAYLOAD" \
    "${BASE_URL}/profile"
