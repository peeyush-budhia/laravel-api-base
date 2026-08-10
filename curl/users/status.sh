#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

USER_ID="${1:-}"
STATUS="${2:-}"

if [[ -z "${USER_ID}" || -z "${STATUS}" ]]; then
    echo "Usage: $0 <user-uuid> <status>" >&2
    echo "Example: $0 <user-uuid> inactive" >&2
    exit 1
fi

print_header "Change User Status"

curl --silent \
    --request PATCH \
    --url "${BASE_URL}/users/${USER_ID}/status" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --header "${AUTH_HEADER}" \
    --data "{
        \"status\": \"${STATUS}\"
    }"

echo
