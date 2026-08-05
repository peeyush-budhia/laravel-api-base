#!/usr/bin/env bash

set -euo pipefail

# Base URL
BASE_URL="http://api-base.test/api/v1"

# Sanctum Personal Access Token
TOKEN="1|HzkTJXgwBJoPdjFPL35PEN05LFkKXUajQ672LDBy1ae1f286"

# Common Headers
AUTH_HEADER="Authorization: Bearer ${TOKEN}"
JSON_HEADER="Accept: application/json"
CONTENT_HEADER="Content-Type: application/json"