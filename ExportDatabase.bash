#!/bin/bash

export_db() {
    if [[ -f mythicaldash.sql ]]; then
        rm mythicaldash.sql
    fi
    DB_NAME="mythicaldash"
    TEMP_DB_NAME="mythicaldash_temp"
    /usr/bin/mariadb -u root -e "DROP DATABASE IF EXISTS $TEMP_DB_NAME;"
    /usr/bin/mariadb -u root -e "CREATE DATABASE $TEMP_DB_NAME;"
    /usr/bin/mariadb-dump -u root --no-data "$DB_NAME" | /usr/bin/mariadb -u root "$TEMP_DB_NAME"
    /usr/bin/mariadb-dump -u root --no-data "$DB_NAME" --tables mythicaldash_logs mythicaldash_settings mythicaldash_users mythicaldash_apikeys mythicaldash_resetpasswords mythicaldash_tickets mythicaldash_tickets_messages | /usr/bin/mariadb -u root "$TEMP_DB_NAME"
    TABLES="$(/usr/bin/mariadb -u root -N -B -e "SHOW TABLES IN $TEMP_DB_NAME")"
    for TABLE in $TABLES; do
        /usr/bin/mariadb -u root -e "TRUNCATE TABLE $TEMP_DB_NAME.$TABLE;"
    done
    /usr/bin/mariadb-dump -u root "$TEMP_DB_NAME" | sed '/^--/d; /^\/\*![0-9]\{5\}.*\*\//d; /^SET/d' > mythicaldash.sql
    /usr/bin/mariadb -u root -e "DROP DATABASE $TEMP_DB_NAME;"
}

cd public
export_db
echo "DO NOT DELETE THIS FILE IF YOU DID NOT INSTALL mythicaldash via the WebInterface first" > FIRST_INSTALL
