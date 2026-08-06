#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

source "${SCRIPT_DIR}/variables.sh"

echo "======================================="
echo "Not Found Test"
echo "======================================="

curl -i \
  -H "Accept: application/json" \
  "$BASE_URL/not-found"

echo