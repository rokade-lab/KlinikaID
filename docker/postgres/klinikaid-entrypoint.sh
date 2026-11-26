#!/bin/sh
set -eu
dst=/run/klinikaid-secrets
if [ "$(id -u)" = "0" ]; then
  install -d -o postgres -g postgres -m 0700 "$dst"
  for name in db_owner_password db_app_password; do
    if [ -r "/run/secrets/$name" ]; then
      install -o postgres -g postgres -m 0400 "/run/secrets/$name" "$dst/$name"
    fi
  done
fi
exec docker-entrypoint.sh "$@"
