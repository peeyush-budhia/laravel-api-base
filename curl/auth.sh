#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

source "${SCRIPT_DIR}/variables.sh"


echo "=======================================">&2
echo "1. Login">&2
echo "=======================================">&2

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
echo
echo "=======================================">&2
echo "Token saved successfully.">&2
echo "=======================================">&2
echo
echo


echo "=======================================">&2
echo "2. Get Authenticated User">&2
echo "=======================================">&2

curl --silent \
    --request GET \
    --url "${BASE_URL}/auth/me" \
    --header "${JSON_HEADER}" \
    --header "Authorization: Bearer ${TOKEN}"

echo
echo


echo "=======================================">&2
echo "3. Logout">&2
echo "=======================================">&2

curl --silent \
    --request POST \
    --url "${BASE_URL}/auth/logout" \
    --header "${JSON_HEADER}" \
    --header "Authorization: Bearer ${TOKEN}"

echo
echo