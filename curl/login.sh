#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

source "${SCRIPT_DIR}/variables.sh"


echo "======================================="
echo "1. Login"
echo "======================================="

RESPONSE=$(curl --silent \
    --request POST \
    --url "${BASE_URL}/auth/login" \
    --header "${JSON_HEADER}" \
    --header "${CONTENT_HEADER}" \
    --data '{
        "login": "peeyush@example.com",
        "password": "password"
    }'
)

echo "$RESPONSE"


TOKEN=$(echo "$RESPONSE" | php -r '
$json = json_decode(stream_get_contents(STDIN), true);
echo $json["data"]["token"] ?? "";
')

if [ -z "$TOKEN" ]; then
    echo "Login failed. Token not received."
    exit 1
fi


echo "$TOKEN" > "${SCRIPT_DIR}/.token"

echo
echo "======================================="
echo "Token saved successfully."
echo "======================================="
echo
