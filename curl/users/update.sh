#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

USER_ID="${1:-}"

if [[ -z "${USER_ID}" ]]; then
    echo "Usage: $0 <user-uuid>" >&2
    exit 1
fi

print_header "Update User"

curl --silent \
    --request PATCH \
    --url "${BASE_URL}/users/${USER_ID}" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --header "${AUTH_HEADER}" \
    --data '{
        "first_name": "John Updated",
        "last_name": "Doe Updated",
        "phone": "9876543211"
    }'

echo
