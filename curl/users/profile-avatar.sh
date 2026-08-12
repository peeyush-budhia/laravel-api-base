#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

source "${ROOT_DIR}/variables.sh"
source "${ROOT_DIR}/helpers.sh"

IMAGE_FILE="${1:-}"

if [[ -z "$IMAGE_FILE" ]]; then
    echo "Usage:"
    echo "  $0 <image-file>"
    echo
    echo "Example:"
    echo "  $0 ~/Pictures/avatar.jpg"
    exit 1
fi

if [[ ! -f "$IMAGE_FILE" ]]; then
    echo "Error: Image file not found:"
    echo "  $IMAGE_FILE"
    exit 1
fi

print_header 'Update Authenticated User Avatar'

echo
echo "Image: $IMAGE_FILE"
echo

RESPONSE="$(
    curl \
        -sS \
        -w $'\n%{http_code}' \
        -X POST \
        -H "Accept: application/json" \
        -H "Authorization: Bearer $(cat "${ROOT_DIR}/.token")" \
        -F "avatar=@${IMAGE_FILE}" \
        "${BASE_URL}/profile/avatar"
)"

HTTP_STATUS="$(printf '%s\n' "$RESPONSE" | tail -n 1)"
BODY="$(printf '%s\n' "$RESPONSE" | sed '$d')"

echo "HTTP Status: ${HTTP_STATUS}"
echo
echo "Response:"
echo "---------------------------------------"

if command -v jq >/dev/null 2>&1 && printf '%s' "$BODY" | jq empty >/dev/null 2>&1; then
    printf '%s\n' "$BODY" | jq
else
    printf '%s\n' "$BODY"
fi

echo "---------------------------------------"

if [[ "$HTTP_STATUS" =~ ^2[0-9][0-9]$ ]]; then
    echo
    echo "✓ Avatar updated successfully."
else
    echo
    echo "✗ Avatar update failed."
    exit 1
fi
