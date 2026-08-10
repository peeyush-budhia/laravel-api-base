#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

curl --silent \
    --request POST \
    --url "${BASE_URL}/auth/logout" \
    --header "${JSON_HEADER}" \
    --header "${AUTH_HEADER}"

delete_token

echo
echo "Logged out."
