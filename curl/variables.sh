#!/usr/bin/env bash

BASE_URL="http://api-base.test/api/v1"

TOKEN_FILE="$(dirname "${BASH_SOURCE[0]}")/.token"

if [ -f "$TOKEN_FILE" ]; then
    TOKEN=$(cat "$TOKEN_FILE")
else
    TOKEN=""
fi

AUTH_HEADER="Authorization: Bearer ${TOKEN}"

JSON_HEADER="Accept: application/json"

CONTENT_HEADER="Content-Type: application/json"