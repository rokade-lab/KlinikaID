# syntax=docker/dockerfile:1
ARG NGINX_IMAGE=nginx:1.30.6-alpine@sha256:583b7f2fce1cdb8da5ad5c39ebd3c33b98b87d493190e548bdee1fa8c619c311
FROM ${NGINX_IMAGE}

RUN apk add --no-cache openssl \
 && rm -f /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/ /etc/nginx/conf.d/
COPY docker/nginx/snippets/ /etc/nginx/snippets/
COPY docker/nginx/entrypoint/ /docker-entrypoint.d/
COPY public/assets/ /srv/klinikaid/public/assets/
RUN chmod 0755 /docker-entrypoint.d/30-klinikaid-tls.sh /docker-entrypoint.d/40-klinikaid-https-port.sh \
 && find /srv/klinikaid -type d -exec chmod 0755 {} + \
 && find /srv/klinikaid -type f -exec chmod 0644 {} + \
 && rm -f /srv/klinikaid/public/assets/.gitkeep

EXPOSE 80 443
