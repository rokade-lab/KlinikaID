#!/bin/sh
set -eu

secret_dir=/run/klinikaid-secrets
[ -r "$secret_dir/db_owner_password" ] || secret_dir=/run/secrets
for name in db_owner_password db_app_password; do
  [ -s "$secret_dir/$name" ] || { echo "klinikaid init: secret $name tidak tersedia" >&2; exit 1; }
done

psql -v ON_ERROR_STOP=1 --no-psqlrc --quiet --username "$POSTGRES_USER" --dbname postgres <<SQL
SET log_min_error_statement = panic;
SET log_statement = 'none';
\set owner_pw \`cat $secret_dir/db_owner_password\`
\set app_pw \`cat $secret_dir/db_app_password\`
CREATE ROLE klinikaid_owner LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION PASSWORD :'owner_pw';
CREATE ROLE klinikaid_app LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION PASSWORD :'app_pw';
\unset owner_pw
\unset app_pw
ALTER DATABASE klinikaid OWNER TO klinikaid_owner;
REVOKE ALL ON DATABASE klinikaid FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE klinikaid TO klinikaid_owner;
GRANT CONNECT ON DATABASE klinikaid TO klinikaid_app;
SQL

psql -v ON_ERROR_STOP=1 --no-psqlrc --quiet --username "$POSTGRES_USER" --dbname klinikaid <<'SQL'
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE, CREATE ON SCHEMA public TO klinikaid_owner;
GRANT USAGE ON SCHEMA public TO klinikaid_app;
ALTER DEFAULT PRIVILEGES FOR ROLE klinikaid_owner GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO klinikaid_app;
ALTER DEFAULT PRIVILEGES FOR ROLE klinikaid_owner GRANT USAGE, SELECT ON SEQUENCES TO klinikaid_app;
ALTER DEFAULT PRIVILEGES FOR ROLE klinikaid_owner GRANT EXECUTE ON FUNCTIONS TO klinikaid_app;
SQL

echo "klinikaid init: role klinikaid_owner dan klinikaid_app dibuat"
