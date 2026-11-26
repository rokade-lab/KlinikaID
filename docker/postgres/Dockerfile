# syntax=docker/dockerfile:1
ARG POSTGRES_IMAGE=postgres:18.6-alpine@sha256:123b17998ccaf47e53b502e8c6a1e79afd8f579c6f3eec83c563da026f957395
FROM ${POSTGRES_IMAGE}

COPY docker/postgres/klinikaid-entrypoint.sh /usr/local/bin/klinikaid-entrypoint.sh
COPY docker/postgres/initdb/10-klinikaid-roles.sh /docker-entrypoint-initdb.d/10-klinikaid-roles.sh
RUN chmod 0755 /usr/local/bin/klinikaid-entrypoint.sh \
 && chmod 0755 /docker-entrypoint-initdb.d/10-klinikaid-roles.sh

ENTRYPOINT ["klinikaid-entrypoint.sh"]
CMD ["postgres"]
