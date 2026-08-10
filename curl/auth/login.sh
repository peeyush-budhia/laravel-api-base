#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

print_header "Login"

RESPONSE=$(curl --silent \
    --request POST \
    --url "${BASE_URL}/auth/login" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --data '{
        "login": "peeyush@example.com",
        "password": "password"
    }')

echo "${RESPONSE}"

TOKEN=$(echo "${RESPONSE}" | php -r '
$json = json_decode(stream_get_contents(STDIN), true);
echo $json["data"]["token"] ?? "";
')

if [[ -z "${TOKEN}" ]]; then
    echo "Login failed." >&2
    exit 1
fi

save_token "${TOKEN}"

echo
echo "Token saved." >&2
