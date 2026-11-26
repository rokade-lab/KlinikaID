#!/bin/sh
set -eu
port="${KLINIKAID_HTTPS_PORT:-443}"
case "$port" in
  ''|*[!0-9]*) echo "KLINIKAID_HTTPS_PORT tidak valid" >&2; exit 1 ;;
esac
if [ "$port" = "443" ]; then suffix=""; else suffix=":$port"; fi
printf 'set $klinikaid_https_suffix "%s";\n' "$suffix" > /etc/nginx/snippets/https-port.conf
