#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"

curl --silent \
    --request GET \
    --url "${BASE_URL}/auth/me" \
    --header "${JSON_HEADER}" \
    --header "${AUTH_HEADER}"

echo
