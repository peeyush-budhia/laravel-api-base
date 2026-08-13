#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

EMAIL="peeyush@example.com"
TOKEN="${1:-}"
NEW_PASSWORD="${2:-}"

if [[ -z "${TOKEN}" || -z "${NEW_PASSWORD}" ]]; then
    echo "Usage: $0 <reset-token> <new-password>" >&2
    exit 1
fi

print_header "Reset Password"

curl --silent \
    --request POST \
    --url "${BASE_URL}/auth/reset-password" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --data "{
        \"token\": \"${TOKEN}\",
        \"email\": \"${EMAIL}\",
        \"password\": \"${NEW_PASSWORD}\",
        \"password_confirmation\": \"${NEW_PASSWORD}\"
    }"

echo
