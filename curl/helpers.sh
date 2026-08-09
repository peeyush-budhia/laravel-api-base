#!/usr/bin/env bash

save_token() {
    local token="$1"

    echo "${token}" > "${TOKEN_FILE}"
}

delete_token() {
    rm -f "${TOKEN_FILE}"
}

load_token() {
    if [[ -f "${TOKEN_FILE}" ]]; then
        cat "${TOKEN_FILE}"
    fi
}

print_header() {
    echo
    echo "=======================================" >&2
    echo "$1" >&2
    echo "=======================================" >&2
    sleep 1
}
