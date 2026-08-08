#!/usr/bin/env bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

BASE_URL="http://api-base.test/api/v1"

TOKEN_FILE="${SCRIPT_DIR}/.token"

if [[ -f "${TOKEN_FILE}" ]]; then
    TOKEN="$(<"${TOKEN_FILE}")"
else
    TOKEN=""
fi

AUTH_HEADER="Authorization: Bearer ${TOKEN}"
JSON_HEADER="Accept: application/json"
CONTENT_HEADER="Content-Type: application/json"
