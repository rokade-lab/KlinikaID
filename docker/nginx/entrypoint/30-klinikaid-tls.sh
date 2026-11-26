#!/bin/sh
set -eu
dir=/etc/nginx/tls
mkdir -p "$dir"
if [ ! -s "$dir/server.pem" ] || [ ! -s "$dir/server-key.pem" ]; then
  openssl req -x509 -newkey rsa:2048 -nodes -days 365 -subj /CN=localhost \
    -addext subjectAltName=DNS:localhost,IP:127.0.0.1 \
    -keyout "$dir/server-key.pem" -out "$dir/server.pem" >/dev/null 2>&1
  chmod 0600 "$dir/server-key.pem"
  chmod 0644 "$dir/server.pem"
  echo "klinikaid: sertifikat TLS lokal dibuat di data/tls"
fi
